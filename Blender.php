<?php
class Blender extends Controller
{
    public function __construct(){ parent::__construct(); }

    public function modelado()    { header('Location: '.BASE_URL.'Curso/ver/'.CID_BLENDER_MODELADO);    exit; }
    public function texturizado() { header('Location: '.BASE_URL.'Curso/ver/'.CID_BLENDER_TEXTURIZADO); exit; }
    public function animacion()   { header('Location: '.BASE_URL.'Curso/ver/'.CID_BLENDER_ANIMACION);   exit; }
}
