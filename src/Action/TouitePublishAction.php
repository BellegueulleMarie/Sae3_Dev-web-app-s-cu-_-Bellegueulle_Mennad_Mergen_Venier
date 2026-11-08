<?php

declare(strict_types=1);

namespace App\Action;

use App\Contracts\ActionInterface;
use App\Contracts\TouiteRepositoryInterface;

final class TouitePublishAction implements ActionInterface
{
    public function __construct(private readonly TouiteRepositoryInterface $repository)
    {
    }

    public function execute(array $parameters = []): void
    {
        $userId = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);
        if ($userId === false || $userId < 1) {
            header('Location: index.php?route=connexion');
            exit;
        }

        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $texte = trim((string) ($_POST['texte'] ?? ''));
            if ($texte === '' || mb_strlen($texte) > 235) {
                $error = 'Le touite doit contenir entre 1 et 235 caractères.';
            } else {
                $this->repository->create($texte, $userId);
                header('Location: index.php');
                exit;
            }
        }

        require dirname(__DIR__, 2) . '/views/publierTouites.php';
    }
}
