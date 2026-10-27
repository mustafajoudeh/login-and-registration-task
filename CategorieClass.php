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
                (int) $row['CategorieID'],
                $row['CategorieName']
            );
        }

        return $categories;
    }

    // READ: Get one categories by ID
    public function GetCategorieById(int $id): ?self
    {
        $sql = "SELECT CategorieID, CategorieName
                FROM categories
                WHERE CategorieID = :CategorieID";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            'CategorieID' => $id
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
        }

        return new self(
            $this->pdo,
            (int) $row['CategorieID'],
            $row['CategorieName']
        );
    }

   public function GetCategorieNameById(int $id): ?string
{
    $sql = "SELECT CategorieName
            FROM categories
            WHERE CategorieID = :CategorieID";

    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([
        'CategorieID' => $id
    ]);

    $result = $stmt->fetchColumn();

    return $result !== false ? (string) $result : null;
}

    // CREATE: Add a new categories
    public function CreateCategorie(string $CategorieName): int
    {
        $CategorieName = trim($CategorieName);

        if ($CategorieName === '') {
            throw new InvalidArgumentException(
                'categories name cannot be empty.'
            );
        }

        $sql = "INSERT INTO categories (CategorieName)
                VALUES (:CategorieName)";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            'CategorieName' => $CategorieName
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    // UPDATE: Change categories name
    public function UpdateCategorie(
        int $id,
        string $CategorieName
    ): int {
        $CategorieName = trim($CategorieName);

        if ($CategorieName === '') {
            throw new InvalidArgumentException(
                'categories name cannot be empty.'
            );
        }

        $sql = "UPDATE categories
                SET CategorieName = :CategorieName
                WHERE CategorieID = :CategorieID";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            'CategorieName' => $CategorieName,
            'CategorieID' => $id
        ]);

        return $stmt->rowCount();
    }

    // DELETE: Remove a categories
    public function DeleteCategorie(int $id): bool
    {
        $sql = "DELETE FROM categories
                WHERE CategorieID = :CategorieID";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            'CategorieID' => $id
        ]);

        return true;
    }
}