<?php

namespace App\Services;

use App\Models\CreditCheck;
use App\Models\Application;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CreditCheckService
{
    protected $apiKey;
    protected $apiUrl;

    public function __construct()
    {
        $this->apiKey = config('services.credit_sense.api_key');
        $this->apiUrl = config('services.credit_sense.api_url');
    }

    /**
     * Perform a credit check
     */
    public function performCheck(CreditCheck $creditCheck): void
    {
        $application = $creditCheck->application;
        $personalDetails = $application->personalDetails;

        // Prepare request data
        $requestData = [
            'first_name' => explode(' ', $personalDetails->full_name)[0],
            'last_name' => substr($personalDetails->full_name, strpos($personalDetails->full_name, ' ') + 1),
            'date_of_birth' => $personalDetails->date_of_birth?->format('Y-m-d'),
            'email' => $personalDetails->email,
            'mobile' => $personalDetails->mobile_phone,
            'addresses' => $this->prepareAddresses($application),
            'employment' => $this->prepareEmployment($application),
        ];

        // Update credit check with request data
        $creditCheck->update([
            'request_data' => $requestData,
        ]);

        try {
            // Make API call to Credit Sense
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl . '/credit-check', $requestData);

            if ($response->successful()) {
                $responseData = $response->json();

                $creditCheck->markAsCompleted(
                    $responseData,
                    $responseData['credit_score'] ?? null
                );

                ActivityLog::logActivity(
                    'completed',
                    'Credit check completed successfully',
                    $creditCheck,
                    null,
                    ['score' => $responseData['credit_score'] ?? 'N/A']
                );

                Log::info("Credit check completed for application {$application->application_number}");

            } else {
                throw new \Exception('API returned error: ' . $response->body());
            }

        } catch (\Exception $e) {
            $creditCheck->markAsFailed($e->getMessage());

            ActivityLog::logActivity(
                'failed',
                'Credit check failed',
                $creditCheck,
                null,
                ['error' => $e->getMessage()]
            );

            Log::error("Credit check failed for application {$application->application_number}: {$e->getMessage()}");

            throw $e;
        }
    }

    /**
     * Prepare addresses for Credit Sense API
     */
    protected function prepareAddresses(Application $application): array
    {
        return $application->residentialAddresses->map(function ($address) {
            return [
                'street_address' => $address->street_address,
                'suburb' => $address->suburb,
                'state' => $address->state,
                'postcode' => $address->postcode,
                'type' => $address->address_type,
                'start_date' => $address->start_date->format('Y-m-d'),
                'end_date' => $address->end_date?->format('Y-m-d'),
            ];
        })->toArray();
    }

    /**
     * Prepare employment for Credit Sense API
     */
    protected function prepareEmployment(Application $application): array
    {
        return $application->employmentDetails->map(function ($employment) {
            return [
                'employer' => $employment->employer_business_name,
                'position' => $employment->position,
                'employment_type' => $employment->employment_type,
                'start_date' => $employment->employment_start_date?->format('Y-m-d'),
                'annual_income' => $employment->getAnnualIncome(),
            ];
        })->toArray();
    }

    /**
     * Get credit score interpretation
     */
    public function interpretScore(?int $score): array
    {
        if (!$score) {
            return [
                'rating' => 'Unknown',
                'description' => 'Credit score not available',
                'color' => 'gray',
            ];
        }

        if ($score >= 800) {
            return [
                'rating' => 'Excellent',
                'description' => 'Exceptional credit history',
                'color' => 'green',
            ];
        } elseif ($score >= 700) {
            return [
                'rating' => 'Very Good',
                'description' => 'Strong credit history',
                'color' => 'blue',
            ];
        } elseif ($score >= 600) {
            return [
                'rating' => 'Good',
                'description' => 'Satisfactory credit history',
                'color' => 'yellow',
            ];
        } elseif ($score >= 500) {
            return [
                'rating' => 'Fair',
                'description' => 'Below average credit history',
                'color' => 'orange',
            ];
        } else {
            return [
                'rating' => 'Poor',
                'description' => 'Needs improvement',
                'color' => 'red',
            ];
        }
    }
}
