<?php

require_once "db.php";
require_once "CategorieClass.php";

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!isset($_SESSION["id"])) {
    header("Location: index.php");
    exit;
}

$errors = [];
$categorieName = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $categorieName = trim($_POST["categorie_name"] ?? "");

    // Validate category name
    if ($categorieName === "") {
        $errors[] = "Category name is required.";
    }

    if (empty($errors)) {
        try {
           $CategorieModule = new Categorie($pdo);
           $categorie = $CategorieModule->CreateCategorie($categorieName);

                header("Location: categories.php?success=created");
                exit;
            

        } catch (PDOException $e) {
            error_log($e->getMessage());
            $errors[] = "Unable to create category. Please try again.";
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

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    <link rel="stylesheet" href="css/variables.css">
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/components.css">
    <link rel="stylesheet" href="css/pages/Registration.css">
</head>

<body>

    <main class="registration-card">

        <a class="back-link" href="categories.php">
            &larr; Back to Categories
        </a>

        <header class="registration-header">
            <span class="brand-label">JORDAN SMART TRAVEL</span>
            <h1>Add New Category</h1>
            <p>Enter a name for the new category.</p>
        </header>

        <?php foreach ($errors as $error): ?>
        <p class="alert alert-error" role="alert">
            <?= e($error) ?>
        </p>
        <?php endforeach; ?>

        <form action="create_categories.php" method="POST" class="registration-form">

            <div class="form-group">
                <label class="form-label" for="categorie_name">
                    Category Name
                </label>

                <input class="form-control" type="text" id="categorie_name" name="categorie_name"
                    placeholder="Enter category name" value="<?= e($categorieName) ?>" maxlength="100" required>
            </div>

            <button type="submit" class="btn btn-primary register-btn">
                Add Category
            </button>

        </form>

    </main>

</body>

</html>