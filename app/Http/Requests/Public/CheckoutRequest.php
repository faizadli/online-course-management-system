<?php

namespace App\Http\Requests\Public;

use App\Services\PaymentGateway;
use App\Support\Basket;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $total = Basket::courses(Basket::CART)->sum('price');
        $needsCard = $total > 0 && $this->input('method', 'card') === 'card';

        return [
            'method' => [$total > 0 ? 'required' : 'nullable', 'in:card,paypal'],
            'card_number' => [$needsCard ? 'required' : 'nullable', 'string', 'max:30'],
            'card_name' => [$needsCard ? 'required' : 'nullable', 'string', 'max:100'],
            'card_expiry' => [$needsCard ? 'required' : 'nullable', 'string', 'max:7'],
            'card_cvc' => [$needsCard ? 'required' : 'nullable', 'digits_between:3,4'],
            'terms' => ['accepted'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $total = Basket::courses(Basket::CART)->sum('price');

            if ($total <= 0 || $this->input('method') !== 'card') {
                return;
            }

            if (! PaymentGateway::passesLuhn((string) $this->input('card_number'))) {
                $validator->errors()->add('card_number', 'Enter a valid card number.');
            }

            if (! preg_match('/^(0[1-9]|1[0-2])\s*\/\s*(\d{2})$/', trim((string) $this->input('card_expiry')), $m)) {
                $validator->errors()->add('card_expiry', 'Use the format MM / YY.');

                return;
            }

            $expiry = \Carbon\Carbon::createFromDate(2000 + (int) $m[2], (int) $m[1], 1)->endOfMonth();

            if ($expiry->isPast()) {
                $validator->errors()->add('card_expiry', 'This card has expired.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'method.required' => 'Please choose a payment method.',
            'card_number.required' => 'Card number is required.',
            'card_name.required' => 'Name on card is required.',
            'card_expiry.required' => 'Expiry date is required.',
            'card_cvc.required' => 'Security code is required.',
            'card_cvc.digits_between' => 'Security code must be 3 or 4 digits.',
            'terms.accepted' => 'You must accept the Terms of Service to continue.',
        ];
    }
}
