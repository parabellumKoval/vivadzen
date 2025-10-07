<?php

namespace App\Exceptions;

class TranslationException extends \Exception
{
    protected $key = null;
    protected $data = [];

    public function __construct($message = "", string $errorKeys, array $errorData = [], $code = 0, \Exception $previous = null)
    {
        $this->key = $errorKeys;
        $this->data = $errorData;
        parent::__construct($message, $code, $previous);
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getData(): array
    {
        return $this->data;
    }
}