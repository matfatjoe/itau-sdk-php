<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "uses()" function to bind a different classes or traits.
|
*/

// uses(Tests\TestCase::class)->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

// expect()->extend('toBeOne', function () {
//     return $this->toBe(1);
// });

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

// function something()
// {
//     // ..
// }

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Itau\Models\Settings;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

function getBoletoParam()
{
    return [
        'process_step' => 'simulacao',
        'recipient' => $_ENV['BENEFICIARIO'],
        'data' => [
            'type' => 'a vista',
            'wallet' => '109',
            'species' => '01',
            'expiration_type' => 3,
            'total_title_value' => '00000000000007700',
            'text_your_number' => '900931',
            'express_discount' => false,
            'payer' => [
                'name' => 'João Pereira da Silva',
                'fantasy_name' => 'João Pereira da Silva',
                'type' => 'F',
                'document' => '00000000000',
                'address' => [
                    'street' => 'Rua X, 1548',
                    'neighborhood' => 'Bairro Teste',
                    'city' => 'São Paulo',
                    'uf' => 'SP',
                    'cep' => '00000000'
                ]
            ],
            'individual_billet_data' => [
                'text_your_number' => '900931',
                'our_number' => '900931',
                'due_date' => '2025-02-22',
                'title_value' => '00000000000007700'
            ],
            'fees' => [
                'code' => '93',
                'quantity_days' => '1',
                'value' => '00000000000000200'
            ],
            'divergent_receipt' => [
                'code' => '03'
            ],
            'billing_message_list' => [
                'Pagavel em qualquer correspondente bancario'
            ],
            'protest' => [
                'protest' => 4,
                'quantity_days' => ''
            ]
        ]
    ];
}

function setSettings()
{
    $settings = new Settings();
    $settings->sandBox = true;

    $settings->clientId = $_ENV['CLIENT_ID'];
    $settings->clientSecret = $_ENV['CLIENT_SECRET'];
    $settings->correlationID = $_ENV['CORRELATION_ID'];

    return $settings;
}

function mockClientItauTokenSuccess($queue = [])
{
    $handler = new MockHandler();
    $responseToken = file_get_contents('tests/AuxFiles/createTokenResponseSuccess.json');

    $handler->append(
        new Response(
            200,
            [],
            $responseToken
        )
    );
    if (!empty($queue)) {
        foreach ($queue as $value) {
            $handler->append(
                $value
            );
        }
    }

    return new Client([
        'handler' => $handler
    ]);
}

function mockClientItauClientException()
{
    $request = new Request(
        'POST',
        'https://sandbox.devportal.itau.com.br/api/oauth/jwt',
        ['Content-Type' => 'application/x-www-form-urlencoded'],
        http_build_query([
            'grant_type' => 'client_credentials',
            'client_id' => 'client_id_invalidoçç',
            'client_secret' => 'client_secret',
        ])
    );

    $response = new Response(404, [], json_encode([
        "message" => "Parâmetro(s) obrigatório(s) no header não preenchido(s)"
    ]));

    $handler = new MockHandler([
        new ClientException(
            'Client error: 400',
            $request,
            $response
        ),
    ]);

    return new Client(['handler' => $handler]);
}

