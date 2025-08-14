<?php
class Unity extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    // ⚠️ Cambia por tus IDs reales
    private int $ID_UNITY_2D = 11;
    private int $ID_UNITY_3D = 12;
    private int $ID_CSHARP   = 13;

    public function unity2d(){ header('Location: '.BASE_URL.'Cursos/ver/'.$this->ID_UNITY_2D); exit; }
    public function unity3d(){ header('Location: '.BASE_URL.'Cursos/ver/'.$this->ID_UNITY_3D); exit; }
    public function recursos(){ header('Location: '.BASE_URL.'Cursos/ver/'.$this->ID_CSHARP);   exit; }
}
