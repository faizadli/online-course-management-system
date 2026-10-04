<?php

namespace App\Services;

use Illuminate\Support\Str;

/**
 * Sandbox payment gateway used by the checkout flow.
 *
 * It mimics the behaviour of a real gateway (card validation, authorisation,
 * decline responses and a transaction reference) without moving any money, so
 * the full enrolment-and-payment workflow can be demonstrated and tested.
 *
 * Test cards:
 *   4242 4242 4242 4242  -> approved (Visa)
 *   5555 5555 5555 4444  -> approved (Mastercard)
 *   4000 0000 0000 0002  -> declined
 */
class PaymentGateway
{
    public const DECLINED_CARD = '4000000000000002';

    /**
     * @param  array{number?: string, name?: string, expiry?: string, cvc?: string}  $card
     * @return array{success: bool, reference: string, brand: ?string, last4: ?string, message: string}
     */
    public function charge(string $method, float $amount, array $card = []): array
    {
        $reference = 'PAY-'.strtoupper(Str::random(10));

        if ($amount <= 0) {
            return ['success' => true, 'reference' => $reference, 'brand' => null, 'last4' => null, 'message' => 'Free enrolment confirmed.'];
        }

        if ($method === 'paypal') {
            return ['success' => true, 'reference' => $reference, 'brand' => 'PayPal', 'last4' => null, 'message' => 'PayPal payment approved.'];
        }

        $number = preg_replace('/\D/', '', (string) ($card['number'] ?? ''));

        if ($number === self::DECLINED_CARD) {
            return [
                'success' => false,
                'reference' => $reference,
                'brand' => self::brand($number),
                'last4' => substr($number, -4),
                'message' => 'Your card was declined by the issuing bank. Please use a different card.',
            ];
        }

        return [
            'success' => true,
            'reference' => $reference,
            'brand' => self::brand($number),
            'last4' => substr($number, -4),
            'message' => 'Card payment approved.',
        ];
    }

    public static function brand(string $number): string
    {
        return match (true) {
            str_starts_with($number, '4') => 'Visa',
            (bool) preg_match('/^(5[1-5]|2[2-7])/', $number) => 'Mastercard',
            (bool) preg_match('/^3[47]/', $number) => 'Amex',
            default => 'Card',
        };
    }

    /** Luhn checksum validation. */
    public static function passesLuhn(string $number): bool
    {
        $number = preg_replace('/\D/', '', $number);

        if (strlen($number) < 13 || strlen($number) > 19) {
            return false;
        }

        $sum = 0;
        $alt = false;

        for ($i = strlen($number) - 1; $i >= 0; $i--) {
            $n = (int) $number[$i];
            if ($alt) {
                $n *= 2;
                if ($n > 9) {
                    $n -= 9;
                }
            }
            $sum += $n;
            $alt = ! $alt;
        }

        return $sum % 10 === 0;
    }
}
