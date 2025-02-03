<?php

namespace Itau\Service;

use Exception;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ClientException;
use Itau\Exceptions\BadRequestException;
use Itau\Exceptions\NotFoundException;
use Itau\Models\Settings;
use KryptonPay\Api\ApiContext;
use Itau\Models\Response;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Tightenco\Collect\Support\Collection;

class Client
{
    const HTTP_EXCEPTION_TYPES = [
        BadRequestException::HTTP_STATUS_CODE => BadRequestException::class,
        422 => BadRequestException::class,
        NotFoundException::HTTP_STATUS_CODE => NotFoundException::class,
    ];

    public $token;
    public $options;
    private $client;
    private $response;
    protected $url;
    protected $authUrl;

    /**
     * @var Settings
     */
    private $settings;

    public function __construct(Settings $settings, $type = 1)
    {
        $this->settings = $settings;
        $this->response = new Response();
        $this->client = new GuzzleClient();

        $this->setUrl($type);
    }

    public function call(string $method, string $endPoint, $token, $data = null)
    {
        try {
            $options['headers'] = [
                'x-itau-apikey' => $this->settings->clientId,
                'x-itau-flowID' => $this->settings->clientSecret,
                'x-itau-correlationID' => $this->settings->correlationID,
                'Authorization' => 'Bearer ' . $token
            ];

            $options['cert'] = $this->settings->certificate->folder . $this->settings->certificate->certFile;
            $options['ssl_key'] = $this->settings->certificate->folder . $this->settings->certificate->privateKey;

            $options['json'] = null;
            $options['query'] = null;

            if ($data) {
                if ($method == "GET") {
                    $options['query'] = $data;
                }
                if ($method == "POST") {
                    $options['json'] = $this->normalize($data);
                }
            }

            return $this->handleApiReturn(
                $this->client->request($method, $this->url . $endPoint, $options)
            );
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $requestParameters = $e->getRequest();

            $statusCode = $response->getStatusCode();
            $bodyContent = json_decode(
                $response->getBody()->getContents(),
                true
            );

            if (isset(self::HTTP_EXCEPTION_TYPES[$statusCode])) {
                $exceptionClass = self::HTTP_EXCEPTION_TYPES[$statusCode];
                $message = $bodyContent['mensagem'];
                if (!empty($bodyContent['campos'])) {
                    $invalidFields = array_map(function ($campo) {
                        return "Campo '{$campo['campo']}' ({$campo['valor']}): {$campo['mensagem']}";
                    }, $bodyContent['campos']);
                    $message .= "<br>Detalhes:<br>" . implode("<br>", $invalidFields);
                }

                $exception = new $exceptionClass($message);
                $exception->setRequestParameters($requestParameters);
                $exception->setBodyContent($bodyContent);
            } else {
                $exception = $e;
            }
            throw $exception;
        } catch (\Exception $e) {
            return $this->handleApiError($e);
        }
    }

    public static function arrayRemoveNull($item)
    {
        if (!\is_array($item)) {
            return $item;
        }

        return (new Collection($item))
            ->reject(function ($item) {
                return null === $item;
            })
            ->flatMap(function ($item, $key) {
                return is_numeric($key)
                    ? [self::arrayRemoveNull($item)]
                    : [$key => self::arrayRemoveNull($item)];
            })
            ->toArray();
    }

    protected function setUrl($type)
    {
        $this->authUrl = 'https://sts.itau.com.br/api/oauth/token';
        switch ($type) {
            case 1:
                $this->url = 'https://api.itau.com.br/cash_management/v2/';
                break;
            case 2:
                $this->url = 'https://secure.api.itau/pix_recebimentos_conciliacoes/v2/';
                break;
            case 3:
                $this->url = 'https://secure.api.itau/pix_recebimentos/v2/';
                break;
            case 4:
                $this->url = 'https://boletos.cloud.itau.com.br/boletos/v3/';
                break;
        }
    }

    private function normalize(object $data): array
    {
        $data = json_decode(json_encode($data), true);
        $data = self::arrayRemoveNull($data);
        foreach ($data as $key => $d) {
            if (empty($d)) {
                unset($data[$key]);
            }
        }

        return $data;
    }

    private function handleApiReturn($response)
    {
        $return = null;
        $successCode = [200, 201, 204];
        if (\in_array($response->getStatusCode(), $successCode)) {
            $return = json_decode($response->getBody());
        }

        return $return;
    }

    private function handleApiError(Exception $e): object
    {
        switch ($e->getCode()) {
            case 400:
            case 422:
                $return = json_decode($e->getResponse()->getBody());
                $this->response->code = (int) $e->getCode();
                $this->response->errorCode = (int) $return->codigo;
                $this->response->messages = $return->mensagem;
                $this->response->errors = $return->campos;

                return $this->response;
                break;
            case 401:
                $return = json_decode($e->getResponse()->getBody());
                $this->response->code = (int) $e->getCode();
                $this->response->errorCode = (int) $return->code;
                $this->response->messages = [$return->message];

                return $this->response;
                break;
            case 403:
                $response = $this->getApiToken();
                $this->settings->apiToken = $response->access_token;
                return $this->call();
                /*$this->response->code = (int) $e->getCode();
                $this->response->messages = ['Erro: 403'];
                unset($this->response->errorCode);

                return $this->response;*/
                break;
            case 404:
                $this->response->code = (int) $e->getCode();
                $this->response->messages = ['Erro: 404'];
                unset($this->response->errorCode);

                return $this->response;
                break;
            case 405:
                $return = json_decode($e->getResponse()->getBody());
                $this->response->code = 405;
                $this->response->messages = 'Erro: 405';
                $this->response->errors = $return->details->msgId;

                return $this->response;
                break;
            case 503:
                $this->response->code = (int) $e->getCode();
                $this->response->messages = ['Erro: 503'];
                unset($this->response->errorCode);

                return $this->response;
                break;
            default:
                $this->response->code = (int) $e->getCode();
                $this->response->messages = ['Erro: 500'];
                unset($this->response->errorCode);

                return $this->response;
                break;
        }
    }

    public function getApiToken()
    {
        try {
            $options = [
                'form_params' => [
                    'grant_type' => 'client_credentials',
                    'client_id' => $this->settings->clientId,
                    'client_secret' => $this->settings->clientSecret,
                ]
            ];

            $options['cert'] = $this->settings->certificate->folder . $this->settings->certificate->certFile;
            $options['ssl_key'] = $this->settings->certificate->folder . $this->settings->certificate->privateKey;
            $options['verify'] = true;

            $this->token = $this->handleApiReturn(
                $this->client->request('POST', $this->authUrl, $options)
            );

            return $this->token;
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $requestParameters = $e->getRequest();

            $statusCode = $response->getStatusCode();
            $bodyContent = json_decode(
                $response->getBody()->getContents(),
                true
            );

            if (isset(self::HTTP_EXCEPTION_TYPES[$statusCode])) {
                $exceptionClass = self::HTTP_EXCEPTION_TYPES[$statusCode];
                $message = $bodyContent['detail'];

                $exception = new $exceptionClass($message);
                $exception->setRequestParameters($requestParameters);
                $exception->setBodyContent($bodyContent);
            } else {
                $exception = $e;
            }
            throw $exception;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function setClient(GuzzleClient $client)
    {
        $this->client = $client;
    }

    public function setCertificate()
    {
        $this->client = new GuzzleClient([
            'cert' => $this->settings->certificate->folder . $this->settings->certificate->certFile,
            'ssl_key' => $this->settings->certificate->folder . $this->settings->certificate->privateKey
        ]);
    }
}
