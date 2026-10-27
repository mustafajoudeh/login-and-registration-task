<?php
declare(strict_types=1);

require_once __DIR__ . "/db.php";

class Products
{
    private PDO $pdo;

    public ?int $id;
    public ?int $CategorieID;
    public ?string $CategorieName;
    public ?string $PhotoURL;
    public string $ProductName;
    public ?string $Description;
    public float $Price;
    public int $Quantity;
    public DateTime $Createddate;

    public function __construct(
        PDO $pdo,
        ?int $id = null,
        ?int $CategorieID = null,
        ?string $PhotoURL = null,
        string $ProductName = "",
        ?string $Description = null,
        float $Price = 0.0,
        int $Quantity = 0,
        ?DateTime $Createddate = null,
        ?string $CategorieName = null
    ) {
        $this->pdo = $pdo;
        $this->id = $id;
        $this->CategorieID = $CategorieID;
        $this->CategorieName = $CategorieName;
        $this->PhotoURL = $PhotoURL;
        $this->ProductName = $ProductName;
        $this->Description = $Description;
        $this->Price = $Price;
        $this->Quantity = $Quantity;
        $this->Createddate = $Createddate ?? new DateTime();
    }

    // READ: Get all products with categories names
    public function GetAllProducts(): array
    {
        $sql = "SELECT
                    p.id,
                    p.CategorieID,
                    c.CategorieName,
                    p.PhotoURL,
                    p.`Product name`,
                    p.Description,
                    p.Price,
                    p.Quantity,
                    p.`Created date`
                FROM product p
                LEFT JOIN categories c
                    ON p.CategorieID = c.CategorieID";

        $stmt = $this->pdo->query($sql);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $products = [];

        foreach ($rows as $row) {
            $products[] = new self(
                $this->pdo,
                (int) $row['id'],
                $row['CategorieID'] !== null
                    ? (int) $row['CategorieID']
                    : null,
                $row['PhotoURL'],
                $row['Product name'],
                $row['Description'],
                (float) $row['Price'],
                (int) $row['Quantity'],
                new DateTime($row['Created date']),
                $row['CategorieName'] ?? null
            );
        }

        return $products;
    }

    // READ: Get one product by ID with its categories name
    public function GetProductById(int $id): ?self
    {
        $sql = "SELECT
                    p.id,
                    p.CategorieID,
                    c.CategorieName,
                    p.PhotoURL,
                    p.`Product name`,
                    p.Description,
                    p.Price,
                    p.Quantity,
                    p.`Created date`
                FROM product p
                LEFT JOIN categories c
                    ON p.CategorieID = c.CategorieID
                WHERE p.id = :id";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            'id' => $id
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
        }

        return new self(
            $this->pdo,
            (int) $row['id'],
            $row['CategorieID'] !== null
                ? (int) $row['CategorieID']
                : null,
            $row['PhotoURL'],
            $row['Product name'],
            $row['Description'],
            (float) $row['Price'],
            (int) $row['Quantity'],
            new DateTime($row['Created date']),
            $row['CategorieName'] ?? null
        );
    }

    // CREATE: Add a new product
    public function CreateProduct(
        int $CategorieID,
        ?string $PhotoURL,
        string $ProductName,
        ?string $Description,
        float $Price,
        int $Quantity
    ): int {
        $sql = "INSERT INTO product (
                    CategorieID,
                    PhotoURL,
                    `Product name`,
                    Description,
                    Price,
                    Quantity,
                    `Created date`
                ) VALUES (
                    :CategorieID,
                    :PhotoURL,
                    :ProductName,
                    :Description,
                    :Price,
                    :Quantity,
                    :Createddate
                )";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            'CategorieID' => $CategorieID,
            'PhotoURL' => $PhotoURL,
            'ProductName' => $ProductName,
            'Description' => $Description,
            'Price' => $Price,
            'Quantity' => $Quantity,
            'Createddate' => date('Y-m-d H:i:s')
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    // UPDATE: Update product details
    public function UpdateProduct(
        int $id,
        int $CategorieID,
        ?string $PhotoURL,
        string $ProductName,
        ?string $Description,
        float $Price,
        int $Quantity
    ): int {
        $sql = "UPDATE product
                SET
                    CategorieID = :CategorieID,
                    PhotoURL = :PhotoURL,
                    `Product name` = :ProductName,
                    Description = :Description,
                    Price = :Price,
                    Quantity = :Quantity
                WHERE id = :id";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            'CategorieID' => $CategorieID,
            'PhotoURL' => $PhotoURL,
            'ProductName' => $ProductName,
            'Description' => $Description,
            'Price' => $Price,
            'Quantity' => $Quantity,
            'id' => $id
        ]);

        return $stmt->rowCount();
    }

    // DELETE: Delete a product
    public function DeleteProduct(int $id): bool
    {
        $sql = "DELETE FROM product WHERE id = :id";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            'id' => $id
        ]);

        return $stmt->rowCount() > 0;
    }
}