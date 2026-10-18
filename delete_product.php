
<?php

require_once "db.php";

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!isset($_SESSION["id"])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: products.php");
    exit;
}

$id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    header("Location: products.php?error=invalid_id");
    exit;
}

try {
    $sql = "DELETE FROM `product` WHERE id = :id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(["id" => $id]);

    if ($stmt->rowCount() === 0) {
        header("Location: products.php?error=not_found");
        exit;
    }

    header("Location: products.php?success=deleted");
    exit;

} catch (PDOException $e) {
    error_log($e->getMessage());

    header("Location: products.php?error=delete_failed");
    exit;
}
