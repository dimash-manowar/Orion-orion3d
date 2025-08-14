<?php
class Admin extends Controller
{
    public function __construct()
    {
        $this->model = new AdminModel();
        parent::__construct();
        requireRole(['admin']);
    }

    public function index()
    {
        $mensajes = '';
        $data = [
            'page_title' => 'Administración de Orion3D',
            'mensajes' => $mensajes           

        ];
        $this->view('Admin/index', $data);
    }

    public function mensajes()
    {
        $this->loadModel('AdminModel');

        // Lee filtros del GET, sin imponer 'abierta' por defecto
        $estado = $_GET['estado'] ?? null; // '', 'abierta', 'respondida', 'cerrada'... o null (todos)
        $buscar = $_GET['q'] ?? null;

        $qna = $this->model->obtenerPreguntasQnA($estado ?: null, $buscar ?: null);

        $data = [
            'page_title'        => 'Mensajes (Q&A)',
            'qna'               => $qna,
            'page_functions_js' => 'admin/mensajes.js',
        ];
        $this->view('Admin/mensajes', $data);
    }



    public function publicaciones()
    {
        $publicaciones = $this->model->obtenerPublicaciones();
        $data = [
            'page_title' => 'Gestor de Publicaciones',
            'publicaciones' => $publicaciones,
            'page_functions_js' => 'publicaciones.js'
        ];
        $this->view('Admin/publicaciones', $data);
    }

    public function usuarios()
    {
        $data = [
            'page_title' => 'Gestión de Usuarios'
        ];
        $this->view('Admin/usuarios', $data);
    }

    public function marcarLeido($id)
    {
        $res = $this->model->marcarLeido((int)$id);
        echo json_encode([
            'success' => $res,
            'message' => $res ? 'Mensaje marcado como leído' : 'No se pudo actualizar'
        ]);
    }

    public function eliminarMensaje($id)
    {
        $res = $this->model->eliminarMensaje((int)$id);
        echo json_encode([
            'success' => $res,
            'message' => $res ? 'Mensaje eliminado' : 'No se pudo eliminar'
        ]);
    }

    public function eliminarPublicacion($id)
    {
        $res = $this->model->eliminarPublicacion($id);
        echo json_encode(['success' => $res, 'message' => $res ? 'Publicación eliminada' : 'No se pudo eliminar']);
    }

    public function publicar($id)
    {
        $res = $this->model->cambiarEstado($id, 1);
        echo json_encode(['success' => $res, 'message' => $res ? 'Publicada' : 'Error']);
    }

    public function despublicar($id)
    {
        $res = $this->model->cambiarEstado($id, 0);
        echo json_encode(['success' => $res, 'message' => $res ? 'Ocultada' : 'Error']);
    }

    public function perfil()
    {
        $data['page_title'] = 'Editar Perfil - Orion3D';
        $data['page_functions_js'] = 'admin/perfil.js';
        $this->view('Admin/perfil', $data);
    }

    public function actualizar()
    {
        $id = $_SESSION['user']['id'];
        $nombre = trim($_POST['nombre']);
        $apellido = trim($_POST['apellido']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $nuevaFoto = null;

        if (!empty($_FILES['foto']['name']) && $_FILES['foto']['error'] === 0) {
            $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $nuevaFoto = uniqid('user_') . '.' . $ext;
            move_uploaded_file($_FILES['foto']['tmp_name'], 'Assets/imagen/users/' . $nuevaFoto);
        }

        $passwordHash = empty($password) ? null : password_hash($password, PASSWORD_DEFAULT);

        $actualizado = $this->model->actualizarPerfil($id, $nombre, $apellido, $email, $passwordHash, $nuevaFoto);

        if ($actualizado) {
            $_SESSION['user'] = $this->model->getById($id);
            $_SESSION['alert'] = [
                'icon' => 'success',
                'title' => 'Perfil actualizado',
                'text' => 'Tus datos se han guardado correctamente.'
            ];
        } else {
            $_SESSION['alert'] = [
                'icon' => 'error',
                'title' => 'Error',
                'text' => 'Hubo un problema al actualizar tu perfil.'
            ];
        }

        header('Location: ' . BASE_URL . 'Admin/perfil');
        exit;
    }

    public function setEstadoQna()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user'])) {
            http_response_code(401);
            exit;
        }
        if (!csrf_verify($_POST['csrf'] ?? '')) {
            http_response_code(419);
            echo json_encode(['success' => false]);
            return;
        }
        $id = (int)($_POST['id'] ?? 0);
        $estado = $_POST['estado'] ?? '';
        $this->loadModel('AdminModel');
        $ok = $this->model->cambiarEstadoPregunta($id, $estado);
        echo json_encode(['success' => $ok]);
    }

    public function setLeidoQna()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user'])) {
            http_response_code(401);
            exit;
        }
        if (!csrf_verify($_POST['csrf'] ?? '')) {
            http_response_code(419);
            echo json_encode(['success' => false]);
            return;
        }
        $id = (int)($_POST['id'] ?? 0);
        $leido = (int)($_POST['leido'] ?? 1);
        $this->loadModel('AdminModel');
        $ok = $this->model->marcarLeidoPregunta($id, $leido);
        echo json_encode(['success' => $ok]);
    }

    public function notificaciones()
    {
        if (empty($_SESSION['user']) || (($_SESSION['user']['rol'] ?? '') !== 'admin')) {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'msg' => 'auth']);
            exit;
        }

        while (ob_get_level()) {
            ob_end_clean();
        }
        ini_set('display_errors', '0');
        header('Content-Type: application/json; charset=utf-8');

        $this->loadModel('AdminModel');
        try {
            $items = $this->model->ultimasQnaPendientes(10);
            $count = $this->model->contarQnaPendientes();
            echo json_encode(['success' => true, 'count' => (int)$count, 'items' => $items], JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'msg' => 'server']);
        }
        exit;
    }

    public function preguntas()
    {
        $estado  = $_GET['estado'] ?? null;
        $buscar  = $_GET['q'] ?? null;
        $this->loadModel('AdminModel');
        $lista   = $this->model->obtenerPreguntasQnA($estado ?: null, $buscar ?: null);

        $data = [
            'page_title' => 'Mensajes de Cursos (Q&A)',
            'qna'        => $lista,
            'page_functions_js' => 'admin/qna.js' // 👈 importante
        ];
        $this->view('Admin/preguntas', $data);
    }

    public function verQna($id)
    {
        $this->loadModel('AdminModel');
        $hilo = $this->model->obtenerHiloQnA((int)$id);
        header('Content-Type: application/json');
        echo json_encode($hilo);
    }

    public function responderQna()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_SESSION['user'])) {
            http_response_code(401);
            exit;
        }
        if (!csrf_verify($_POST['csrf'] ?? '')) {
            http_response_code(419);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'msg' => 'CSRF']);
            return;
        }

        $preguntaId = (int)($_POST['pregunta_id'] ?? 0);
        $htmlIn     = trim($_POST['contenido_html'] ?? '');
        if ($preguntaId <= 0 || $htmlIn === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'msg' => 'bad']);
            return;
        }

        $safeHtml = strip_tags($htmlIn, '<p><b><strong><i><em><u><ul><ol><li><br><a><code><pre><img>');
        $safeHtml = preg_replace('/on\w+="[^"]*"/i', '', $safeHtml);
        $safeHtml = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $safeHtml);

        $imgPath = null;
        if (!empty($_FILES['imagen']['name']) && $_FILES['imagen']['error'] === 0) {
            $ext = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif']) && $_FILES['imagen']['size'] <= 2 * 1024 * 1024) {
                $dir = 'Assets/imagen/qna_respuestas/';
                if (!is_dir($dir)) @mkdir($dir, 0777, true);
                $fname = 'r_' . date('Ymd_His') . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
                $dest  = $dir . $fname;
                if (move_uploaded_file($_FILES['imagen']['tmp_name'], $dest)) {
                    $imgPath = $dest;
                }
            }
        }

        $adminId = (int)$_SESSION['user']['id'];
        $adminModel = new AdminModel();
        $respId = $adminModel->responderPreguntaQnA($preguntaId, $adminId, $safeHtml, $imgPath);

        if ($respId > 0) {
            $adminModel->marcarLeidoPregunta($preguntaId, 1);
            $adminModel->cambiarEstadoPregunta($preguntaId, 'respondida');
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => true, 'item' => [
                'id' => $respId,
                'contenido_html' => $safeHtml,
                'imagen' => $imgPath
            ]]);
            return;
        }
        echo json_encode(['success' => false, 'msg' => 'server']);
    }
}
