<?php

use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    Cache::flush();
});

function makeOrder($t): array
{
    return $t->postJson('/api/orders', [
        'client_name' => 'Tester',
        'items' => [
            ['description' => 'Foo', 'quantity' => 1, 'unit_price' => 5],
        ],
    ])->assertCreated()->json();
}

it('avanza de initiated → sent y luego delivered (elimina)', function () {
    $o = makeOrder($this);

    $this->postJson("/api/orders/{$o['id']}/advance")
        ->assertOk()
        ->assertJson(['status' => 'sent']);

    $this->getJson("/api/orders/{$o['id']}")->assertOk()
        ->assertJson(['status' => 'sent']);

    $this->postJson("/api/orders/{$o['id']}/advance")
        ->assertOk();

    $this->getJson("/api/orders/{$o['id']}")->assertStatus(404);
});

it('retorna 404 si el id no existe', function () {
    $this->postJson('/api/orders/00000000-0000-0000-0000-000000000000/advance')
        ->assertStatus(404);
});

it('maneja avances consecutivos rápidos sin corromper el estado', function () {
    $o = makeOrder($this);

    $r1 = $this->postJson("/api/orders/{$o['id']}/advance");
    $r2 = $this->postJson("/api/orders/{$o['id']}/advance");

    expect(in_array($r1->getStatusCode(), [200, 423]))->toBeTrue();
    expect(in_array($r2->getStatusCode(), [200, 423]))->toBeTrue();

    $this->getJson("/api/orders/{$o['id']}")->assertStatus(404);
});
