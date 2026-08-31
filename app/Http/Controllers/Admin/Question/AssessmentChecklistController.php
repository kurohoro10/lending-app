<?php

namespace App\Http\Controllers\Admin\Question;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Application;
use App\Models\Communication;
use App\Models\Question;
use App\Notifications\Admin\CustomEmail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Bulk "Request Assessment Documents" action — the primary replacement for
 * asking checklist questions one-by-one. A single click/send:
 *  1. Idempotently creates any checklist Question rows missing for this
 *     application (identified by doc_category_hint against the canonical
 *     list in config('assessment_checklist.items') — never duplicates).
 *  2. Sends exactly one email (via the existing CustomEmail notification)
 *     with the admin's edited subject/body plus an "Open Client Portal"
 *     action button.
 *
 * Re-running this action is always safe: if nothing is missing, step 1 is a
 * no-op and step 2 simply resends the email as a reminder.
 */
class AssessmentChecklistController extends Controller
{
    /**
     * Idempotently create missing checklist items, then send the one
     * assessment-request email.
     */
    public function send(Request $request, Application $application): JsonResponse
    {
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $createdCount = $this->createMissingChecklistItems($application);

        try {
            $actionUrl = route('applications.show', $application) . '#client-questions-section';

            $application->user->notify(new CustomEmail(
                $application,
                $validated['subject'],
                $validated['message'],
                'Open Client Portal',
                $actionUrl
            ));

            $this->logCommunication($request, $application, $validated);

            ActivityLog::logActivity(
                'assessment_checklist_requested',
                $createdCount > 0
                    ? "Assessment documents requested ({$createdCount} new checklist item(s))"
                    : 'Assessment document request re-sent (no new checklist items)',
                $application,
                null,
                ['items_created' => $createdCount, 'subject' => $validated['subject']]
            );

            return response()->json([
                'success'       => true,
                'message'       => 'Assessment request sent to ' . $application->user->email,
                'items_created' => $createdCount,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send assessment checklist request', [
                'application_id' => $application->id,
                'error'          => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send the assessment request. Please check logs for details.',
            ], 500);
        }
    }

    /**
     * Render the default subject/body for the popup preview, and report
     * which items (if any) are missing so the admin isn't surprised by
     * what a click on "Send" is about to create.
     */
    public function preview(Application $application): JsonResponse
    {
        $existingSlugs = $this->existingChecklistSlugs($application);
        $missingSlugs  = collect(config('assessment_checklist.items'))
            ->pluck('slug')
            ->reject(fn ($slug) => $existingSlugs->contains($slug));

        return response()->json([
            'subject'        => $this->defaultSubject($application),
            'body'           => $this->defaultBody($application),
            'items_missing'  => $missingSlugs->count(),
            'items_existing' => $existingSlugs->count(),
        ]);
    }

    // =========================================================================
    // Private Helpers — Idempotent Creation
    // =========================================================================

    private function existingChecklistSlugs(Application $application): \Illuminate\Support\Collection
    {
        return $application->questions()
            ->whereNotNull('review_status')
            ->pluck('doc_category_hint');
    }

    private function createMissingChecklistItems(Application $application): int
    {
        $existingSlugs = $this->existingChecklistSlugs($application)->all();

        $missing = collect(config('assessment_checklist.items'))
            ->reject(fn (array $item) => in_array($item['slug'], $existingSlugs, true));

        foreach ($missing as $item) {
            $application->questions()->create([
                'asked_by'          => auth()->id(),
                'asked_at'          => now(),
                'question'          => $item['label'],
                'is_mandatory'      => $item['is_mandatory'],
                'doc_category_hint' => $item['slug'],
                'status'            => 'pending',
                'review_status'     => Question::REVIEW_PENDING,
            ]);
        }

        return $missing->count();
    }

    // =========================================================================
    // Private Helpers — Email Content
    // =========================================================================

    private function defaultSubject(Application $application): string
    {
        return 'Assessment Documents Required - Application ' . $application->application_number;
    }

    private function defaultBody(Application $application): string
    {
        $lines = [
            "Thank you for your application. As per our loan assessment, to complete the processing of your application, we will require you to send us the following documents, if you already provided some of below please just provide the rest of documents to us at your earliest convenience. Please also reply to our email at support@ahamoney.com.au:",
            "",
            "• Your 2 most payslips to verify the employment",
            "• Please confirm you have already declared all liabilities and no Financial Hardship at all or in near further.",
            "• Banklink details as below please click the link",
            "• Most Recent Car registration certificate with your full name and VIN number for us to do PPSR check or if you are purchasing vehicle please provide us the car contract or invoices from Registered Car dealer.",
            "• We do not do private transactions for car purchase.",
            "• Max lending is 70% of the car value ONLY, pls ensure you have sufficient fund for the admin cost and 30% downpayment.",
            "• Full comprehensive insurance certificate list AHA MONEY is the interested party. This document to be provided before settlement.",
            "• Most recent council rate notice to show your name (if apply).",
            "• Current lease agreement with your name on it / or anything approve your current living address.",
            "• 2 Relatives or closed friends contact number for reference check.",
            "• Photo of yourself holding your Driver's license plus your signature on a white paper.",
            "• 100 points of identification, we need to do Equifax credit check:",
            "    - Passport current or expired within the last two years, not cancelled (70 points)",
            "    - Birth certificate (70 points)",
            "    - Driver's license (40 points)",
            "    - Visa if non Australian citizen",
            "",
            "If you come across any issue, please call us 1300 680 477 and we will be more than happy to assist you. Once we have received all of the documents we will continue processing your application and will get back to you as soon as possible.",
            "",
            "Getting bank statements is time consuming. To speed up the application process, we can obtain your transactions history from the bank electronically via our trusted partner – Credit Sense.",
            "",
            "To do this, please click the button below to open your Client Portal and follow the prompts.",
            "",
            "Enter your internet banking credentials and complete the transaction.",
            "",
            "(For more information on Credit Sense. Refer below)",
            "",
            "Why do we use Credit Sense:",
            "",
            "• Fast application process",
            "• 100% Australian owned and operated company",
            "• Used by leading Australian and NZ lenders",
            "• Subject to and complies with all Australian and NZ laws",
            "",
            "If you have more questions please view Credit Sense FAQ:",
            "https://creditsense.com.au/consumers",
        ];

        return implode("\n", $lines);
    }

    // =========================================================================
    // Private Helpers — Logging
    // =========================================================================

    private function logCommunication(Request $request, Application $application, array $validated): void
    {
        Communication::create([
            'application_id' => $application->id,
            'user_id'        => auth()->id(),
            'type'           => 'email_out',
            'direction'      => 'outbound',
            'from_address'   => config('mail.from.address'),
            'to_address'     => $application->user->email,
            'subject'        => $validated['subject'],
            'body'           => $validated['message'],
            'status'         => 'sent',
            'sent_at'        => now(),
            'sender_ip'      => $request->ip(),
            'metadata'       => ['template_key' => 'assessment_checklist_request'],
        ]);
    }
}
