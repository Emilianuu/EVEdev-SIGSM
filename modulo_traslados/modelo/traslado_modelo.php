<?php
// modelo/TrasladoModelo.php
require_once __DIR__ . '/../../servicios_comunes/conexion_BD/conexion.php';

class TrasladoModelo
{
    public static function obtenerTodosLosTraslados()
    {
        global $conexion;
        try {
            $sql = "SELECT 
                        t.id_traslado, 
                        DATE_FORMAT(s.fecha_solicitud, '%d/%m/%Y') AS fecha,
                        TIME_FORMAT(s.fecha_solicitud, '%H:%i') AS hora,
                        v.matricula AS vehiculo,
                        s.categoria_traslado AS tipo_traslado,
                        u_dest.nombre AS destino,
                        e.nombre AS estado,
                        COALESCE(CONCAT(pac.nombre, ' ', pac.apellido), s.detalle_carga, 'Sin especificar') AS paciente_elemento,
                        (SELECT CONCAT(p.nombre, ' ', p.apellido) 
                         FROM PERSONAL_TRASLADO pt JOIN PERSONAL p ON pt.id_personal = p.id_personal 
                         WHERE pt.id_traslado = t.id_traslado AND pt.rol_en_traslado = 'CONDUCTOR' LIMIT 1) AS conductor,
                        (SELECT CONCAT(p.nombre, ' ', p.apellido) 
                         FROM PERSONAL_TRASLADO pt JOIN PERSONAL p ON pt.id_personal = p.id_personal 
                         WHERE pt.id_traslado = t.id_traslado AND pt.rol_en_traslado = 'ENFERMERO' LIMIT 1) AS enfermero
                    FROM TRASLADO t
                    JOIN SOLICITUD_TRASLADO s ON t.id_solicitud = s.id_solicitud
                    LEFT JOIN PACIENTE pac ON s.ci_paciente = pac.ci
                    JOIN VEHICULO v ON t.id_vehiculo = v.id_vehiculo
                    JOIN RUTA r ON t.id_ruta = r.id_ruta
                    JOIN UBICACION u_dest ON r.id_destino = u_dest.id_ubicacion
                    LEFT JOIN (
                        SELECT id_traslado, id_estado FROM HISTORIAL_ESTADO 
                        WHERE id_historial IN (SELECT MAX(id_historial) FROM HISTORIAL_ESTADO GROUP BY id_traslado)
                    ) he ON t.id_traslado = he.id_traslado
                    LEFT JOIN ESTADO_TRASLADO e ON he.id_estado = e.id_estado
                    ORDER BY s.fecha_solicitud DESC";

            $stmt = $conexion->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error: " . $e->getMessage());
            return [];
        }
    }

    public static function obtenerSolicitudesPendientes()
    {
        global $conexion;
        try {
            $sql = "SELECT 
                        s.id_solicitud, 
                        DATE_FORMAT(s.fecha_solicitud, '%d/%m/%Y %H:%i') AS fecha,
                        s.categoria_traslado,
                        s.tipo_movilidad,
                        s.requiere_soporte_uti,
                        COALESCE(CONCAT(pac.nombre, ' ', pac.apellido), s.detalle_carga, 'Sin especificar') AS paciente_elemento,
                        u_dest.nombre AS destino,
                        s.motivo
                    FROM SOLICITUD_TRASLADO s
                    LEFT JOIN PACIENTE pac ON s.ci_paciente = pac.ci
                    JOIN UBICACION u_dest ON s.id_destino = u_dest.id_ubicacion
                    WHERE s.estado_solicitud = 'PENDIENTE' 
                    ORDER BY s.fecha_solicitud ASC";

            $stmt = $conexion->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerSolicitudesPendientes: " . $e->getMessage());
            return [];
        }
    }

    public static function guardarTraslado($datos, $id_usuario)
    {
        global $conexion;
        try {
            $conexion->beginTransaction();

            $categoria = $datos['categoria_traslado'];
            $ci_paciente = null;
            $detalle_carga = null;
            $tipo_movilidad = 'NO_APLICA';
            $requiere_soporte_uti = 0;

            if ($categoria === 'PACIENTE') {
                $ci_paciente = $datos['ci_paciente'];
                $tipo_movilidad = $datos['tipo_movilidad'];
                $requiere_soporte_uti = isset($datos['requiere_soporte_uti']) ? 1 : 0;

                $stmtPac = $conexion->prepare("SELECT ci FROM PACIENTE WHERE ci = ?");
                $stmtPac->execute([$ci_paciente]);
                if ($stmtPac->rowCount() == 0) {
                    $stmtInsPac = $conexion->prepare("INSERT INTO PACIENTE (ci, nombre, apellido, fecha_nacimiento) VALUES (?, 'Paciente', 'Temporal', '1990-01-01')");
                    $stmtInsPac->execute([$ci_paciente]);
                }
            } else {
                $detalle_carga = $datos['detalle_carga'];
            }

            $fecha_solicitud = $datos['fecha'] . ' ' . $datos['hora'] . ':00';

            // --- NUEVA LÓGICA: GESTIÓN DE DESTINO (TEXTO LIBRE) ---
            $nombre_destino = trim($datos['destino']);

            // Verificamos si ese destino ya existe en la base de datos
            $stmtDest = $conexion->prepare("SELECT id_ubicacion FROM UBICACION WHERE nombre = ?");
            $stmtDest->execute([$nombre_destino]);
            $ubi = $stmtDest->fetch(PDO::FETCH_ASSOC);

            if ($ubi) {
                // Si ya existe, usamos su ID
                $id_destino = $ubi['id_ubicacion'];
            } else {
                // Si no existe, lo creamos al vuelo como ubicación 'EXTERNA'
                $stmtInsUbi = $conexion->prepare("INSERT INTO UBICACION (nombre, tipo_ubicacion) VALUES (?, 'EXTERNA')");
                $stmtInsUbi->execute([$nombre_destino]);
                $id_destino = $conexion->lastInsertId();
            }
            // ------------------------------------------------------

            $sqlSol = "INSERT INTO SOLICITUD_TRASLADO 
                   (categoria_traslado, ci_paciente, detalle_carga, tipo_movilidad, requiere_soporte_uti, id_origen, id_destino, id_canal, id_usuario_solicit, fecha_solicitud, motivo, estado_solicitud) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, 2, ?, ?, ?, 'PENDIENTE')";

            $stmtSol = $conexion->prepare($sqlSol);
            $stmtSol->execute([
                $categoria,
                $ci_paciente,
                $detalle_carga,
                $tipo_movilidad,
                $requiere_soporte_uti,
                $datos['origen'],
                $id_destino,
                $id_usuario,
                $fecha_solicitud,
                $datos['motivo']
            ]);

            $conexion->commit();
            return ['status' => 'success', 'mensaje' => 'Solicitud de traslado registrada como PENDIENTE.'];
        } catch (PDOException $e) {
            $conexion->rollBack();
            return ['status' => 'error', 'mensaje' => $e->getMessage()];
        }
    }

    public static function asignarRecursos($datos, $id_usuario)
    {
        global $conexion;
        try {
            $conexion->beginTransaction();

            $id_solicitud = $datos['id_solicitud'];
            $id_vehiculo = $datos['vehiculo'];
            $id_conductor = $datos['conductor'];
            $id_enfermero = !empty($datos['enfermero']) ? $datos['enfermero'] : null;

            // 1. Buscar origen y destino de la solicitud
            $stmtSol = $conexion->prepare("SELECT id_origen, id_destino FROM SOLICITUD_TRASLADO WHERE id_solicitud = ?");
            $stmtSol->execute([$id_solicitud]);
            $solicitud = $stmtSol->fetch(PDO::FETCH_ASSOC);

            // 2. Obtener o crear ruta
            $stmtRuta = $conexion->prepare("SELECT id_ruta FROM RUTA WHERE id_origen = ? AND id_destino = ?");
            $stmtRuta->execute([$solicitud['id_origen'], $solicitud['id_destino']]);
            $ruta = $stmtRuta->fetch(PDO::FETCH_ASSOC);

            if ($ruta) {
                $id_ruta = $ruta['id_ruta'];
            } else {
                $stmtInsRuta = $conexion->prepare("INSERT INTO RUTA (id_origen, id_destino, distancia_km) VALUES (?, ?, 5.00)");
                $stmtInsRuta->execute([$solicitud['id_origen'], $solicitud['id_destino']]);
                $id_ruta = $conexion->lastInsertId();
            }

            // 3. Crear traslado
            $sqlTras = "INSERT INTO TRASLADO (id_solicitud, id_vehiculo, id_ruta) VALUES (?, ?, ?)";
            $stmtTras = $conexion->prepare($sqlTras);
            $stmtTras->execute([$id_solicitud, $id_vehiculo, $id_ruta]);
            $id_traslado = $conexion->lastInsertId();

            // 4. Asignar personal
            $stmtPers = $conexion->prepare("INSERT INTO PERSONAL_TRASLADO (id_traslado, id_personal, rol_en_traslado) VALUES (?, ?, 'CONDUCTOR')");
            $stmtPers->execute([$id_traslado, $id_conductor]);

            if ($id_enfermero) {
                $stmtEnf = $conexion->prepare("INSERT INTO PERSONAL_TRASLADO (id_traslado, id_personal, rol_en_traslado) VALUES (?, ?, 'ENFERMERO')");
                $stmtEnf->execute([$id_traslado, $id_enfermero]);
            }

            // 5. Registrar Historial del traslado
            $stmtHist = $conexion->prepare("INSERT INTO HISTORIAL_ESTADO (id_traslado, id_estado, id_usuario, observaciones) VALUES (?, 1, ?, 'Recursos asignados operativamente')");
            $stmtHist->execute([$id_traslado, $id_usuario]);

            // 6. ACTUALIZAR ESTADO DE LA SOLICITUD (Esto la elimina de la tabla inferior)
            $stmtUpdateSol = $conexion->prepare("UPDATE SOLICITUD_TRASLADO SET estado_solicitud = 'ASIGNADA' WHERE id_solicitud = ?");
            $stmtUpdateSol->execute([$id_solicitud]);

            $conexion->commit();
            return ['status' => 'success', 'mensaje' => 'Recursos asignados y traslado confirmado.'];
        } catch (PDOException $e) {
            $conexion->rollBack();
            return ['status' => 'error', 'mensaje' => $e->getMessage()];
        }
    }
    public static function rechazarSolicitud($id_solicitud)
    {
        global $conexion;
        try {
            // Actualizamos el estado a RECHAZADA
            $stmt = $conexion->prepare("UPDATE SOLICITUD_TRASLADO SET estado_solicitud = 'RECHAZADA' WHERE id_solicitud = ?");
            $stmt->execute([$id_solicitud]);

            return ['status' => 'success', 'mensaje' => 'Solicitud rechazada correctamente.'];
        } catch (PDOException $e) {
            return ['status' => 'error', 'mensaje' => 'Error al rechazar: ' . $e->getMessage()];
        }
    }
    public static function obtenerTrasladoPorId($id_traslado)
    {
        global $conexion;
        try {
            $sql = "SELECT t.id_traslado, t.id_vehiculo,
                           (SELECT pt.id_personal FROM PERSONAL_TRASLADO pt WHERE pt.id_traslado = t.id_traslado AND pt.rol_en_traslado = 'CONDUCTOR' LIMIT 1) as id_conductor,
                           (SELECT pt.id_personal FROM PERSONAL_TRASLADO pt WHERE pt.id_traslado = t.id_traslado AND pt.rol_en_traslado = 'ENFERMERO' LIMIT 1) as id_enfermero,
                           (SELECT id_estado FROM HISTORIAL_ESTADO WHERE id_traslado = t.id_traslado ORDER BY id_historial DESC LIMIT 1) as id_estado
                    FROM TRASLADO t WHERE t.id_traslado = ?";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([$id_traslado]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    public static function modificarTraslado($datos, $id_usuario)
    {
        global $conexion;
        try {
            $conexion->beginTransaction();
            $id_traslado = $datos['id_traslado'];

            // 1. Actualizar Vehículo
            if (!empty($datos['vehiculo'])) {
                $stmtV = $conexion->prepare("UPDATE TRASLADO SET id_vehiculo = ? WHERE id_traslado = ?");
                $stmtV->execute([$datos['vehiculo'], $id_traslado]);
            }

            // 2. Actualizar Personal (Borramos los actuales y reinsertamos)
            $stmtDel = $conexion->prepare("DELETE FROM PERSONAL_TRASLADO WHERE id_traslado = ?");
            $stmtDel->execute([$id_traslado]);

            if (!empty($datos['conductor'])) {
                $stmtC = $conexion->prepare("INSERT INTO PERSONAL_TRASLADO (id_traslado, id_personal, rol_en_traslado) VALUES (?, ?, 'CONDUCTOR')");
                $stmtC->execute([$id_traslado, $datos['conductor']]);
            }
            if (!empty($datos['enfermero'])) {
                $stmtE = $conexion->prepare("INSERT INTO PERSONAL_TRASLADO (id_traslado, id_personal, rol_en_traslado) VALUES (?, ?, 'ENFERMERO')");
                $stmtE->execute([$id_traslado, $datos['enfermero']]);
            }

            // 3. Actualizar Estado (Registramos nuevo historial si cambió)
            if (!empty($datos['estado'])) {
                $stmtH = $conexion->prepare("INSERT INTO HISTORIAL_ESTADO (id_traslado, id_estado, id_usuario, observaciones) VALUES (?, ?, ?, 'Modificación manual de traslado')");
                $stmtH->execute([$id_traslado, $datos['estado'], $id_usuario]);
            }

            $conexion->commit();
            return ['status' => 'success', 'mensaje' => 'Traslado modificado exitosamente.'];
        } catch (PDOException $e) {
            $conexion->rollBack();
            return ['status' => 'error', 'mensaje' => 'Error al modificar: ' . $e->getMessage()];
        }
    }
    public static function cancelarTraslado($id_traslado, $id_usuario)
    {
        global $conexion;
        try {
            $conexion->beginTransaction();

            // 1. Buscamos el ID del estado "CANCELADO" (usualmente es el 4 o 5 dependiendo de tu BD)
            $stmtEst = $conexion->prepare("SELECT id_estado FROM ESTADO_TRASLADO WHERE nombre = 'CANCELADO' LIMIT 1");
            $stmtEst->execute();
            $id_estado_cancelado = $stmtEst->fetchColumn();

            // Fallback por si el nombre varía en la base de datos
            if (!$id_estado_cancelado) $id_estado_cancelado = 4;

            // 2. Registramos el nuevo estado en el historial del traslado
            $stmtHist = $conexion->prepare("INSERT INTO HISTORIAL_ESTADO (id_traslado, id_estado, id_usuario, observaciones) VALUES (?, ?, ?, 'Traslado cancelado por el operador')");
            $stmtHist->execute([$id_traslado, $id_estado_cancelado, $id_usuario]);

            // 3. (Opcional pero recomendado) Actualizamos también la solicitud a CANCELADA
            $stmtSol = $conexion->prepare("UPDATE SOLICITUD_TRASLADO SET estado_solicitud = 'CANCELADA' WHERE id_solicitud = (SELECT id_solicitud FROM TRASLADO WHERE id_traslado = ?)");
            $stmtSol->execute([$id_traslado]);

            $conexion->commit();
            return ['status' => 'success', 'mensaje' => 'Traslado cancelado correctamente.'];
        } catch (PDOException $e) {
            $conexion->rollBack();
            return ['status' => 'error', 'mensaje' => 'Error al cancelar: ' . $e->getMessage()];
        }
    }
    public static function obtenerDatosDashboard()
    {
        global $conexion;
        try {
            // 1. Contador de Traslados Activos (Excluye completados y cancelados)
            $sqlActivos = "SELECT COUNT(DISTINCT t.id_traslado) 
                           FROM TRASLADO t
                           LEFT JOIN (
                               SELECT id_traslado, id_estado FROM HISTORIAL_ESTADO 
                               WHERE id_historial IN (SELECT MAX(id_historial) FROM HISTORIAL_ESTADO GROUP BY id_traslado)
                           ) he ON t.id_traslado = he.id_traslado
                           LEFT JOIN ESTADO_TRASLADO e ON he.id_estado = e.id_estado
                           WHERE e.nombre NOT IN ('COMPLETADO', 'CANCELADO') OR e.nombre IS NULL";
            $stmtAct = $conexion->prepare($sqlActivos);
            $stmtAct->execute();
            $cantActivos = $stmtAct->fetchColumn();

            // 2. Contador de Traslados del Día
            $sqlDia = "SELECT COUNT(*) FROM SOLICITUD_TRASLADO WHERE DATE(fecha_solicitud) = CURDATE()";
            $stmtDia = $conexion->prepare($sqlDia);
            $stmtDia->execute();
            $cantDia = $stmtDia->fetchColumn();

            // 3. Lista de Traslados Activos para las Tarjetas
            $sqlLista = "SELECT 
                            t.id_traslado, 
                            s.categoria_traslado,
                            COALESCE(e.nombre, 'ASIGNADO') AS estado,
                            u_dest.nombre AS destino,
                            COALESCE(CONCAT(pac.nombre, ' ', pac.apellido), s.detalle_carga, 'Sin especificar') AS paciente_elemento
                        FROM TRASLADO t
                        JOIN SOLICITUD_TRASLADO s ON t.id_solicitud = s.id_solicitud
                        LEFT JOIN PACIENTE pac ON s.ci_paciente = pac.ci
                        JOIN RUTA r ON t.id_ruta = r.id_ruta
                        JOIN UBICACION u_dest ON r.id_destino = u_dest.id_ubicacion
                        LEFT JOIN (
                            SELECT id_traslado, id_estado FROM HISTORIAL_ESTADO 
                            WHERE id_historial IN (SELECT MAX(id_historial) FROM HISTORIAL_ESTADO GROUP BY id_traslado)
                        ) he ON t.id_traslado = he.id_traslado
                        LEFT JOIN ESTADO_TRASLADO e ON he.id_estado = e.id_estado
                        WHERE e.nombre NOT IN ('COMPLETADO', 'CANCELADO') OR e.nombre IS NULL
                        ORDER BY s.fecha_solicitud DESC LIMIT 5";
            $stmtList = $conexion->prepare($sqlLista);
            $stmtList->execute();
            $listaActivos = $stmtList->fetchAll(PDO::FETCH_ASSOC);

            return [
                'status' => 'success',
                'traslados_activos' => $cantActivos,
                'traslados_dia' => $cantDia,
                'lista_activos' => $listaActivos
            ];
        } catch (PDOException $e) {
            return [
                'status' => 'error',
                'mensaje' => $e->getMessage(),
                'traslados_activos' => 0,
                'traslados_dia' => 0,
                'lista_activos' => []
            ];
        }
    }
    public static function obtenerDetalleSeguimiento($id_traslado)
    {
        global $conexion;
        try {
            // 1. Datos generales del traslado y personal
            $sqlDetalle = "SELECT 
                            t.id_traslado, 
                            v.matricula AS vehiculo,
                            u_orig.nombre AS origen,
                            u_dest.nombre AS destino,
                            COALESCE(CONCAT(pac.nombre, ' ', pac.apellido), s.detalle_carga, 'Sin especificar') AS paciente_elemento,
                            pac.ci AS ci_paciente,
                            (SELECT CONCAT(p.nombre, ' ', p.apellido) FROM PERSONAL_TRASLADO pt JOIN PERSONAL p ON pt.id_personal = p.id_personal WHERE pt.id_traslado = t.id_traslado AND pt.rol_en_traslado = 'CONDUCTOR' LIMIT 1) AS conductor,
                            (SELECT CONCAT(p.nombre, ' ', p.apellido) FROM PERSONAL_TRASLADO pt JOIN PERSONAL p ON pt.id_personal = p.id_personal WHERE pt.id_traslado = t.id_traslado AND pt.rol_en_traslado = 'ENFERMERO' LIMIT 1) AS enfermero
                        FROM TRASLADO t
                        JOIN SOLICITUD_TRASLADO s ON t.id_solicitud = s.id_solicitud
                        LEFT JOIN PACIENTE pac ON s.ci_paciente = pac.ci
                        JOIN VEHICULO v ON t.id_vehiculo = v.id_vehiculo
                        JOIN RUTA r ON t.id_ruta = r.id_ruta
                        JOIN UBICACION u_orig ON r.id_origen = u_orig.id_ubicacion
                        JOIN UBICACION u_dest ON r.id_destino = u_dest.id_ubicacion
                        WHERE t.id_traslado = ?";
            $stmt = $conexion->prepare($sqlDetalle);
            $stmt->execute([$id_traslado]);
            $detalle = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$detalle) return ['status' => 'error', 'mensaje' => 'Traslado no encontrado'];

            // 2. Historial de estados para la Línea de Tiempo
            $sqlHist = "SELECT e.nombre as estado, he.observaciones 
                        FROM HISTORIAL_ESTADO he
                        JOIN ESTADO_TRASLADO e ON he.id_estado = e.id_estado
                        WHERE he.id_traslado = ?
                        ORDER BY he.id_historial ASC";
            $stmtH = $conexion->prepare($sqlHist);
            $stmtH->execute([$id_traslado]);
            $historial = $stmtH->fetchAll(PDO::FETCH_ASSOC);

            return ['status' => 'success', 'detalle' => $detalle, 'historial' => $historial];
        } catch (PDOException $e) {
            return ['status' => 'error', 'mensaje' => $e->getMessage()];
        }
    }
    public static function actualizarEstado($datos, $id_usuario)
    {
        global $conexion;

        $id_traslado = $datos['id_traslado'] ?? null;
        $id_estado = $datos['id_estado'] ?? null;
        $observaciones = $datos['observaciones'] ?? '';

        if (!$id_traslado || !$id_estado) {
            return ['status' => 'error', 'mensaje' => 'Faltan datos obligatorios para actualizar el estado.'];
        }

        try {
            // El trigger 'trg_historial_propaga_estado' de la BD se encargará 
            // automáticamente de actualizar la disponibilidad del vehículo y la solicitud.
            $sql = "INSERT INTO HISTORIAL_ESTADO (id_traslado, id_estado, observaciones, id_usuario) 
                VALUES (?, ?, ?, ?)";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([$id_traslado, $id_estado, $observaciones, $id_usuario]);

            return ['status' => 'success'];
        } catch (Exception $e) {
            return ['status' => 'error', 'mensaje' => $e->getMessage()];
        }
    }
}
