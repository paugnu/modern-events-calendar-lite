<?php

namespace Stripe;

/**
 * Class RequestTelemetry.
 *
 * Tracks client request telemetry
 */
class RequestTelemetry
{
    /**
     * Initialize a new telemetry object.
     *
     * @param string $requestId the request's request ID
     * @param int $requestDuration the request's duration in milliseconds
     */
    public function __construct(public $requestId, public $requestDuration)
    {
    }
}
