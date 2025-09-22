<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\v1;

use App\Actions\CreateCarParkBookingAction;
use App\Actions\UpdateCarParkBookingAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\v1\CreateBookingRequest;
use App\Http\Requests\v1\UpdateBookingRequest;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

final class BookingController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $bookings = Auth::user()->isAdmin()
            ? Booking::query()->with('user')->paginate()
            : Booking::query()->with('user')->forUser(Auth::user()->id)->paginate();

        return BookingResource::collection($bookings);
    }

    public function show(int $id): JsonResponse
    {
        $booking = Auth::user()->isAdmin() ? Booking::query()->findOrFail($id) : Auth::user()->bookings()->findOrFail($id);
        $booking->load(['user']);

        return response()->json([
            'success' => true,
            'data' => BookingResource::make($booking),
        ], Response::HTTP_OK);
    }

    public function destroy(int $id): JsonResponse
    {
        $user = Auth::user();

        $booking = Auth::user()->isAdmin() ? Booking::query()->findOrFail($id) : Auth::user()->bookings()->findOrFail($id);

        $booking->delete();

        return response()->json([
            'success' => true,
            'message' => 'Booking has been deleted.',
        ], Response::HTTP_OK);
    }

    public function store(CreateBookingRequest $request, CreateCarParkBookingAction $action): JsonResponse
    {
        $result = $action->handle($request->validated());

        $result->load(['user']);

        return response()->json([
            'success' => true,
            'message' => 'Booking has been created.',
            'data' => BookingResource::make($result),
        ], Response::HTTP_CREATED);
    }

    public function update(UpdateBookingRequest $request, int $id, UpdateCarParkBookingAction $action): JsonResponse
    {
        $result = $action->handle($id, $request->validated());
        $result->load(['user']);

        return response()->json([
            'success' => true,
            'message' => 'Booking has been updated.',
            'data' => BookingResource::make($result),
        ], Response::HTTP_OK);
    }
}
