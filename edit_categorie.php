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

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    header("Location: categories.php?error=invalid_id");
    exit;
}

$CategorieModule = new Categorie($pdo);
$errors = [];

try {
    $categorie = $CategorieModule->GetCategorieById($id);
} catch (Throwable $e) {
    error_log($e->getMessage());
    exit("Unable to load category.");
}

if ($categorie === null) {
    header("Location: categories.php?error=not_found");
    exit;
}

$categorieName = $categorie->CategorieName;

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $categorieName = trim($_POST["categorie_name"] ?? "");

    if ($categorieName === "") {
        $errors[] = "Category name is required.";

    } elseif (mb_strlen($categorieName) > 100) {
        $errors[] = "Category name cannot exceed 100 characters.";
    }

    if (empty($errors)) {
        try {
            // Exclude the current category from the duplicate check
            if (
                $CategorieModule->CategorieNameExists(
                    $categorieName,
                    $id
                )
            ) {
                $errors[] = "This category name already exists.";

            } else {
                $CategorieModule->UpdateCategorie(
                    $id,
                    $categorieName
                );

                header("Location: categories.php?success=updated");
                exit;
            }

        } catch (Throwable $e) {
            error_log($e->getMessage());
            $errors[] = "Unable to update category. Please try again.";
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

    <title>Edit Category</title>

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
            <h1>Edit Category</h1>
            <p>Update the category name.</p>
        </header>

        <?php foreach ($errors as $error): ?>
        <p class="alert alert-error" role="alert">
            <?= e($error) ?>
        </p>
        <?php endforeach; ?>

        <form action="edit_categorie.php?id=<?= (int) $id ?>" method="POST" class="registration-form">

            <div class="form-group">
                <label class="form-label" for="categorie_name">
                    Category Name
                </label>

                <input class="form-control" type="text" id="categorie_name" name="categorie_name"
                    placeholder="Enter category name" value="<?= e($categorieName) ?>" maxlength="100" required>
            </div>

            <button type="submit" class="btn btn-primary register-btn">
                Update Category
            </button>

        </form>

    </main>

</body>

</html>