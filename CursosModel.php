<?php
class CursosModel extends Mysql
{
    public function __construct()
    {
        parent::__construct();
    }

    public function listarPublicados(?string $categoria = null, ?string $nivel = null, ?string $q = null): array
    {
        $params = [];
        $where  = " WHERE c.publicado = 1 ";

        if ($categoria) {
            $where .= " AND c.categoria = ? ";
            $params[] = $categoria;
        }
        if ($nivel) {
            $where .= " AND c.nivel = ? ";
            $params[] = $nivel;
        }
        if ($q) {
            $where .= " AND (c.titulo LIKE ? OR c.descripcion LIKE ?) ";
            $params[] = "%$q%";
            $params[] = "%$q%";
        }

        $sql = "SELECT 
                    c.id, c.titulo, c.slug, c.categoria, c.nivel, c.portada,
                    SUBSTRING(c.descripcion,1,160) AS resumen,
                    COUNT(DISTINCT l.id) AS total_lecciones
                FROM cursos c
                LEFT JOIN lecciones l ON l.curso_id = c.id
                $where
                GROUP BY c.id
                ORDER BY c.categoria, c.titulo";
        return $this->select($sql, $params);
    }

    public function categorias(): array
    {
        return ['Unity', 'Web', 'Blender'];
    }

    public function niveles(): array
    {
        return ['principiante', 'intermedio', 'avanzado'];
    }
    public function listarPublicadosPaged(
        ?string $categoria,
        ?string $nivel,
        ?string $q,
        string $sort,
        int $page,
        int $perPage
    ): array {
        // Sanitizar sort
        $mapSort = [
            'recientes'   => 'c.creado_at DESC',
            'populares'   => 'alumnos DESC, c.creado_at DESC',
            'titulo'      => 'c.titulo ASC'
        ];
        $orderBy = $mapSort[$sort] ?? $mapSort['recientes'];

        $params = [];
        $where  = " WHERE c.publicado = 1 ";
        if ($categoria) {
            $where .= " AND c.categoria = ? ";
            $params[] = $categoria;
        }
        if ($nivel) {
            $where .= " AND c.nivel = ? ";
            $params[] = $nivel;
        }
        if ($q) {
            $where .= " AND (c.titulo LIKE ? OR c.descripcion LIKE ?) ";
            $params[] = "%$q%";
            $params[] = "%$q%";
        }

        // Total
        $total = $this->select_one("SELECT COUNT(*) t FROM cursos c $where", $params)['t'] ?? 0;

        // Items con métricas (alumnos = popularidad)
        $offset = max(0, ($page - 1) * $perPage);
        $sql = "SELECT 
                c.id, c.titulo, c.slug, c.categoria, c.nivel, c.portada,
                SUBSTRING(c.descripcion,1,160) AS resumen,
                COUNT(DISTINCT l.id) AS total_lecciones,
                COUNT(DISTINCT m.usuario_id) AS alumnos
            FROM cursos c
            LEFT JOIN lecciones  l ON l.curso_id = c.id
            LEFT JOIN matriculas m ON m.curso_id = c.id
            $where
            GROUP BY c.id
            ORDER BY $orderBy
            LIMIT $perPage OFFSET $offset";
        $items = $this->select($sql, $params);

        return ['items' => $items, 'total' => (int)$total, 'page' => $page, 'perPage' => $perPage];
    }

    public function getLeccionEstado(int $userId, int $leccionId): int
    {
        $r = $this->select_one(
            "SELECT completada FROM lecciones_usuarios WHERE usuario_id=? AND leccion_id=?",
            [$userId, $leccionId]
        );
        return $r ? (int)$r['completada'] : 0;
    }
    public function getProgresoCurso(int $userId, int $cursoId): array
    {
        $tot = $this->select_one("SELECT COUNT(*) t FROM lecciones WHERE curso_id=?", [$cursoId]);
        $comp = $this->select_one("SELECT COUNT(*) c
                              FROM lecciones l
                              INNER JOIN lecciones_usuarios lu ON lu.leccion_id=l.id AND lu.usuario_id=?
                              WHERE l.curso_id=? AND lu.completada=1", [$userId, $cursoId]);
        $lt = (int)($tot['t'] ?? 0);
        $lc = (int)($comp['c'] ?? 0);
        $pct = $lt > 0 ? round(($lc / $lt) * 100) : 0;
        return ['tot' => $lt, 'comp' => $lc, 'pct' => $pct];
    }


    public function getLeccionesCurso(int $userId, int $cursoId): array
    {
        $sql = "SELECT l.id, l.titulo,
                   COALESCE(lu.completada, 0) AS completada
            FROM lecciones l
            LEFT JOIN lecciones_usuarios lu
              ON lu.leccion_id = l.id AND lu.usuario_id = ?
            WHERE l.curso_id = ?
            ORDER BY l.id ASC";
        return $this->select($sql, [$userId, $cursoId]);
    }

    public function setLeccionEstado(int $userId, int $leccionId, int $estado): bool
    {
        $row = $this->select_one(
            "SELECT id FROM lecciones_usuarios WHERE usuario_id=? AND leccion_id=?",
            [$userId, $leccionId]
        );
        if ($row) {
            return $this->update(
                "UPDATE lecciones_usuarios SET completada=?, updated_at=NOW() WHERE id=?",
                [$estado, $row['id']]
            );
        } else {
            $this->insert(
                "INSERT INTO lecciones_usuarios (usuario_id, leccion_id, completada) VALUES (?, ?, ?)",
                [$userId, $leccionId, $estado]
            );
            return true;
        }
    }

    public function getCursoIdByLeccion(int $leccionId): ?int
    {
        $row = $this->select_one("SELECT curso_id FROM lecciones WHERE id=?", [$leccionId]);
        return $row ? (int)$row['curso_id'] : null;
    }




    // Suma minutos a la fecha de hoy (crea/actualiza fila)
    public function addStudyMinutes(int $userId, int $minutes): bool
    {
        $sql = "INSERT INTO sesiones_estudio (usuario_id, fecha, minutos)
          VALUES (?, CURDATE(), ?)
          ON DUPLICATE KEY UPDATE minutos = minutos + VALUES(minutos)";
        return (bool)$this->insert($sql, [$userId, $minutes]);
    }

    // (opcional) Resta minutos pero nunca por debajo de 0
    public function subStudyMinutes(int $userId, int $minutes): bool
    {
        $row = $this->select_one("SELECT id, minutos FROM sesiones_estudio WHERE usuario_id=? AND fecha=CURDATE()", [$userId]);
        if (!$row) return true;
        $nuevo = max(0, ((int)$row['minutos']) - $minutes);
        return $this->update("UPDATE sesiones_estudio SET minutos=? WHERE id=?", [$nuevo, (int)$row['id']]);
    }



    public function listPreguntas(int $cursoId, int $leccionId): array
    {
        return $this->select("SELECT p.id, p.contenido_html, p.imagen, p.creado_at,
                               u.nombre_usuario, u.nombre
                        FROM preguntas p
                        JOIN users u ON u.id = p.usuario_id
                        WHERE p.curso_id=? AND p.leccion_id=?
                        ORDER BY p.id DESC", [$cursoId, $leccionId]);
    }
    
    public function getLeccionInfo(int $leccionId)
    {
        return $this->select_one("
    SELECT l.id, l.curso_id, l.titulo, l.video_url, l.descripcion
    FROM lecciones l WHERE l.id=?", [$leccionId]);
    }
    // Lista lecciones del curso con estado de completada para un usuario
    public function getLeccionesDeCursoConEstado(int $cursoId, int $userId): array
    {
        $sql = "SELECT 
              l.id, l.titulo, l.video_url, l.descripcion,
              COALESCE(lu.completada, 0) AS completada,
              lu.actualizado_at
            FROM lecciones l
            LEFT JOIN lecciones_usuarios lu
              ON lu.leccion_id = l.id AND lu.usuario_id = ?
            WHERE l.curso_id = ?
            ORDER BY l.id ASC";
        return $this->select($sql, [$userId, $cursoId]);
    }

    public function listarLeccionesConEstado(int $cursoId, int $userId): array
    {
        $sql = "SELECT 
              l.id, l.titulo, l.video_url, l.descripcion,
              COALESCE(lu.completada, 0) AS completada,
              lu.actualizado_at
            FROM lecciones l
            LEFT JOIN lecciones_usuarios lu
              ON lu.leccion_id = l.id AND lu.usuario_id = ?
            WHERE l.curso_id = ?
            ORDER BY l.id ASC";
        return $this->select($sql, [$userId, $cursoId]); // 👈 CORRECTO
    }

    public function resumenProgresoCurso(int $cursoId, int $userId): array
    {
        $tot  = $this->select_one("SELECT COUNT(*) t FROM lecciones WHERE curso_id=?", [$cursoId])['t'] ?? 0;
        $comp = $this->select_one(
            "SELECT COUNT(*) c
                             FROM lecciones_usuarios
                             WHERE usuario_id=? AND completada=1
                               AND leccion_id IN (SELECT id FROM lecciones WHERE curso_id=?)",
            [$userId, $cursoId]
        )['c'] ?? 0;
        $pct = $tot > 0 ? round($comp / $tot * 100) : 0;
        return ['tot' => (int)$tot, 'comp' => (int)$comp, 'pct' => (int)$pct];
    }
    public function getCursoById(int $id): ?array
    {
        $row = $this->select_one("SELECT * FROM cursos WHERE id = ?", [$id]);
        return $row ?: null; // 👈 false -> null
    }

    public function getLeccionDeCurso(int $cursoId, int $leccionId): ?array
    {
        $sql = "SELECT id, titulo, video_url, descripcion
            FROM lecciones
            WHERE id = ? AND curso_id = ?
            LIMIT 1";
        $row = $this->select_one($sql, [$leccionId, $cursoId]);
        return $row ?: null; // 👈 false -> null
    }
    public function insertPregunta(int $usuarioId, int $cursoId, int $leccionId, string $html, ?string $imgPath): int
    {
        $sql = "INSERT INTO qna_preguntas (usuario_id, curso_id, leccion_id, contenido_html, imagen)
            VALUES (?,?,?,?,?)";
        return (int)$this->insert($sql, [$usuarioId, $cursoId, $leccionId, $html, $imgPath]);
    }

    public function getPreguntasLeccion(int $leccionId): array
    {
        $sql = "SELECT q.id, q.usuario_id, q.contenido_html, q.imagen, q.creado_at,
                   u.nombre_usuario AS nombre
            FROM qna_preguntas q
            JOIN users u ON u.id = q.usuario_id
            WHERE q.leccion_id = ?
            ORDER BY q.id DESC";
        return $this->select($sql, [$leccionId]);
    }
}
