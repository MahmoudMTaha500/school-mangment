<?php

namespace App\Modules\Wallet\Interfaces\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Wallet\Application\ConfirmTopupIntent;
use App\Modules\Wallet\Application\FailTopupIntent;
use App\Modules\Wallet\Domain\Models\PaymentIntent;
use App\Modules\Wallet\Domain\Models\ProcessedWebhookEvent;
use App\Modules\Wallet\Infrastructure\Payments\StripeSignatureVerifier;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class StripeWebhookController extends Controller
{
    public function handle(Request $request, ConfirmTopupIntent $confirmTopupIntent, FailTopupIntent $failTopupIntent): JsonResponse
    {
        $payload = $request->getContent();
        $verifier = new StripeSignatureVerifier(
            (string) config('services.stripe.webhook_secret'),
            (int) config('services.stripe.webhook_tolerance', 300),
        );

        abort_unless($verifier->isValid($payload, $request->header('Stripe-Signature')), 400, 'Invalid webhook signature.');

        $event = json_decode($payload, true);
        abort_unless(is_array($event) && isset($event['id'], $event['type']), 400, 'Malformed webhook payload.');

        try {
            ProcessedWebhookEvent::query()->create([
                'provider' => 'stripe',
                'event_id' => $event['id'],
                'event_type' => $event['type'],
                'processed_at' => now(),
            ]);
        } catch (QueryException) {
            return response()->json(['status' => 'duplicate']);
        }

        $this->dispatch($event, $confirmTopupIntent, $failTopupIntent);

        return response()->json(['status' => 'ok']);
    }

    private function dispatch(array $event, ConfirmTopupIntent $confirmTopupIntent, FailTopupIntent $failTopupIntent): void
    {
        $session = $event['data']['object'] ?? [];
        $sessionId = $session['id'] ?? null;
        if (! is_string($sessionId)) {
            return;
        }

        $intent = PaymentIntent::query()->where('gateway', 'stripe')->where('gateway_payment_id', $sessionId)->first();
        if (! $intent) {
            return;
        }

        match ($event['type']) {
            'checkout.session.completed',
            'checkout.session.async_payment_succeeded' => $confirmTopupIntent->handle($intent),
            'checkout.session.async_payment_failed',
            'checkout.session.expired' => $this->failIfPending($intent, $failTopupIntent, $event['type']),
            default => null,
        };
    }

    private function failIfPending(PaymentIntent $intent, FailTopupIntent $failTopupIntent, string $eventType): void
    {
        if ($intent->status === 'pending') {
            $failTopupIntent->handle($intent, "Stripe reported {$eventType}.");
        }
    }
}
