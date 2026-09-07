<?php

namespace Stripe;

use Stripe\Util\CaseInsensitiveArray;

/**
 * Class ApiResponse.
 */
class ApiResponse
{
    /**
     * @param string $body
     * @param int $code
     * @param null|array|CaseInsensitiveArray $headers
     * @param null|array $json
     */
    public function __construct(public $body, public $code, public $headers, public $json)
    {
    }
}
