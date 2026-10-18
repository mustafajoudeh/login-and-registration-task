
<?php

require_once "db.php";
session_start();

if (!isset($_SESSION["id"])) {
    header("Location: index.php");
    exit;
}

// Validate user ID
$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    http_response_code(400);
    exit("Invalid user ID.");
}

// Fetch one user using a prepared statement
$sql = "SELECT id, fullName, username, email
        FROM `user`
        WHERE id = :id";

$stmt = $pdo->prepare($sql);
$stmt->execute(["id" => $id]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    http_response_code(404);
    exit("User not found.");
}

$errors = [];

$fullName = $user["fullName"];
$username = $user["username"];
$email = $user["email"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $fullName = trim($_POST["fullName"] ?? "");
    $username = trim($_POST["username"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirmPassword = $_POST["confirmPassword"] ?? "";

    // Validate full name
    if ($fullName === "" || mb_strlen($fullName) > 100) {
        $errors[] = "Full name is required and must not exceed 100 characters.";
    }

    // Validate username
    if (!preg_match('/\A[a-zA-Z0-9_.-]{3,30}\z/', $username)) {
        $errors[] = "Username must be 3-30 characters using letters, numbers, dots, underscores or hyphens.";
    }

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }

    // Password is optional when editing
    if ($password !== "" || $confirmPassword !== "") {

        if (strlen($password) < 8) {
            $errors[] = "Password must contain at least 8 bytes.";
        }

        if ($password !== $confirmPassword) {
            $errors[] = "Passwords do not match.";
        }
    }

    // Check whether another user has the same username or email
    if (empty($errors)) {

        $checkSql = "SELECT id
                     FROM `user`
                     WHERE (username = :username OR email = :email)
                       AND id <> :id
                     LIMIT 1";

        $checkStmt = $pdo->prepare($checkSql);

        $checkStmt->execute([
            "username" => $username,
            "email" => $email,
            "id" => $id
        ]);

        if ($checkStmt->fetch()) {
            $errors[] = "Username or email is already used by another account.";
        }
    }

    // Update user
    if (empty($errors)) {

        try {
            if ($password !== "") {

                // Update details and new hashed password
                $updateSql = "UPDATE `user`
                              SET fullName = :fullName,
                                  username = :username,
                                  email = :email,
                                  password = :password
                              WHERE id = :id";

                $params = [
                    "fullName" => $fullName,
                    "username" => $username,
                    "email" => $email,
                    "password" => password_hash(
                        $password,
                        PASSWORD_DEFAULT
                    ),
                    "id" => $id
                ];

            } else {

                // Update details without changing the password
                $updateSql = "UPDATE `user`
                              SET fullName = :fullName,
                                  username = :username,
                                  email = :email
                              WHERE id = :id";

                $params = [
                    "fullName" => $fullName,
                    "username" => $username,
                    "email" => $email,
                    "id" => $id
                ];
            }

            $updateStmt = $pdo->prepare($updateSql);
            $updateStmt->execute($params);

            header("Location: users.php?success=updated");
            exit;

        } catch (PDOException $e) {
            if ($e->getCode() === "23000") {
                $errors[] = "Username or email already exists.";
            } else {
                error_log($e->getMessage());
                $errors[] = "Unable to update user. Please try again.";
            }
        }
    }
}

function e($value): string
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES | ENT_SUBSTITUTE,
        "UTF-8"
    );
}

?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit User Account | Jordan Smart Travel</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="css/variables.css">
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/components.css">
    <link rel="stylesheet" href="css/pages/Registration.css">
</head>

<body>
<main class="registration-card">

    <a class="back-link" href="users.php">
        &larr; Back to Users
    </a>

    <header class="registration-header">
        <span class="brand-label">JORDAN SMART TRAVEL</span>
        <h1>Edit User Account</h1>
        <p>Update the user's account information.</p>
    </header>

    <?php foreach ($errors as $error): ?>
        <p class="alert alert-error" role="alert">
            <?= e($error) ?>
        </p>
    <?php endforeach; ?>

    <form
        action="edit_user.php?id=<?= (int) $id ?>"
        method="POST"
        class="registration-form"
    >

        <div class="form-group">
            <label class="form-label" for="fullName">
                Full Name
            </label>

            <input
                class="form-control"
                type="text"
                id="fullName"
                name="fullName"
                maxlength="100"
                autocomplete="name"
                value="<?= e($fullName) ?>"
                required
            >
        </div>

        <div class="form-group">
            <label class="form-label" for="email">
                Email Address
            </label>

            <input
                class="form-control"
                type="email"
                id="email"
                name="email"
                maxlength="254"
                autocomplete="email"
                value="<?= e($email) ?>"
                required
            >
        </div>

        <div class="form-group">
            <label class="form-label" for="username">
                Username
            </label>

            <input
                class="form-control"
                type="text"
                id="username"
                name="username"
                minlength="3"
                maxlength="30"
                pattern="[a-zA-Z0-9_.-]{3,30}"
                autocomplete="username"
                value="<?= e($username) ?>"
                required
            >
        </div>

        <div class="form-group">
            <label class="form-label" for="password">
                New Password (Optional)
            </label>

            <input
                class="form-control"
                type="password"
                id="password"
                name="password"
                minlength="8"
                autocomplete="new-password"
            >

            <small class="field-hint">
                Leave blank to keep the current password.
            </small>
        </div>

        <div class="form-group">
            <label class="form-label" for="confirmPassword">
                Confirm New Password
            </label>

            <input
                class="form-control"
                type="password"
                id="confirmPassword"
                name="confirmPassword"
                minlength="8"
                autocomplete="new-password"
            >
        </div>

        <button type="submit" class="btn btn-primary register-btn">
            Save Changes
        </button>

    </form>

</main>
</body>
</html>
