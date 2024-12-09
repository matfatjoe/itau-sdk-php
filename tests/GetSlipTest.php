<?php

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Itau\Exceptions\BadRequestException;
use Itau\Exceptions\NotFoundException;
use Itau\Service\Factory;

it("deve retornar um boleto válido", function () {
    $settings = setSettings();
    $factory = new Factory($settings, 1);

    $response = file_get_contents('tests/AuxFiles/getSlipResponseSuccess.json');

    $queue = [
        new Response(
            200,
            [
                'Content-Type' => 'application/json',
                'x-itau-correlationID' => '3fcef2fc-faaa-4fef-948e-580388c5f18c',
                'x-itau-flowID' => '153fdaed-e2d6-425a-af58-41997197b25a'
            ],
            $response
        )
    ];
    $clientToken = mockClientItauTokenSuccess($queue);
    $factory->setClient($clientToken);
    $result = $factory->build(getBoletoParam(), 'GET');

    expect($result->value->data[0]->id_boleto)->toBe('05d6489c-0dce-4467-b8ea-0f6bfd3bf039');
    expect($result->value->data[0]->dado_boleto->dados_individuais_boleto[0]->numero_nosso_numero)->toBe('00000000');
    expect($result->value->data[0]->dado_boleto->dados_individuais_boleto[0]->texto_seu_numero)->toBe('123456aby');
    expect($result->value->data[0]->dado_boleto->dados_individuais_boleto[0]->valor_titulo)->toBe('2100.00');
});

it("deve levantar uma exceção NotFoundException", function () {
    $settings = setSettings();
    $factory = new Factory($settings, 1);

    $request = new Request(
        'GET',
        'https://api.itau.com.br/cash_management/v2/boletos',
        ['Content-Type' => 'application/x-www-form-urlencoded'],
        http_build_query([
            'grant_type' => 'client_credentials',
            'client_id' => 'client_id_invalidoçç',
            'client_secret' => 'client_secret',
        ])
    );
    $response = new Response(400, [], json_encode([
        "message" => "Parâmetro(s) obrigatório(s) no header não preenchido(s)"
    ]));

    $queue = [
        new ClientException(
            'Client error: 400',
            $request,
            $response
        )
    ];
    $clientToken = mockClientItauTokenSuccess($queue);
    $factory->setClient($clientToken);
    $factory->build(getBoletoParam(), 'GET');
})->throws(BadRequestException::class);
