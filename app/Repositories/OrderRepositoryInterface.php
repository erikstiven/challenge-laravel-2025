<?php
namespace App\Repositories;

use App\Models\Order;
use Illuminate\Support\Collection;

interface OrderRepositoryInterface {
    public function getActive(): Collection;
    public function find(int $id): ?Order;
    public function create(array $data, array $items): Order;
    public function advance(Order $order): ?Order;
    public function delete(Order $order): void;
}
