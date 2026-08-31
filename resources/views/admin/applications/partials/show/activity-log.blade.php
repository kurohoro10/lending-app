{{-- resources/views/admin/applications/partials/show/activity-log.blade.php --}}
@php
    $page = \App\Helpers\ActivityLogFormatter::forApplicationPaginated($application);
@endphp

<div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
    <div class="p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4" id="activity-log-heading">
            Activity Log
        </h3>

        @if($page['logs']->isEmpty())
            <p class="text-sm text-gray-500 italic">No activity recorded yet.</p>
        @else
            <div
                id="activity-log-panel"
                data-url="{{ route('admin.applications.activityLog', $application) }}"
                data-cursor="{{ $page['next_cursor'] }}"
            >
                {{-- Compact filter toolbar — one row, wraps on narrow screens. --}}
                <div class="flex flex-wrap items-center gap-2 mb-3" role="search" aria-label="Filter activity log">
                    <input
                        type="search"
                        id="activity-log-search"
                        placeholder="Search activity…"
                        class="flex-1 min-w-[140px] text-xs rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    />

                    <select id="activity-log-category" class="text-xs rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All categories</option>
                        @foreach(\App\Helpers\ActivityLogPresenter::categoryOptions() as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>

                    <select id="activity-log-actor" class="text-xs rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All actors</option>
                        @foreach(\App\Helpers\ActivityLogPresenter::actorOptions() as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>

                    <div class="flex items-center gap-1">
                        <input type="date" id="activity-log-date-from" title="From date"
                               class="text-xs rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                        <span class="text-gray-300 text-xs">–</span>
                        <input type="date" id="activity-log-date-to" title="To date"
                               class="text-xs rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                    </div>

                    <button type="button" id="activity-log-clear-filters"
                            class="hidden text-xs font-medium text-gray-400 hover:text-gray-600 focus:outline-none focus:underline">
                        Clear
                    </button>
                </div>

                <div id="activity-log-list" aria-labelledby="activity-log-heading">
                    @include('admin.applications.partials.show.activity-log-rows', [
                        'logs' => $page['logs'],
                        'continueGroup' => null,
                    ])
                </div>

                <div id="activity-log-load-more-wrap" class="mt-3 text-center">
                    @if($page['has_more'])
                        <button
                            type="button"
                            id="activity-log-load-more"
                            class="text-xs font-medium text-indigo-600 hover:text-indigo-800 focus:outline-none focus:underline"
                        >
                            Load more
                        </button>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>

@once
    <script>
        (function () {
            function debounce(fn, wait) {
                let t;
                return function (...args) {
                    clearTimeout(t);
                    t = setTimeout(() => fn.apply(this, args), wait);
                };
            }

            function getPanel() {
                return document.getElementById('activity-log-panel');
            }

            function currentFilters() {
                const search   = document.getElementById('activity-log-search');
                const category = document.getElementById('activity-log-category');
                const actor    = document.getElementById('activity-log-actor');
                const dateFrom = document.getElementById('activity-log-date-from');
                const dateTo   = document.getElementById('activity-log-date-to');

                return {
                    search: search ? search.value.trim() : '',
                    category: category ? category.value : '',
                    actor: actor ? actor.value : '',
                    date_from: dateFrom ? dateFrom.value : '',
                    date_to: dateTo ? dateTo.value : '',
                };
            }

            function hasActiveFilters(filters) {
                return Object.values(filters).some(v => v !== '');
            }

            function loadMoreButtonHtml() {
                return '<button type="button" id="activity-log-load-more" '
                    + 'class="text-xs font-medium text-indigo-600 hover:text-indigo-800 focus:outline-none focus:underline">'
                    + 'Load more</button>';
            }

            function renderLoadMore(panel, hasMore, nextCursor) {
                const wrap = document.getElementById('activity-log-load-more-wrap');
                if (!wrap) return;

                panel.dataset.cursor = nextCursor || '';
                wrap.innerHTML = hasMore ? loadMoreButtonHtml() : '';
            }

            function fetchBatch({ before, continueGroup, replace }) {
                const panel = getPanel();
                if (!panel) return;

                const list = document.getElementById('activity-log-list');
                const clearBtn = document.getElementById('activity-log-clear-filters');
                const filters = currentFilters();

                const url = new URL(panel.dataset.url, window.location.origin);
                Object.entries(filters).forEach(([key, value]) => {
                    if (value) url.searchParams.set(key, value);
                });
                if (before) {
                    url.searchParams.set('before', before);
                }
                if (continueGroup) {
                    url.searchParams.set('continue_group', continueGroup);
                }

                if (clearBtn) {
                    clearBtn.classList.toggle('hidden', !hasActiveFilters(filters));
                }

                return fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' } })
                    .then(function (response) {
                        if (!response.ok) throw new Error('Request failed');
                        return response.json();
                    })
                    .then(function (data) {
                        if (replace) {
                            list.innerHTML = data.html;
                        } else {
                            list.insertAdjacentHTML('beforeend', data.html);
                        }
                        renderLoadMore(panel, data.has_more, data.next_cursor);
                    });
            }

            function applyFilters() {
                fetchBatch({ before: null, continueGroup: null, replace: true });
            }

            const debouncedApplyFilters = debounce(applyFilters, 300);

            // Delegated listeners so this keeps working regardless of how many
            // times the panel itself is re-rendered — nothing here needs
            // re-binding after a "Load More" or filter-triggered refresh.
            document.addEventListener('click', function (event) {
                if (event.target.closest('#activity-log-load-more')) {
                    const panel = getPanel();
                    const list = document.getElementById('activity-log-list');
                    if (!panel || !list) return;

                    const groups = list.querySelectorAll('.activity-log-day-group');
                    const lastLabel = groups.length ? groups[groups.length - 1].dataset.dayLabel : '';

                    const btn = event.target.closest('#activity-log-load-more');
                    btn.disabled = true;
                    const original = btn.textContent;
                    btn.textContent = 'Loading…';

                    fetchBatch({ before: panel.dataset.cursor, continueGroup: lastLabel, replace: false })
                        .catch(function () {
                            btn.disabled = false;
                            btn.textContent = original;
                        });
                    return;
                }

                if (event.target.closest('#activity-log-clear-filters')) {
                    ['activity-log-search', 'activity-log-category', 'activity-log-actor', 'activity-log-date-from', 'activity-log-date-to']
                        .forEach(function (id) {
                            const el = document.getElementById(id);
                            if (el) el.value = '';
                        });
                    applyFilters();
                }
            });

            document.addEventListener('input', function (event) {
                if (event.target.id === 'activity-log-search') {
                    debouncedApplyFilters();
                }
            });

            document.addEventListener('change', function (event) {
                if (['activity-log-category', 'activity-log-actor', 'activity-log-date-from', 'activity-log-date-to'].includes(event.target.id)) {
                    applyFilters();
                }
            });
        })();
    </script>
@endonce
