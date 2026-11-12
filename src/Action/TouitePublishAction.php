<?php

declare(strict_types=1);

namespace App\Action;

use App\Contracts\ActionInterface;
use App\Contracts\TouiteRepositoryInterface;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

final class TouitePublishAction implements ActionInterface
{
    private const MAX_IMAGE_SIZE = 5 * 1024 * 1024;
    private const ALLOWED_IMAGE_TYPES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
    ];

    public function __construct(
        private readonly TouiteRepositoryInterface $repository,
        private readonly string $imageDirectory
    ) {}

    public function execute(array $parameters = []): void
    {
        $userId = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);
        if ($userId === false || $userId < 1) {
            header('Location: index.php?route=connexion');
            exit;
        }

        $error = '';
        $texte = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $texte = trim((string) ($_POST['texte'] ?? ''));
            if ($texte === '' || mb_strlen($texte) > 235) {
                $error = 'Le touite doit contenir entre 1 et 235 caractères.';
            } else {
                try {
                    $imageName = $this->storeImage($_FILES['image'] ?? null);
                } catch (InvalidArgumentException $exception) {
                    $error = $exception->getMessage();
                }

                if ($error === '') {
                    try {
                        $this->repository->create($texte, $userId, $imageName);
                    } catch (Throwable $exception) {
                        if ($imageName !== null) {
                            unlink($this->imageDirectory . DIRECTORY_SEPARATOR . $imageName);
                        }
                        throw $exception;
                    }

                    header('Location: index.php');
                    exit;
                }
            }
        }

        require dirname(__DIR__, 2) . '/views/publierTouites.php';
    }

    private function storeImage(mixed $file): ?string
    {
        if ($file === null ||
            (is_array($file) && ($file['error'] ?? null) === UPLOAD_ERR_NO_FILE)) {
            return null;
        }

        if (!is_array($file) ||
            !isset($file['error'], $file['tmp_name']) ||
            !is_int($file['error']) ||
            $file['error'] !== UPLOAD_ERR_OK ||
            !is_string($file['tmp_name']) ||
            !is_uploaded_file($file['tmp_name'])) {
            throw new InvalidArgumentException('Le téléversement de l’image a échoué.');
        }

        $size = filesize($file['tmp_name']);
        if ($size === false || $size === 0 || $size > self::MAX_IMAGE_SIZE) {
            throw new InvalidArgumentException('L’image doit peser au maximum 5 Mo.');
        }

        $imageInfo = getimagesize($file['tmp_name']);
        $mimeType = (new \finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        if ($imageInfo === false ||
            $mimeType === false ||
            $imageInfo['mime'] !== $mimeType ||
            !isset(self::ALLOWED_IMAGE_TYPES[$mimeType])) {
            throw new InvalidArgumentException('Formats acceptés : JPEG, PNG, GIF et WebP.');
        }

        if ($imageInfo[0] > 10000 || $imageInfo[1] > 10000 ||
            $imageInfo[0] * $imageInfo[1] > 40000000) {
            throw new InvalidArgumentException('Les dimensions de l’image sont trop importantes.');
        }

        if (!is_dir($this->imageDirectory) &&
            !mkdir($this->imageDirectory, 0700, true) &&
            !is_dir($this->imageDirectory)) {
            throw new RuntimeException('Impossible de créer le répertoire de stockage des images.');
        }
        if (is_link($this->imageDirectory)) {
            throw new RuntimeException('Le répertoire de stockage des images ne peut pas être un lien symbolique.');
        }

        $imageName = bin2hex(random_bytes(16)) . '.' . self::ALLOWED_IMAGE_TYPES[$mimeType];
        $destination = $this->imageDirectory . DIRECTORY_SEPARATOR . $imageName;
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new RuntimeException('Impossible de stocker l’image téléversée.');
        }
        if (!chmod($destination, 0600)) {
            unlink($destination);
            throw new RuntimeException('Impossible de sécuriser l’image téléversée.');
        }

        return $imageName;
    }
}
