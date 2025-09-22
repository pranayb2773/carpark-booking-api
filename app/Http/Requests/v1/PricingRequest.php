<?php

declare(strict_types=1);

namespace App\Http\Requests\v1;

use Illuminate\Foundation\Http\FormRequest;

final class PricingRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'from_date' => 'required|date|after_or_equal:today',
            'to_date' => 'required|date|after:from_date',
        ];
    }
}
