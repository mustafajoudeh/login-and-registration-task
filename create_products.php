<?php
require_once "db.php";
session_start();

if($_SERVER["REQUEST_METHOD"] === "POST")
{
    $ProductName = trim($_POST["product_name"]??"");
    $Description = trim($_POST["description"]??"");
    $Price = $_POST["price"] ?? 0.0;
    $Quantity =$_POST["quantity"]??0;
    $CreatedDate =  date("Y-m-d");
    

    
    if (
    $ProductName === "" ||
    $Description === "" ||
    $Price === 0.0 ||
    $Quantity === 0 
    ) 
    {
    exit("Please complete all required fields.");
    }

try{
    $sql = "INSERT INTO `product`( `Product name`, `Description`, `Price`
    , `Quantity`, `Created date`) 
    VALUES (:ProductName,:Description,:Price,:Quantity,:CreatedDate)";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        "ProductName" => $ProductName,
        "Description" => $Description,
        "Price" => $Price,
        "Quantity" => $Quantity,
        "CreatedDate"=> $CreatedDate,
    ]);

    header("Location: products.php");
            exit;
}
catch(PDOException $e)
{
 error_log($e->getMessage());
 exit("Something went wrong. Please try again.");
}

}

?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>Add New Product | Jordan Smart Travel</title>

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
        <h1>Add New Product</h1>
        <p>Fill in the information below to add a new product.</p>
      </header>

      <form action="create_products.php" method="POST" class="registration-form">
        <div class="form-group">
          <label class="form-label" for="product_name">Product Name</label>
          <input
            class="form-control"
            type="text"
            id="product_name"
            name="product_name"
            placeholder="Enter product name"
            required
          />
        </div>

        <div class="form-group">
          <label class="form-label" for="description">Description</label>
          <textarea
            class="form-control"
            id="description"
            name="description"
            placeholder="Enter product description"
            required
          ></textarea>
        </div>

        <div class="form-group">
          <label class="form-label" for="price">Price ($)</label>
          <input
            class="form-control"
            type="number"
            id="price"
            name="price"
            placeholder="Enter product price"
            min="0"
            step="0.01"
            required
          />
        </div>

        <div class="form-group">
          <label class="form-label" for="quantity">Quantity</label>
          <input
            class="form-control"
            type="number"
            id="quantity"
            name="quantity"
            placeholder="Enter product quantity"
            min="0"
            step="1"
            required
          />
        </div>

        <button type="submit" class="btn btn-primary register-btn">
          Add Product
        </button>
      </form>
    </main>
  </body>
</html>
