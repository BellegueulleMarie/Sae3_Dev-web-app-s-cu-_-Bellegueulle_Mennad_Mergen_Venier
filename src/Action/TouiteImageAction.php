<?php

declare(strict_types=1);

namespace App\Action;

use App\Contracts\ActionInterface;
use App\Contracts\TouiteRepositoryInterface;

final class TouiteImageAction implements ActionInterface
{
    private const ALLOWED_IMAGE_TYPES = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
    ];

    public function __construct(
        private readonly TouiteRepositoryInterface $repository,
        private readonly string $imageDirectory
    ) {}

    public function execute(array $parameters = []): void
    {
        $id = filter_var($parameters['id'] ?? null, FILTER_VALIDATE_INT);
        $touite = $id !== false && $id > 0
            ? $this->repository->find((int) $id)
            : null;
        $imageName = $touite['id_img'] ?? null;

        if (!is_string($imageName) ||
            preg_match('/\A[a-f0-9]{32}\.(?:jpg|png|gif|webp)\z/', $imageName) !== 1) {
            http_response_code(404);
            return;
        }

        $imagePath = $this->imageDirectory . DIRECTORY_SEPARATOR . $imageName;
        $realImagePath = realpath($imagePath);
        $realImageDirectory = realpath($this->imageDirectory);
        if ($realImagePath === false ||
            $realImageDirectory === false ||
            dirname($realImagePath) !== $realImageDirectory ||
            !is_file($realImagePath)) {
            http_response_code(404);
            return;
        }

        $mimeType = (new \finfo(FILEINFO_MIME_TYPE))->file($realImagePath);
        if ($mimeType === false || !in_array($mimeType, self::ALLOWED_IMAGE_TYPES, true)) {
            http_response_code(404);
            return;
        }

        header('Content-Type: ' . $mimeType);
        header('Content-Length: ' . (string) filesize($realImagePath));
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: private, max-age=3600');
        readfile($realImagePath);
    }
}
