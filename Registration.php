
<?php
require_once "db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $fullName = trim($_POST["fullName"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirmPassword = $_POST["confirmPassword"] ?? "";
    $terms = isset($_POST["terms"]);

    if (
    $fullName === "" ||
    $email === "" ||
    $username === "" ||
    $password === "" ||
    !$terms
) {
    exit("Please complete all required fields.");
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    exit("Please enter a valid email address.");
}

if (strlen($password) < 8) {
    exit("Password must be at least 8 characters.");
}

if ($password !== $confirmPassword) {
    exit("Passwords do not match.");
}

 // Hash the password before storing it
    $passwordHash = password_hash(
        $password,
        PASSWORD_DEFAULT
    );
    
try {
   

    // Insert user data into the database
    $sql = "INSERT INTO `user`
            (`fullName`, `username`, `password`, `email`)
            VALUES
            (:fullName, :username, :password, :email)";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        "fullName" => $fullName,
        "username" => $username,
        "password" => $passwordHash,
        "email" => $email
    ]);

    echo "Account created successfully!";

} catch (PDOException $e) {
    error_log($e->getMessage());

    if ($e->getCode() === "23000") {
        exit("This email or username may already exist.");
    }

    exit("Something went wrong. Please try again.");
}


}


?> 

<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>Create Account | Jordan Smart Travel</title>

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
      <a class="back-link" href="index.php"> &larr; Back to Home </a>

      <header class="registration-header">
        <span class="brand-label">JORDAN SMART TRAVEL</span>
        <h1>Create Your Account</h1>
        <p>Start discovering Jordan your way.</p>
      </header>

      <form action="Registration.php" method="post" class="registration-form">
        <div class="form-group">
          <label class="form-label" for="fullName">Full Name</label>
          <input
            class="form-control"
            type="text"
            id="fullName"
            name="fullName"
            placeholder="Enter your full name"
            autocomplete="name"
            required
          />
        </div>

        <div class="form-group">
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
        </div>

        <div class="form-group">
          <label class="form-label" for="username">Username</label>
          <input
            class="form-control"
            type="text"
            id="username"
            name="username"
            placeholder="Choose a username"
            autocomplete="username"
            required
          />
        </div>

        <div class="form-group">
          <label class="form-label" for="password">Password</label>
          <input
            class="form-control"
            type="password"
            id="password"
            name="password"
            placeholder="Create a password"
            autocomplete="new-password"
            minlength="8"
            required
          />
          <small class="field-hint"> Use at least 8 characters. </small>
        </div>

        <div class="form-group">
          <label class="form-label" for="confirmPassword">
            Confirm Password
          </label>
          <input
            class="form-control"
            type="password"
            id="confirmPassword"
            name="confirmPassword"
            placeholder="Enter your password again"
            autocomplete="new-password"
            minlength="8"
            required
          />
        </div>

        <label class="terms-label" for="terms">
          <input type="checkbox" id="terms" name="terms" required />
          <span>
            I agree to the <a href="#">Terms of Service</a> and
            <a href="#">Privacy Policy</a>.
          </span>
        </label>

        <button type="submit" class="btn btn-primary register-btn">
          Create Account
        </button>

        <p class="login-prompt">
          Already have an account?
          <a href="index.php">Log In</a>
        </p>
      </form>
    </main>
  </body>
</html>
