<?php
// app/Actions/Application/GenerateSubmissionPdf.php

namespace App\Actions\Application;

use App\Models\ActivityLog;
use App\Models\Application;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class GenerateSubmissionPdf
{
    // Storage disk and folder for submission PDFs.
    // Uses the 'local' disk (storage/app/) — not publicly accessible.
    // Admin downloads are streamed via SubmissionPdfController.
    public const DISK   = 'local';
    public const FOLDER = 'submissions';

    /**
     * Generate the client-facing submission confirmation PDF.
     *
     * Saves a copy to storage for admin access, logs the generation event,
     * then streams the file as a download to the client.
     *
     * @param  Application  $application  The submitted application.
     * @return Response                   A PDF download response.
     */
    public function handle(Application $application): Response
    {
        $application->loadMissing([
            'user',
            'personalDetails.user',
            'borrowerInformation',
            'borrowerDirectors',
            'residentialAddresses',
            'employmentDetails',
            'livingExpenses',
            'directorAssets',
            'directorLiabilities',
            'companyAssets',
            'companyLiabilities',
            'accountantDetail',
            'declarations',
        ]);

        // Grab the final submission declaration — this contains the signature
        $declaration = $application->declarations
            ->where('declaration_type', 'final_submission')
            ->where('is_agreed', true)
            ->first();

        $pdf = Pdf::loadView('applications.pdf.submission', [
            'application' => $application,
            'declaration' => $declaration,
            'generatedAt' => now(),
        ]);

        $pdf->setPaper('a4', 'portrait');

        $filename = 'loan-application-' . $application->application_number . '.pdf';
        $storagePath = self::FOLDER . '/' . $filename;

        // Save to storage/app/submissions/ — works on all environments.
        $saved = $this->saveToStorage($pdf->output(), $storagePath);

        ActivityLog::logActivity(
            'document_generated',
            'Submission PDF generated',
            $application,
            null,
            [
                'doc_type'     => 'submission',
                'doc_label'    => 'Submission PDF',
                'storage_path' => $saved ? $storagePath : null,
                'saved'        => $saved,
            ]
        );

        return $pdf->download($filename);
    }

    /**
     * Save raw PDF bytes to the storage disk.
     * Returns true on success, false on failure.
     */
    private function saveToStorage(string $pdfBytes, string $path): bool
    {
        try {
            Storage::disk(self::DISK)->put($path, $pdfBytes);
            return true;
        } catch (\Exception $e) {
            \Log::warning('Failed to save submission PDF to storage', [
                'path'  => $path,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}