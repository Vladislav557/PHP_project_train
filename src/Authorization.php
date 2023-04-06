<?php

namespace App;

use PDO;
use App\Validator;

/**
 * Class for registration and authorization
 */
class Authorization
{
    /**
     * @var PDO
     * @var Validator
     */
    private PDO $connection;
    private Validator $validator;

    /**
     * @param PDO $connection
     */
    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
        $this->validator = new Validator($connection);
    }

    /**
     * Method for registration a new user
     *
     * @param array $data
     * @return boolean
     */
    public function registration(array $data): bool
    {
        foreach ($data as $key => $value) {
            if ($this->validator->isEmpty($value)) {
                throw new AuthorizationException("The {$key} should not be empty");
            }
        }

        if (!$this->validator->isMatch($data['password'], $data['confirm_password'])) {
            throw new AuthorizationException('The password and confirm password should match');
        }

        if ($this->validator->isExists('username', $data['username'], 'users')) {
            throw new AuthorizationException('User with such username exists');
        }

        if ($this->validator->isExists('email', $data['email'], 'users')) {
            throw new AuthorizationException('User with such email alredy exists');
        }

        $statement = $this
            ->connection
            ->prepare('INSERT INTO `users` (`username`, `email`, `password`) VALUES (:username, :email, :password)');

        $statement->execute([
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => password_hash($data['password'], PASSWORD_BCRYPT)
        ]);
        
        return true;
    }

    /**
     * Method for authorization
     *
     * @param string $username
     * @param string $password
     * @return boolean
     */
    public function login(string $username, string|int $password): bool
    {
        if ($this->validator->isEmpty($username)) {
            throw new AuthorizationException("The username should not be empty");
        }

        if ($this->validator->isEmpty($password)) {
            throw new AuthorizationException("The password should not be empty");
        }

        if ($this->validator->verifyPassword($username, $password)) {
            return true;
        }

        throw new AuthorizationException('Invalid username or password');
    }
}