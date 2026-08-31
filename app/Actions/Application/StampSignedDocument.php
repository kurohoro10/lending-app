<?php

namespace App\Actions\Application;

use App\Models\Application;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Fpdi;

/**
 * Stamps the client's signature onto every page of the originally-uploaded
 * document, without recreating the document itself. Uses FPDI to import
 * each page as a template (preserving fonts/text/layout) and draws the
 * signature image at a fixed, config-driven position anchored to each
 * page's own bottom-right corner.
 *
 * Reads only persisted data (document_signing_file_path / document_signing_data)
 * — never request input — and returns raw PDF bytes. Nothing is written to disk.
 */
class StampSignedDocument
{
    public function execute(Application $application): string
    {
        $sourcePath = Storage::disk('local')->path($application->document_signing_file_path);
        $signature  = $application->document_signing_data['signature'] ?? null;

        ['width' => $width, 'height' => $height, 'margin_right' => $marginRight, 'margin_bottom' => $marginBottom]
            = config('document-signing.signature');

        $pdf = new Fpdi();
        $pageCount = $pdf->setSourceFile($sourcePath);

        $signatureFile = null;
        if ($signature) {
            $signatureFile = tempnam(sys_get_temp_dir(), 'sig') . '.png';
            file_put_contents($signatureFile, base64_decode(preg_replace('#^data:image/\w+;base64,#', '', $signature)));
        }

        for ($page = 1; $page <= $pageCount; $page++) {
            $templateId = $pdf->importPage($page);
            $size = $pdf->getTemplateSize($templateId);

            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($templateId);

            if ($signatureFile) {
                // Anchored to this page's own bottom-right corner — adapts to any page size/orientation.
                $x = $size['width'] - $marginRight - $width;
                $y = $size['height'] - $marginBottom - $height;

                $pdf->Image($signatureFile, $x, $y, $width, $height);
            }
        }

        if ($signatureFile) {
            @unlink($signatureFile);
        }

        return $pdf->Output('S');
    }
}
