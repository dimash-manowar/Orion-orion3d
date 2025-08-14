<?php
class Notificaciones extends Controller
{
    public function __construct(){ parent::__construct(); }

    public function unread()
    {
        if (empty($_SESSION['user'])) { http_response_code(401); exit; }
        $uid = (int)$_SESSION['user']['id'];
        $this->loadModel('NotificacionesModel');
        $n = $this->model->contarNoLeidas($uid);
        header('Content-Type: application/json');
        echo json_encode(['unread'=>$n]);
    }

    public function list()
    {
        if (empty($_SESSION['user'])) { http_response_code(401); exit; }
        $uid = (int)$_SESSION['user']['id'];
        $this->loadModel('NotificacionesModel');
        $items = $this->model->listar($uid, 30);
        header('Content-Type: application/json');
        echo json_encode(['items'=>$items]);
    }

    public function read()
    {
        if (empty($_SESSION['user'])) { http_response_code(401); exit; }
        if ($_SERVER['REQUEST_METHOD']!=='POST' || !csrf_verify($_POST['csrf'] ?? '')) { http_response_code(419); exit; }
        $uid = (int)$_SESSION['user']['id'];
        $id  = (int)($_POST['id'] ?? 0);
        $this->loadModel('NotificacionesModel');
        $ok = $id ? $this->model->marcarLeida($uid, $id) : false;
        echo json_encode(['success'=>$ok]);
    }

    public function readAll()
    {
        if (empty($_SESSION['user'])) { http_response_code(401); exit; }
        if ($_SERVER['REQUEST_METHOD']!=='POST' || !csrf_verify($_POST['csrf'] ?? '')) { http_response_code(419); exit; }
        $uid = (int)$_SESSION['user']['id'];
        $this->loadModel('NotificacionesModel');
        $ok = $this->model->marcarTodasLeidas($uid);
        echo json_encode(['success'=>$ok]);
    }
}