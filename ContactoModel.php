<?php
class ContactoModel extends Mysql
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Inserta un nuevo mensaje en la base de datos
     */
    public function insertarMensaje($nombre, $email, $mensaje)
    {
        $sql = "INSERT INTO contactos (nombre, email, mensaje) VALUES (?, ?, ?)";
        $arrData = [$nombre, $email, $mensaje];
        return $this->insert($sql, $arrData);
    }

   

    /**
     * Obtiene un mensaje por su ID
     */
    public function obtenerMensajePorId($id)
    {
        $sql = "SELECT * FROM contactos WHERE id = ?";
        return $this->select_one($sql, [$id]);
    }
    
}
