<?php

namespace Itau\Exceptions;

use Exception;

class ItauException extends Exception
{
    protected $requestParameters;
    protected $bodyContent;

    /**
     * Get the value of requestParameters
     */
    public function getRequestParameters()
    {
        return $this->requestParameters;
    }

    /**
     * Set the value of requestParameters
     */
    public function setRequestParameters($request): self
    {
        $this->requestParameters = [
            'method' => $request->getMethod(),
            'uri' => $request->getUri(),
            'headers' => $request->getHeaders(),
            'body' => (string)$request->getBody(),
        ];

        return $this;
    }
    /**
     * Get the value of bodyContent
     */
    public function getBodyContent()
    {
        return $this->bodyContent;
    }

    /**
     * Set the value of bodyContent
     */
    public function setBodyContent($bodyContent): self
    {
        $this->bodyContent = $bodyContent;

        return $this;
    }
}
