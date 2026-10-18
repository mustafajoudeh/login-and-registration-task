<?php

require_once "db.php";
session_start();

// Protect the page: only logged-in users can access it
if (!isset($_SESSION["id"])) {
    header("Location: index.php");
    exit;
}

$totalUsers = (int) $pdo->query("SELECT COUNT(*) FROM `user`")->fetchColumn();

$totalProducts = (int) $pdo->query("SELECT COUNT(*) FROM `product`")->fetchColumn();

$inventoryValue = (float) $pdo->query(
    "SELECT COALESCE(SUM(`Price` * `Quantity`), 0) FROM `product`"
)->fetchColumn();

$recentUsers = $pdo->query(
    "SELECT fullName, email FROM `user` ORDER BY id DESC LIMIT 5"
)->fetchAll(PDO::FETCH_ASSOC);

$recentProducts = $pdo->query(
    "SELECT `Product name`, `Price` FROM `product` ORDER BY id DESC LIMIT 5"
)->fetchAll(PDO::FETCH_ASSOC);

?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>Dashboard</title>

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
  </head>

  <body>
    <div class="app-layout">
      <?php include "includes/sidebar.php"; ?>

      <main class="app-content">
        <div class="dashboard">
          <header class="dashboard__header">
            <h1>Dashboard</h1>
            <p>Welcome back, <?= htmlspecialchars($_SESSION["fullName"]) ?>.</p>
          </header>

          <section class="stats-grid">
            <article class="card stat-card">
              <span class="stat-card__label">Total Users</span>
              <span class="stat-card__value"><?= $totalUsers ?></span>
            </article>

            <article class="card stat-card">
              <span class="stat-card__label">Total Products</span>
              <span class="stat-card__value"><?= $totalProducts ?></span>
            </article>

            <article class="card stat-card">
              <span class="stat-card__label">Inventory Value</span>
              <span class="stat-card__value">$<?= number_format($inventoryValue, 2) ?></span>
            </article>
          </section>

          <section class="dashboard__panels">
            <article class="card">
              <div class="card-header">
                <h2 class="card-title">Recent Users</h2>
                <a class="link" href="users.php">View all</a>
              </div>
              <?php if (!empty($recentUsers)): ?>
              <ul class="panel-list">
                <?php foreach ($recentUsers as $u): ?>
                <li>
                  <span><?= htmlspecialchars($u["fullName"]) ?></span>
                  <span><?= htmlspecialchars($u["email"]) ?></span>
                </li>
                <?php endforeach; ?>
              </ul>
              <?php else: ?>
              <p class="panel-empty">No users yet.</p>
              <?php endif; ?>
            </article>

            <article class="card">
              <div class="card-header">
                <h2 class="card-title">Recent Products</h2>
                <a class="link" href="products.php">View all</a>
              </div>
              <?php if (!empty($recentProducts)): ?>
              <ul class="panel-list">
                <?php foreach ($recentProducts as $p): ?>
                <li>
                  <span><?= htmlspecialchars((string)$p["Product name"]) ?></span>
                  <span>$<?= htmlspecialchars((string)$p["Price"]) ?></span>
                </li>
                <?php endforeach; ?>
              </ul>
              <?php else: ?>
              <p class="panel-empty">No products yet.</p>
              <?php endif; ?>
            </article>
          </section>
        </div>
      </main>
    </div>
  </body>
</html>
