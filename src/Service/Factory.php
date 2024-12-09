<?php

namespace Itau\Service;

use Itau\Models\DataSlip;
use Itau\Models\DataSlipPix;
use Itau\Models\Pix\Pix;
use Exception;

class Factory extends Client
{
    const BOLETO = 1;
    const BOLETO_PIX = 2;
    const PIX = 3;

    public $type;

    public function __construct($settings, $type)
    {
        $this->type = $type;
        parent::__construct($settings, $type);
    }

    public function update(array $data, string $updateType, $idBoleto)
    {
        switch ($updateType) {
            case 'BaixaImediata':
                $endpoint = "boletos/{$idBoleto}/baixa";
                break;
            case 'ValorNominal':
                $endpoint = "boletos/{$idBoleto}/valor_nominal";
                break;
            case 'Juros':
                $endpoint = "boletos/{$idBoleto}/juros";
                break;
            case 'DataVencimento':
                $endpoint = "boletos/{$idBoleto}/data_vencimento";
                break;
            case 'Desconto':
                $endpoint = "boletos/{$idBoleto}/desconto";
                break;
            case 'Abatimento':
                $endpoint = "boletos/{$idBoleto}/abatimento";
                break;
            case 'Multa':
                $endpoint = "boletos/{$idBoleto}/multa";
                break;
            case 'Protesto':
                $endpoint = "boletos/{$idBoleto}/protesto";
                break;
            case 'SeuNumero':
                $endpoint = "boletos/{$idBoleto}/seu_numero";
                break;
            case 'DataLimitePagamento':
                $endpoint = "boletos/{$idBoleto}/data_limite_pagamento";
                break;
            case 'Negativacao':
                $endpoint = "boletos/{$idBoleto}/negativacao";
                break;
            case 'Pagador':
                $endpoint = "boletos/{$idBoleto}/pagador";
                break;
            case 'RecebimentoDivergente':
                $endpoint = "boletos/{$idBoleto}/recebimento_divergente";
                break;
            default:
                throw new Exception("Tipo de atualização inválido: {$updateType}");
        }

        return $this->register($data, 'PATCH', $endpoint);
    }

    /**
     * @throws Exception
     */
    public function build(array $data, $method)
    {
        switch ($this->type) {
            case self::BOLETO:
                $endpoint = 'boletos';
                $object = new DataSlip();
                break;
            case self::BOLETO_PIX:
                $endpoint = 'boletos_pix';
                $object = new DataSlipPix();
                break;
            case self::PIX:
                $endpoint = 'cob';
                $object = new Pix();
                break;
            default:
                throw new Exception('Tipo inválido');
        }

        return $this->register($object->build($data), $method, $endpoint);
    }

    public function register($data, $method, $endpoint)
    {
        $token = $this->getApiToken();
        return $this->call($method, $endpoint, $token->access_token, $data);
    }
}
