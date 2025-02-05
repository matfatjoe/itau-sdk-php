<?php

namespace Itau\Models;

class Discount
{

    /**
     * @var string Código do desconto. Caso exista mais de um desconto, todos os “tipo_desconto” deverão ter o mesmo código.<br/>'00' - Quando não houver condição de desconto – sem desconto<br/>'01' - Quando o desconto for um valor fixo se o título for pago até a data informada (data_desconto)<br/>'02' - Quando o desconto for um percentual do valor do título e for pago até a data informada (data_desconto)<br/>'90' - Percentual por antecipação(utilizando parâmetros do cadastro de beneficiário para dias úteis ou corridos)"<br/>'91' -Valor por antecipação (utilizando parâmetros do cadastro de beneficiário para dias úteis ou corridos)"
     */
    public $codigo_tipo_desconto;

    /**
     * @var array Lista de objetos DiscountDetail representando os descontos aplicados.
     */
    public $descontos = [];


    public function build($data)
    {
        $this->codigo_tipo_desconto = $data['code'] ?? null;
        foreach ($data['discounts'] as $key => $discount) {
            $this->descontos[$key] = new DiscountDetail();
            $this->descontos[$key]->build($discount);
        }
    }
}
