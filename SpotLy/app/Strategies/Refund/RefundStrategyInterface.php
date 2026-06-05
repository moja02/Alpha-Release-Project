<?php

namespace App\Strategies\Refund;

interface RefundStrategyInterface
{
    public function calculateRefund(float $originalCost, int $minutesToStart): float;
}
