<?php

namespace App\Http\Controllers\Api;

use App\Enums\LeadStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLeadRequest;
use App\Http\Resources\LeadResource;
use App\Jobs\SendNewLeadNotificationJob;
use App\Models\Lead;
use App\Traits\ResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class StoreLeadController extends Controller
{
    use ResponseTrait;

    /**
     * Handle the incoming request to store a new lead from a form submission.
     */
    public function __invoke(StoreLeadRequest $request): JsonResponse
    {
        try {
            $lead = DB::transaction(function () use ($request): Lead {
                $leadData = $request->validated();

                // Enforce default status for incoming external leads
                $leadData['status'] = LeadStatus::New;

                $lead = Lead::create($leadData);

                if ($lead->campaign_id !== null) {
                    $lead->load('campaign');
                }

                return $lead;
            });

            // Send new lead notification email
            SendNewLeadNotificationJob::dispatch($lead);

            return $this->success201(new LeadResource($lead), 'Lead registered successfully.');
        } catch (Throwable $e) {
            Log::error('Failed to store lead:', [
                'error' => $e->getMessage(),
                'exception' => $e,
                'request' => $request->except(['fbclid', 'gclid', 'ttclid']),
            ]);

            return $this->error500('Something went wrong. Try again later.', 'Something went wrong. Try again later.');
        }
    }
}
