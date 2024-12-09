<?php

use Itau\Exceptions\NotFoundException;
use Itau\Service\Factory;

it("deve gerar e retornar um token de acesso", function () {
    $settings = setSettings();
    $factory = new Factory($settings, 1);
    $clientToken = mockClientItauTokenSuccess();
    $factory->setClient($clientToken);
    $result = $factory->getApiToken();
    expect($result->access_token)->toBe('mock_access_token');
    expect($result->token_type)->toBe('Bearer');
    expect($result->expires_in)->toBe(300);
    expect($result->active)->toBeTrue();
    expect($result->scope)->toBe('cash_management_ext_v2-scope');
});

it("deve levantar uma exceção NotFoundException ", function () {
    $settings = setSettings();
    $factory = new Factory($settings, 1);
    $clientToken = mockClientItauClientException();
    $factory->setClient($clientToken);
    $factory->getApiToken();

})->throws(NotFoundException::class);
