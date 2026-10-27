<?php
require_once "db.php";
require_once "ProductsClass.php";
require_once "CategorieClass.php";
session_start();
try{
   $CategorieModule = new Categorie($pdo);

    $categories = $CategorieModule->GetAllCategories();
}
catch(PDOException $e)
{
  exit($e);
}


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $ProductName = trim($_POST["product_name"] ?? "");
    $CategorieID = filter_var(
        $_POST["categorie_id"] ?? "",
        FILTER_VALIDATE_INT
    );
    $Description = trim($_POST["description"] ?? "");

    $PriceInput = trim($_POST["price"] ?? "");
    $Quantity = filter_var(
        $_POST["quantity"] ?? "",
        FILTER_VALIDATE_INT
    );

    // Validate form fields
    if (
        $ProductName === "" ||
        $CategorieID === false ||
        $CategorieID <= 0 ||
        $Description === "" ||
        $PriceInput === "" ||
        !is_numeric($PriceInput) ||
        (float) $PriceInput < 0 ||
        $Quantity === false ||
        $Quantity < 0
    ) {
        exit("Please enter valid product information.");
    }

    $Price = (float) $PriceInput;

    // The image is optional
    $PhotoURL = null;

    try {

        // Upload image if one was selected
        if (
            isset($_FILES["fileToUpload"]) &&
            $_FILES["fileToUpload"]["error"] !== UPLOAD_ERR_NO_FILE
        ) {
            $file = $_FILES["fileToUpload"];

            if ($file["error"] !== UPLOAD_ERR_OK) {
                throw new RuntimeException("Image upload failed.");
            }

            // Maximum image size: 5 MB
            if ($file["size"] > 5 * 1024 * 1024) {
                throw new RuntimeException(
                    "Image size must not exceed 5 MB."
                );
            }

            // Verify that the uploaded file is an image
            $imageInfo = getimagesize($file["tmp_name"]);

            if ($imageInfo === false) {
                throw new RuntimeException("Invalid image file.");
            }

            // Allow specific image formats
            $allowedTypes = [
                IMAGETYPE_JPEG => "jpg",
                IMAGETYPE_PNG  => "png",
                IMAGETYPE_WEBP => "webp",
                IMAGETYPE_GIF  => "gif"
            ];

            $imageType = $imageInfo[2];

            if (!isset($allowedTypes[$imageType])) {
                throw new RuntimeException(
                    "Only JPG, PNG, WEBP and GIF images are allowed."
                );
            }

            // Physical directory on the server
            $uploadDirectory = __DIR__ . "/img/";

            if (
                !is_dir($uploadDirectory) &&
                !mkdir($uploadDirectory, 0755, true) &&
                !is_dir($uploadDirectory)
            ) {
                throw new RuntimeException(
                    "Could not create image directory."
                );
            }

            // Generate a unique filename
            $fileName = bin2hex(random_bytes(16))
                . "."
                . $allowedTypes[$imageType];

            $destination = $uploadDirectory . $fileName;

            if (!move_uploaded_file($file["tmp_name"], $destination)) {
                throw new RuntimeException(
                    "Could not save the uploaded image."
                );
            }

            // Relative path saved in the database
            $PhotoURL = "img/" . $fileName;
        }

        // Create product
        $ProductModel = new Products($pdo);

        $ProductModel->CreateProduct(
            (int) $CategorieID,
            $PhotoURL,
            $ProductName,
            $Description,
            $Price,
            (int) $Quantity
        );

        header("Location: products.php");
        exit;

    } catch (Throwable $e) {

        // Remove uploaded image if creating the product failed
        if ($PhotoURL !== null) {
            $savedImage = __DIR__ . "/" . $PhotoURL;

            if (is_file($savedImage)) {
                unlink($savedImage);
            }
        }

        error_log($e->getMessage());

        exit("Something went wrong. Please try again.");
    }
}


?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />



    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />

    <link
        href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap"
        rel="stylesheet" />

    <link rel="stylesheet" href="css/variables.css" />
    <link rel="stylesheet" href="css/global.css" />
    <link rel="stylesheet" href="css/components.css" />
    <link rel="stylesheet" href="css/pages/Registration.css" />
</head>

<body>
    <main class="registration-card">
        <a class="back-link" href="products.php"> &larr; Back to Products </a>

        <header class="registration-header">
            <h1>Add New Product</h1>
            <p>Fill in the information below to add a new product.</p>
        </header>

        <form action="create_products.php" method="POST" class="registration-form" enctype="multipart/form-data">
            <div class="form-group">
                <label class="form-label" for="product_name">Product Name</label>
                <input class="form-control" type="text" id="product_name" name="product_name"
                    placeholder="Enter product name" required />
            </div>

            <div class="form-group">
                <label class="form-label" for="categorie">Choose a categorie :</label>
                <select name="categorie_id" id="categorie_id" class="form-control">
                    <option value="">--Please choose an option--</option>
                    <?php foreach($categories as $categorie) :?>
                    <option value=<?= (string)htmlspecialchars($categorie -> CategorieID)?>>
                        <?= htmlspecialchars($categorie -> CategorieName)?></option>

                    <?php endforeach;?>

                </select>
            </div>

            <div class="form-group">
                Select image to upload:
                <input type="file" name="fileToUpload" id="fileToUpload">
            </div>

            <div class="form-group">
                <label class="form-label" for="description">Description</label>
                <textarea class="form-control" id="description" name="description"
                    placeholder="Enter product description" required></textarea>
            </div>

            <div class="form-group">
                <label class="form-label" for="price">Price ($)</label>
                <input class="form-control" type="number" id="price" name="price" placeholder="Enter product price"
                    min="0" step="0.01" required />
            </div>

            <div class="form-group">
                <label class="form-label" for="quantity">Quantity</label>
                <input class="form-control" type="number" id="quantity" name="quantity"
                    placeholder="Enter product quantity" min="0" step="1" required />
            </div>

            <button type="submit" class="btn btn-primary register-btn">
                Add Product
            </button>
        </form>
    </main>
</body>

</html>