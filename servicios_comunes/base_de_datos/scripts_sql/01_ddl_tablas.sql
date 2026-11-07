-- =====================================================================
-- S.I.G.S.M. - Sistema de Gestión de Traslados
-- Hospital de Clínicas Dr. Manuel Quintela
-- Script DDL - Creación de base de datos y tablas
-- EVEdev - Programación Fullstack - I.S.B.O. 3°MJ
-- =====================================================================

DROP DATABASE IF EXISTS sigsm;

CREATE DATABASE sigsm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE sigsm;

SET FOREIGN_KEY_CHECKS = 0;

-- =====================================================================
-- BLOQUE 1 — USUARIOS Y ROLES
-- =====================================================================

CREATE TABLE USUARIO (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre_usuario VARCHAR(50) NOT NULL,
    contrasena_hash VARCHAR(255) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    activo BOOLEAN NOT NULL DEFAULT TRUE,
    fecha_creacion DATE NOT NULL DEFAULT(CURRENT_DATE),
    ultimo_login DATE NULL,
    CONSTRAINT uq_usuario_nombre_usuario UNIQUE (nombre_usuario),
    CONSTRAINT uq_usuario_email UNIQUE (email),
    CONSTRAINT chk_usuario_email_formato CHECK (
        email REGEXP '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\\.[A-Za-z]{2,}$'
    )
) ENGINE = InnoDB;

CREATE TABLE ROL (
    id_rol INT AUTO_INCREMENT PRIMARY KEY,
    nombre_rol VARCHAR(50) NOT NULL,
    descripcion VARCHAR(255) NULL,
    activo BOOLEAN NOT NULL DEFAULT TRUE,
    CONSTRAINT uq_rol_nombre UNIQUE (nombre_rol)
) ENGINE = InnoDB;

CREATE TABLE USUARIO_ROL (
    id_usuario INT NOT NULL,
    id_rol INT NOT NULL,
    fecha_asignacion DATE NOT NULL DEFAULT(CURRENT_DATE),
    PRIMARY KEY (id_usuario, id_rol),
    CONSTRAINT fk_usuariorol_usuario FOREIGN KEY (id_usuario) REFERENCES USUARIO (id_usuario) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_usuariorol_rol FOREIGN KEY (id_rol) REFERENCES ROL (id_rol) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE = InnoDB;

-- =====================================================================
-- BLOQUE 2 — CONTENIDOS Y ENCUESTAS
-- =====================================================================

CREATE TABLE PACIENTE (
    ci VARCHAR(15) PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    fecha_nacimiento DATE NOT NULL,
    email VARCHAR(150) NULL,
    telefono VARCHAR(20) NULL
) ENGINE = InnoDB;

CREATE TABLE CATEGORIA (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion VARCHAR(255) NULL,
    CONSTRAINT uq_categoria_nombre UNIQUE (nombre)
) ENGINE = InnoDB;

CREATE TABLE DOCUMENTO (
    id_documento INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(150) NOT NULL,
    archivo_url VARCHAR(255) NOT NULL,
    codigo_qr VARCHAR(255) NULL,
    fecha_expiracion_qr DATETIME NULL,
    fecha_carga DATE NOT NULL DEFAULT(CURRENT_DATE),
    id_categoria INT NOT NULL,
    id_usuario_carga INT NOT NULL,
    activo BOOLEAN NOT NULL DEFAULT TRUE,
    CONSTRAINT fk_documento_categoria FOREIGN KEY (id_categoria) REFERENCES CATEGORIA (id_categoria) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_documento_usuario FOREIGN KEY (id_usuario_carga) REFERENCES USUARIO (id_usuario) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE = InnoDB;

CREATE TABLE ENCUESTA (
    id_encuesta INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(150) NOT NULL,
    descripcion VARCHAR(255) NULL,
    es_anonima BOOLEAN NOT NULL DEFAULT FALSE,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    segmento VARCHAR(50) NOT NULL,
    id_usuario_creador INT NOT NULL,
    CONSTRAINT fk_encuesta_usuario FOREIGN KEY (id_usuario_creador) REFERENCES USUARIO (id_usuario) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT chk_encuesta_fechas CHECK (fecha_fin >= fecha_inicio)
) ENGINE = InnoDB;

CREATE TABLE PREGUNTA (
    id_pregunta INT AUTO_INCREMENT PRIMARY KEY,
    id_encuesta INT NOT NULL,
    texto_pregunta VARCHAR(255) NOT NULL,
    tipo_pregunta ENUM(
        'OPCION_MULTIPLE',
        'TEXTO_LIBRE',
        'ESCALA'
    ) NOT NULL,
    orden INT NOT NULL,
    requerida BOOLEAN NOT NULL DEFAULT TRUE,
    CONSTRAINT fk_pregunta_encuesta FOREIGN KEY (id_encuesta) REFERENCES ENCUESTA (id_encuesta) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT uq_pregunta_orden UNIQUE (id_encuesta, orden)
) ENGINE = InnoDB;

CREATE TABLE OPCION_RESPUESTA (
    id_opcion INT AUTO_INCREMENT PRIMARY KEY,
    id_pregunta INT NOT NULL,
    texto_opcion VARCHAR(150) NOT NULL,
    orden INT NOT NULL,
    CONSTRAINT fk_opcion_pregunta FOREIGN KEY (id_pregunta) REFERENCES PREGUNTA (id_pregunta) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT uq_opcion_orden UNIQUE (id_pregunta, orden)
) ENGINE = InnoDB;

CREATE TABLE RESPUESTA_ENCUESTA (
    id_resp_encuesta INT AUTO_INCREMENT PRIMARY KEY,
    id_encuesta INT NOT NULL,
    ci_paciente VARCHAR(15) NULL,
    fecha_envio DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_respencuesta_encuesta FOREIGN KEY (id_encuesta) REFERENCES ENCUESTA (id_encuesta) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_respencuesta_paciente FOREIGN KEY (ci_paciente) REFERENCES PACIENTE (ci) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT uq_respencuesta_paciente UNIQUE (id_encuesta, ci_paciente)
) ENGINE = InnoDB;

CREATE TABLE RESPUESTA_PREGUNTA (
    id_resp_pregunta INT AUTO_INCREMENT PRIMARY KEY,
    id_resp_encuesta INT NOT NULL,
    id_pregunta INT NOT NULL,
    id_opcion INT NULL,
    texto_libre VARCHAR(500) NULL,
    CONSTRAINT fk_resppregunta_respencuesta FOREIGN KEY (id_resp_encuesta) REFERENCES RESPUESTA_ENCUESTA (id_resp_encuesta) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_resppregunta_pregunta FOREIGN KEY (id_pregunta) REFERENCES PREGUNTA (id_pregunta) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_resppregunta_opcion FOREIGN KEY (id_opcion) REFERENCES OPCION_RESPUESTA (id_opcion) ON DELETE RESTRICT ON UPDATE CASCADE
    -- Nota: la exclusividad mutua id_opcion / texto_libre se valida con
    -- trigger (trg_resppregunta_exclusividad, ver 02_triggers.sql) porque
    -- MariaDB no admite CHECK sobre columnas que participan de una FK.
) ENGINE = InnoDB;

-- =====================================================================
-- BLOQUE 3 — TRASLADOS
-- =====================================================================

CREATE TABLE CANAL_SOLICITUD (
    id_canal INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    CONSTRAINT uq_canal_nombre UNIQUE (nombre)
) ENGINE = InnoDB;

-- Se eliminó TIPO_ELEMENTO y COMPATIBILIDAD. 
-- Ahora las capacidades son atributos del vehículo.
CREATE TABLE TIPO_VEHICULO (
    id_tipo_vehiculo INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion VARCHAR(255) NULL,
    apto_silla_ruedas BOOLEAN NOT NULL DEFAULT FALSE,
    apto_camilla BOOLEAN NOT NULL DEFAULT FALSE,
    apto_uti BOOLEAN NOT NULL DEFAULT FALSE,
    apto_biologico BOOLEAN NOT NULL DEFAULT FALSE,
    apto_insumos BOOLEAN NOT NULL DEFAULT FALSE,
    CONSTRAINT uq_tipovehiculo_nombre UNIQUE (nombre)
) ENGINE = InnoDB;

CREATE TABLE UBICACION (
    id_ubicacion INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    direccion VARCHAR(255) NOT NULL,
    tipo_ubicacion ENUM('HOSPITAL','CLINICA','DOMICILIO','OTRO') NOT NULL
) ENGINE = InnoDB;

CREATE TABLE RUTA (
    id_ruta INT AUTO_INCREMENT PRIMARY KEY,
    id_origen INT NOT NULL,
    id_destino INT NOT NULL,
    descripcion VARCHAR(255) NULL,
    distancia_km DECIMAL(6, 2) NOT NULL,
    CONSTRAINT fk_ruta_origen FOREIGN KEY (id_origen) REFERENCES UBICACION (id_ubicacion) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_ruta_destino FOREIGN KEY (id_destino) REFERENCES UBICACION (id_ubicacion) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT chk_ruta_distancia CHECK (distancia_km > 0)
) ENGINE = InnoDB;

-- SOLICITUD DE TRASLADO completamente normalizada
CREATE TABLE SOLICITUD_TRASLADO (
    id_solicitud INT AUTO_INCREMENT PRIMARY KEY,
    categoria_traslado ENUM('PACIENTE', 'MATERIAL_BIOLOGICO', 'EQUIPAMIENTO', 'INSUMOS') NOT NULL,
    ci_paciente VARCHAR(15) NULL,         -- NULL si es un objeto/logística
    detalle_carga VARCHAR(255) NULL,      -- Ej: "Tubo de sangre", "Ecógrafo" (NULL si es paciente)
    tipo_movilidad ENUM('AMBULATORIO', 'SILLA_RUEDAS', 'CAMILLA', 'NO_APLICA') NOT NULL,
    requiere_soporte_uti BOOLEAN NOT NULL DEFAULT FALSE,
    id_origen INT NOT NULL,
    id_destino INT NOT NULL,
    id_canal INT NOT NULL,
    id_usuario_solicit INT NOT NULL,
    fecha_solicitud DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    motivo VARCHAR(255) NOT NULL,
    estado_solicitud ENUM('PENDIENTE', 'ASIGNADA', 'EN_CAMINO', 'CANCELADA', 'COMPLETADA', 'RECHAZADA') NOT NULL DEFAULT 'PENDIENTE',
    CONSTRAINT fk_solicitud_paciente FOREIGN KEY (ci_paciente) REFERENCES PACIENTE (ci) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_solicitud_origen FOREIGN KEY (id_origen) REFERENCES UBICACION (id_ubicacion) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_solicitud_destino FOREIGN KEY (id_destino) REFERENCES UBICACION (id_ubicacion) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_solicitud_canal FOREIGN KEY (id_canal) REFERENCES CANAL_SOLICITUD (id_canal) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_solicitud_usuario FOREIGN KEY (id_usuario_solicit) REFERENCES USUARIO (id_usuario) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE = InnoDB;

CREATE TABLE VEHICULO (
    id_vehiculo INT AUTO_INCREMENT PRIMARY KEY,
    matricula VARCHAR(15) NOT NULL,
    marca VARCHAR(50) NOT NULL,
    modelo VARCHAR(50) NOT NULL,
    anho INT NOT NULL,
    id_tipo_vehiculo INT NOT NULL,
    activo BOOLEAN NOT NULL DEFAULT TRUE,
    estado_disponibilidad ENUM(
        'DISPONIBLE',
        'EN_USO',
        'MANTENIMIENTO'
    ) NOT NULL DEFAULT 'DISPONIBLE',
    CONSTRAINT uq_vehiculo_matricula UNIQUE (matricula),
    CONSTRAINT fk_vehiculo_tipo FOREIGN KEY (id_tipo_vehiculo) REFERENCES TIPO_VEHICULO (id_tipo_vehiculo) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT chk_vehiculo_anho CHECK (anho >= 1980)
) ENGINE = InnoDB;

CREATE TABLE PERSONAL (
    id_personal INT AUTO_INCREMENT PRIMARY KEY,
    ci VARCHAR(15) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    rol_personal ENUM(
        'CONDUCTOR',
        'ENFERMERO',
        'AUXILIAR'
    ) NOT NULL,
    activo BOOLEAN NOT NULL DEFAULT TRUE,
    CONSTRAINT uq_personal_ci UNIQUE (ci)
) ENGINE = InnoDB;

CREATE TABLE PROVEEDOR_EXTERNO (
    id_proveedor INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    contacto VARCHAR(150) NULL
) ENGINE = InnoDB;

CREATE TABLE ESTADO_TRASLADO (
    id_estado INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    CONSTRAINT uq_estadotraslado_nombre UNIQUE (nombre)
) ENGINE = InnoDB;

CREATE TABLE TRASLADO (
    id_traslado INT AUTO_INCREMENT PRIMARY KEY,
    id_solicitud INT NOT NULL,
    id_vehiculo INT NOT NULL,
    id_ruta INT NOT NULL,
    id_proveedor INT NULL,
    hora_salida_real DATETIME NULL,
    hora_llegada_real DATETIME NULL,
    hora_retorno DATETIME NULL,
    CONSTRAINT uq_traslado_solicitud UNIQUE (id_solicitud), -- relación 1:1
    CONSTRAINT fk_traslado_solicitud FOREIGN KEY (id_solicitud) REFERENCES SOLICITUD_TRASLADO (id_solicitud) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_traslado_vehiculo FOREIGN KEY (id_vehiculo) REFERENCES VEHICULO (id_vehiculo) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_traslado_ruta FOREIGN KEY (id_ruta) REFERENCES RUTA (id_ruta) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_traslado_proveedor FOREIGN KEY (id_proveedor) REFERENCES PROVEEDOR_EXTERNO (id_proveedor) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT chk_traslado_horas CHECK (
        (
            hora_llegada_real IS NULL
            OR hora_salida_real IS NULL
            OR hora_llegada_real >= hora_salida_real
        )
        AND (
            hora_retorno IS NULL
            OR hora_llegada_real IS NULL
            OR hora_retorno >= hora_llegada_real
        )
    )
) ENGINE = InnoDB;

CREATE TABLE PERSONAL_TRASLADO (
    id_traslado INT NOT NULL,
    id_personal INT NOT NULL,
    rol_en_traslado ENUM(
        'CONDUCTOR',
        'ENFERMERO',
        'APOYO'
    ) NOT NULL,
    PRIMARY KEY (id_traslado, id_personal),
    CONSTRAINT fk_persontraslado_traslado FOREIGN KEY (id_traslado) REFERENCES TRASLADO (id_traslado) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_persontraslado_personal FOREIGN KEY (id_personal) REFERENCES PERSONAL (id_personal) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE = InnoDB;

CREATE TABLE HISTORIAL_ESTADO (
    id_historial INT AUTO_INCREMENT PRIMARY KEY,
    id_traslado INT NOT NULL,
    id_estado INT NOT NULL,
    fecha_hora DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    id_usuario INT NOT NULL,
    observaciones VARCHAR(255) NULL,
    CONSTRAINT fk_historial_traslado FOREIGN KEY (id_traslado) REFERENCES TRASLADO (id_traslado) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_historial_estado FOREIGN KEY (id_estado) REFERENCES ESTADO_TRASLADO (id_estado) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_historial_usuario FOREIGN KEY (id_usuario) REFERENCES USUARIO (id_usuario) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE = InnoDB;

SET FOREIGN_KEY_CHECKS = 1;