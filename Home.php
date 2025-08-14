<?php
class Home extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $data['page_title'] = 'Inicio - Orion3D';        
        $data['page_functions_js'] = "home.js";
        $this->view('Home/index', $data);
    }
}
