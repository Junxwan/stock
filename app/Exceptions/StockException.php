<?php

namespace App\Exceptions;

use Throwable;

class StockException extends \Exception
{
    /**
     * @var \Exception
     */
    private $e;

    /**
     * StockException constructor.
     *
     * @param \Exception $e
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(\Exception $e, $code = 0, Throwable $previous = null)
    {
        $this->e = $e;
        parent::__construct($e->getMessage(), $code, $previous);
    }

    /**
     * @param $name
     * @param $arguments
     */
    public function __call($name, $arguments)
    {
        $this->e->{$name}($arguments);
    }
}
