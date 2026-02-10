<?php

namespace App\Services;

use App\Models\Application;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfExportService
{
    /**
     * Export application to PDF
     */
    public function exportApplication(Application $application): \Barryvdh\DomPDF\PDF
    {
        $application->load([
            'user',
            'personalDetails',
            'residentialAddresses',
            'employmentDetails',
            'livingExpenses',
            'documents',
            'comments' => function ($query) {
                $query->where('type', '!=', 'internal')->orderBy('created_at');
            },
            'questions.askedBy',
            'declarations',
            'creditChecks',
            'activityLogs' => function ($query) {
                $query->orderBy('created_at')->limit(50);
            },
        ]);

        $data = [
            'application' => $application,
            'exportDate' => now(),
            'exportedBy' => auth()->user(),
        ];

        return PDF::loadView('admin.applications.pdf', $data);
    }

    /**
     * Export compliance report
     */
    public function exportComplianceReport(Application $application): \Barryvdh\DomPDF\PDF
    {
        $application->load([
            'personalDetails',
            'residentialAddresses',
            'employmentDetails',
            'livingExpenses.verifiedBy',
            'declarations',
            'activityLogs' => function ($query) {
                $query->where('action', '!=', 'viewed')->orderBy('created_at');
            },
            'comments' => function ($query) {
                $query->orderBy('created_at');
            },
        ]);

        $data = [
            'application' => $application,
            'exportDate' => now(),
            'exportedBy' => auth()->user(),
            'reportType' => 'Compliance Report',
        ];

        return PDF::loadView('admin.applications.compliance-pdf', $data);
    }

    /**
     * Export communications log
     */
    public function exportCommunications(Application $application): \Barryvdh\DomPDF\PDF
    {
        $communications = $application->communications()
            ->with('user')
            ->orderBy('created_at')
            ->get();

        $data = [
            'application' => $application,
            'communications' => $communications,
            'exportDate' => now(),
            'exportedBy' => auth()->user(),
        ];

        return PDF::loadView('admin.communications.pdf', $data);
    }

    /**
     * Export activity log
     */
    public function exportActivityLog(Application $application): \Barryvdh\DomPDF\PDF
    {
        $activityLogs = $application->activityLogs()
            ->with('user')
            ->orderBy('created_at')
            ->get();

        $data = [
            'application' => $application,
            'activityLogs' => $activityLogs,
            'exportDate' => now(),
            'exportedBy' => auth()->user(),
        ];

        return PDF::loadView('admin.activity-logs.pdf', $data);
    }

    /**
     * Download PDF
     */
    public function download(\Barryvdh\DomPDF\PDF $pdf, string $filename): \Illuminate\Http\Response
    {
        return $pdf->download($filename);
    }

    /**
     * Stream PDF to browser
     */
    public function stream(\Barryvdh\DomPDF\PDF $pdf): \Illuminate\Http\Response
    {
        return $pdf->stream();
    }
}
