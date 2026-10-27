<?php
require_once "db.php";
require_once "ProductsClass.php";

session_start();

if (!isset($_SESSION["id"])) {
    header("Location: index.php");
    exit;
}

$ProductModle = new Products($pdo);
$products = $ProductModle->GetAllProducts();
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
        rel="stylesheet" />

    <link rel="stylesheet" href="css/variables.css" />
    <link rel="stylesheet" href="css/global.css" />
    <link rel="stylesheet" href="css/components.css" />
    <link rel="stylesheet" href="css/pages/dashboard.css" />
    <link rel="stylesheet" href="css/pages/users.css" />
    <link rel="stylesheet" href="css/pages/Products.css" />
</head>

<body>
    <div class="app-layout">

        <?php include "includes/sidebar.php"; ?>

        <div class="app-content">

            <main class="div-container products-page">

                <!-- Page Header -->
                <div class="products-header">
                    <div>
                        <h1>Products</h1>

                        <p class="products-subtitle">
                            Manage your products, prices and stock.
                        </p>
                    </div>

                    <a href="create_products.php" class="products-add-btn">
                        + Add New Product
                    </a>
                </div>

                <!-- Welcome Message -->
                <p class="products-welcome">
                    Welcome,
                    <?= htmlspecialchars((string) $_SESSION["fullName"]) ?>
                </p>

                <!-- Products Summary -->
                <section class="products-summary">
                    <div class="products-summary-card">
                        <span class="products-summary-label">
                            Total Products
                        </span>

                        <strong class="products-summary-value">
                            <?= count($products) ?>
                        </strong>
                    </div>
                </section>

                <!-- Products Table -->
                <?php if (!empty($products)): ?>

                <div class="products-table-wrap">

                    <table class="products-table">

                        <thead>
                            <tr>
                                <th>Product Photo</th>
                                <th>Product Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Actions</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php foreach ($products as $product): ?>

                            <tr>
                                <td>
                                    <img class="product-thumbnail"
                                        src="<?= htmlspecialchars((string) ($product->PhotoURL ?? '')) ?>"
                                        alt="<?= htmlspecialchars($product->ProductName) ?>" />
                                </td>

                                <td class="product-name">
                                    <?= htmlspecialchars($product->ProductName) ?>
                                </td>



                                <td>
                                    <?= htmlspecialchars($product->CategorieName ?? 'Uncategorized') ?>
                                </td>



                                <td class="product-price">
                                    <?= htmlspecialchars((string) $product->Price) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars((string) $product->Quantity) ?>
                                </td>



                                <td>
                                    <div class="product-actions">

                                        <a class="products-edit-btn"
                                            href="edit_product.php?id=<?= (int) $product->id ?>">
                                            Edit
                                        </a>

                                        <form action="delete_product.php" method="POST"
                                            onsubmit="return confirm('Are you sure you want to delete this product?');">
                                            <input type="hidden" name="id" value="<?= (int) $product->id ?>" />

                                            <button type="submit" class="products-delete-btn">
                                                Delete
                                            </button>
                                        </form>

                                    </div>
                                </td>

                            </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

                <?php else: ?>

                <p class="products-empty">
                    No products found.
                </p>

                <?php endif; ?>

            </main>

        </div>
    </div>
</body>

</html>