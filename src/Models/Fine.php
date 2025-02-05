<?php

namespace Itau\Models;

class Fine
{
    /**
     * @var string Código da multa '01' - Quando se deseja cobrar um valor fixo de multa após o vencimento. '02' - Quando se deseja cobrar um percentual do valor do título de multa após o vencimento. '03' - Quando não se deseja cobrar multa caso o pagamento seja feito após o vencimento (isento)
     */
    public $codigo_tipo_multa;

    /**
     * @var string Valor da multa cobrada. Obrigatório para codigo_tipo_multa 01. Formato do campo: 15 dígitos inteiros e 2 casas decimais.
     */
    public $valor_multa;

    /**
     * @var string Percentual da multa cobrada. Obrigatório para tipo_multa 02. Formato do campo: 7 dígitos inteiros e 5 casas decimais.
     */
    public $percentual_multa;

    /**
     * @var string Data de início de cobrança de multa. Caso o campo esteja vazio, será automaticamente assumido que a cobrança de multa se inicia logo após o vencimento. Formato: AAAA-MM-DD
     */
    public $data_multa;

    public function build($data)
    {
        $this->codigo_tipo_multa = $data['code'];
        if (isset($data['value'])) {
            $this->valor_multa = str_pad(
                number_format((float) $data['value'], 2, '', ''),
                17,
                '0',
                STR_PAD_LEFT
            ) ?? null;
        }
        if (isset($data['percent'])) {
            $this->percentual_multa = str_pad(
                number_format((float) $data['percent'], 5, '', ''),
                12,
                '0',
                STR_PAD_LEFT
            ) ?? null;
        }
        $this->data_multa = $data['date'] ?? null;
    }
}
