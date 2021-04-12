<?php

namespace App;


class Auth
{
    private DataBase $connection;
    private Session $session;

    public function __construct(DataBase $_connection, Session $_session)
    {
        $this->connection = $_connection;
        $this->session = $_session;
    }

    public function register(array $data): bool
    {
        if('' === $data['username']) {
            throw new AuthException('The username should not be empty');
        }
        if('' === $data['email']) {
            throw new AuthException('The email should not be empty');
        }
        if('' === $data['password']) {
            throw new AuthException('The password should not be empty');
        }
        if($data['password'] !== $data['confirm_password']) {
            throw new AuthException('The Password and Confirm password should match');
        }

        $stmt = $this->connection->getConnection()->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute([
            "email" => $data['email'],
        ]);

        $email = $stmt->fetch();
        if(!empty($email)) {
            throw new AuthException('User with such email exist');
        }

        $stmt = $this->connection->getConnection()->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->execute([
            "username" => $data['username'],
        ]);

        $username = $stmt->fetch();
        if(!empty($username)) {
            throw new AuthException('User with such username exist. Try another Username');
        }

        $stmt = $this->connection->getConnection()->prepare(
            "INSERT INTO users (email, username, password) VALUES (:email, :username, :password)"
        );
        $stmt->execute([
            "email" => $data['email'],
            "username" => $data['username'],
            "password" => password_hash($data['password'], PASSWORD_BCRYPT),
        ]);

        return true;
    }

    public function login(array $data): bool
    {
        if('' === $data['email']) {
            throw new AuthException('The email should not be empty');
        }
        if('' === $data['password']) {
            throw new AuthException('The password should not be empty');
        }

        $stmt = $this->connection->getConnection()->prepare("SELECT email, username, password FROM users WHERE email=:email");
        $stmt->execute([
            "email" => $data['email'],
        ]);

        $user = $stmt->fetch();
        if(empty($user)) {
            throw new AuthException("User with such email not found");
        }

        if(password_verify($data['password'], $user['password'])) {
            $this->session->setData('user', [
                'username' => $user['username'],
                'email' => $user['email']
            ]);
            return true;
        }

        throw new AuthException("Incorrect email or password");
    }
}