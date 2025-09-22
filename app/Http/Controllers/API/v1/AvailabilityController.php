<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\v1;

use App\Actions\CheckAvailabilityAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\v1\AvailabilityRequest;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class AvailabilityController extends Controller
{
    public function __invoke(AvailabilityRequest $request, CheckAvailabilityAction $action): JsonResponse
    {
        $result = $action->handle($request->from_date, $request->to_date);

        return response()->json([
            'success' => true,
            'data' => $result,
        ], Response::HTTP_OK);
    }
}
