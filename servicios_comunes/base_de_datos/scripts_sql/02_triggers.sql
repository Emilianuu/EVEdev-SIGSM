-- =====================================================================
-- S.I.G.S.M. - Triggers para restricciones no estructurales
-- =====================================================================
USE sigsm;

DELIMITER $$

-- ---------------------------------------------------------------------
-- BLOQUE 1
-- ---------------------------------------------------------------------

-- Solo se pueden asignar roles con activo = true
CREATE TRIGGER trg_usuariorol_rol_activo
BEFORE INSERT ON USUARIO_ROL
FOR EACH ROW
BEGIN
    DECLARE v_activo BOOLEAN;
    SELECT activo INTO v_activo FROM ROL WHERE id_rol = NEW.id_rol;
    IF v_activo IS NULL OR v_activo = FALSE THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'No se puede asignar un rol inactivo a un usuario.';
    END IF;
END$$

-- ---------------------------------------------------------------------
-- BLOQUE 2
-- ---------------------------------------------------------------------

-- Una encuesta solo acepta respuestas dentro de su vigencia,
-- y respeta la coherencia es_anonima <-> ci_paciente
CREATE TRIGGER trg_respencuesta_reglas
BEFORE INSERT ON RESPUESTA_ENCUESTA
FOR EACH ROW
BEGIN
    DECLARE v_inicio DATE;
    DECLARE v_fin DATE;
    DECLARE v_anonima BOOLEAN;

    SELECT fecha_inicio, fecha_fin, es_anonima
      INTO v_inicio, v_fin, v_anonima
      FROM ENCUESTA WHERE id_encuesta = NEW.id_encuesta;

    IF CURDATE() < v_inicio OR CURDATE() > v_fin THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'La encuesta no está vigente (fuera de fecha_inicio/fecha_fin).';
    END IF;

    IF v_anonima = TRUE AND NEW.ci_paciente IS NOT NULL THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'La encuesta es anónima: ci_paciente debe ser NULL.';
    END IF;

    IF v_anonima = FALSE AND NEW.ci_paciente IS NULL THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'La encuesta no es anónima: ci_paciente es obligatorio.';
    END IF;
END$$

-- id_opcion y texto_libre son mutuamente excluyentes (exactamente uno de los dos)
CREATE TRIGGER trg_resppregunta_exclusividad
BEFORE INSERT ON RESPUESTA_PREGUNTA
FOR EACH ROW
BEGIN
    IF (NEW.id_opcion IS NULL AND NEW.texto_libre IS NULL)
       OR (NEW.id_opcion IS NOT NULL AND NEW.texto_libre IS NOT NULL) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Debe indicarse exactamente uno: id_opcion (opción múltiple) o texto_libre.';
    END IF;
END$$

-- ---------------------------------------------------------------------
-- BLOQUE 3
-- ---------------------------------------------------------------------

-- Una ruta no puede tener el mismo origen y destino
CREATE TRIGGER trg_ruta_origen_destino
BEFORE INSERT ON RUTA
FOR EACH ROW
BEGIN
    IF NEW.id_origen = NEW.id_destino THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Una ruta no puede tener el mismo origen y destino.';
    END IF;
END$$

-- Una solicitud de traslado no puede tener el mismo origen y destino
CREATE TRIGGER trg_solicitud_origen_destino
BEFORE INSERT ON SOLICITUD_TRASLADO
FOR EACH ROW
BEGIN
    IF NEW.id_origen = NEW.id_destino THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Una solicitud de traslado no puede tener el mismo origen y destino.';
    END IF;
END$$

-- El vehículo asignado a un traslado debe estar DISPONIBLE,
-- ser compatible con el tipo de elemento de la solicitud,
-- y la ruta debe coincidir con el origen/destino de la solicitud.
-- Además valida la exclusión mutua proveedor externo / personal propio (parte 1: proveedor + insert previo de personal no aplica aquí).

-- 1. NUEVO: Asegura la coherencia de datos al crear una solicitud
CREATE TRIGGER trg_solicitud_coherencia
BEFORE INSERT ON SOLICITUD_TRASLADO
FOR EACH ROW
BEGIN
    IF NEW.categoria_traslado = 'PACIENTE' THEN
        IF NEW.ci_paciente IS NULL THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Un traslado de paciente REQUIERE una cédula de paciente.';
        END IF;
    ELSE
        -- Si es Biológico, Equipo o Insumo
        IF NEW.ci_paciente IS NOT NULL THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Un traslado logístico NO DEBE tener cédula de paciente.';
        END IF;
        IF NEW.detalle_carga IS NULL OR NEW.detalle_carga = '' THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Debe especificar qué objeto transporta en detalle_carga.';
        END IF;
        IF NEW.tipo_movilidad != 'NO_APLICA' THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Para objetos logísticos, el tipo de movilidad debe ser NO_APLICA.';
        END IF;
    END IF;
END$$

-- 2. ACTUALIZADO: Comprueba que el vehículo pueda llevar la carga/paciente
CREATE TRIGGER trg_traslado_validaciones
BEFORE INSERT ON TRASLADO
FOR EACH ROW
BEGIN
    DECLARE v_disponibilidad VARCHAR(20);
    DECLARE v_apto_sr, v_apto_cam, v_apto_uti, v_apto_bio, v_apto_ins BOOLEAN;
    DECLARE v_cat ENUM('PACIENTE', 'MATERIAL_BIOLOGICO', 'EQUIPAMIENTO', 'INSUMOS');
    DECLARE v_movilidad ENUM('AMBULATORIO', 'SILLA_RUEDAS', 'CAMILLA', 'NO_APLICA');
    DECLARE v_uti BOOLEAN;
    DECLARE v_sol_origen, v_sol_destino, v_ruta_origen, v_ruta_destino INT;

    -- Datos del Vehículo
    SELECT v.estado_disponibilidad, tv.apto_silla_ruedas, tv.apto_camilla, tv.apto_uti, tv.apto_biologico, tv.apto_insumos
      INTO v_disponibilidad, v_apto_sr, v_apto_cam, v_apto_uti, v_apto_bio, v_apto_ins
      FROM VEHICULO v JOIN TIPO_VEHICULO tv ON v.id_tipo_vehiculo = tv.id_tipo_vehiculo 
     WHERE v.id_vehiculo = NEW.id_vehiculo;

    IF v_disponibilidad <> 'DISPONIBLE' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El vehículo asignado no está DISPONIBLE.';
    END IF;

    -- Datos de la Solicitud
    SELECT categoria_traslado, tipo_movilidad, requiere_soporte_uti, id_origen, id_destino
      INTO v_cat, v_movilidad, v_uti, v_sol_origen, v_sol_destino
      FROM SOLICITUD_TRASLADO WHERE id_solicitud = NEW.id_solicitud;

    -- LÓGICA DE COMPATIBILIDAD DINÁMICA
    IF v_cat = 'PACIENTE' THEN
        IF v_uti = TRUE AND v_apto_uti = FALSE THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El vehículo no tiene soporte UTI/Respirador.';
        END IF;
        IF v_movilidad = 'SILLA_RUEDAS' AND v_apto_sr = FALSE THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El vehículo no está adaptado para silla de ruedas.';
        END IF;
        IF v_movilidad = 'CAMILLA' AND v_apto_cam = FALSE THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El vehículo no tiene espacio para camilla.';
        END IF;
    ELSEIF v_cat = 'MATERIAL_BIOLOGICO' AND v_apto_bio = FALSE THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El vehículo no cuenta con cadena de frío / permisos biológicos.';
    ELSEIF v_cat IN ('EQUIPAMIENTO', 'INSUMOS') AND v_apto_ins = FALSE THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Vehículo no apto para logística pesada/voluminosa.';
    END IF;

    -- Ruta
    SELECT id_origen, id_destino INTO v_ruta_origen, v_ruta_destino FROM RUTA WHERE id_ruta = NEW.id_ruta;
    IF v_ruta_origen <> v_sol_origen OR v_ruta_destino <> v_sol_destino THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'La ruta asignada no coincide con el origen/destino de la solicitud.';
    END IF;

END$$

-- Un traslado no puede tener proveedor externo y personal propio a la vez
CREATE TRIGGER trg_personaltraslado_exclusion
BEFORE INSERT ON PERSONAL_TRASLADO
FOR EACH ROW
BEGIN
    DECLARE v_proveedor INT;
    SELECT id_proveedor INTO v_proveedor FROM TRASLADO WHERE id_traslado = NEW.id_traslado;
    IF v_proveedor IS NOT NULL THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'El traslado ya tiene un proveedor externo asignado: no puede tener personal propio.';
    END IF;
END$$

CREATE TRIGGER trg_traslado_exclusion_proveedor
BEFORE UPDATE ON TRASLADO
FOR EACH ROW
BEGIN
    DECLARE v_personal_count INT;
    IF NEW.id_proveedor IS NOT NULL THEN
        SELECT COUNT(*) INTO v_personal_count
          FROM PERSONAL_TRASLADO WHERE id_traslado = NEW.id_traslado;
        IF v_personal_count > 0 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'El traslado ya tiene personal propio asignado: no puede tener proveedor externo.';
        END IF;
    END IF;
END$$

-- estado_solicitud debe respetar la secuencia PENDIENTE -> ASIGNADA -> COMPLETADA
-- (CANCELADA es válida desde cualquier estado previo a COMPLETADA)
CREATE TRIGGER trg_solicitud_secuencia_estado
BEFORE UPDATE ON SOLICITUD_TRASLADO
FOR EACH ROW
BEGIN
    IF OLD.estado_solicitud = 'COMPLETADA' AND NEW.estado_solicitud <> 'COMPLETADA' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'No se puede modificar el estado de una solicitud ya COMPLETADA.';
    END IF;
    IF OLD.estado_solicitud = 'CANCELADA' AND NEW.estado_solicitud <> 'CANCELADA' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'No se puede modificar el estado de una solicitud CANCELADA.';
    END IF;
    IF OLD.estado_solicitud = 'PENDIENTE' AND NEW.estado_solicitud = 'COMPLETADA' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Una solicitud PENDIENTE no puede pasar directamente a COMPLETADA (debe pasar por ASIGNADA).';
    END IF;
END$$

-- Al registrar un cambio de estado en HISTORIAL_ESTADO, se propaga el
-- estado a SOLICITUD_TRASLADO a través del TRASLADO correspondiente.
CREATE TRIGGER trg_historial_propaga_estado
AFTER INSERT ON HISTORIAL_ESTADO
FOR EACH ROW
BEGIN
    DECLARE v_nombre_estado VARCHAR(50);
    DECLARE v_id_solicitud INT;

    SELECT nombre INTO v_nombre_estado FROM ESTADO_TRASLADO WHERE id_estado = NEW.id_estado;
    SELECT id_solicitud INTO v_id_solicitud FROM TRASLADO WHERE id_traslado = NEW.id_traslado;

    -- Actualiza el estado de la Solicitud
    IF v_nombre_estado = 'COMPLETADO' THEN
        UPDATE SOLICITUD_TRASLADO SET estado_solicitud = 'COMPLETADA' WHERE id_solicitud = v_id_solicitud;
    ELSEIF v_nombre_estado = 'CANCELADO' THEN
        UPDATE SOLICITUD_TRASLADO SET estado_solicitud = 'CANCELADA' WHERE id_solicitud = v_id_solicitud;
    ELSEIF v_nombre_estado = 'EN_CAMINO' THEN
        UPDATE SOLICITUD_TRASLADO SET estado_solicitud = 'EN_CAMINO' WHERE id_solicitud = v_id_solicitud;
    END IF;

    -- Ocupa el vehículo solo cuando el traslado inicia realmente
    IF v_nombre_estado = 'EN_CAMINO' THEN
        UPDATE VEHICULO v JOIN TRASLADO t ON t.id_vehiculo = v.id_vehiculo
           SET v.estado_disponibilidad = 'EN_USO'
         WHERE t.id_traslado = NEW.id_traslado;
    END IF;

    -- Libera el vehículo cuando el traslado se completa o cancela
    IF v_nombre_estado IN ('COMPLETADO', 'CANCELADO') THEN
        UPDATE VEHICULO v JOIN TRASLADO t ON t.id_vehiculo = v.id_vehiculo
           SET v.estado_disponibilidad = 'DISPONIBLE'
         WHERE t.id_traslado = NEW.id_traslado;
    END IF;
END$$

DELIMITER ;