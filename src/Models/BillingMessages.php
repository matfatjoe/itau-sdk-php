<?php

namespace Itau\Models;

class BillingMessages
{
    /**
     * @var string Mensagem a ser impressa no boleto.
     */
    public $mensagem;

    public function build($data)
    {
        $this->mensagem = $data;
    }
}
