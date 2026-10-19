
<?php

require_once "db.php";

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!isset($_SESSION["id"])) {
    header("Location: index.php");
    exit;
}

// Get category ID
$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    header("Location: categories.php?error=invalid_id");
    exit;
}

try {
    // Check whether category exists
    $sql = "SELECT COUNT(*)
            FROM `categories`
            WHERE `CategorieID` = :id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(["id" => $id]);

    if ($stmt->fetchColumn() == 0) {
        header("Location: categories.php?error=not_found");
        exit;
    }

    // Check whether products use this category
    $sql = "SELECT COUNT(*)
            FROM `product`
            WHERE `CategorieID` = :id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(["id" => $id]);

    if ($stmt->fetchColumn() > 0) {
        header("Location: categories.php?error=category_in_use");
        exit;
    }

    // Delete category
    $sql = "DELETE FROM `categories`
            WHERE `CategorieID` = :id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(["id" => $id]);

    header("Location: categories.php?success=deleted");
    exit;

} catch (PDOException $e) {
    error_log($e->getMessage());

    header("Location: categories.php?error=delete_failed");
    exit;
}
