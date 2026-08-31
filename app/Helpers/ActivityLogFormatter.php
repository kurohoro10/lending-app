<?php

namespace App\Helpers;

use App\Models\Application;
use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class ActivityLogFormatter
{
    private const DOC_TYPE_LABELS = [
        'export'      => 'Application Export PDF',
        'submission'  => 'Submission PDF',
        'loan_deed'   => 'Loan Deed PDF',
        'guarantor'   => 'Guarantor Form PDF',
        'declaration' => 'Business Declaration PDF',
        'signing'     => 'Signed Document PDF',
    ];

    /** Default page size for the paginated "Load More" feed. */
    public const PER_PAGE = 20;

    /**
     * Get the most recent activity logs for display, newest first.
     *
     * Queries the database directly — safe to call whether or not
     * `activityLogs` has been eager-loaded on $application, and doesn't
     * pull more rows than $limit regardless of how many the application
     * actually has. Each row carries the original fields (id, datetime,
     * iso, user, description, action — unchanged, still safe for any
     * existing consumer) plus presentation metadata resolved via
     * ActivityLogPresenter: category, icon, color, actor type, and — for
     * status_changed rows with old/new status present — a status_transition
     * pill pair. Presenter is a stateless lookup composed here exactly the
     * way DateFormatter already is below; Blade never calls it directly.
     */
    public static function forApplication(Application $application, int $limit = 10): Collection
    {
        return $application->activityLogs()
            ->with('user.roles')
            ->latest('id')
            ->limit($limit)
            ->get()
            ->map(fn ($log) => self::formatRow($log));
    }

    /**
     * Cursor-paginated activity log feed, for the "Load More" control.
     *
     * Fetches one row beyond $perPage so has_more can be determined without
     * a second COUNT query. $beforeId, when given, resumes strictly before
     * that row's id. `id` is used as the cursor rather than `created_at`
     * because it's monotonically increasing and matches insertion order
     * exactly for this table (nothing backdates or imports historical
     * rows), which avoids any timestamp tie-breaking a datetime cursor
     * could hit.
     *
     * $filters is purely additive — every existing caller passing 3 (or
     * fewer) arguments is unaffected. Supported keys, all optional:
     *   - search    (string)  free-text, see scopeSearch()
     *   - category  (string)  one of ActivityLogPresenter::categoryOptions()
     *   - actor     (string)  one of ActivityLogPresenter::actorOptions()
     *   - date_from (string, Y-m-d)
     *   - date_to   (string, Y-m-d)
     *
     * @return array{logs: Collection, next_cursor: ?int, has_more: bool}
     */
    public static function forApplicationPaginated(
        Application $application,
        int $perPage = self::PER_PAGE,
        ?int $beforeId = null,
        array $filters = []
    ): array {
        $query = $application->activityLogs()->with('user.roles');

        self::applyFilters($query, $filters);

        $rows = $query
            ->when($beforeId, fn ($q) => $q->where('id', '<', $beforeId))
            ->latest('id')
            ->limit($perPage + 1)
            ->get();

        $hasMore = $rows->count() > $perPage;
        $page    = $rows->take($perPage);

        return [
            'logs'        => $page->map(fn ($log) => self::formatRow($log))->values(),
            'next_cursor' => $hasMore ? $page->last()->id : null,
            'has_more'    => $hasMore,
        ];
    }

    /**
     * Apply search/category/actor/date-range filters to an activity_logs
     * query in place. All filtering happens in SQL — nothing here loads
     * rows into memory to test them.
     *
     * $query is the HasMany relation instance returned by
     * $application->activityLogs() (not yet a plain Builder — Eloquent
     * relations proxy the query builder's methods via __call rather than
     * extending Builder itself), hence the union type here and on the
     * scope*() methods below. The nested closures those methods pass to
     * where(Closure) genuinely do receive a plain Builder — Laravel's
     * forNestedWhere() always builds one via newQuery() — so only these
     * outer, directly-called methods need the wider type.
     */
    private static function applyFilters(Builder|HasMany $query, array $filters): void
    {
        if (! empty($filters['category'])) {
            self::scopeCategory($query, $filters['category']);
        }

        if (! empty($filters['actor'])) {
            self::scopeActor($query, $filters['actor']);
        }

        if (! empty($filters['search'])) {
            self::scopeSearch($query, $filters['search']);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }
    }

    /**
     * Constrain the query to rows belonging to $category, replicating
     * ActivityLogPresenter::category()'s own resolution precedence exactly
     * (model_type override wins over the action map) so the filter can
     * never disagree with what the row actually renders as.
     */
    private static function scopeCategory(Builder|HasMany $query, string $category): void
    {
        $actions          = ActivityLogPresenter::actionsForCategory($category);
        $modelTypes       = ActivityLogPresenter::modelTypesForCategory($category);
        $overriddenTypes  = ActivityLogPresenter::categorizedModelTypes();

        $query->where(function (Builder $q) use ($category, $actions, $modelTypes, $overriddenTypes) {
            if (! empty($modelTypes)) {
                $q->orWhereIn('model_type', $modelTypes);
            }

            if (! empty($actions)) {
                $q->orWhere(function (Builder $q2) use ($actions, $overriddenTypes) {
                    $q2->whereIn('action', $actions);

                    // A model_type override always wins over the action map
                    // (see ActivityLogPresenter::category()), so an action
                    // that matches this category by string must NOT also
                    // belong to a model_type that overrides to a different
                    // category — e.g. 'updated' matches Application's action
                    // map, but an 'updated' row on a Comment resolves to
                    // Comments instead, and must not be double-counted here.
                    if (! empty($overriddenTypes)) {
                        $q2->where(function (Builder $q3) use ($overriddenTypes) {
                            $q3->whereNull('model_type')->orWhereNotIn('model_type', $overriddenTypes);
                        });
                    }
                });
            }

            // The 'other' fallback category has no explicit action/model_type
            // entries of its own — it's whatever matches neither map.
            if ($category === ActivityLogPresenter::CATEGORY_OTHER) {
                $allActions = ActivityLogPresenter::categorizedActions();

                $q->orWhere(function (Builder $q2) use ($overriddenTypes, $allActions) {
                    $q2->where(fn (Builder $q3) => $q3->whereNull('model_type')->orWhereNotIn('model_type', $overriddenTypes))
                        ->where(fn (Builder $q3) => $q3->whereNull('action')->orWhereNotIn('action', $allActions));
                });
            }
        });
    }

    /**
     * Constrain the query to rows whose actor resolves to $actor, mirroring
     * ActivityLogPresenter::actor()'s own logic: null user_id splits into
     * system/webhook via the same WEBHOOK_ACTIONS list; a real user_id
     * filters by Spatie role, reusing the same `role()` query scope already
     * used elsewhere in this codebase (Admin\ApplicationController).
     */
    private static function scopeActor(Builder|HasMany $query, string $actor): void
    {
        $webhookActions = ActivityLogPresenter::webhookActions();

        match ($actor) {
            'system'  => $query->whereNull('user_id')->whereNotIn('action', $webhookActions),
            'webhook' => $query->whereNull('user_id')->whereIn('action', $webhookActions),
            'admin', 'assessor', 'client' => $query
                ->whereNotNull('user_id')
                ->whereHas('user', fn (Builder $q) => $q->role($actor)),
            default => null,
        };
    }

    /**
     * Lightweight, database-side free-text search. Safe at this table's
     * scale specifically because every call site already scopes by the
     * indexed application_id column first — this never runs against more
     * than one application's (small) row set. Searches the plain
     * description/action columns, several JSON paths inside old_values/
     * new_values that are known to carry searchable text (subject,
     * recipient/sender, delivery status, application status, document
     * label/type), the acting user's name, and — since "System"/"Webhook"
     * are computed labels that are never stored anywhere — matches those
     * two words directly against the same WEBHOOK_ACTIONS split used
     * everywhere else, so searching "webhook" finds webhook-originated rows.
     */
    private static function scopeSearch(Builder|HasMany $query, string $term): void
    {
        $like = '%' . $term . '%';

        $query->where(function (Builder $q) use ($term, $like) {
            $q->where('description', 'like', $like)
                ->orWhere('action', 'like', $like)
                ->orWhere('new_values->subject', 'like', $like)
                ->orWhere('new_values->to', 'like', $like)
                ->orWhere('new_values->from', 'like', $like)
                ->orWhere('new_values->status', 'like', $like)
                ->orWhere('new_values->doc_label', 'like', $like)
                ->orWhere('new_values->doc_type', 'like', $like)
                ->orWhere('old_values->old_status', 'like', $like)
                ->orWhere('new_values->new_status', 'like', $like)
                ->orWhereHas('user', function (Builder $q2) use ($like) {
                    // users.name is a PHP accessor (first/middle/last_name
                    // concatenated), not a real column. Matching each part
                    // individually would miss a full "First Last" search
                    // (e.g. "System Admin" isn't a substring of first_name
                    // "System" alone), so match against the same
                    // concatenation the accessor itself produces.
                    $q2->whereRaw(
                        "CONCAT_WS(' ', first_name, middle_name, last_name, name_extension) LIKE ?",
                        [$like]
                    );
                });

            $webhookActions = ActivityLogPresenter::webhookActions();
            $needle         = strtolower($term);

            if (str_contains('system', $needle)) {
                $q->orWhere(fn (Builder $q2) => $q2->whereNull('user_id')->whereNotIn('action', $webhookActions));
            }

            if (str_contains('webhook', $needle)) {
                $q->orWhere(fn (Builder $q2) => $q2->whereNull('user_id')->whereIn('action', $webhookActions));
            }
        });
    }

    /**
     * Return all document_generated activity log entries for an application,
     * newest first, formatted for the document-timeline partial.
     *
     * Queries directly (WHERE action = 'document_generated') rather than
     * filtering an already-loaded collection — Document History intentionally
     * shows the complete, unpaginated document trail, so this stays a single
     * full fetch, just pushed to SQL instead of PHP.
     */
    public static function forDocuments(Application $application): Collection
    {
        return $application->activityLogs()
            ->with('user')
            ->where('action', 'document_generated')
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($log) {
                $docType  = $log->new_values['doc_type'] ?? 'unknown';
                $docLabel = self::DOC_TYPE_LABELS[$docType]
                    ?? ($log->new_values['doc_label'] ?? 'Document');

                return [
                    'id'           => $log->id,
                    'datetime'     => DateFormatter::datetime($log->created_at),
                    'iso'          => DateFormatter::iso($log->created_at),
                    'user'         => $log->user->name ?? 'System',
                    'doc_type'     => $docType,
                    'doc_label'    => $docLabel,
                    'saved'        => $log->new_values['saved'] ?? null,
                    'storage_path' => $log->new_values['storage_path'] ?? null,
                ];
            });
    }

    /**
     * Shared row-shaping logic for forApplication() and
     * forApplicationPaginated() — kept in one place so the two entry
     * points can't silently drift apart from each other.
     */
    private static function formatRow(ActivityLog $log): array
    {
        return array_merge([
            'id'          => $log->id,
            'datetime'    => DateFormatter::datetime($log->created_at),
            'iso'         => DateFormatter::iso($log->created_at),
            'created_at'  => $log->created_at,
            'user'        => $log->user->name ?? 'System',
            'description' => $log->description,
            'action'      => $log->action ?? null,
            'old_values'  => $log->old_values,
            'new_values'  => $log->new_values,
        ], ActivityLogPresenter::resolve($log));
    }
}
