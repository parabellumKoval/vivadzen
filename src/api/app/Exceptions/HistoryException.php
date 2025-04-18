<?php

namespace App\Exceptions;

class HistoryException extends \Exception
{
    private $key;

    public function __construct($message, $key = '')
    {
        parent::__construct($message);
        $this->key = $key;
    }

    public function getKey()
    {
        return $this->key;
    }
}