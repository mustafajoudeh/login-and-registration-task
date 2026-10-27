<?php

require_once __DIR__ . "/db.php";
require_once __DIR__ . "/CategorieClass.php";

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!isset($_SESSION["id"])) {
    header("Location: index.php");
    exit;
}

// The categories table sends the ID using POST
$id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    header("Location: categories.php?error=invalid_id");
    exit;
}

$CategorieModule = new Categorie($pdo);

try {
    // Check whether the category exists
    $categorie = $CategorieModule->GetCategorieById($id);

    if ($categorie === null) {
        header("Location: categories.php?error=not_found");
        exit;
    }

    // Prevent deleting categories that have products
    if ($CategorieModule->HasProducts($id)) {
        header("Location: categories.php?error=category_in_use");
        exit;
    }

    // Delete the category
    $deletedRows = $CategorieModule->DeleteCategorie($id);

    if ($deletedRows === 0) {
        header("Location: categories.php?error=not_found");
        exit;
    }

    header("Location: categories.php?success=deleted");
    exit;

} catch (Throwable $e) {
    error_log($e->getMessage());

    header("Location: categories.php?error=delete_failed");
    exit;
}