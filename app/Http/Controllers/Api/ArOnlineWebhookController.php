<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ArOnlineNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ArOnlineWebhookController extends Controller
{
    /**
     * Handle incoming webhooks from AR Online.
     */
    public function handle(Request $request)
    {
        $payload = $request->all();
        
        Log::info('AR Online Webhook Received:', $payload);

        $notificationId = $payload['notificationID'] ?? null;
        $statusDescription = null;

        if ($notificationId) {
            if (isset($payload['metadata']['webhookVersion']) && $payload['metadata']['webhookVersion'] === 'v2') {
                $statusDescription = $payload['status'] ?? 'Unknown';
            } else {
                $statusDescription = $payload['description'] ?? 'Unknown';
            }

            ArOnlineNotification::updateOrCreate(
                ['notification_id' => $notificationId],
                [
                    'status' => $statusDescription,
                    'payload' => $payload,
                ]
            );
            
            Log::info("AR Online Notification updated: ID $notificationId to status $statusDescription");
        } else {
            Log::warning('AR Online Webhook: notificationID missing in payload.', $payload);
        }

        return response()->json(['message' => 'Webhook received successfully'], 200);
    }
}
