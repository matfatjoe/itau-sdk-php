<?php

namespace Itau\Exceptions;

class BadRequestException extends ItauException
{
    const HTTP_STATUS_CODE = 400;

    public function getStatusCode()
    {
        return self::HTTP_STATUS_CODE;
    }
}
