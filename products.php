<?php
require_once "db.php";

session_start();

if (!isset($_SESSION["id"])) {
    header("Location: index.php");
    exit;
}


$sql = "SELECT 
    p.`id`, 
    c.`CategorieName` AS `Categorie`, 
    p.`Product name`, 
    p.`Description`, 
    p.`Price`, 
    p.`Quantity`, 
    p.`Created date` 
FROM `product` p
INNER JOIN `categories` c ON p.`CategorieID` = c.`CategorieID`;";

$stmt = $pdo->query($sql);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>Products Management</title>

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />

    <link
      href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap"
      rel="stylesheet"
    />

    <link rel="stylesheet" href="css/variables.css" />
    <link rel="stylesheet" href="css/global.css" />
    <link rel="stylesheet" href="css/components.css" />
    <link rel="stylesheet" href="css/pages/dashboard.css" />
    <link rel="stylesheet" href="css/pages/users.css" />
  </head>

  <body>
    <div class="app-layout">
      <?php include "includes/sidebar.php"; ?>

      <div class="app-content">

    <main class="div-container">
      <h1>Products</h1>

      <p>Welcome, <?= htmlspecialchars($_SESSION["fullName"]) ?></p>

      <p>Total Products: <?= count($products) ?></p>

      <p>
        <a href="create_products.php">+ Add New Product</a>
      </p>

      <?php if (!empty($products)): ?>

      <table>
        <thead>
          <tr>
            <th>Product Name</th>
            <th>Categorie</th>
            <th>Description</th>
            <th>Price</th>
            <th>Quantity</th>
            <th>Created Date</th>
            <th>Actions</th>
          </tr>
        </thead>

        <tbody>
          <?php foreach ($products as $product): ?>
          <tr>
            <td><?= htmlspecialchars((string)$product["Product name"]) ?></td>

            <td><?=htmlspecialchars((string)$product["Categorie"])?></td>

            <td><?= htmlspecialchars((string)$product["Description"]) ?></td>

            <td><?= htmlspecialchars((string)$product["Price"]) ?></td>

            <td><?= htmlspecialchars((string)$product["Quantity"]) ?></td>

            <td><?= htmlspecialchars((string)$product["Created date"]) ?></td>

            <td>
              <a
                class="btn btn-secondary btn-sm"
                href="edit_product.php?id=<?= (int)$product['id'] ?>"
              >
                Edit
              </a>

              <form
                action="delete_product.php"
                method="POST"
                onsubmit="return confirm('Are you sure you want to delete this product?');"
              >
                <input type="hidden" name="id" value="<?= (int)$product['id'] ?>" />

                <button type="submit" class="btn btn-secondary btn-sm btn-delete">
                  Delete
                </button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <?php else: ?>

      <p>No products found.</p>

      <?php endif; ?>
    </main>
      </div>
    </div>
  </body>
</html>
