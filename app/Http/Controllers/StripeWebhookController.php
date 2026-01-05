<?php

namespace App\Http\Controllers;

use App\Services\StripeWebhookService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, StripeWebhookService $stripeWebhookService): Response
    {
        $webhookSecret = config('services.stripe.webhook_secret');

        if (! is_string($webhookSecret) || $webhookSecret === '') {
            Log::error('Stripe webhook: STRIPE_WEBHOOK_SECRET is not configured.');
            abort(500, 'STRIPE_WEBHOOK_SECRET is not configured.');
        }

        $payload = (string) $request->getContent();
        $signatureHeader = (string) $request->header('Stripe-Signature');

        try {
            $event = Webhook::constructEvent($payload, $signatureHeader, $webhookSecret);
        } catch (SignatureVerificationException) {
            return response('Invalid signature.', 400);
        } catch (\UnexpectedValueException) {
            return response('Invalid payload.', 400);
        }

        /** @var array<string, mixed> $eventArray */
        $eventArray = $event->toArray();
        try {
            $stripeWebhookService->handle($eventArray);
        } catch (\Throwable $e) {
            Log::error('Stripe webhook: handler failed.', [
                'event_id' => $eventArray['id'] ?? null,
                'event_type' => $eventArray['type'] ?? null,
                'exception' => $e,
            ]);

            // Return 5xx so Stripe retries. Our handler is idempotent by event.id.
            return response('Webhook handling failed.', 500);
        }

        return response()->noContent();
    }
}
