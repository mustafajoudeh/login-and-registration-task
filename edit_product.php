
<?php

require_once "db.php";

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!isset($_SESSION["id"])) {
    header("Location: index.php");
    exit;
}

// Get product ID
$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    exit("Invalid product ID.");
}

try {
    // Get categories
    $sql = "SELECT `CategorieID`, `CategorieName`
            FROM `categories`
            ORDER BY `CategorieName`";

    $catStmt = $pdo->query($sql);
    $categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);

    // Get product including its category
    $sql = "SELECT `id`, `Product name`, `CategorieID`,
                   `Description`, `Price`, `Quantity`,
                   `Created date`
            FROM `product`
            WHERE `id` = :id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(["id" => $id]);

    $product = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log($e->getMessage());
    exit("Unable to load product information.");
}

if (!$product) {
    exit("Product not found.");
}

// Initial form values
$productName = $product["Product name"];
$categorieID = (string) $product["CategorieID"];
$description = $product["Description"];
$price = (string) $product["Price"];
$quantity = (string) $product["Quantity"];

$errors = [];

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $productName = trim($_POST["ProductName"] ?? "");
    $categorieID = trim($_POST["categorie_id"] ?? "");
    $description = trim($_POST["Description"] ?? "");
    $price = trim($_POST["Price"] ?? "");
    $quantity = trim($_POST["Quantity"] ?? "");

    // Validate product name
    if ($productName === "") {
        $errors[] = "Product name is required.";
    }

    // Validate category
    $categoryIds = array_map(
        "strval",
        array_column($categories, "CategorieID")
    );

    if (
        $categorieID === "" ||
        !ctype_digit($categorieID) ||
        !in_array($categorieID, $categoryIds, true)
    ) {
        $errors[] = "Please select a valid category.";
    }

    // Validate description
    if ($description === "") {
        $errors[] = "Description is required.";
    }

    // Validate price
    if (
        $price === "" ||
        !is_numeric($price) ||
        !is_finite((float) $price) ||
        (float) $price < 0
    ) {
        $errors[] = "Price must be a non-negative number.";
    }

    // Validate quantity
    $validatedQuantity = filter_var(
        $quantity,
        FILTER_VALIDATE_INT
    );

    if (
        $validatedQuantity === false ||
        $validatedQuantity < 0
    ) {
        $errors[] = "Quantity must be a non-negative integer.";
    }

    // Update product if there are no validation errors
    if (empty($errors)) {
        try {
            $sql = "UPDATE `product`
                    SET `Product name` = :name,
                        `CategorieID` = :categorieID,
                        `Description` = :description,
                        `Price` = :price,
                        `Quantity` = :quantity
                    WHERE `id` = :id";

            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                "name" => $productName,
                "categorieID" => (int) $categorieID,
                "description" => $description,
                "price" => $price,
                "quantity" => $validatedQuantity,
                "id" => $id
            ]);

            header("Location: products.php?success=updated");
            exit;

        } catch (PDOException $e) {
            error_log($e->getMessage());
            $errors[] = "Unable to update product.";
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

    <title>Edit Product | Jordan Smart Travel</title>

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

    <a class="back-link" href="products.php">
        &larr; Back to Products
    </a>

    <header class="registration-header">
        <span class="brand-label">JORDAN SMART TRAVEL</span>
        <h1>Edit Product</h1>
        <p>Update the product information.</p>
    </header>

    <?php foreach ($errors as $error): ?>
        <p class="alert alert-error" role="alert">
            <?= e($error) ?>
        </p>
    <?php endforeach; ?>

    <form
        action="edit_product.php?id=<?= (int) $id ?>"
        method="POST"
        class="registration-form"
    >

        <div class="form-group">
            <label class="form-label" for="ProductName">
                Product Name
            </label>

            <input
                class="form-control"
                type="text"
                id="ProductName"
                name="ProductName"
                value="<?= e($productName) ?>"
                required
            >
        </div>

        <div class="form-group">
            <label class="form-label" for="categorie_id">
                Category
            </label>

            <select
                class="form-control"
                id="categorie_id"
                name="categorie_id"
                required
            >
                <option value="">-- Choose a category --</option>

                <?php foreach ($categories as $categorie): ?>
                    <option
                        value="<?= e($categorie["CategorieID"]) ?>"
                        <?= (string) $categorie["CategorieID"] === $categorieID
                            ? "selected"
                            : "" ?>
                    >
                        <?= e($categorie["CategorieName"]) ?>
                    </option>
                <?php endforeach; ?>

            </select>
        </div>

        <div class="form-group">
            <label class="form-label" for="Description">
                Description
            </label>

            <textarea
                class="form-control"
                id="Description"
                name="Description"
                required
            ><?= e($description) ?></textarea>
        </div>

        <div class="form-group">
            <label class="form-label" for="Price">
                Price ($)
            </label>

            <input
                class="form-control"
                type="number"
                id="Price"
                name="Price"
                min="0"
                step="0.01"
                value="<?= e($price) ?>"
                required
            >
        </div>

        <div class="form-group">
            <label class="form-label" for="Quantity">
                Quantity
            </label>

            <input
                class="form-control"
                type="number"
                id="Quantity"
                name="Quantity"
                min="0"
                step="1"
                value="<?= e($quantity) ?>"
                required
            >
        </div>

        <button
            type="submit"
            class="btn btn-primary register-btn"
        >
            Update Product
        </button>

    </form>

</main>

</body>
</html>
