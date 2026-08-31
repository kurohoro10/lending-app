<?php

namespace App\Actions\Application;

use App\Models\Application;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class GenerateGuarantorForm
{
    public function execute(Application $application): string
    {
        if ($application->status !== Application::STATUS_SETTLED) {
            throw new RuntimeException(
                "Guarantor form can only be generated for settled applications. Current status: {$application->status}"
            );
        }

        $application->loadMissing([
            'user',
            'personalDetails',
            'borrowerInformation',
            'borrowerDirectors',
            'residentialAddresses',
            'employmentDetails',
            'directorAssets',
            'directorLiabilities',
            'companyAssets',
            'companyLiabilities',
            'declarations',
        ]);

        $pdf = Pdf::loadView('admin.applications.pdf.guarantor-form', [
            'application' => $application,
            'generatedAt' => now(),
        ]);

        $pdf->setPaper('a4', 'portrait');

        $directory   = public_path('guarantor-forms');
        $filename    = "{$application->application_number}-guarantor.pdf";
        $storagePath = "guarantor-forms/{$filename}";

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $pdf->save(public_path($storagePath));

        $application->update([
            'guarantor_form_path'         => $storagePath,
            'guarantor_form_generated_at' => now(),
        ]);

        Log::info("Guarantor form generated for {$application->application_number}", [
            'application_id' => $application->id,
            'path'           => $storagePath,
        ]);

        return $storagePath;
    }
}