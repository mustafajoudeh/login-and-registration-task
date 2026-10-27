<?php
declare(strict_types=1);

require_once __DIR__ . "/db.php";

class Categorie
{
    private PDO $pdo;

    public ?int $CategorieID;
    public string $CategorieName;

    public function __construct(
        PDO $pdo,
        ?int $CategorieID = null,
        string $CategorieName = ""
    ) {
        $this->pdo = $pdo;
        $this->CategorieID = $CategorieID;
        $this->CategorieName = $CategorieName;
    }

    // READ: Get all categories
    public function GetAllCategories(): array
    {
        $sql = "SELECT CategorieID, CategorieName
                FROM categories
                ORDER BY CategorieName ASC";

        $stmt = $this->pdo->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $categories = [];

        foreach ($rows as $row) {
            $categories[] = new self(
                $this->pdo,
                (int) $row["CategorieID"],
                $row["CategorieName"]
            );
        }

        return $categories;
    }

    // READ: Get one category by ID
    public function GetCategorieById(int $id): ?self
    {
        $sql = "SELECT CategorieID, CategorieName
                FROM categories
                WHERE CategorieID = :id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(["id" => $id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
        }

        return new self(
            $this->pdo,
            (int) $row["CategorieID"],
            $row["CategorieName"]
        );
    }

    // READ: Get category name by ID
    public function GetCategorieNameById(int $id): ?string
    {
        $sql = "SELECT CategorieName
                FROM categories
                WHERE CategorieID = :id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(["id" => $id]);

        $name = $stmt->fetchColumn();

        return $name !== false ? (string) $name : null;
    }

    // Check whether a category name already exists
    public function CategorieNameExists(
        string $name,
        ?int $excludeId = null
    ): bool {
        if ($excludeId !== null) {
            $sql = "SELECT COUNT(*)
                    FROM categories
                    WHERE CategorieName = :name
                    AND CategorieID <> :id";

            $stmt = $this->pdo->prepare($sql);

            $stmt->execute([
                "name" => trim($name),
                "id" => $excludeId
            ]);
        } else {
            $sql = "SELECT COUNT(*)
                    FROM categories
                    WHERE CategorieName = :name";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(["name" => trim($name)]);
        }

        return (int) $stmt->fetchColumn() > 0;
    }

    // CREATE: Add category
    public function CreateCategorie(string $CategorieName): int
    {
        $CategorieName = trim($CategorieName);

        if ($CategorieName === "") {
            throw new InvalidArgumentException(
                "Category name is required."
            );
        }

        $sql = "INSERT INTO categories (CategorieName)
                VALUES (:name)";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(["name" => $CategorieName]);

        return (int) $this->pdo->lastInsertId();
    }

    // UPDATE: Update category name
    public function UpdateCategorie(
        int $id,
        string $CategorieName
    ): int {
        $CategorieName = trim($CategorieName);

        if ($CategorieName === "") {
            throw new InvalidArgumentException(
                "Category name is required."
            );
        }

        $sql = "UPDATE categories
                SET CategorieName = :name
                WHERE CategorieID = :id";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            "name" => $CategorieName,
            "id" => $id
        ]);

        return $stmt->rowCount();
    }

    // Check whether products use this category
    public function HasProducts(int $id): bool
    {
        $sql = "SELECT COUNT(*)
                FROM product
                WHERE CategorieID = :id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(["id" => $id]);

        return (int) $stmt->fetchColumn() > 0;
    }

    // DELETE: Delete category
    public function DeleteCategorie(int $id): int
    {
        $sql = "DELETE FROM categories
                WHERE CategorieID = :id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(["id" => $id]);

        return $stmt->rowCount();
    }
}