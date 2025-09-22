<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\v1;

use App\Actions\PriceCalculatorAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\v1\PricingRequest;
use Illuminate\Support\Number;

final class PricingController extends Controller
{
    public function __invoke(PricingRequest $request, PriceCalculatorAction $priceCalculatorAction)
    {
        $totalPrice = $priceCalculatorAction->handle($request->from_date, $request->to_date);

        return response()->json([
            'success' => true,
            'total_price' => Number::currency($totalPrice, config('parking.currency')),
            'currency' => config('parking.currency'),
        ]);
    }
}
