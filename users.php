<?php

require_once "db.php";
session_start();


// Protect the page: only logged-in users can access it
if (!isset($_SESSION["id"])) {
    header("Location: index.php");
    exit;
}

// Retrieve all registered users without passwords
$sql = "SELECT id, fullName, username, email
        FROM `user`
        ORDER BY id DESC";

$stmt = $pdo->query($sql);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Users Page</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="css/variables.css">
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/components.css">
    <link rel="stylesheet" href="css/pages/dashboard.css" />
    <link rel="stylesheet" href="css/pages/users.css" />
</head>

<body>
    <div class="app-layout">
      <?php include "includes/sidebar.php"; ?>

      <div class="app-content">
    <main class="div-container">

        <h1>Registered Users</h1>

        <p>
            Welcome,
            <?= htmlspecialchars($_SESSION["fullName"]) ?>
        </p>

        <p>Total Users: <?= count($users) ?></p>

        <p>
            <a href="create_user.php">+ Add New User</a>
        </p>

        <?php  if (!empty($users)):  ?>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Full Name</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td>
                        <?= htmlspecialchars((string)$user["id"]) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($user["fullName"]) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($user["username"]) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($user["email"]) ?>
                    </td>
                    <td>
                        <a class="btn btn-secondary btn-sm" href="edit_user.php?id=<?= (int) $user["id"] ?>">
                            Edit
                        </a>

                        <form action="delete_user.php" method="POST"
                            onsubmit="return confirm('Are you sure you want to delete this user?');">

                            <input type="hidden" name="id" value="<?= (int) $user['id'] ?>">

                            

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

        <p>No registered users found.</p>

        <?php endif; ?>

    </main>
      </div>
    </div>

</body>

</html>