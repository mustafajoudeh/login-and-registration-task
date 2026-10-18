<?php


require_once "db.php";
session_start();

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($email === "" || $password === "") {
        $error = "Please enter your email and password.";
    } else {

        $sql = "SELECT id, fullName, username, email, password
                FROM `user`
                WHERE email = :email
                LIMIT 1";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(["email" => $email]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify(
            $password,
            $user["password"]
        )) {

            session_regenerate_id(true);

            $_SESSION["id"] = $user["id"];
            $_SESSION["fullName"] = $user["fullName"];
            $_SESSION["username"] = $user["username"];
            $_SESSION["email"] = $user["email"];

            header("Location: dashboard.php");
            exit;

        } else {
            $error = "Invalid email or password.";
        }
    }
}


?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login page</title>

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />

    <link
      href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap"
      rel="stylesheet"
    />

    <link rel="stylesheet" href="css/variables.css" />
    <link rel="stylesheet" href="css/global.css" />
    <link rel="stylesheet" href="css/components.css" />

    <link rel="stylesheet" href="css/pages/Login.css" />
  </head>
  <body>
    <div class="div-container">
      <h1>Login</h1>
      <?php if ($error !== ""): ?>
        <p class="alert alert-error" role="alert"><?= htmlspecialchars($error) ?></p>
      <?php endif; ?>
      <form class="form-group" action="index.php" method="post">
        <label class="form-label" for="email">Email Address</label>
          <input
            class="form-control"
            type="email"
            id="email"
            name="email"
            placeholder="you@example.com"
            autocomplete="email"
            required
          />
        <label class="form-label" for="password">Password</label>
          <input
            class="form-control"
            type="password"
            id="password"
            name="password"
            minlength="8"
            required
          />
        <a href="#">forgot password?</a>
        <input type="submit" value="Login" class="btn" />
        <p>Dont have an account? <a href="Registration.php">Sign Up</a></p>
      </form>
    </div>
  </body>
</html>
