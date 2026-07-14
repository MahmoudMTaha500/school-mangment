<?php

namespace App\Modules\Wallet\Infrastructure\Payments;

use App\Modules\Wallet\Application\Contracts\PaymentGateway;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\PendingRequest;
use RuntimeException;

final class StripePaymentGateway implements PaymentGateway
{
    /** @param array{secret:string,base_url:string,success_url:string,cancel_url:string} $config */
    public function __construct(private readonly HttpFactory $http, private readonly array $config) {}

    public function createCheckout(int $amount, string $currency, string $reference): array
    {
        $response = $this->client()
            ->withHeaders(['Idempotency-Key' => 'checkout:'.$reference])
            ->asForm()
            ->post($this->url('/v1/checkout/sessions'), [
                'mode' => 'payment',
                'client_reference_id' => $reference,
                'success_url' => $this->config['success_url'],
                'cancel_url' => $this->config['cancel_url'],
                'line_items[0][quantity]' => 1,
                'line_items[0][price_data][currency]' => strtolower($currency),
                'line_items[0][price_data][unit_amount]' => $amount,
                'line_items[0][price_data][product_data][name]' => 'Wallet top-up',
                'metadata[reference]' => $reference,
                'payment_intent_data[metadata][reference]' => $reference,
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('Stripe checkout session creation failed: '.$response->body());
        }

        $session = $response->json();

        return ['payment_id' => (string) $session['id'], 'checkout_url' => (string) $session['url']];
    }

    public function isPaid(string $paymentId): bool
    {
        $response = $this->client()->get($this->url("/v1/checkout/sessions/{$paymentId}"));

        if (! $response->successful()) {
            return false;
        }

        return $response->json('payment_status') === 'paid';
    }

    private function client(): PendingRequest
    {
        return $this->http->withToken($this->config['secret'])->acceptJson();
    }

    private function url(string $path): string
    {
        return rtrim($this->config['base_url'], '/').$path;
    }
}
