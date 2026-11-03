<?php

declare(strict_types=1);

namespace App\Http;

use App\Contracts\RequestDataReaderInterface;

final class RequestDataReader implements RequestDataReaderInterface
{
    public function read(): array
    {
        $input = json_decode(file_get_contents('php://input') ?: '', true);
        return is_array($input) ? $input : $_POST;
    }
}
