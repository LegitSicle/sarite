<?php
namespace sarite\data;

use PDO;
use PDOException;
use PDOStatement;
use function sprintf;

class Database {
    public readonly PDO $pdo;

    public function __construct(
        private string $root,
        array $config
    ) {
        $dsn = "mysql:host={$config["host"]};port={$config["port"]};dbname={$config["name"]}";
        $user = $config["user"] ?? "";
        $password = $config["password"] ?? "";
        $this->pdo = new PDO($dsn, $user, $password);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Used for setting up the user and database specified in the `.env` file.
     * @param array $config
     * @return void
     */
    public static function initialize(array $config) {
        try {
            $user = "'{$config["user"]}'@'{$config["host"]}'";
            $pdo = new PDO("mysql:host={$config["host"]}", "root", "", [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);

            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$config["name"]}`;");
            echo "[SUCCESS] Database '{$config["name"]}' created.\n";

            $pdo->exec("CREATE USER IF NOT EXISTS $user IDENTIFIED BY '{$config["password"]}';");
            $pdo->exec("ALTER USER $user IDENTIFIED BY '{$config["password"]}';");
            $pdo->exec("GRANT ALL PRIVILEGES ON `{$config["name"]}`.* TO $user;");
            $pdo->exec("FLUSH PRIVILEGES;");
            echo "[SUCCESS] MySQL user $user created and granted access to '{$config["name"]}'.\n";
        } catch (PDOException $e) {
            echo "[ERROR] Database Initialization Failed: " . $e->getMessage() . "\n";
            exit(1);
        }
    }

    /**
     * Applies all migration `.sql` files that have not been commited to the Migrations table yet.
     * @return void
     */
    public function migrate(): void {
        $newMigrations = [];
        $this->createMigrations();
        $applied = $this->getApplied();
        $files = scandir($this->root . DIRECTORY_SEPARATOR . "migrations");
        $toApply = array_diff($files, $applied);
        echo "[INFO] Committing migrations...\n";
        foreach ($toApply as $migration) {
            if ($migration === "." || $migration === "..") continue;
            $sql = file_get_contents($this->root . DIRECTORY_SEPARATOR . "migrations" . DIRECTORY_SEPARATOR . $migration);
            $this->log("Applying $migration");
            $this->pdo->exec($sql);
            $newMigrations[] = $migration;
        }

        if (!empty($newMigrations)) {
            $this->saveMigrations($newMigrations);
        } else {
            $this->log("All Migrations Have Been Applied");
        }
    }

    public function selectOne(string $tableName, array $where): PDOStatement {
        $attributes = array_keys($where);
        $whereSQL = implode("AND ", array_map(fn($attr) => "$attr = :$attr", $attributes));
        $statement = $this->pdo->prepare("SELECT 1 FROM $tableName WHERE $whereSQL");
        foreach ($where as $attr => $value) {
            $statement->bindValue(":$attr", $value);
        }
        $statement->execute();
        return $statement;
    }

    public function selectAll(string $tableName, array $where): PDOStatement {
        $attributes = array_keys($where);
        $whereSQL = implode("AND ", array_map(fn($attr) => "$attr = :$attr", $attributes));
        $statement = $this->pdo->prepare("SELECT * FROM $tableName WHERE $whereSQL");
        foreach ($where as $attr => $value) {
            $statement->bindValue(":$attr", $value);
        }
        $statement->execute();
        return $statement;
    }

    public function insertInto(string $tableName, array $columnValues): PDOStatement {
        $attributes = array_keys($columnValues);
        $params = array_map(fn($attr) => ":$attr", $attributes);
        $sql = sprintf(
            "INSERT INTO `%s` (%s) VALUES (%s)",
            $tableName,
            implode(',', $attributes),
            implode(',', $params)
        );
        $statement = $this->pdo->prepare($sql);
        foreach ($attributes as $attribute) {
            $statement->bindValue(":$attribute", $columnValues[$attribute]);
        }
        $statement->execute();
        return $statement;
    }

    private function saveMigrations(array $migrations): void {
        $values = implode(",", array_map(fn($m) => "('$m')", $migrations));
        $statement = $this->pdo->prepare("INSERT INTO Migrations (migration) VALUES $values;");
        $statement->execute();
    }

    private function createMigrations(): void {
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS Migrations (
            id int AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=INNODB;");
    }

    private function getApplied(): array {
        $statement = $this->pdo->prepare("SELECT migration FROM Migrations");
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_COLUMN);
    }

    private function log(string $message): void {
        echo '['.date("Y-m-d H:i:s")."] - $message" . PHP_EOL;
    }
}