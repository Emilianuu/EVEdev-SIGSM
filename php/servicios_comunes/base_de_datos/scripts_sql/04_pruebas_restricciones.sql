-- =====================================================================
-- S.I.G.S.M. - Pruebas de las restricciones no estructurales
-- Cada sentencia de este script DEBE FALLAR (por diseño) mostrando el
-- error definido en el trigger correspondiente. Se recomienda ejecutar
-- una por una en phpMyAdmin (pestaña SQL) y capturar el mensaje de error
-- como evidencia.
-- =====================================================================
USE sigsm;

-- Prueba 1: no se puede asignar un rol inactivo (ROL.activo = FALSE)
-- Esperado: ERROR 1644 - "No se puede asignar un rol inactivo a un usuario."
INSERT INTO USUARIO_ROL (id_usuario, id_rol) VALUES (2, 4);

-- Prueba 2: una ruta no puede tener el mismo origen y destino
-- Esperado: ERROR 1644 - "Una ruta no puede tener el mismo origen y destino."
INSERT INTO RUTA (id_origen, id_destino, descripcion, distancia_km) VALUES (1, 1, 'Ruta inválida', 1.0);

-- Prueba 3: RESPUESTA_PREGUNTA sin id_opcion ni texto_libre
-- Esperado: ERROR 1644 - "Debe indicarse exactamente uno: id_opcion... o texto_libre."
INSERT INTO RESPUESTA_PREGUNTA (id_resp_encuesta, id_pregunta, id_opcion, texto_libre) VALUES (1, 1, NULL, NULL);

-- Prueba 4: RESPUESTA_PREGUNTA con id_opcion y texto_libre a la vez
-- Esperado: mismo error que la prueba 3 (exclusividad mutua)
INSERT INTO RESPUESTA_PREGUNTA (id_resp_encuesta, id_pregunta, id_opcion, texto_libre) VALUES (1, 1, 1, 'texto');

-- Prueba 5: asignar un vehículo que está en MANTENIMIENTO
-- Esperado: ERROR 1644 - "El vehículo asignado no está DISPONIBLE."
INSERT INTO TRASLADO (id_solicitud, id_vehiculo, id_ruta) VALUES (2, 3, 3);

-- Prueba 6: asignar un vehículo incompatible con el elemento solicitado
-- Esperado: ERROR 1644 - "El tipo de vehículo no es compatible..."
INSERT INTO TIPO_ELEMENTO (nombre, descripcion) VALUES ('Incubadora', 'Traslado neonatal');
INSERT INTO SOLICITUD_TRASLADO (ci_paciente, id_tipo_elemento, id_origen, id_destino, id_canal, id_usuario_solicit, motivo)
VALUES ('50912834', LAST_INSERT_ID(), 1, 2, 1, 2, 'Prueba de incompatibilidad de vehículo');
INSERT INTO TRASLADO (id_solicitud, id_vehiculo, id_ruta) VALUES (LAST_INSERT_ID(), 2, 2);

-- Prueba 7: registrar respuesta con ci_paciente en una encuesta anónima
-- Esperado: ERROR 1644 - "La encuesta es anónima: ci_paciente debe ser NULL."
INSERT INTO RESPUESTA_ENCUESTA (id_encuesta, ci_paciente) VALUES (2, '45789231');

-- Prueba 8: modificar el estado de una solicitud ya COMPLETADA
-- Esperado: ERROR 1644 - "No se puede modificar el estado de una solicitud ya COMPLETADA."
UPDATE SOLICITUD_TRASLADO SET estado_solicitud = 'PENDIENTE' WHERE id_solicitud = 1;
