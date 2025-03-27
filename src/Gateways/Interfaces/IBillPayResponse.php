<?php

namespace GlobalPayments\Api\Gateways\Interfaces;

interface IBillPayResponse
{
    public function map();

    public function withResponseTagName(String $tagName): IBillPayResponse;

    public function withResponse(String $response): IBillPayResponse;
}