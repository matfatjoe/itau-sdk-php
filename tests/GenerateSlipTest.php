<?php

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Itau\Exceptions\BadRequestException;
use Itau\Exceptions\NotFoundException;
use Itau\Service\Factory;

it("deve gerar e retornar um token de acesso", function () {
    $settings = setSettings();
    $factory = new Factory($settings, 1);

    $response = file_get_contents('tests/AuxFiles/createSlipResponseSuccess.json');

    $queue = [
        new Response(
            200,
            [],
            $response
        )
    ];
    $clientToken = mockClientItauTokenSuccess($queue);
    $factory->setClient($clientToken);
    $result = $factory->build(getBoletoParam(), 'POST');

    expect($result->value->data->etapa_processo_boleto)->toBe('efetivacao');
    expect($result->value->data->dado_boleto->dados_individuais_boleto[0]->numero_nosso_numero)->toBe('2000000');
    expect($result->value->data->dado_boleto->dados_individuais_boleto[0]->texto_seu_numero)->toBe('2');
    expect($result->value->data->dado_boleto->dados_individuais_boleto[0]->valor_titulo)->toBe('00000000000119900');
});

it("ao gerar uma boleto deve levantar uma exceção BadRequest", function () {
    $settings = setSettings();
    $factory = new Factory($settings, 1);

    $request = new Request(
        'POST',
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
    $factory->build(getBoletoParam(), 'POST');
})->throws(BadRequestException::class);
