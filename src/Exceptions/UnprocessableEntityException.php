<?php

namespace Itau\Exceptions;

class UnprocessableEntityException extends ItauException
{
    const HTTP_STATUS_CODE = 422;

    public function getStatusCode()
    {
        return self::HTTP_STATUS_CODE;
    }
}
