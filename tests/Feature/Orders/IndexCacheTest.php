<?php

use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    Cache::flush();
});

function createOrder($t): array
{
    return $t->postJson('/api/orders', [
        'client_name' => 'Cliente',
        'items' => [
            ['description' => 'Item', 'quantity' => 1, 'unit_price' => 10],
        ],
    ])->assertCreated()->json();
}

it('lista órdenes, cachea 30s y se invalida al crear/avanzar', function () {
    $o1 = createOrder($this);

    $list1 = $this->getJson('/api/orders')->assertOk()->json();
    expect($list1)->toBeArray()->and(count($list1))->toBe(1);

    $o2 = createOrder($this);

    $list2 = $this->getJson('/api/orders')->assertOk()->json();
    expect(count($list2))->toBe(2);

    $this->postJson("/api/orders/{$o2['id']}/advance")->assertOk();
    $this->postJson("/api/orders/{$o2['id']}/advance")->assertOk();

    $list3 = $this->getJson('/api/orders')->assertOk()->json();
    expect(count($list3))->toBe(1);
});
