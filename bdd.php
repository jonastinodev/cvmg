<?php
// bdd.php — Connexion unique à la base MySQL (PDO), partagée par tous les scripts.
// Centralise les options PDO et la gestion d'erreur : en cas d'échec de connexion,
// on renvoie un message générique SANS jamais divulguer le DSN ni les identifiants.

require_once __DIR__ . '/config.php';

function bdd(): PDO {
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }
    try {
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]
        );
    } catch (PDOException $e) {
        error_log('Connexion BDD échouée : ' . $e->getMessage());
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['erreur' => 'Service momentanément indisponible.'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    return $pdo;
}
