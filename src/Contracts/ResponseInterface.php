<?php

declare(strict_types=1);

namespace App\Contracts;

interface ResponseInterface
{
    public function send(mixed $data, int $status = 200): void;
}
