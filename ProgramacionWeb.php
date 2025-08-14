<?php
class ProgramacionWeb extends Controller
{
    public function __construct(){ parent::__construct(); }

    public function html_css()  { header('Location: '.BASE_URL.'Cursos/ver/'.CID_WEB_HTMLCSS); exit; }
    public function javascript(){ header('Location: '.BASE_URL.'Cursos/ver/'.CID_WEB_JS);      exit; }
    public function php_mysql() { header('Location: '.BASE_URL.'Cursos/ver/'.CID_WEB_PHP);     exit; }
}
