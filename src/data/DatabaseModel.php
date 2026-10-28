<?php
namespace sarite\data;

use Override;
use PDOException;
use ReflectionClass;
// ISSUE: RULES NOT ENFORCED FOR RULE_MIN;
/**
 * This extends the `Model` class and allows data to be inserted into the MySQL database.
 */
abstract class DatabaseModel extends Model {
    protected static Database $database;

    /**
     * Makes a property unable to be inserted into the database if it is already a value for another.<br>
     * `[..., SELF::RULE_REQUIRED]`
     * @var string
     */
    protected const RULE_UNIQUE = "unique";

    protected static function table(): string {
        return (new ReflectionClass(static::class))->getShortName();
    }
    
    abstract protected function attributes(): array;
    abstract public function primaryKey(): string;

    public function attemptSave(): bool {
        try {
            $tableName = $this->table();
            $attributes = $this->attributes();
            $columnValues = [];
            foreach ($attributes as $attribute) {
                $columnValues[$attribute] = $this->{$attribute};
            }
            static::$database->insertInto($tableName, $columnValues);
            $this->{$this->primaryKey()} = static::$database->pdo->lastInsertId();
            return true;
        } catch (PDOException $e) {
            echo $e->getMessage();
            return false;
        }
    }

    /**
     * Similar to `validate()` of its parent class but this allows access to the database allowing it to check values.
     * @return bool Whether there was a rule broken.
     */
    public function validate(): bool {
        $this->parseSelf(function (string $ruleName, string $attribute, mixed $param, mixed $value) {
            $this->defaultChecker($ruleName, $attribute, $param, $value);
            if ($ruleName === self::RULE_UNIQUE) {
                $tableName = $this->table();
                $statement = static::$database->selectOne($tableName, [$attribute => $value]);
                if ($statement->fetchObject()) {
                    $this->addErrorForRule($attribute, self::RULE_UNIQUE);
                }
            }
        });
        return empty($this->errors);
    }

    #[Override]
    protected function errorMessage(): array {
        $messages = parent::errorMessage();
        $messages[self::RULE_UNIQUE] = "This field already exists";
        return $messages;
    }

    public static function findOne(array $where): static|bool {
        $tableName = static::table();
        $statement = static::$database->selectAll($tableName, $where);
        return $statement->fetchObject(static::class);
    }

    public static function connectDatabase(Database $database): void {
        static::$database = $database;
    }
}