<?php

class Mysql extends Conexion
{
    protected $pdo;
    public $conect;
    public function __construct()
    {
        parent::__construct(); // Llama a Conexion y crea PDO
        $this->pdo = $this->conect; // Guarda la conexión en $pdo

        if (!$this->pdo instanceof PDO) {
            die("❌ Error: No se pudo establecer la conexión con la base de datos.");
        }
    }

    public function select($query, $arrValues = [])
    {

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($arrValues);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }



    public function insert($query, $arrValues = [])
    {
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($arrValues);
        return $this->pdo->lastInsertId();
    }

    // System/Mysql.php
    public function select_one($query, $arrValues = [])
    {
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($arrValues);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row === false ? null : $row; // 👈 así nunca devuelve false
    }




    public function update($query, $arrValues = [])
    {
        $stmt = $this->pdo->prepare($query);
        return $stmt->execute($arrValues);
    }

    public function delete($query, $arrValues = [])
    {
        $stmt = $this->pdo->prepare($query);
        return $stmt->execute($arrValues);
    }
}
