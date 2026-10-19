<?php
require_once "db.php";

session_start();

if (!isset($_SESSION["id"])) {
    header("Location: index.php");
    exit;
}


$sql = "SELECT `CategorieID`, `CategorieName` FROM `categories`;";

$stmt = $pdo->query($sql);
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>categories Management</title>

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
      <h1>categories</h1>

      <p>Welcome, <?= htmlspecialchars($_SESSION["fullName"]) ?></p>

      <p>Total categories: <?= count($categories) ?></p>

      <p>
        <a href="create_categories.php">+ Add New categorie</a>
      </p>

      <?php if (!empty($categories)): ?>

      <table>
        <thead>
          <tr>
            <th>CategorieID</th>
            <th>CategorieName</th>
            <th>Actions</th>
          </tr>
        </thead>

        <tbody>
          <?php foreach ($categories as $categorie): ?>
          <tr>
            <td><?= htmlspecialchars((string)$categorie["CategorieID"]) ?></td>

            <td><?=htmlspecialchars((string)$categorie["CategorieName"])?></td>

            <td>
              <a
                class="btn btn-secondary btn-sm"
                href="edit_categorie.php?id=<?= (int)$categorie['CategorieID'] ?>"
              >
                Edit
              </a>

              <form
                action="delete_categorie.php"
                method="POST"
                onsubmit="return confirm('Are you sure you want to delete this categorie?');"
              >
                <input type="hidden" name="id" value="<?= (int)$categorie['CategorieID'] ?>" />

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

      <p>No categories found.</p>

      <?php endif; ?>
    </main>
      </div>
    </div>
  </body>
</html>
