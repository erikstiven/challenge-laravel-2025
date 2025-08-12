<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    /** Claves de cache (Redis) */
    private const DATA_KEY  = 'orders.data.v1';   // diccionario id => order
    private const INDEX_KEY = 'orders.index.v1';  // listado cacheado 30s

    /** Helpers */
    private function load(): array
    {
        return Cache::get(self::DATA_KEY, []);
    }

    private function save(array $orders): void
    {
        Cache::forever(self::DATA_KEY, $orders);
        Cache::forget(self::INDEX_KEY); // invalida listado
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'client_name'         => ['required', 'string', 'max:120'],
            'items'               => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:180'],
            'items.*.quantity'    => ['required', 'integer', 'min:1'],
            'items.*.unit_price'  => ['required', 'numeric', 'min:0'],
        ]);

        $total = collect($data['items'])
            ->reduce(fn($sum, $i) => $sum + ($i['quantity'] * $i['unit_price']), 0);

        $order = [
            'id'          => (string) \Illuminate\Support\Str::uuid(),
            'client_name' => $data['client_name'],
            'items'       => $data['items'],
            'total'       => $total,      // <-- usa 'total' (o mantén 'subtotal' pero sé consistente)
            'currency'    => 'PEN',
            'status'      => 'initiated', // <-- requerido por el reto
            'created_at'  => now()->toIso8601String(),
        ];

        $orders = $this->load();
        $orders[$order['id']] = $order;
        $this->save($orders);

        return response()->json($order, 201, [
            'Content-Type' => 'application/json; charset=utf-8'
        ], JSON_UNESCAPED_UNICODE);
    }


    public function index()
    {
        // Redis TTL: 30s
        $data = Cache::remember(self::INDEX_KEY, 30, function () {
            return array_values($this->load());
        });

        return response()->json($data, 200, [
            'Content-Type' => 'application/json; charset=utf-8'
        ], JSON_UNESCAPED_UNICODE);
    }

    public function show(string $id)
    {
        $orders = $this->load();
        if (!isset($orders[$id])) {
            return response()->json(['message' => 'Not found'], 404, [
                'Content-Type' => 'application/json; charset=utf-8'
            ], JSON_UNESCAPED_UNICODE);
        }

        return response()->json($orders[$id], 200, [
            'Content-Type' => 'application/json; charset=utf-8'
        ], JSON_UNESCAPED_UNICODE);
    }

    public function advance(string $id)
    {
        $lockKey = "orders.lock.$id";
        return \Illuminate\Support\Facades\Cache::lock($lockKey, 5)->block(3, function () use ($id) {

            $orders = $this->load();
            if (!isset($orders[$id])) {
                return response()->json(['message' => 'Not found'], 404, [
                    'Content-Type' => 'application/json; charset=utf-8'
                ], JSON_UNESCAPED_UNICODE);
            }

            $order = $orders[$id];

            if ($order['status'] === 'initiated') {
                $order['status'] = 'sent';
                $orders[$id] = $order;
                $this->save($orders);
                return response()->json($order, 200, [  
                    'Content-Type' => 'application/json; charset=utf-8'
                ], JSON_UNESCAPED_UNICODE);
            }

            if ($order['status'] === 'sent') {
                unset($orders[$id]);       
                $this->save($orders);
               
                return response()->json(['message' => 'Order delivered and deleted'], 200, [
                    'Content-Type' => 'application/json; charset=utf-8'
                ], JSON_UNESCAPED_UNICODE);
            }

           
            return response()->json(['message' => 'Invalid state transition'], 409, [
                'Content-Type' => 'application/json; charset=utf-8'
            ], JSON_UNESCAPED_UNICODE);
        });
    }
}
