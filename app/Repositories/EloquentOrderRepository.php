<?php

namespace App\Repositories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStateLog;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EloquentOrderRepository implements OrderRepositoryInterface
{
    public function getActive(): Collection
    {
        return Order::with('items')
            ->where('status', '!=', 'delivered')
            ->orderByDesc('id')
            ->get();
    }

    public function find(int $id): ?Order
    {
        return Order::with('items')->find($id);
    }

    public function create(array $data, array $items): Order
    {
        return DB::transaction(function () use ($data, $items) {
            $order = Order::create([
                'client_name' => $data['client_name'],
                'status'      => 'initiated',
                'total'       => 0,
            ]);

            $total = 0;
            foreach ($items as $it) {
                $qty  = (int) $it['quantity'];
                $unit = (float) $it['unit_price'];
                $sub  = $qty * $unit;
                $total += $sub;

                $order->items()->create([
                    'description' => $it['description'],
                    'quantity'    => $qty,
                    'unit_price'  => $unit,
                    'subtotal'    => $sub,
                ]);
            }

            $order->update(['total' => $total]);

            OrderStateLog::create([
                'order_id' => $order->id,
                'from'     => null,
                'to'       => 'initiated',
            ]);

            return $order->load('items');
        });
    }

    public function advance(Order $order): ?Order
    {
        $next = match ($order->status) {
            'initiated' => 'sent',
            'sent'      => 'delivered',
            default     => 'delivered',
        };

        if ($next === $order->status) {
            return null; // ya estaba en delivered
        }

        $from = $order->status;

        $order->update(['status' => $next]);

        OrderStateLog::create([
            'order_id' => $order->id,
            'from'     => $from,
            'to'       => $next,
        ]);

        if ($next === 'delivered') {
            $this->delete($order); // cascade borra items y logs
            return null;
        }

        return $order->fresh('items');
    }

    public function delete(Order $order): void
    {
        $order->delete();
    }
}
