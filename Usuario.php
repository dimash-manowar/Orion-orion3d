<?php
class Usuario extends Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] !== 'usuario') {
            header('Location: ' . BASE_URL);
            exit;            
        }
        $this->loadModel('UsuariosModel');
    }

    public function index()
    {
        $data['page_title'] = 'Panel de Usuario - Orion3D';
        $data['page_functions_js'] = 'usuario.js';
        $this->view('Usuarios/index', $data);
    }
    public function perfil()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_SESSION['user']['id'];
            $nombre = trim($_POST['nombre']);
            $apellido = trim($_POST['apellido']);
            $email = trim($_POST['email']);
            $password = $_POST['password'];
            $foto = $_FILES['foto'];

            $fotoNombre = $_SESSION['user']['foto'];
            if (!empty($foto['name']) && $foto['error'] === 0) {
                $ext = pathinfo($foto['name'], PATHINFO_EXTENSION);
                $fotoNombre = uniqid('user_') . '.' . $ext;
                move_uploaded_file($foto['tmp_name'], 'Assets/imagen/users/' . $fotoNombre);
            }

            $query = "UPDATE usuarios SET nombre = ?, apellido = ?, email = ?, foto = ?" .
                (!empty($password) ? ", password = ?" : "") . " WHERE id = ?";
            $params = [$nombre, $apellido, $email, $fotoNombre];
            if (!empty($password)) {
                $params[] = password_hash($password, PASSWORD_DEFAULT);
            }
            $params[] = $id;

            $this->model->update($query, $params);

            $_SESSION['user']['nombre'] = $nombre;
            $_SESSION['user']['apellido'] = $apellido;
            $_SESSION['user']['email'] = $email;
            $_SESSION['user']['foto'] = $fotoNombre;

            $_SESSION['alert'] = [
                'icon' => 'success',
                'title' => 'Perfil actualizado',
                'text' => 'Tus datos han sido guardados correctamente.'
            ];

            header('Location: ' . BASE_URL . 'Usuario/perfil');
            exit;
        }

        $data['page_title'] = 'Mi Perfil - Orion3D';
        $this->view('Usuarios/perfil', $data);
    }
    public function progreso()
    {
        if (empty($_SESSION['user'])) {
            header('Location: ' . BASE_URL . 'Auth');
            exit;
        }
        $userId = (int)$_SESSION['user']['id'];

        $this->loadModel('UsuariosModel');

        $resumen  = $this->model->getResumenProgreso($userId);
        $actividad = $this->model->getActividadSemanal($userId);
        $cursos   = $this->model->getCursosUsuario($userId);

        $payload = [
            'resumen'   => $resumen,
            'actividad' => $actividad,
            'cursos'    => $cursos,
        ];

        $data = [
            'page_title'       => 'Mi Progreso - Orion3D',
            'page_functions_js' => 'Usuarios/usuarioProgreso.js',
            'payload'          => $payload
        ];
        $this->view('Usuarios/progreso', $data);
    }


    public function favoritos()
    {
        $data['page_title'] = 'Favoritos - Orion3D';
        $this->view('Usuarios/favoritos', $data);
    }

    // Endpoint para badges del sidebar
    public function badges()
    {
        if (!isset($_SESSION['user'])) {
            http_response_code(401);
            exit;
        }
        $userId = (int)$_SESSION['user']['id'];

        // Intenta cargar UsuariosModel si existe; si no, devuelve 0,0
        $this->loadModel('UsuariosModel');
        if ($this->model && method_exists($this->model, 'getBadges')) {
            $res = $this->model->getBadges($userId);
        } else {
            $res = ['pendientes' => 0, 'favoritos' => 0];
        }

        header('Content-Type: application/json');
        echo json_encode($res);
    }
    public function toggleFavorito()
    {
        if (empty($_SESSION['user'])) {
            http_response_code(401);
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit;
        }
        if (!csrf_verify($_POST['csrf'] ?? '')) {
            http_response_code(419);
            echo json_encode(['success' => false, 'message' => 'Sesión caducada']);
            return;
        }

        $userId = (int)$_SESSION['user']['id'];
        $tipo   = strtolower(trim($_POST['tipo'] ?? ''));
        $refId  = (int)($_POST['id'] ?? 0);

        if (!$tipo || !$refId || !in_array($tipo, ['curso', 'leccion', 'post'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Parámetros inválidos']);
            return;
        }

        $this->loadModel('UsuariosModel');
        $favAhora = $this->model->isFavorite($userId, $tipo, $refId);

        $ok = $favAhora
            ? $this->model->removeFavorite($userId, $tipo, $refId)
            : $this->model->addFavorite($userId, $tipo, $refId);

        $favDespues = !$favAhora;

        // Recalcular badges
        $counts = $this->model->getBadges($userId);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => (bool)$ok,
            'fav'     => $favDespues,
            'counts'  => $counts
        ]);
    }

    public function listarFavoritos()
    {
        if (empty($_SESSION['user'])) {
            http_response_code(401);
            exit;
        }
        $userId = (int)$_SESSION['user']['id'];

        $this->loadModel('UsuariosModel');
        $items = $this->model->listFavoritos($userId);

        header('Content-Type: application/json');
        echo json_encode(['items' => $items]);
    }
    public function cursos()
    {
        // Ya tienes el check de sesión en el __construct
        $this->loadModel('UsuariosModel');
        $userId = (int)$_SESSION['user']['id'];

        $cat = isset($_GET['cat']) ? trim($_GET['cat']) : null;
        $q   = isset($_GET['q'])   ? trim($_GET['q'])   : null;

        $cursos = $this->model->listarCursosUsuario($userId, $cat ?: null, $q ?: null);

        // Siguiente lección para cada curso (simple; N consultas relativamente pequeñas)
        foreach ($cursos as &$c) {
            $next = $this->model->getSiguienteLeccion($userId, (int)$c['id']);
            $c['next_id']    = $next['id']    ?? null;
            $c['next_titulo'] = $next['titulo'] ?? null;
        }

        $data = [
            'page_title'        => 'Mis Cursos - Orion3D',
            'cursos'            => $cursos,
            'filtro_cat'        => $cat,
            'busqueda'          => $q,
            'page_functions_js' => 'Usuarios/usuarioCursos.js'
        ];
        $this->view('Usuarios/cursos', $data);
    }
}