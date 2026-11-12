<?php

declare(strict_types=1);

namespace App\Action;

use App\Contracts\ActionInterface;
use App\Contracts\TouiteRepositoryInterface;

/**
 * Classe TouiteImageAction
 *
 * Sert l'image associée à un touite de façon sécurisée.
 * Vérifie que le chemin de l'image est valide et que le fichier
 * se trouve bien dans le répertoire autorisé (protection path traversal).
 * Retourne une 404 si l'image est introuvable ou invalide.
 */
final class TouiteImageAction implements ActionInterface
{
    /**
     * Types MIME d'images autorisés en sortie.
     */
    private const ALLOWED_IMAGE_TYPES = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
    ];

    /**
     * Constructeur.
     *
     * @param TouiteRepositoryInterface $repository    Accès aux touites
     * @param string                    $imageDirectory Chemin absolu du dossier d'images
     */
    public function __construct(
        private readonly TouiteRepositoryInterface $repository,
        private readonly string $imageDirectory
    ) {}

    /**
     * Envoie l'image du touite au navigateur.
     *
     * @param array<string, string> $parameters Paramètres de l'URL (['id' => '<touite_id>'])
     */
    public function execute(array $parameters = []): void
    {
        $id = filter_var($parameters['id'] ?? null, FILTER_VALIDATE_INT);
        $touite = $id !== false && $id > 0
            ? $this->repository->find((int) $id)
            : null;
        $imageName = $touite['id_img'] ?? null;

        // Vérifie que le nom de fichier correspond au format attendu (hex32.ext)
        if (!is_string($imageName) ||
            preg_match('/\A[a-f0-9]{32}\.(?:jpg|png|gif|webp)\z/', $imageName) !== 1) {
            http_response_code(404);
            return;
        }

        // Protection contre les attaques par traversée de répertoire
        $imagePath      = $this->imageDirectory . DIRECTORY_SEPARATOR . $imageName;
        $realImagePath  = realpath($imagePath);
        $realImageDir   = realpath($this->imageDirectory);
        if ($realImagePath === false ||
            $realImageDir === false ||
            dirname($realImagePath) !== $realImageDir ||
            !is_file($realImagePath)) {
            http_response_code(404);
            return;
        }

        // Vérifie le type MIME réel du fichier
        $mimeType = (new \finfo(FILEINFO_MIME_TYPE))->file($realImagePath);
        if ($mimeType === false || !in_array($mimeType, self::ALLOWED_IMAGE_TYPES, true)) {
            http_response_code(404);
            return;
        }

        // Envoie les en-têtes puis le contenu du fichier
        header('Content-Type: ' . $mimeType);
        header('Content-Length: ' . (string) filesize($realImagePath));
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: private, max-age=3600');
        readfile($realImagePath);
    }
}
