<?php

namespace PraiseDare\Monnify\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use PraiseDare\Monnify\Monnify;
use PraiseDare\Monnify\Exceptions\MonnifyException;
use PraiseDare\Monnify\Models\Transaction;
use PraiseDare\Monnify\Notifications\PaymentSuccessfulNotification;
use PraiseDare\Monnify\Notifications\PaymentFailedNotification;
use PraiseDare\Monnify\Enums\WebhookEvents;
use Illuminate\Routing\Controller;

class WebhookController extends Controller
{
    private Monnify $monnify;

    public function __construct()
    {
        $this->monnify = app(Monnify::class);
    }

    /**
     * Handle Monnify webhooks
     */
    public function __invoke(Request $request)
    {
        try {
            $webhookData = $request->getContent();
            $signature = $request->header('MNFY-SIGNATURE');

            Log::info('Monnify webhook received', [
                'signature' => $signature ? 'present' : 'missing',
                'body_length' => strlen($webhookData),
                'headers' => $request->headers->all(),
            ]);

            // Verify webhook signature
            if (!$this->monnify->webhook()->verify($webhookData, $signature)) {
                Log::error('Invalid Monnify webhook signature', [
                    'signature' => $signature,
                    'body' => $webhookData,
                ]);
                return response()->json(['error' => 'Invalid signature'], 400);
            }

            $payload = $this->monnify->webhook()->parse($webhookData);
            $eventType = $this->monnify->webhook()->getEventType($payload);

            Log::info('Processing Monnify webhook', [
                'event_type' => $eventType,
                'payload' => $payload,
            ]);

            // Process webhook based on event type
            $handler = match (true) {
                in_array(
                    $eventType,
                    array_column(WebhookEvents::cases(), 'value')
                ) => $this->monnify->webhook()->getWebhookEventHandler($eventType),

                default => function () use ($eventType, $payload) {
                    Log::info('Unknown Monnify webhook event type', [
                        'event_type' => $eventType,
                        'payload' => $payload,
                    ]);
                    return response()->json(['status' => 'ignored']);
                }
            };

            $handler($payload);

        } catch (MonnifyException $e) {
            Log::error('Monnify webhook error', [
                'error' => $e->getFormattedMessage(),
                'error_code' => $e->getErrorCode(),
                'error_type' => $e->getErrorType(),
                'body' => $request->getContent(),
            ]);
            return response()->json(['error' => 'Webhook processing failed'], 500);
        } catch (\Exception $e) {
            Log::error('Unexpected webhook error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'body' => $request->getContent(),
            ]);
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }
}
