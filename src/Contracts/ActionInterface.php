<?php

declare(strict_types=1);

namespace App\Contracts;

interface ActionInterface
{
    /** @param array<string, string> $parameters */
    public function execute(array $parameters = []): void;
}
