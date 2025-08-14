<?php
class Conexion {
    protected $conect;

    public function __construct() {
        $connectionString = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        try {
            $this->conect = new PDO($connectionString, DB_USER, DB_PASSWORD);
            $this->conect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            $this->conect = null;
            echo "ERROR: " . $e->getMessage();
        }
    }

    public function conect() {
        return $this->conect;
    }
}
