<?php

/*
 * This file is part of the Carbon package.
 *
 * (c) Brian Nesbitt <brian@nesbot.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Carbon\Exceptions;

use Exception;
use InvalidArgumentException;

class InvalidDateException extends InvalidArgumentException
{
    /**
     * Constructor.
     *
     * @param string          $field
     * @param mixed           $value
     * @param int             $code
     * @param \Exception|null $previous
     */
    public function __construct(/**
     * The invalid field.
     */
    private $field, /**
     * The invalid value.
     */
    private $value, $code = 0, ?Exception $previous = null)
    {
        parent::__construct($this->field.' : '.$this->value.' is not a valid value.', $code, $previous);
    }

    /**
     * Get the invalid field.
     *
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * Get the invalid value.
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
}
