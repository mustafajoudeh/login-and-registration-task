<?php

require_once "db.php";
require_once "UsersClass.php";

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Check if the user is logged in
if (!isset($_SESSION["id"])) {
    header("Location: index.php");
    exit;
}

// Allow POST requests only
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: users.php");
    exit;
}



// Validate user ID
$id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    header("Location: users.php?error=invalid_id");
    exit;
}

// Prevent deleting the currently logged-in account
if ($id === (int) $_SESSION["id"]) {
    header("Location: users.php?error=cannot_delete_self");
    exit;
}

try {

     $userModle = new User($pdo);
     $user = $userModle->GetUser($id);
    // Check if the user exists
    $sql = "SELECT id FROM `user` WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(["id" => $id]);

    if (!$user) {
        header("Location: users.php?error=user_not_found");
        exit;
    }

    // Delete the user
    $user->DeleteUser($id);
    header("Location: users.php?success=deleted");
    exit;

} catch (PDOException $e) {
    error_log($e->getMessage());

    header("Location: users.php?error=delete_failed");
    exit;
}