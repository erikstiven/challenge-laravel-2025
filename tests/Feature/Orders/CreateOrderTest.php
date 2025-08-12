<?php

use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    Cache::flush();
});

function sampleBody(): array
{
    return [
        'client_name' => 'Carlos Gómez',
        'items' => [
            ['description' => 'Lomo saltado', 'quantity' => 1, 'unit_price' => 60],
            ['description' => 'Inka Kola',    'quantity' => 2, 'unit_price' => 10],
        ],
    ];
}

it('crea una orden y devuelve 201 con la estructura esperada', function () {
    $res = $this->postJson('/api/orders', sampleBody())
        ->assertCreated()
        ->json();

    expect($res)
        ->toHaveKeys(['id','client_name','items','total','currency','status','created_at'])
        ->and($res['client_name'])->toBe('Carlos Gómez')
        ->and($res['total'])->toBe(80)
        ->and($res['status'])->toBe('initiated');
});

it('valida el payload y responde 422 si faltan campos', function () {
    $this->postJson('/api/orders', [
        'client_name' => '',
        'items' => [],
    ])->assertStatus(422);
});
