<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;


class OrderController extends Controller
{
    public function __construct(private OrderService $service) {}

    public function index(): JsonResponse
    {
        return response()->json($this->service->listActive());
    }

    public function store(StoreOrderRequest $request): JsonResponse
    {
        $order = $this->service->create(
            $request->only('client_name'),
            $request->input('items')
        );
        return response()->json($order, 201);
    }

    public function show(int $id): JsonResponse
    {
        try {
            return response()->json($this->service->show($id));
        } catch (\RuntimeException $e) {
            return response()->json(['message' => 'Not found'], 404);
        }
    }

    public function advance(int $id): JsonResponse
    {
        try {
            $result = $this->service->advance($id);
            if ($result === null) {
                return response()->json(['message' => 'Order delivered and deleted'], 200);
            }
            return response()->json($result);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => 'Not found'], 404);
        }
    }
}
