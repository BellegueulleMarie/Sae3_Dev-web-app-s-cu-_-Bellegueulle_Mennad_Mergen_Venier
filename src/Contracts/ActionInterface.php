<?php

declare(strict_types=1);

namespace App\Contracts;

/**
 * Interface ActionInterface
 *
 * Contrat que toutes les actions du projet doivent respecter.
 * Chaque action correspond à une route et possède une méthode execute()
 * qui est appelée par le Dispatcher.
 */
interface ActionInterface
{
    /**
     * Exécute l'action associée à la route.
     *
     * @param array<string, string> $parameters Paramètres extraits de l'URL (ex: ['id' => '5'])
     */
    public function execute(array $parameters = []): void;
}
