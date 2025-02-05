<?php

namespace Itau\Models;

class DiscountDetail
{
    /**
     * @var string Data limite de cobrança de desconto. Caso o campo esteja vazio, será automaticamente assumido que a cobrança de desconto é até a data de vencimento. Formato: AAAA-MM-DD
     */
    public $data_desconto;

    /**
     * @var string Valor do desconto a ser cobrado. Obrigatório para codigo_tipo_desconto 1 ou 91. Formato do campo: 15 dígitos inteiros e 2 casas decimais
     */
    public $valor_desconto;

    /**
     * @var string Percentual do desconto concedido. Obrigatório para codigo_tipo_desconto 2 ou 90. Formato do campo: 7 dígitos inteiros e 5 casas decimais.
     */
    public $percentual_desconto;

    public function build($data)
    {
        if (isset($data['value'])) {
            $this->valor_desconto = str_pad(
                number_format((float) $data['value'], 2, '', ''),
                17,
                '0',
                STR_PAD_LEFT
            );
        }
        if (isset($data['percent'])) {
            $this->percentual_desconto = str_pad(
                number_format((float) $data['percent'], 5, '', ''),
                12,
                '0',
                STR_PAD_LEFT
            );
        }
        $this->data_desconto = $data['date'] ?? null;
    }
}
