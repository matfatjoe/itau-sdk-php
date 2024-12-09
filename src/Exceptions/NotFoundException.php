<?php

namespace Itau\Exceptions;

class NotFoundException extends ItauException
{
    const HTTP_STATUS_CODE = 404;

    public function getStatusCode()
    {
        return self::HTTP_STATUS_CODE;
    }
}
