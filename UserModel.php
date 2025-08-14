<?php

class UserModel extends Mysql
{
    public function __construct()
    {
        parent::__construct();
    }

    // Obtener usuario por nombre de usuario
    public function getByUsername($username)
    {
        $sql = "SELECT * FROM users WHERE nombre_usuario = ?";
        return $this->select_one($sql, [$username]);
    }

    // Crear nuevo usuario
    public function createUser($nombre, $apellido, $nombre_usuario, $email, $password, $foto = null)
    {
        $sql = "INSERT INTO users (nombre, apellido, nombre_usuario, email, password, foto)
            VALUES (?, ?, ?, ?, ?, ?)";
        try {
            return $this->insert($sql, [
                $nombre,
                $apellido,
                $nombre_usuario,
                $email,
                $password,
                $foto
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }    

    public function actualizarPerfil($id, $nombre, $apellido, $email, $password = null, $foto = null)
    {
        $campos = [];
        $params = [];

        $campos[] = "nombre = ?";
        $params[] = $nombre;

        $campos[] = "apellido = ?";
        $params[] = $apellido;

        $campos[] = "email = ?";
        $params[] = $email;

        if (!empty($password)) {
            $campos[] = "password = ?";
            $params[] = $password;
        }

        if (!empty($foto)) {
            $campos[] = "foto = ?";
            $params[] = $foto;
        }

        $params[] = $id;

        $sql = "UPDATE users SET " . implode(", ", $campos) . " WHERE id = ?";
        return $this->update($sql, $params);
    }
    public function existsEmail(string $email): bool
    {
        $row = $this->select_one("SELECT 1 FROM users WHERE email = ? LIMIT 1", [strtolower($email)]);
        return (bool)$row;
    }

    public function existsUsername(string $username): bool
    {
        $row = $this->select_one("SELECT 1 FROM users WHERE nombre_usuario = ? LIMIT 1", [$username]);
        return (bool)$row;
    }

    public function getByEmail(string $email)
    {
        return $this->select_one("SELECT * FROM users WHERE email = ? LIMIT 1", [strtolower($email)]);
    }
    // ... dentro de class UserModel extends Mysql
    public function existsEmailExceptId(string $email, int $id): bool
    {
        $row = $this->select_one(
            "SELECT 1 FROM users WHERE email = ? AND id <> ? LIMIT 1",
            [strtolower($email), $id]
        );
        return (bool)$row;
    }
    public function getAdmins(): array
    {
        return $this->select("SELECT id, nombre, nombre_usuario, email FROM users WHERE rol='admin'");
    }
    public function getById(int $id)
    {
        return $this->select_one("SELECT * FROM users WHERE id = ?", [$id]);
    }
}
