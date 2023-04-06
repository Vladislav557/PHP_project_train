<?php

namespace App;

use PDO;

/**
 * Class for validation data
 */
class Validator
{
    /**
     * @var PDO
     */
    private PDO $connection;

    /**
     * @param PDO $connection
     */
    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param string|integer $value
     * @param integer $minLength
     * @return boolean
     */
    public function isEnoughtLength(string|int $value, int $minLength): bool
    {
        return strlen($value) < $minLength;
    }

    /**
     * @param string|integer $value
     * @return boolean
     */
    public function isEmpty(string|int $value): bool
    {
        return $this->isEnoughtLength($value, 1);
    }

    /**
     * @param string $key
     * @param string $value
     * @param string $dbname
     * @return boolean
     */
    public function isExists(string $key, string $value, string $dbname): bool
    {
        $statement = $this
            ->connection
            ->prepare("SELECT * FROM {$dbname} WHERE {$key} = :{$key}");

        $statement->execute([
            $key => $value
        ]);

        return !empty($statement->fetch());
    }

    public function isMatch(string|int $value1, string|int $value2): bool 
    {
        return $value1 === $value2;
    }

    public function verifyPassword(string $username, string|int $password): bool 
    {
        $statement = $this  
            ->connection
            ->prepare('SELECT * FROM `users` WHERE `username` = :username');
        $statement->execute([
            'username' => $username
        ]);
        $user = $statement->fetch();
        if (password_verify($password, $user['password'])) {
            return true;
        } 
        return false;
    }
}