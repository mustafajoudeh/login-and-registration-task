
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
    exit("Invalid category ID.");
}

$errors = [];
$categorieName = "";

try {
    // Get category
    $sql = "SELECT `CategorieID`, `CategorieName`
            FROM `categories`
            WHERE `CategorieID` = :id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(["id" => $id]);

    $categorie = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log($e->getMessage());
    exit("Unable to load category.");
}

if (!$categorie) {
    exit("Category not found.");
}

// Initial value
$categorieName = $categorie["CategorieName"];

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $categorieName = trim($_POST["categorie_name"] ?? "");

    // Validate name
    if ($categorieName === "") {
        $errors[] = "Category name is required.";
    } elseif (mb_strlen($categorieName) > 100) {
        $errors[] = "Category name cannot exceed 100 characters.";
    }

    if (empty($errors)) {
        try {
            // Check duplicate name, excluding current category
            $sql = "SELECT COUNT(*)
                    FROM `categories`
                    WHERE `CategorieName` = :name
                    AND `CategorieID` <> :id";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                "name" => $categorieName,
                "id" => $id
            ]);

            if ($stmt->fetchColumn() > 0) {
                $errors[] = "This category name already exists.";
            } else {

                // Update category
                $sql = "UPDATE `categories`
                        SET `CategorieName` = :name
                        WHERE `CategorieID` = :id";

                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    "name" => $categorieName,
                    "id" => $id
                ]);

                header("Location: categories.php?success=updated");
                exit;
            }

        } catch (PDOException $e) {
            error_log($e->getMessage());
            $errors[] = "Unable to update category. Please try again.";
        }
    }
}

// Escape output
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
        rel="stylesheet"
    >

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

    <form
        action="edit_categorie.php?id=<?= (int) $id ?>"
        method="POST"
        class="registration-form"
    >

        <div class="form-group">
            <label class="form-label" for="categorie_name">
                Category Name
            </label>

            <input
                class="form-control"
                type="text"
                id="categorie_name"
                name="categorie_name"
                placeholder="Enter category name"
                value="<?= e($categorieName) ?>"
                maxlength="100"
                required
            >
        </div>

        <button
            type="submit"
            class="btn btn-primary register-btn"
        >
            Update Category
        </button>

    </form>

</main>

</body>
</html>
