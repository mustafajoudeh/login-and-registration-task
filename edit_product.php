
<?php

require_once "db.php";

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!isset($_SESSION["id"])) {
    header("Location: index.php");
    exit;
}

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    exit("Invalid product ID.");
}

// Get product
$sql = "SELECT id, `Product name`, `Description`,
               `Price`, `Quantity`, `Created date`
        FROM `product`
        WHERE id = :id";

$stmt = $pdo->prepare($sql);
$stmt->execute(["id" => $id]);

$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    exit("Product not found.");
}

$errors = [];

$productName = $product["Product name"];
$description = $product["Description"];
$price = $product["Price"];
$quantity = $product["Quantity"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $productName = trim($_POST["ProductName"] ?? "");
    $description = trim($_POST["Description"] ?? "");
    $price = trim($_POST["Price"] ?? "");
    $quantity = trim($_POST["Quantity"] ?? "");

    if ($productName === "") {
        $errors[] = "Product name is required.";
    }

    if (!is_numeric($price) || (float)$price < 0) {
        $errors[] = "Price must be a non-negative number.";
    }

    $validatedQuantity = filter_var(
        $quantity,
        FILTER_VALIDATE_INT
    );

    if ($validatedQuantity === false || $validatedQuantity < 0) {
        $errors[] = "Quantity must be a non-negative integer.";
    }

    if (empty($errors)) {
        try {
            $sql = "UPDATE `product`
                    SET `Product name` = :name,
                        `Description` = :description,
                        `Price` = :price,
                        `Quantity` = :quantity
                    WHERE id = :id";

            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                "name" => $productName,
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

function e($value): string
{
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES | ENT_SUBSTITUTE,
        "UTF-8"
    );
}
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>Edit Product | Jordan Smart Travel</title>

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />

    <link
      href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap"
      rel="stylesheet"
    />

    <link rel="stylesheet" href="css/variables.css" />
    <link rel="stylesheet" href="css/global.css" />
    <link rel="stylesheet" href="css/components.css" />
    <link rel="stylesheet" href="css/pages/Registration.css" />
  </head>

  <body>
    <main class="registration-card">
      <a class="back-link" href="products.php"> &larr; Back to Products </a>

      <header class="registration-header">
        <span class="brand-label">JORDAN SMART TRAVEL</span>
        <h1>Edit Product</h1>
        <p>Update the product information.</p>
      </header>

      <?php foreach ($errors as $error): ?>
      <p class="alert alert-error" role="alert"><?= e($error) ?></p>
      <?php endforeach; ?>

      <form
        action="edit_product.php?id=<?= (int)$id ?>"
        method="POST"
        class="registration-form"
      >
        <div class="form-group">
          <label class="form-label" for="ProductName">Product Name</label>
          <input
            class="form-control"
            type="text"
            id="ProductName"
            name="ProductName"
            value="<?= e($productName) ?>"
            required
          />
        </div>

        <div class="form-group">
          <label class="form-label" for="Description">Description</label>
          <textarea
            class="form-control"
            id="Description"
            name="Description"
          ><?= e($description) ?></textarea>
        </div>

        <div class="form-group">
          <label class="form-label" for="Price">Price ($)</label>
          <input
            class="form-control"
            type="number"
            id="Price"
            name="Price"
            min="0"
            step="0.01"
            value="<?= e($price) ?>"
            required
          />
        </div>

        <div class="form-group">
          <label class="form-label" for="Quantity">Quantity</label>
          <input
            class="form-control"
            type="number"
            id="Quantity"
            name="Quantity"
            min="0"
            step="1"
            value="<?= e($quantity) ?>"
            required
          />
        </div>

        <button type="submit" class="btn btn-primary register-btn">
          Update Product
        </button>
      </form>
    </main>
  </body>
</html>
