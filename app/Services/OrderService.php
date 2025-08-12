<?php

namespace App\Services;

use App\Models\Order;
use App\Repositories\OrderRepositoryInterface;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Collection;
use RuntimeException;

class OrderService
{
    public function __construct(
        private OrderRepositoryInterface $repo,
        private \Illuminate\Contracts\Cache\Repository $cache
    ) {}


    public function listActive(): Collection
    {
        return $this->cache->remember('orders.active', 30, fn() => $this->repo->getActive());
    }

    public function create(array $data, array $items): Order
    {
        $order = $this->repo->create($data, $items);
        $this->cache->forget('orders.active');
        return $order;
    }

    public function show(int $id): Order
    {
        $order = $this->repo->find($id);
        if (!$order) {
            throw new RuntimeException('Order not found');
        }
        return $order;
    }

    public function advance(int $id): ?Order
    {
        $order = $this->repo->find($id);
        if (!$order) {
            throw new RuntimeException('Order not found');
        }

        $result = $this->repo->advance($order);

        $this->cache->forget('orders.active');

        return $result;
    }
}
