<?php

namespace App;

use PDO;
use PDOException;
use InvalidArgumentException;

class Database
{
    private static ?PDO $connection = null;

    final private function __construct(){}
    final private function __clone(){}

    public static function getConnection(string $dsn, string $username, string $password, array $options = []): PDO
    {
        if (is_null(self::$connection)) {
            try {
                self::$connection = new PDO($dsn, $username, $password, $options);
            } catch (PDOException $exception) {
                throw new InvalidArgumentException('Error connection -> ' . $exception->getMessage());
            }
        }

        return self::$connection;
    }
}