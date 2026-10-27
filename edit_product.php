<?php

require_once  "db.php";
require_once  "ProductsClass.php";
require_once  "CategorieClass.php";

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!isset($_SESSION["id"])) {
    header("Location: index.php");
    exit;
}

function e($value): string
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES | ENT_SUBSTITUTE,
        "UTF-8"
    );
}

// Get product ID
$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    exit("Invalid product ID.");
}

// Load product and categories
try {
    $CategorieModule = new Categorie($pdo);
    $categories = $CategorieModule->GetAllCategories();

    $ProductModel = new Products($pdo);
    $product = $ProductModel->GetProductById($id);

} catch (Throwable $e) {
    error_log($e->getMessage());
    echo"". $e->getMessage() ."";
    exit("Unable to load product information.");
}

if (!$product) {
    exit("Product not found.");
}

// Initial form values
$productName = $product->ProductName;
$categorieID = (string) $product->CategorieID;
$photoURL = $product->PhotoURL;
$description = $product->Description ?? "";
$price = (string) $product->Price;
$quantity = (string) $product->Quantity;

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
        static fn($categorie): string =>
            (string) $categorie->CategorieID,
        $categories
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

    // Keep the old image unless a new one is uploaded
    $photoURL = $product->PhotoURL;
    $newUploadedFilePath = null;

    if (empty($errors)) {
        try {

            // Check whether a new image was selected
            if (
                isset($_FILES["fileToUpload"]) &&
                $_FILES["fileToUpload"]["error"] !== UPLOAD_ERR_NO_FILE
            ) {
                $file = $_FILES["fileToUpload"];

                if ($file["error"] !== UPLOAD_ERR_OK) {
                    $errors[] = "Image upload failed.";

                } elseif ($file["size"] > 5 * 1024 * 1024) {
                    $errors[] = "Image size must not exceed 5 MB.";

                } else {
                    // Verify that the uploaded file is a real image
                    $imageInfo = @getimagesize($file["tmp_name"]);

                    $allowedTypes = [
                        IMAGETYPE_JPEG => "jpg",
                        IMAGETYPE_PNG  => "png",
                        IMAGETYPE_WEBP => "webp",
                        IMAGETYPE_GIF  => "gif"
                    ];

                    if ($imageInfo === false) {
                        $errors[] = "Please select a valid image.";

                    } else {
                        $imageType = $imageInfo[2];

                        if (!isset($allowedTypes[$imageType])) {
                            $errors[] =
                                "Only JPG, PNG, WEBP and GIF images are allowed.";

                        } else {
                            // Save the image in the project's img folder
                            $uploadDirectory = __DIR__ . "/img/";

                            if (
                                !is_dir($uploadDirectory) &&
                                !mkdir($uploadDirectory, 0755, true) &&
                                !is_dir($uploadDirectory)
                            ) {
                                throw new RuntimeException(
                                    "Unable to create image directory."
                                );
                            }

                            // Generate a unique filename
                            $fileName = bin2hex(random_bytes(16))
                                . "."
                                . $allowedTypes[$imageType];

                            $destination = $uploadDirectory . $fileName;

                            if (
                                !move_uploaded_file(
                                    $file["tmp_name"],
                                    $destination
                                )
                            ) {
                                throw new RuntimeException(
                                    "Unable to save uploaded image."
                                );
                            }

                            // Store the relative URL in the database
                            $photoURL = "img/" . $fileName;
                            $newUploadedFilePath = $destination;
                        }
                    }
                }
            }

            // Update the database only if validation succeeded
            if (empty($errors)) {
                $UpdatedProducts = $ProductModel->UpdateProduct($id,
                $categorieID,$photoURL,$productName,
                $description,$price,$quantity);

                header("Location: products.php?success=updated");
                exit;
            }

        } catch (Throwable $e) {
            error_log($e->getMessage());

            // Remove the newly uploaded file if the update failed
            if (
                $newUploadedFilePath !== null &&
                is_file($newUploadedFilePath)
            ) {
                unlink($newUploadedFilePath);
            }

            // Preserve the original image
            $photoURL = $product->PhotoURL;

            $errors[] = "Unable to update product. Please try again.";
        }
    }
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
        rel="stylesheet">

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

        <form action="edit_product.php?id=<?= (int) $id ?>" method="POST" class="registration-form"
            enctype="multipart/form-data">

            <div class="form-group">
                <label class="form-label" for="ProductName">
                    Product Name
                </label>

                <input class="form-control" type="text" id="ProductName" name="ProductName"
                    value="<?= e($productName) ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="categorie_id">
                    Category
                </label>

                <select class="form-control" id="categorie_id" name="categorie_id" required>

                    <option value="">-- Choose a category --</option>

                    <?php foreach ($categories as $categorie): ?>
                    <option value="<?= e($categorie->CategorieID) ?>" <?= (string) $categorie->CategorieID === $categorieID
                                ? "selected"
                                : "" ?>>

                        <?= e($categorie->CategorieName) ?>
                    </option>
                    <?php endforeach; ?>

                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Current Product Image</label>

                <?php if (!empty($photoURL)): ?>
                <img src="<?= e($photoURL) ?>" alt="<?= e($productName) ?>" style="
                            display: block;
                            width: 140px;
                            height: 140px;
                            object-fit: contain;
                            border: 1px solid #e5e8e1;
                            border-radius: 10px;
                            padding: 6px;
                            background: #fff;
                        ">
                <?php else: ?>
                <p>No product image available.</p>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label class="form-label" for="fileToUpload">
                    Change Product Image (Optional)
                </label>

                <input class="form-control" type="file" name="fileToUpload" id="fileToUpload"
                    accept="image/jpeg,image/png,image/webp,image/gif">
            </div>

            <div class="form-group">
                <label class="form-label" for="Description">
                    Description
                </label>

                <textarea class="form-control" id="Description" name="Description"
                    required><?= e($description) ?></textarea>
            </div>

            <div class="form-group">
                <label class="form-label" for="Price">
                    Price ($)
                </label>

                <input class="form-control" type="number" id="Price" name="Price" min="0" step="0.01"
                    value="<?= e($price) ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="Quantity">
                    Quantity
                </label>

                <input class="form-control" type="number" id="Quantity" name="Quantity" min="0" step="1"
                    value="<?= e($quantity) ?>" required>
            </div>

            <button type="submit" class="btn btn-primary register-btn">
                Update Product
            </button>

        </form>

    </main>

</body>

</html>