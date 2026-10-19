<?php
$currentPage = basename($_SERVER["SCRIPT_NAME"]);

$menuItems = [
    "dashboard.php" => "Dashboard",
    "users.php"     => "Users",
    "products.php"  => "Products",
    "categories.php" => "Categorie"
];
?>
<aside class="sidebar">
    <div class="sidebar__brand">
        <span class="brand-label">Login System</span>
    </div>

    <nav class="sidebar__nav">
        <?php foreach ($menuItems as $file => $label): ?>
            <a
                class="sidebar__link<?= $currentPage === $file ? " sidebar__link--active" : "" ?>"
                href="<?= $file ?>"
            ><?= $label ?></a>
        <?php endforeach; ?>
    </nav>

    <div class="sidebar__footer">
        <p class="sidebar__user"><?= htmlspecialchars($_SESSION["fullName"]) ?></p>
        <a class="btn btn-secondary btn-sm btn-block" href="logout.php">Logout</a>
    </div>
</aside>
