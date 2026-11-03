<?php

declare(strict_types=1);

namespace App\Action;

use App\Contracts\ActionInterface;

final class HomeAction implements ActionInterface
{
    public function execute(array $parameters = []): void
    {
        require dirname(__DIR__, 2) . '/views/home.php';
    }
}
