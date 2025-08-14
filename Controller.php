<?php
class Controller
{
    protected $model;
    protected $views;

    public function __construct()
    {
        $this->loadModel();
    }

    // Cargar vista con header y footer automáticamente
    public function view($view, $data = [])
    {
        extract($data);

        // Ruta física del archivo de vista
        $filePath = BASE_PATH . "Views/" . $view . ".php";

        if (file_exists($filePath)) {
            include BASE_PATH . 'Views/Principal/header.php';
            include $filePath;
            include BASE_PATH . 'Views/Principal/footer.php';
        } else {
            die("La vista '$view' no existe en: $filePath");
        }
    }



    // Cargar el modelo asociado    
    public function loadModel($modelName = null)
    {
        if ($modelName === null) {
            $modelName = get_class($this) . "Model";
        }

        $file = BASE_PATH . "Models/" . $modelName . ".php";
        if (file_exists($file)) {
            require_once $file;
            if (class_exists($modelName)) {
                $this->model = new $modelName();
            }
        }
    }
    // Añade dentro de tu clase Controller:
    public function json(array $payload, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
        exit; // MUY IMPORTANTE: evita que se añada HTML después del JSON
    }
}
