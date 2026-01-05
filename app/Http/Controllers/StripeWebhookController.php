<?php

namespace App\Http\Controllers;

use App\Services\StripeWebhookService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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
            abort(500, 'STRIPE_WEBHOOK_SECRET is not configured.');
        }

        $payload = (string) $request->getContent();
        $signatureHeader = (string) $request->header('Stripe-Signature');

        try {
            Webhook::constructEvent($payload, $signatureHeader, $webhookSecret);
        } catch (SignatureVerificationException) {
            return response('Invalid signature.', 400);
        } catch (\UnexpectedValueException) {
            return response('Invalid payload.', 400);
        }

        $event = json_decode($payload, true);
        if (! is_array($event)) {
            return response('Invalid payload.', 400);
        }

        $stripeWebhookService->handle($event);

        return response()->noContent();
    }
}
