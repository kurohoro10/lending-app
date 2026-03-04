<?php

namespace App\Http\Controllers\Admin\CreditControllers;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\ActivityLog;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CreditSenseController extends Controller
{
    private const PROVIDER_NAME = 'CreditSense';

    private function config(string $key): ?string
    {
        return Setting::where('key', "creditsense_{$key}")->value('value') ?: null;
    }

    private function baseUrl(): string   { return rtrim($this->config('base_url') ?? 'https://au-api.creditsense.com.au', '/'); }
    private function clientCode(): ?string { return $this->config('client_code'); }
    private function apiKey(): ?string     { return $this->config('api_key'); }

    // ── Fetch report (REST API fallback) ─────────────────────────────────

    /**
     * POST admin/applications/{application}/creditsense/fetch-report
     * Manually pull the CreditSense report when the webhook hasn't fired.
     */
    public function fetchReport(Application $application): JsonResponse
    {
        $this->authorize('update', $application);

        if (blank($this->clientCode()) || blank($this->apiKey())) {
            return response()->json(['error' => 'CreditSense API credentials are not configured.'], 500);
        }

        try {
            $response = Http::timeout(15)
                ->withBasicAuth($this->clientCode(), $this->apiKey())
                ->withHeaders(['Accept' => 'application/json'])
                ->get("{$this->baseUrl()}/v2/applications/{$application->application_number}");

            if ($response->notFound()) {
                return response()->json([
                    'error' => 'No CreditSense report found yet. The customer may not have completed the bank connection.',
                ], 404);
            }

            if ($response->failed()) {
                Log::error('[CreditSense] Fetch report failed', [
                    'application_id' => $application->id,
                    'status'         => $response->status(),
                    'body'           => $response->body(),
                ]);
                return response()->json(['error' => 'Failed to retrieve report from CreditSense.'], 502);
            }

            $application->update([
                'credit_sense_report'             => $response->json(),
                'credit_sense_report_received_at' => now(),
                'bank_api_provider_name'          => self::PROVIDER_NAME,
            ]);

            ActivityLog::logActivity('credit_sense_report_fetched', 'CreditSense report manually fetched via REST API', $application);

            return response()->json(['success' => true, 'message' => 'Report retrieved and saved successfully.']);

        } catch (\Illuminate\Http\Client\ConnectionException) {
            return response()->json(['error' => 'Could not reach CreditSense API.'], 503);
        } catch (\Throwable $e) {
            Log::error('[CreditSense] Fetch report exception: ' . $e->getMessage());
            return response()->json(['error' => 'An unexpected error occurred.'], 500);
        }
    }

    // ── Create quicklink ──────────────────────────────────────────────────

    /**
     * POST admin/applications/{application}/creditsense/quicklink
     * Generate a URL to send the customer so they can complete bank connection
     * on their own device via email or SMS.
     */
    public function createQuicklink(Application $application): JsonResponse
    {
        $this->authorize('update', $application);

        if (blank($this->clientCode()) || blank($this->apiKey())) {
            return response()->json(['error' => 'CreditSense API credentials are not configured.'], 500);
        }

        $details = $application->personalDetails;

        try {
            $response = Http::timeout(15)
                ->withBasicAuth($this->clientCode(), $this->apiKey())
                ->withHeaders(['Accept' => 'application/json'])
                ->post("{$this->baseUrl()}/v2/quicklinks", [
                    'appRef'    => $application->application_number,
                    'firstName' => $details?->first_name  ?? '',
                    'lastName'  => $details?->last_name   ?? '',
                    'email'     => $details?->email        ?? $application->user?->email ?? '',
                    'mobile'    => $details?->mobile_phone ?? null,
                ]);

            if ($response->failed()) {
                Log::error('[CreditSense] Quicklink creation failed', [
                    'application_id' => $application->id,
                    'status'         => $response->status(),
                    'body'           => $response->body(),
                ]);
                return response()->json(['error' => 'Failed to create CreditSense quicklink.'], 502);
            }

            ActivityLog::logActivity('credit_sense_quicklink_created', 'CreditSense quicklink created for customer delivery', $application);

            return response()->json([
                'success' => true,
                'url'     => $response->json('url') ?? $response->json('quicklink'),
            ]);

        } catch (\Throwable $e) {
            Log::error('[CreditSense] Quicklink exception: ' . $e->getMessage());
            return response()->json(['error' => 'An unexpected error occurred.'], 500);
        }
    }
}
