<?php

declare(strict_types=1);

/**
 * Fichier index.php
 *
 * Point d'entrée unique de l'application (Front Controller).
 * Détermine le chemin demandé (URL Rewriting ou paramètre ?route=),
 * initialise l'application et dispatche la requête vers l'action correspondante.
 */

use App\Application;

// Chargement de l'autoloader Composer (PSR-4)
require __DIR__ . '/vendor/autoload.php';

// Gestion des fichiers statiques (CSS) pour le serveur interne PHP (php -S)
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
if (PHP_SAPI === 'cli-server' &&
    str_starts_with($requestPath, '/CSS/') &&
    is_file(__DIR__ . $requestPath)) {
    return false;
}

// Calcul du chemin de base pour gérer les sous-dossiers (ex: /Sae/.../)
$scriptPath = parse_url($_SERVER['SCRIPT_NAME'] ?? '', PHP_URL_PATH) ?: '';
$basePath = rtrim(str_replace('\\', '/', dirname($scriptPath)), '/.');

// Nettoie le préfixe du sous-dossier si présent dans l'URI
if ($basePath !== '' && $basePath !== '/' &&
    str_starts_with($requestPath, $basePath)) {
    $requestPath = substr($requestPath, strlen($basePath)) ?: '/';
}

if ($requestPath === '/admin.php') {
    require __DIR__ . '/admin.php';
    return;
}

// Support du paramètre ?route=... (ex: index.php?route=influenceurs)
if (isset($_GET['route']) && is_string($_GET['route'])) {
    $requestPath = '/' . ltrim($_GET['route'], '/');
}

if ($scriptPath !== '' && ltrim(rtrim($requestPath, '/'), '/') === basename($scriptPath)) {
    $requestPath = '/';
}

// Instanciation de l'application et dispatching de la route demandée
$application = Application::create();
$application['dispatcher']->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $requestPath);
