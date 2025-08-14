<?php
class Blog extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function ultimas()
    {
        $data['page_title'] = 'Últimas Noticias - Orion3D';
        $this->view('Blog/ultimas', $data);
    }

    public function destacados()
    {
        $data['page_title'] = 'Artículos Destacados - Orion3D';
        $this->view('Blog/destacados', $data);
    }
}
