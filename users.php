
<?php

require_once "db.php";
session_start();


// Protect the page: only logged-in users can access it
if (!isset($_SESSION["id"])) {
    echo"mlmlmlmlmlml";
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

    <link rel="stylesheet" href="css/variables.css">
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/components.css">
</head>

<body>

    <main class="div-container">

        <h1>Registered Users</h1>

        <p>
            Welcome,
            <?= htmlspecialchars($_SESSION["fullName"]) ?>
        </p>

        <p>Total Users: <?= count($users) ?></p>

        <?php if (count($users) > 0): ?>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>Username</th>
                        <th>Email</th>
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
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

        <?php else: ?>

            <p>No registered users found.</p>

        <?php endif; ?>

        <p>
            <a href="logout.php">Logout</a>
        </p>

    </main>

</body>
</html>
