<?php
class Middleware
{
    // 👉 Controlador/ruta de login (tu router hará ucfirst al resolver)
    private const LOGIN_ROUTE = 'auth';

    /**
     * Rutas públicas (no requieren login)
     */
    private static array $public = [
        'home'     => ['index'],
        'auth'     => ['index', 'login', 'logout', 'register'], // sin dologin ni store
        'blog'     => ['index', 'ver'],
        'cursos'   => ['index', 'ver'],  // preguntar queda protegido (requiere login)
        'contacto' => ['index', 'enviar'],
    ];

    /**
     * Claves de sesión que aceptamos como “logueado” (compatibilidad)
     */
    private static array $sessionKeys = [
        'user',
        'user_id',
        'idusuario',
        'idUsuario',
        'idUser',
        'usuario',
        'login'
    ];

    public static function guard(string $controller, string $method): void
    {
        $c = strtolower($controller);
        $m = strtolower($method);

        // Permitir assets y raíz
        $url = strtolower(trim($_GET['url'] ?? '', '/'));
        if ($url === '' || preg_match('#^(assets|asset|public)/#i', $url)) {
            self::ensureCsrfSeed();
            return;
        }

        // Rutas públicas
        if (isset(self::$public[$c])) {
            $allowed = self::$public[$c];
            if ($allowed === '*' || (is_array($allowed) && in_array($m, $allowed, true))) {
                self::ensureCsrfSeed();
                return;
            }
        }

        // Requiere login
        if (!self::isLoggedIn()) {
            header('Location: ' . BASE_URL . self::LOGIN_ROUTE);
            exit;
        }

        // Validar CSRF en POST (solo rutas protegidas)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf'] ?? '';
            $ok = isset($_SESSION['csrf']) && is_string($token) && hash_equals($_SESSION['csrf'], $token);
            if (!$ok) {
                http_response_code(419);
                if (self::wantsJson()) {
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode(['success' => false, 'msg' => 'CSRF']);
                } else {
                    echo 'CSRF token inválido.';
                }
                exit;
            }
        }

        self::ensureCsrfSeed();
    }

    private static function isLoggedIn(): bool
    {
        // Caso preferido: estructura de Auth actual
        if (!empty($_SESSION['user']) && !empty($_SESSION['user']['id'])) {
            return true;
        }
        // Compatibilidad con claves antiguas
        foreach (self::$sessionKeys as $k) {
            if (!empty($_SESSION[$k])) return true;
        }
        return false;
    }

    public static function csrfInput(): string
    {
        self::ensureCsrfSeed();
        $t = htmlspecialchars($_SESSION['csrf'], ENT_QUOTES, 'UTF-8');
        return '<input type="hidden" name="csrf" value="' . $t . '">';
    }

    private static function ensureCsrfSeed(): void
    {
        if (empty($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
        }
    }

    private static function wantsJson(): bool
    {
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $xhr    = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';
        return (stripos($accept, 'application/json') !== false) || (strtolower($xhr) === 'xmlhttprequest');
    }
}
