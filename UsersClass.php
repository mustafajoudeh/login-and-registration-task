<?php
declare(strict_types=1);
require_once "db.php";

class User
{
    private PDO $pdo;
    public ?int $id ;
    public string $fullName;
    public string $username;
    public string $Password;
    public string $email;

    public function __construct(
        PDO $pdo,
        ?int $id = null,
        string $fullName = "",
        string $username = "",
        string $email = "",
        string $Password = "")
    {
        $this->pdo =$pdo;
        $this->id = $id;
        $this->fullName = $fullName;
        $this->username = $username;
        $this->email = $email;
        $this->Password = $Password;

    }

    public function GetAllUsers():array
    {
              
       $sql = "SELECT id, fullName, username, email
               FROM `user`
               ORDER BY id DESC";

        $stmt = $this->pdo->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $users = [];

        foreach ($rows as $row) {
            $users[] = new self(
                $this->pdo,
                (int) $row["id"],
                $row["fullName"],
                $row["username"],
                $row["email"]
            );
        }

        return $users;

    }

    public function CheckUser()
    {}

    public function GetUser(int $id) : ?self
    {

    $sql = "SELECT id, fullName, username, email
        FROM `user`
        WHERE id = :id
        LIMIT 1";

    $stmt = $this->pdo->prepare($sql);
    $stmt->execute(["id" => $id]);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
        }

        return new self(
            $this->pdo,
            (int) $row["id"],
            $row["fullName"],
            $row["username"],
            $row["email"]
        );

    }

    public function UpdateUser(int $id , string $fullName ,string $username,string $email,string $password=""):bool
    {
      $checkSql = "SELECT id
      FROM `user`
      WHERE (username = :username OR email = :email)
      AND id <> :id
      LIMIT 1";

      $checkStmt = $this->pdo->prepare($checkSql);

      $checkStmt->execute([
      "username" => $username,
      "email" => $email,
      "id" => $id
      ]);

    if ($checkStmt->fetch(PDO::FETCH_ASSOC) !== false) {
    throw new DomainException(
    "Username or email is already used by another account."
    );
    }

    if ($password !== "") {

    // Update details and new hashed password
    $updateSql = "UPDATE `user`
    SET fullName = :fullName,
    username = :username,
    email = :email,
    password = :password
    WHERE id = :id";

    $params = [
    "fullName" => $fullName,
    "username" => $username,
    "email" => $email,
    "password" => password_hash(
    $password,
    PASSWORD_DEFAULT
    ),
    "id" => $id
    ];

    } else {

    // Update details without changing the password
    $updateSql = "UPDATE `user`
    SET fullName = :fullName,
    username = :username,
    email = :email
    WHERE id = :id";

    $params = [
    "fullName" => $fullName,
    "username" => $username,
    "email" => $email,
    "id" => $id
    ];
    }

    $updateStmt = $this->pdo->prepare($updateSql);
    $updateStmt->execute($params);

    return true;

    }

    public function DeleteUser(int $id):bool
    {
         $sql = "DELETE FROM `user` WHERE id = :id";
         $stmt = $this->pdo->prepare($sql);
         $stmt->execute(["id" => $id]);
         return true;
    }

    public function CreateUser(string $fullName ,string $username,string $email,string $passwordHash):void
    {
// Insert user data into the database
    $sql = "INSERT INTO `user`
            (`fullName`, `username`, `password`, `email`)
            VALUES
            (:fullName, :username, :password, :email)";

    $stmt = $this->pdo->prepare($sql);

    $stmt->execute([
        "fullName" => $fullName,
        "username" => $username,
        "email" => $email,
        "password" => $passwordHash,
    ]);
    
    }

    
    
   
}