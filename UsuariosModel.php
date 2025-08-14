<?php
class UsuariosModel extends Mysql
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getBadges(int $userId): array
    {
        $pend = $this->select_one("SELECT COUNT(*) c FROM lecciones_usuarios WHERE usuario_id = ? AND completada = 0", [$userId]);
        $fav  = $this->select_one("SELECT COUNT(*) c FROM favoritos WHERE usuario_id = ?", [$userId]);
        return ['pendientes' => (int)($pend['c'] ?? 0), 'favoritos' => (int)($fav['c'] ?? 0)];
    }

    public function isFavorite(int $userId, string $tipo, int $refId): bool
    {
        $row = $this->select_one(
            "SELECT 1 FROM favoritos WHERE usuario_id=? AND tipo=? AND ref_id=? LIMIT 1",
            [$userId, $tipo, $refId]
        );
        return (bool)$row;
    }

    public function addFavorite(int $userId, string $tipo, int $refId): bool
    {
        try {
            $this->insert(
                "INSERT INTO favoritos (usuario_id, tipo, ref_id) VALUES (?,?,?)",
                [$userId, $tipo, $refId]
            );
            return true;
        } catch (PDOException $e) {
            // Si existe UNIQUE, ignoramos duplicado
            return false;
        }
    }

    public function removeFavorite(int $userId, string $tipo, int $refId): bool
    {
        return $this->delete(
            "DELETE FROM favoritos WHERE usuario_id=? AND tipo=? AND ref_id=?",
            [$userId, $tipo, $refId]
        );
    }

    public function listFavoritos(int $userId): array
    {
        // Ajusta a tu esquema real de cursos/lecciones/posts si lo tienes.
        // De momento, devolvemos básico.
        return $this->select(
            "SELECT id, tipo, ref_id, created_at FROM favoritos WHERE usuario_id=? ORDER BY created_at DESC",
            [$userId]
        );
    }
    /* === Resumen global de progreso === */
    public function getResumenProgreso(int $userId): array
    {
        $cursos = $this->select_one("
          SELECT COUNT(DISTINCT m.curso_id) c
          FROM matriculas m
          WHERE m.usuario_id = ?", [$userId]);

        $tot = $this->select_one("
          SELECT COUNT(*) t
          FROM lecciones l
          INNER JOIN matriculas m ON m.curso_id = l.curso_id
          WHERE m.usuario_id = ?", [$userId]);

        $comp = $this->select_one("
          SELECT COUNT(*) c
          FROM lecciones_usuarios lu
          INNER JOIN lecciones l ON l.id = lu.leccion_id
          INNER JOIN matriculas m ON m.curso_id = l.curso_id
          WHERE lu.usuario_id = ? AND lu.completada = 1", [$userId]);

        $leccionesTot = (int)($tot['t'] ?? 0);
        $leccionesComp = (int)($comp['c'] ?? 0);
        $progresoPct  = $leccionesTot > 0 ? round(($leccionesComp / $leccionesTot) * 100) : 0;

        // horas de la última semana
        $horasSemana = $this->getActividadSemanal($userId)['horas'];

        return [
            'cursos'         => (int)($cursos['c'] ?? 0),
            'lecciones_tot'  => $leccionesTot,
            'lecciones_comp' => $leccionesComp,
            'progreso_pct'   => $progresoPct,
            'horas_semana'   => array_sum($horasSemana),
        ];
    }
    /* === Actividad por día (últimos 7) === */
    public function getActividadSemanal(int $userId): array
    {
        // Traer minutos por fecha (últimos 7 días)
        $rows = $this->select("
          SELECT fecha, SUM(minutos) min
          FROM sesiones_estudio
          WHERE usuario_id = ? AND fecha >= (CURDATE() - INTERVAL 6 DAY)
          GROUP BY fecha
          ORDER BY fecha ASC", [$userId]);

        // Mapa fecha -> horas
        $map = [];
        foreach ($rows as $r) {
            $map[$r['fecha']] = round(($r['min'] ?? 0) / 60, 2);
        }

        // Completar los 7 días
        $labels = [];
        $horas  = [];
        for ($i = 6; $i >= 0; $i--) {
            $f = date('Y-m-d', strtotime("-$i day"));
            // Etiquetas: L M X J V S D
            $dia = date('N', strtotime($f)); // 1..7
            $labels[] = ['', 'L', 'M', 'X', 'J', 'V', 'S', 'D'][$dia];
            $horas[]  = isset($map[$f]) ? (float)$map[$f] : 0.0;
        }
        return ['labels' => $labels, 'horas' => $horas];
    }

    // Lista cursos matriculados del usuario con progreso agregado
    public function listarCursosUsuario(int $userId, ?string $categoria = null, ?string $q = null): array
    {
        $params = [$userId, $userId];
        $where  = "";
        if ($categoria) {
            $where .= " AND c.categoria = ?";
            $params[] = $categoria;
        }
        if ($q) {
            $where .= " AND c.titulo LIKE ?";
            $params[] = "%$q%";
        }

        $sql = "SELECT 
                c.id, c.titulo, c.slug, c.categoria, c.nivel, c.portada,
                COUNT(DISTINCT l.id) AS total_lecciones,
                COALESCE(SUM(CASE WHEN lu.completada=1 THEN 1 ELSE 0 END),0) AS comp,
                ROUND( COALESCE(SUM(CASE WHEN lu.completada=1 THEN 1 ELSE 0 END),0) / NULLIF(COUNT(DISTINCT l.id),0) * 100 ) AS pct,
                MAX(lu.actualizado_at) AS last_at
            FROM cursos c
            JOIN matriculas m ON m.curso_id = c.id AND m.usuario_id = ?
            LEFT JOIN lecciones l ON l.curso_id = c.id
            LEFT JOIN lecciones_usuarios lu ON lu.leccion_id = l.id AND lu.usuario_id = ?
            WHERE 1=1 $where
            GROUP BY c.id
            ORDER BY c.categoria, c.titulo";
        return $this->select($sql, $params);
    }
    /* === Cursos del usuario con progreso por curso === */
    public function getCursosUsuario(int $userId): array
    {
        // Trae todos los cursos del usuario
        $cursos = $this->select("
          SELECT c.id, c.titulo
          FROM cursos c
          INNER JOIN matriculas m ON m.curso_id = c.id
          WHERE m.usuario_id = ?
          ORDER BY c.titulo ASC", [$userId]);

        // Para cada curso: total lecciones, completadas, último acceso (por lección)
        foreach ($cursos as &$c) {
            $tot = $this->select_one("SELECT COUNT(*) t FROM lecciones WHERE curso_id = ?", [$c['id']]);
            $comp = $this->select_one("
                SELECT COUNT(*) c
                FROM lecciones_usuarios lu
                INNER JOIN lecciones l ON l.id = lu.leccion_id
                WHERE lu.usuario_id = ? AND lu.completada=1 AND l.curso_id = ?", [$userId, $c['id']]);
            $last = $this->select_one("
                SELECT MAX(lu.updated_at) u
                FROM lecciones_usuarios lu
                INNER JOIN lecciones l ON l.id = lu.leccion_id
                WHERE lu.usuario_id = ? AND l.curso_id = ?", [$userId, $c['id']]);

            $lt = (int)($tot['t'] ?? 0);
            $lc = (int)($comp['c'] ?? 0);
            $c['lecciones_tot'] = $lt;
            $c['lecciones_comp'] = $lc;
            $c['progreso']      = $lt > 0 ? round(($lc / $lt) * 100) : 0;
            $c['ultimo_acceso'] = $last['u'] ?? null;
        }
        unset($c);
        return $cursos;
    }

    // Primera lección del curso (fallback)
    public function getPrimeraLeccion(int $cursoId): ?array
    {
        return $this->select_one("SELECT id, titulo FROM lecciones WHERE curso_id=? ORDER BY id ASC LIMIT 1", [$cursoId]);
    }

    // Siguiente lección pendiente para el usuario; si todo completado, primera
    public function getSiguienteLeccion(int $userId, int $cursoId): ?array
    {
        $pend = $this->select_one("
        SELECT l.id, l.titulo
        FROM lecciones l
        LEFT JOIN lecciones_usuarios lu
          ON lu.leccion_id=l.id AND lu.usuario_id=?
        WHERE l.curso_id=? AND COALESCE(lu.completada,0)=0
        ORDER BY l.id ASC
        LIMIT 1", [$userId, $cursoId]);
        if ($pend) return $pend;
        return $this->getPrimeraLeccion($cursoId);
    }   
    

    // (por si tu controlador los usa también)
    public function getCursoById(int $id): ?array
    {
        return $this->select_one("SELECT * FROM cursos WHERE id = ?", [$id]);
    }
    public function getLeccionById(int $id): ?array
    {
        return $this->select_one("SELECT * FROM lecciones WHERE id = ?", [$id]);
    }
    public function resumenProgresoCurso(int $cursoId, int $userId): array
    {
        $tot  = $this->select_one("SELECT COUNT(*) t FROM lecciones WHERE curso_id=?", [$cursoId])['t'] ?? 0;
        $comp = $this->select_one("
        SELECT COUNT(*) c FROM lecciones_usuarios 
        WHERE usuario_id=? AND leccion_id IN (SELECT id FROM lecciones WHERE curso_id=?) 
          AND completada=1", [$userId, $cursoId])['c'] ?? 0;
        $pct = $tot > 0 ? round($comp / $tot * 100) : 0;
        return ['tot' => (int)$tot, 'comp' => (int)$comp, 'pct' => (int)$pct];
    }
}
