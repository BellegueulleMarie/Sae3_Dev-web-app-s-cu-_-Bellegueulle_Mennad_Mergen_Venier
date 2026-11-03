<?php

declare(strict_types=1);

namespace App\Contracts;

interface RequestDataReaderInterface
{
    /** @return array<string, mixed> */
    public function read(): array;
}
