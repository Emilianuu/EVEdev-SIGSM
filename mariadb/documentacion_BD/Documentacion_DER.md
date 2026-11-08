# Documentación DER - Programación Fullstack

## S.I.G.S.M. - EVEdev

**Equipo de Trabajo:**

| ROL                | C.I.      | APELLIDO | NOMBRE    | E-MAIL                       |
| :----------------- | :-------- | :------- | :-------- | :--------------------------- |
| **Coordinador**    | 5742262-3 | Chiessa  | Emiliano  | emiliano603099@gmail.com     |
| **Subcoordinador** | 5745443-2 | Gomez    | Ezequiel  | ezequielg0o0mez23@gmail.com  |
| **Integrante**     | 5771900-0 | Burgeño  | Valentino | valentinoburgeno16@gmail.com |

**I.S.B.O.** **Docente:** Acosta, Fabián  
**Fecha de culminación:** 02/09/26  
**SEGUNDA ENTREGA** | **3°MJ**

[Descargar Documentación DER (PDF)](./Documentacion_DER.pdf)

---

## Índice

1. [Introducción](#1-introducción)
2. [Bloque 1 - Usuarios y Roles](#2-bloque-1---usuarios-y-roles)
   - [Tabla: USUARIO](#tabla-usuario)
   - [Tabla: ROL](#tabla-rol)
   - [Tabla: USUARIO_ROL](#tabla-usuario_rol)
   - [Relaciones (Bloque 1)](#relaciones-bloque-1)
   - [Cambios respecto al DER original (Bloque 1)](#cambios-respecto-al-der-original-bloque-1)
   - [Restricciones no estructurales (Bloque 1)](#restricciones-no-estructurales-bloque-1)
   - [Normalización (Bloque 1)](#normalizacion-bloque-1)
3. [Bloque 2 - Contenidos y Encuestas](#3-bloque-2---contenidos-y-encuestas)
   - [Tabla: CATEGORIA](#tabla-categoria)
   - [Tabla: DOCUMENTO](#tabla-documento)
   - [Tabla: ENCUESTA](#tabla-encuesta)
   - [Tabla: PREGUNTA](#tabla-pregunta)
   - [Tabla: OPCION_RESPUESTA](#tabla-opcion_respuesta)
   - [Tabla: PACIENTE](#tabla-paciente)
   - [Tabla: RESPUESTA_ENCUESTA](#tabla-respuesta_encuesta)
   - [Tabla: RESPUESTA_PREGUNTA](#tabla-respuesta_pregunta)
   - [Relaciones (Bloque 2)](#relaciones-bloque-2)
   - [Cambios respecto al DER original (Bloque 2)](#cambios-respecto-al-der-original-bloque-2)
   - [Restricciones no estructurales (Bloque 2)](#restricciones-no-estructurales-bloque-2)
   - [Normalización (Bloque 2)](#normalizacion-bloque-2)
4. [Bloque 3 - Traslados](#4-bloque-3---traslados)
   - [Tabla: CANAL_SOLICITUD](#tabla-canal_solicitud)
   - [Tabla: TIPO_ELEMENTO](#tabla-tipo_elemento)
   - [Tabla: TIPO_VEHICULO](#tabla-tipo_vehiculo)
   - [Tabla: COMPATIBILIDAD](#tabla-compatibilidad)
   - [Tabla: UBICACION](#tabla-ubicacion)
   - [Tabla: RUTA](#tabla-ruta)
   - [Tabla: PACIENTE](#tabla-paciente-1)
   - [Tabla: SOLICITUD_TRASLADO](#tabla-solicitud_traslado)
   - [Tabla: VEHICULO](#tabla-vehiculo)
   - [Tabla: PERSONAL](#tabla-personal)
   - [Tabla: TRASLADO](#tabla-traslado)
   - [Tabla: PERSONAL_TRASLADO](#tabla-personal_traslado)
   - [Tabla: PROVEEDOR_EXTERNO](#tabla-proveedor_externo)
   - [Tabla: ESTADO_TRASLADO](#tabla-estado_traslado)
   - [Tabla: HISTORIAL_ESTADO](#tabla-historial_estado)
   - [Relaciones (Bloque 3)](#relaciones-bloque-3)
   - [Cambios respecto al DER original (Bloque 3)](#cambios-respecto-al-der-original-bloque-3)
   - [Restricciones no estructurales (Bloque 3)](#restricciones-no-estructurales-bloque-3)
   - [Normalización (Bloque 3)](#normalizacion-bloque-3)
5. [Implementación de la base de datos](#5-implementación-de-la-base-de-datos)
   - [5.1. Scripts entregados](#51-scripts-entregados)
   - [5.2. Convención general de reglas](#52-convención-general-de-reglas)
   - [5.3. Restricciones implementadas - Bloque 1](#53-restricciones-implementadas---bloque-1)
   - [5.4. Restricciones implementadas - Bloque 2](#54-restricciones-implementadas---bloque-2)
   - [5.5. Restricciones implementadas - Bloque 3](#55-restricciones-implementadas---bloque-3)
   - [5.6. Evidencia de funcionamiento](#56-evidencia-de-funcionamiento)

---

## 1. Introducción

Este documento describe en detalle el diseño relacional del sistema, organizado en tres bloques funcionales independientes.

Para cada bloque se documenta cada tabla, sus atributos y restricciones, las relaciones entre entidades, los cambios respecto al DER original, y las restricciones no estructurales (reglas de negocio que el esquema por sí solo no puede garantizar).

Los tres bloques son:

1. **Bloque 1 - Usuarios y Roles:** Gestión de cuentas de usuario y control de acceso por roles.
2. **Bloque 2 - Contenidos y Encuestas:** Documentos por categoría, encuestas, preguntas, opciones y respuestas de pacientes.
3. **Bloque 3 - Traslados:** Todo el flujo operativo de solicitudes y ejecución de traslados de pacientes.

---

## 2. Bloque 1 - Usuarios y Roles

Gestiona las cuentas de usuario del sistema y el mecanismo de control de acceso basado en roles. Un usuario puede tener múltiples roles simultáneamente y un rol puede estar asignado a múltiples usuarios, por lo que la relación N:M se resuelve con la tabla intermedia `USUARIO_ROL`.

### Tabla: USUARIO

Almacena las credenciales y datos personales de cada cuenta de acceso al sistema.

| Clave | Atributo        | Tipo    | Descripción                                                        |
| :---- | :-------------- | :------ | :----------------------------------------------------------------- |
| PK    | id_usuario      | int     | Identificador autoincremental único de la cuenta.                  |
|       | nombre_usuario  | string  | Nombre de login, debe ser único en el sistema.                     |
|       | contrasena_hash | string  | Hash de la contraseña (bcrypt/Argon2). Nunca texto plano.          |
|       | nombre          | string  | Nombre real del usuario.                                           |
|       | apellido        | string  | Apellido real del usuario.                                         |
|       | email           | string  | Correo electrónico, debe ser único y válido.                       |
|       | activo          | boolean | Soft-delete: `false` deshabilita el acceso sin borrar el registro. |
|       | fecha_creacion  | date    | Fecha en que se creó la cuenta (generada automáticamente).         |
|       | ultimo_login    | date    | Fecha del último inicio de sesión exitoso.                         |

### Tabla: ROL

Catálogo de roles disponibles en el sistema. Cada rol agrupa un conjunto de permisos.

| Clave | Atributo    | Tipo    | Descripción                                               |
| :---- | :---------- | :------ | :-------------------------------------------------------- |
| PK    | id_rol      | int     | Identificador autoincremental único del rol.              |
|       | nombre_rol  | string  | Nombre del rol (ej: ADMIN, OPERADOR, ENFERMERO).          |
|       | descripcion | string  | Descripción del alcance y permisos del rol.               |
|       | activo      | boolean | Permite desactivar un rol sin eliminarlo ni su historial. |

### Tabla: USUARIO_ROL

Tabla de intersección que resuelve la relación N:M entre usuarios y roles. La PK compuesta `(id_usuario, id_rol)` garantiza que no se asigne el mismo rol dos veces al mismo usuario.

| Clave   | Atributo         | Tipo | Descripción                                                 |
| :------ | :--------------- | :--- | :---------------------------------------------------------- |
| PK, FK1 | id_usuario       | int  | Referencia a USUARIO. Parte de la clave primaria compuesta. |
| PK, FK2 | id_rol           | int  | Referencia a ROL. Parte de la clave primaria compuesta.     |
|         | fecha_asignacion | date | Fecha en que se asignó el rol al usuario.                   |

### Relaciones (Bloque 1)

| Origen / Destino           | Tipo | Descripción                                                          |
| :------------------------- | :--- | :------------------------------------------------------------------- |
| USUARIO &rarr; USUARIO_ROL | 1:N  | Un usuario puede tener asignados varios roles a lo largo del tiempo. |
| ROL &rarr; USUARIO_ROL     | 1:N  | Un rol puede estar asignado a múltiples usuarios.                    |

### Cambios respecto al DER original (Bloque 1)

| Tipo       | Elemento               | Descripción                                                              |
| :--------- | :--------------------- | :----------------------------------------------------------------------- |
| Añadido    | USUARIO.fecha_creacion | Permite auditar cuándo se creó cada cuenta.                              |
| Añadido    | USUARIO.ultimo_login   | Facilita detectar cuentas inactivas y aplicar políticas de expiración.   |
| Añadido    | ROL.activo             | Permite desactivar roles obsoletos sin perder historial de asignaciones. |
| Modificado | USUARIO_ROL PK         | Se explicitan FK1 y FK2 para mayor claridad en la documentación.         |

### Restricciones no estructurales (Bloque 1)

- `nombre_usuario` debe ser `UNIQUE NOT NULL`.
- `email` debe ser `UNIQUE` y cumplir formato de correo válido.
- `contrasena_hash` no debe almacenarse en texto plano; el hash se genera fuera de la BD.
- Solo se pueden asignar roles con `activo = true`.
- Cuando un usuario es desactivado (`activo = false`) se deben invalidar todas sus sesiones activas.
- Un usuario con `activo = false` no puede iniciar sesión aunque sus credenciales sean correctas.

### Normalizacion (Bloque 1)

El bloque cumple con **3FN**:

- **1FN:** Todos los atributos son atómicos, sin grupos repetitivos.
- **2FN:** En `USUARIO_ROL`, `fecha_asignacion` depende de la PK compuesta completa (no de una parte).
- **3FN:** No existen dependencias transitivas; todos los atributos no-clave dependen directamente de su PK.

---

## 3. Bloque 2 - Contenidos y Encuestas

Gestiona dos funcionalidades: (a) el repositorio de documentos clínicos o informativos organizados por categorías, y (b) el sistema de encuestas de satisfacción completadas por los pacientes.

### Tabla: CATEGORIA

Clasifica los documentos del repositorio en grupos temáticos.

| Clave | Atributo     | Tipo   | Descripción                                             |
| :---- | :----------- | :----- | :------------------------------------------------------ |
| PK    | id_categoria | int    | Identificador autoincremental de la categoría.          |
|       | nombre       | string | Nombre de la categoría (ej: Protocolo, Consentimiento). |
|       | descripcion  | string | Descripción del tipo de documentos que agrupa.          |

### Tabla: DOCUMENTO

Repositorio de archivos (PDFs, imágenes, formularios) accesibles para el personal y pacientes.

| Clave | Atributo         | Tipo    | Descripción                                                  |
| :---- | :--------------- | :------ | :----------------------------------------------------------- |
| PK    | id_documento     | int     | Identificador autoincremental único del documento.           |
|       | titulo           | string  | Nombre descriptivo del documento.                            |
|       | archivo_url      | string  | Ruta o URL al archivo almacenado (almacenamiento externo).   |
|       | codigo_qr        | string  | Código QR para acceso rápido desde dispositivos móviles.     |
|       | fecha_carga      | date    | Fecha en que se subió el documento.                          |
| FK    | id_categoria     | int     | Categoría a la que pertenece el documento. Ref: `CATEGORIA`. |
| FK    | id_usuario_carga | int     | Usuario que subió el documento. Ref: `USUARIO` (Bloque 1).   |
|       | activo           | boolean | Soft-delete para ocultar documentos sin eliminarlos.         |

### Tabla: ENCUESTA

Define una encuesta de satisfacción o relevamiento clínico, con su período de vigencia y segmento objetivo.

| Clave | Atributo           | Tipo    | Descripción                                        |
| :---- | :----------------- | :------ | :------------------------------------------------- |
| PK    | id_encuesta        | int     | Identificador autoincremental de la encuesta.      |
|       | titulo             | string  | Título visible de la encuesta.                     |
|       | descripcion        | string  | Texto introductorio mostrado al paciente.          |
|       | es_anonima         | boolean | Si `true`, no se vincula la respuesta al paciente. |
|       | fecha_inicio       | date    | Fecha desde la que la encuesta está activa.        |
|       | fecha_fin          | date    | Fecha de cierre de la encuesta.                    |
|       | segmento           | string  | Grupo objetivo (ej: pacientes_traslado, todos).    |
| FK    | id_usuario_creador | int     | Usuario que creó la encuesta. Ref: `USUARIO`.      |

### Tabla: PREGUNTA

Cada ítem dentro de una encuesta. Soporta preguntas de opción múltiple y respuesta libre.

| Clave | Atributo       | Tipo    | Descripción                                                 |
| :---- | :------------- | :------ | :---------------------------------------------------------- |
| PK    | id_pregunta    | int     | Identificador autoincremental de la pregunta.               |
| FK    | id_encuesta    | int     | Encuesta a la que pertenece esta pregunta. Ref: `ENCUESTA`. |
|       | texto_pregunta | string  | Texto de la pregunta tal como se muestra al paciente.       |
|       | tipo_pregunta  | string  | Categoría: `OPCION_MULTIPLE`, `TEXTO_LIBRE`, `ESCALA`, etc. |
|       | orden          | int     | Posición de la pregunta dentro de la encuesta.              |
|       | requerida      | boolean | Si `true`, el paciente no puede saltear esta pregunta.      |

### Tabla: OPCION_RESPUESTA

Opciones predefinidas para preguntas de tipo opción múltiple. Las preguntas de texto libre no tienen registros aquí.

| Clave | Atributo     | Tipo   | Descripción                                               |
| :---- | :----------- | :----- | :-------------------------------------------------------- |
| PK    | id_opcion    | int    | Identificador autoincremental de la opción.               |
| FK    | id_pregunta  | int    | Pregunta a la que pertenece esta opción. Ref: `PREGUNTA`. |
|       | texto_opcion | string | Texto de la opción mostrado al paciente.                  |
|       | orden        | int    | Posición de la opción en la lista.                        |

### Tabla: PACIENTE

Entidad compartida entre Bloque 2 y Bloque 3. Almacena los datos personales del paciente y sirve como ancla de integridad referencial.

| Clave | Atributo         | Tipo   | Descripción                                                    |
| :---- | :--------------- | :----- | :------------------------------------------------------------- |
| PK    | ci               | string | Cédula de identidad, identificador natural único del paciente. |
|       | nombre           | string | Nombre del paciente.                                           |
|       | apellido         | string | Apellido del paciente.                                         |
|       | fecha_nacimiento | date   | Fecha de nacimiento para cálculo de edad.                      |
|       | email            | string | Correo electrónico para notificaciones.                        |
|       | telefono         | string | Número de teléfono de contacto.                                |

### Tabla: RESPUESTA_ENCUESTA

Representa una sesión de respuesta: un paciente completando una encuesta en particular. Actúa como cabecera del detalle por pregunta.

| Clave | Atributo         | Tipo   | Descripción                                                                        |
| :---- | :--------------- | :----- | :--------------------------------------------------------------------------------- |
| PK    | id_resp_encuesta | int    | Identificador autoincremental del envío.                                           |
| FK    | id_encuesta      | int    | Encuesta respondida. Ref: `ENCUESTA`.                                              |
| FK    | ci_paciente      | string | Paciente que respondió (puede ser `null` si `es_anonima = true`). Ref: `PACIENTE`. |
|       | fecha_envio      | date   | Fecha y hora en que se envió la respuesta.                                         |

### Tabla: RESPUESTA_PREGUNTA

Detalle de la respuesta a cada pregunta dentro de una sesión. Soporta tanto opción seleccionada como texto libre en la misma fila.

| Clave | Atributo         | Tipo   | Descripción                                                                  |
| :---- | :--------------- | :----- | :--------------------------------------------------------------------------- |
| PK    | id_resp_pregunta | int    | Identificador autoincremental del registro de respuesta.                     |
| FK    | id_resp_encuesta | int    | Sesión de respuesta a la que pertenece. Ref: `RESPUESTA_ENCUESTA`.           |
| FK    | id_pregunta      | int    | Pregunta respondida. Ref: `PREGUNTA`.                                        |
| FK    | id_opcion        | int    | Opción seleccionada (`null` si es texto libre). Ref: `OPCION_RESPUESTA`.     |
|       | texto_libre      | string | Respuesta de texto ingresada por el paciente (`null` si es opción múltiple). |

### Relaciones (Bloque 2)

| Origen / Destino                             | Tipo | Descripción                                                               |
| :------------------------------------------- | :--- | :------------------------------------------------------------------------ |
| CATEGORIA &rarr; DOCUMENTO                   | 1:N  | Una categoría agrupa muchos documentos.                                   |
| ENCUESTA &rarr; PREGUNTA                     | 1:N  | Una encuesta contiene múltiples preguntas.                                |
| PREGUNTA &rarr; OPCION_RESPUESTA             | 1:N  | Una pregunta puede tener múltiples opciones.                              |
| ENCUESTA &rarr; RESPUESTA_ENCUESTA           | 1:N  | Una encuesta puede recibir múltiples respuestas (de distintos pacientes). |
| PACIENTE &rarr; RESPUESTA_ENCUESTA           | 1:N  | Un paciente puede completar múltiples encuestas.                          |
| RESPUESTA_ENCUESTA &rarr; RESPUESTA_PREGUNTA | 1:N  | Cada sesión de respuesta se detalla pregunta a pregunta.                  |

### Cambios respecto al DER original (Bloque 2)

| Tipo       | Elemento                       | Descripción                                                                                                                               |
| :--------- | :----------------------------- | :---------------------------------------------------------------------------------------------------------------------------------------- |
| Añadido    | Entidad PACIENTE               | En el DER original `ci_paciente` era un string suelto en `RESPUESTA_ENCUESTA`. Se crea la entidad para garantizar integridad referencial. |
| Modificado | RESPUESTA_ENCUESTA.ci_paciente | Pasa de string a FK referenciando `PACIENTE.ci`, permitiendo JOIN y validación.                                                           |
| Añadido    | PACIENTE.email / telefono      | Datos de contacto necesarios para envío de encuestas y notificaciones.                                                                    |
| Modificado | RESPUESTA_PREGUNTA.id_opcion   | Se explicita que puede ser NULL para preguntas de texto libre.                                                                            |

### Restricciones no estructurales (Bloque 2)

- Una encuesta solo acepta respuestas si `fecha_inicio <= CURRENT_DATE <= fecha_fin`.
- Si `ENCUESTA.es_anonima = true`, `RESPUESTA_ENCUESTA.ci_paciente` debe ser `NULL`.
- Si `ENCUESTA.es_anonima = false`, `ci_paciente` no puede ser `NULL`.
- `RESPUESTA_PREGUNTA.id_opcion` y `texto_libre` son mutuamente exclusivos: exactamente uno debe tener valor según `tipo_pregunta`.
- Si `PREGUNTA.requerida = true`, toda `RESPUESTA_ENCUESTA` debe incluir un `RESPUESTA_PREGUNTA` para esa pregunta.
- El orden de preguntas y opciones debe ser único por encuesta/pregunta respectivamente.
- Un mismo paciente no debe poder responder la misma encuesta más de una vez (`UNIQUE` sobre `id_encuesta + ci_paciente`).

### Normalizacion (Bloque 2)

El bloque cumple con **3FN**:

- **1FN:** No hay atributos multivaluados; las opciones múltiples se modelan en `OPCION_RESPUESTA`.
- **2FN:** En `RESPUESTA_PREGUNTA`, `texto_libre` depende de la PK completa (`id_resp_pregunta`), no de una parte.
- **3FN:** No existen dependencias transitivas. El segmento de `ENCUESTA` es atributo directo, no derivado de otro.

---

## 4. Bloque 3 - Traslados

Modela el ciclo completo de un traslado de paciente: desde la solicitud inicial hasta el seguimiento del estado en tiempo real. Cubre vehículos, rutas, personal asignado, proveedores externos y el historial de estados.

### Tabla: CANAL_SOLICITUD

Catálogo de los medios por los cuales se puede originar una solicitud de traslado.

| Clave | Atributo | Tipo   | Descripción                                             |
| :---- | :------- | :----- | :------------------------------------------------------ |
| PK    | id_canal | int    | Identificador autoincremental del canal.                |
|       | nombre   | string | Nombre del canal (ej: Teléfono, Portal Web, App Móvil). |

### Tabla: TIPO_ELEMENTO

Catálogo de elementos o equipos médicos que puede requerir un traslado (ej: silla de ruedas, camilla, respirador).

| Clave | Atributo         | Tipo   | Descripción                                         |
| :---- | :--------------- | :----- | :-------------------------------------------------- |
| PK    | id_tipo_elemento | int    | Identificador autoincremental.                      |
|       | nombre           | string | Nombre del elemento (ej: Silla de Ruedas, Camilla). |
|       | descripcion      | string | Detalle del uso o características del elemento.     |

### Tabla: TIPO_VEHICULO

Catálogo de categorías de vehículos disponibles para traslados (ej: Ambulancia, Traslado Básico, Van).

| Clave | Atributo         | Tipo   | Descripción                                         |
| :---- | :--------------- | :----- | :-------------------------------------------------- |
| PK    | id_tipo_vehiculo | int    | Identificador autoincremental.                      |
|       | nombre           | string | Nombre del tipo (ej: Ambulancia UTI).               |
|       | descripcion      | string | Características y capacidades del tipo de vehículo. |

### Tabla: COMPATIBILIDAD

Tabla de intersección N:M que define qué tipos de elementos pueden transportarse en qué tipos de vehículos. Permite validar automáticamente si el vehículo asignado soporta el elemento requerido.

| Clave  | Atributo         | Tipo   | Descripción                                           |
| :----- | :--------------- | :----- | :---------------------------------------------------- |
| PK, FK | id_tipo_vehiculo | int    | Ref: `TIPO_VEHICULO`. Parte de la PK compuesta.       |
| PK, FK | id_tipo_elemento | int    | Ref: `TIPO_ELEMENTO`. Parte de la PK compuesta.       |
|        | observaciones    | string | Notas sobre condiciones especiales de compatibilidad. |

### Tabla: UBICACION

Catálogo de ubicaciones físicas que pueden ser origen o destino de traslados (hospitales, clínicas, domicilios, etc.).

| Clave | Atributo       | Tipo   | Descripción                                        |
| :---- | :------------- | :----- | :------------------------------------------------- |
| PK    | id_ubicacion   | int    | Identificador autoincremental.                     |
|       | nombre         | string | Nombre descriptivo de la ubicación.                |
|       | direccion      | string | Dirección postal completa.                         |
|       | tipo_ubicacion | string | Clasificación: HOSPITAL, CLINICA, DOMICILIO, OTRO. |

### Tabla: RUTA

Define trayectos predefinidos entre pares de ubicaciones. Evita recalcular la ruta en cada traslado y permite estadísticas de distancia.

| Clave | Atributo     | Tipo    | Descripción                               |
| :---- | :----------- | :------ | :---------------------------------------- |
| PK    | id_ruta      | int     | Identificador autoincremental.            |
| FK    | id_origen    | int     | Ubicación de partida. Ref: `UBICACION`.   |
| FK    | id_destino   | int     | Ubicación de llegada. Ref: `UBICACION`.   |
|       | descripcion  | string  | Descripción de la ruta o nombre informal. |
|       | distancia_km | decimal | Distancia en kilómetros del trayecto.     |

### Tabla: PACIENTE

Entidad propia introducida en el diseño mejorado. Reemplaza el campo `ci_paciente` (string suelto) de la versión original, garantizando integridad referencial.

| Clave | Atributo         | Tipo   | Descripción                                                    |
| :---- | :--------------- | :----- | :------------------------------------------------------------- |
| PK    | ci               | string | Cédula de identidad, identificador natural único del paciente. |
|       | nombre           | string | Nombre del paciente.                                           |
|       | apellido         | string | Apellido del paciente.                                         |
|       | fecha_nacimiento | date   | Fecha de nacimiento.                                           |

### Tabla: SOLICITUD_TRASLADO

Registro de cada pedido de traslado. Captura quién lo pidió, para qué paciente, con qué elemento, entre qué puntos y por qué canal.

| Clave | Atributo           | Tipo     | Descripción                                                        |
| :---- | :----------------- | :------- | :----------------------------------------------------------------- |
| PK    | id_solicitud       | int      | Identificador autoincremental de la solicitud.                     |
| FK    | ci_paciente        | string   | Paciente para quien se solicita el traslado. Ref: `PACIENTE`.      |
| FK    | id_tipo_elemento   | int      | Elemento requerido para el traslado. Ref: `TIPO_ELEMENTO`.         |
| FK    | id_origen          | int      | Ubicación de origen. Ref: `UBICACION`.                             |
| FK    | id_destino         | int      | Ubicación de destino. Ref: `UBICACION`.                            |
| FK    | id_canal           | int      | Canal por el que se realizó la solicitud. Ref: `CANAL_SOLICITUD`.  |
| FK    | id_usuario_solicit | int      | Usuario del sistema que registró la solicitud. Ref: `USUARIO`.     |
|       | fecha_solicitud    | datetime | Fecha y hora exacta del registro de la solicitud.                  |
|       | motivo             | string   | Motivo clínico o razón del traslado.                               |
|       | estado_solicitud   | string   | Estado actual: `PENDIENTE`, `ASIGNADA`, `CANCELADA`, `COMPLETADA`. |

### Tabla: VEHICULO

Inventario de vehículos de la flota. Incluye el estado de disponibilidad en tiempo real para facilitar la asignación.

| Clave | Atributo              | Tipo    | Descripción                                                                   |
| :---- | :-------------------- | :------ | :---------------------------------------------------------------------------- |
| PK    | id_vehiculo           | int     | Identificador autoincremental.                                                |
|       | matricula             | string  | Matrícula del vehículo, debe ser única.                                       |
|       | marca                 | string  | Marca del vehículo.                                                           |
|       | modelo                | string  | Modelo del vehículo.                                                          |
|       | anho                  | int     | Año de fabricación.                                                           |
| FK    | id_tipo_vehiculo      | int     | Tipo de vehículo. Ref: `TIPO_VEHICULO`.                                       |
|       | activo                | boolean | Soft-delete del vehículo de la flota.                                         |
|       | estado_disponibilidad | string  | `DISPONIBLE`, `EN_USO`, `MANTENIMIENTO`. Campo añadido en el diseño mejorado. |

### Tabla: PERSONAL

Unifica en una sola entidad a todos los integrantes del equipo operativo (conductores, enfermeros, auxiliares). Reemplaza los campos `id_chofer` e `id_enfermero` hardcodeados en la versión original.

| Clave | Atributo     | Tipo    | Descripción                                        |
| :---- | :----------- | :------ | :------------------------------------------------- |
| PK    | id_personal  | int     | Identificador autoincremental.                     |
|       | ci           | string  | Cédula de identidad del personal.                  |
|       | nombre       | string  | Nombre del integrante.                             |
|       | apellido     | string  | Apellido del integrante.                           |
|       | rol_personal | string  | Rol base: `CONDUCTOR`, `ENFERMERO`, `AUXILIAR`.    |
|       | activo       | boolean | Soft-delete para dar de baja sin perder historial. |

### Tabla: TRASLADO

Registro de la ejecución del traslado approved. Vincula la solicitud con el vehículo, la ruta y el proveedor externo cuando aplica.

| Clave | Atributo          | Tipo     | Descripción                                                                         |
| :---- | :---------------- | :------- | :---------------------------------------------------------------------------------- |
| PK    | id_traslado       | int      | Identificador autoincremental del traslado ejecutado.                               |
| FK    | id_solicitud      | int      | Solicitud que originó este traslado. Ref: `SOLICITUD_TRASLADO`.                     |
| FK    | id_vehiculo       | int      | Vehículo asignado. Ref: `VEHICULO`.                                                 |
| FK    | id_ruta           | int      | Ruta seguida. Ref: `RUTA`.                                                          |
| FK    | id_proveedor      | int      | Proveedor externo si el traslado se terceriza (nullable). Ref: `PROVEEDOR_EXTERNO`. |
|       | hora_salida_real  | datetime | Hora real de salida del vehículo.                                                   |
|       | hora_llegada_real | datetime | Hora real de llegada al destino.                                                    |
|       | hora_retorno      | datetime | Hora real de retorno a la base.                                                     |

### Tabla: PERSONAL_TRASLADO

Tabla de intersección N:M que asigna personal a traslados. Reemplaza los campos fijos `id_chofer` e `id_enfermero` del DER original, permitiendo cualquier cantidad y combinación de personal.

| Clave  | Atributo        | Tipo   | Descripción                                                         |
| :----- | :-------------- | :----- | :------------------------------------------------------------------ |
| PK, FK | id_traslado     | int    | Traslado al que se asigna el personal. Ref: `TRASLADO`.             |
| PK, FK | id_personal     | int    | Personal asignado. Ref: `PERSONAL`.                                 |
|        | rol_en_traslado | string | Rol específico en este traslado: `CONDUCTOR`, `ENFERMERO`, `APOYO`. |

### Tabla: PROVEEDOR_EXTERNO

Catálogo de empresas o personas externas que pueden proveer servicios de traslado cuando la flota propia no está disponible.

| Clave | Atributo     | Tipo   | Descripción                             |
| :---- | :----------- | :----- | :-------------------------------------- |
| PK    | id_proveedor | int    | Identificador autoincremental.          |
|       | nombre       | string | Razón social o nombre del proveedor.    |
|       | contacto     | string | Teléfono o email de contacto operativo. |

### Tabla: ESTADO_TRASLADO

Catálogo de estados posibles del ciclo de vida de un traslado.

| Clave | Atributo  | Tipo   | Descripción                                                             |
| :---- | :-------- | :----- | :---------------------------------------------------------------------- |
| PK    | id_estado | int    | Identificador autoincremental del estado.                               |
|       | nombre    | string | Nombre del estado (`ASIGNADO`, `EN_CAMINO`, `COMPLETADO`, `CANCELADO`). |

### Tabla: HISTORIAL_ESTADO

Bitácora de cada cambio de estado de un traslado. Permite auditar el progreso completo y calcular tiempos entre etapas.

| Clave | Atributo      | Tipo     | Descripción                                               |
| :---- | :------------ | :------- | :-------------------------------------------------------- |
| PK    | id_historial  | int      | Identificador autoincremental del registro de auditoría.  |
| FK    | id_traslado   | int      | Traslado auditado. Ref: `TRASLADO`.                       |
| FK    | id_estado     | int      | Nuevo estado registrado. Ref: `ESTADO_TRASLADO`.          |
|       | fecha_hora    | datetime | Marca de tiempo exacta del cambio de estado.              |
| FK    | id_usuario    | int      | Usuario que realizó el cambio. Ref: `USUARIO` (Bloque 1). |
|       | observaciones | string   | Notas libres sobre el cambio (ej: motivo de cancelación). |

### Relaciones (Bloque 3)

| Origen / Destino                          | Tipo | Descripción                                                                  |
| :---------------------------------------- | :--- | :--------------------------------------------------------------------------- |
| PACIENTE &rarr; SOLICITUD_TRASLADO        | 1:N  | Un paciente puede tener múltiples solicitudes de traslado.                   |
| CANAL_SOLICITUD &rarr; SOLICITUD_TRASLADO | 1:N  | Un canal puede originar múltiples solicitudes.                               |
| TIPO_ELEMENTO &rarr; SOLICITUD_TRASLADO   | 1:N  | Un tipo de elemento puede ser requerido en múltiples solicitudes.            |
| UBICACION &rarr; SOLICITUD_TRASLADO       | 1:N  | Una ubicación puede ser origen o destino de muchas solicitudes.              |
| TIPO_VEHICULO &harr; TIPO_ELEMENTO        | N:M  | Resuelta por `COMPATIBILIDAD`.                                               |
| TIPO_VEHICULO &rarr; VEHICULO             | 1:N  | Un tipo clasifica múltiples vehículos.                                       |
| UBICACION &rarr; RUTA                     | 1:N  | Una ubicación puede ser extremo de múltiples rutas.                          |
| SOLICITUD_TRASLADO &rarr; TRASLADO        | 1:1  | Una solicitud aprobada genera exactamente un traslado.                       |
| VEHICULO &rarr; TRASLADO                  | 1:N  | Un vehículo puede participar en múltiples traslados (en distintos momentos). |
| RUTA &rarr; TRASLADO                      | 1:N  | Una ruta puede usarse en múltiples traslados.                                |
| PROVEEDOR_EXTERNO &rarr; TRASLADO         | 1:N  | Un proveedor puede ejecutar múltiples traslados.                             |
| TRASLADO &harr; PERSONAL                  | N:M  | Resuelta por `PERSONAL_TRASLADO`.                                            |
| TRASLADO &rarr; HISTORIAL_ESTADO          | 1:N  | Un traslado acumula múltiples cambios de estado a lo largo del tiempo.       |
| ESTADO_TRASLADO &rarr; HISTORIAL_ESTADO   | 1:N  | Un estado puede aparecer múltiples veces en distintos traslados.             |

### Cambios respecto al DER original (Bloque 3)

| Tipo       | Elemento                       | Descripción                                                                                                                    |
| :--------- | :----------------------------- | :----------------------------------------------------------------------------------------------------------------------------- |
| Añadido    | Entidad PACIENTE               | `ci_paciente` era un string suelto sin integridad referencial en `SOLICITUD_TRASLADO`. Se crea la entidad propia.              |
| Añadido    | Entidad PERSONAL               | Unifica CHOFER y ENFERMERO bajo un esquema flexible con campo `rol_personal`.                                                  |
| Añadido    | Tabla PERSONAL_TRASLADO        | Reemplaza los campos hardcodeados `id_chofer` e `id_enfermero` de `TRASLADO`, permitiendo asignar N personas de cualquier rol. |
| Eliminado  | TRASLADO.id_chofer             | Absorbido por `PERSONAL_TRASLADO`.                                                                                             |
| Eliminado  | TRASLADO.id_enfermero          | Absorbido por `PERSONAL_TRASLADO`.                                                                                             |
| Añadido    | VEHICULO.estado_disponibilidad | Permite saber si el vehículo está `DISPONIBLE`, `EN_USO` o en `MANTENIMIENTO` sin consultar traslados activos.                 |
| Modificado | SOLICITUD_TRASLADO.ci_paciente | Cambia de string libre a FK referenciando la nueva entidad `PACIENTE`.                                                         |

### Restricciones no estructurales (Bloque 3)

- `id_origen != id_destino` en `SOLICITUD_TRASLADO` y en `RUTA` (un traslado no puede tener el mismo punto de partida y llegada).
- El `VEHICULO` asignado a un `TRASLADO` debe tener `estado_disponibilidad = DISPONIBLE` en el momento de la asignación.
- El `TIPO_VEHICULO` del vehículo asignado debe ser compatible con el `id_tipo_elemento` de la solicitud (verificable en `COMPATIBILIDAD`).
- Un `TRASLADO` no puede tener `id_proveedor` y personal propio (`PERSONAL_TRASLADO`) simultáneamente: son mutuamente excluyentes.
- `hora_llegada_real >= hora_salida_real; hora_retorno >= hora_llegada_real`.
- La `RUTA` asignada al traslado debe coincidir con `id_origen / id_destino` de la solicitud correspondiente.
- Un mismo `PERSONAL` no puede estar asignado a dos `TRASLADO` activos en el mismo horario.
- `estado_solicitud` debe seguir la secuencia: `PENDIENTE` &rarr; `ASIGNADA` &rarr; `COMPLETADA` (o `CANCELADA` desde cualquier estado anterior a `COMPLETADA`).
- Al cambiar el estado en `HISTORIAL_ESTADO`, debe actualizarse también `estado_solicitud` en `SOLICITUD_TRASLADO`.

### Normalizacion (Bloque 3)

El bloque cumple con **3FN**:

- **1FN:** Eliminación de atributos multivaluados — el personal se descompone en `PERSONAL_TRASLADO`.
- **2FN:** En `PERSONAL_TRASLADO`, `rol_en_traslado` depende de la PK compuesta completa `(id_traslado, id_personal)`.
- **3FN:** No hay dependencias transitivas. `distancia_km` en `RUTA` depende de `(id_origen, id_destino)`, que es su PK semántica.
- La separación de `ESTADO_TRASLADO` evita la anomalía de modificación: cambiar el nombre de un estado actualiza un único registro.

---

## 5. Implementación de la base de datos

Esta sección documenta cómo se llevó el DER de los tres bloques a una base de datos física en MySQL/MariaDB: el script DDL de las 24 tablas, los triggers que implementan las restricciones no estructurales, los datos de prueba, y la evidencia de funcionamiento.

### 5.1. Scripts entregados

| Archivo                        | Contenido                                                                                    |
| :----------------------------- | :------------------------------------------------------------------------------------------- |
| `01_ddl_tablas.sql`            | Creación de las 24 tablas: claves primarias, foráneas, `UNIQUE` y `CHECK`.                   |
| `02_triggers.sql`              | 10 triggers que implementan las restricciones no estructurales dependientes de otras tablas. |
| `03_dml_datos_prueba.sql`      | Datos de prueba para las 24 tablas de los tres bloques.                                      |
| `04_pruebas_restricciones.sql` | 8 sentencias que deben fallar a propósito, usadas como evidencia.                            |

### 5.2. Convención general de reglas

| Mecanismo                                  | Se usa cuando…                                                                     |
| :----------------------------------------- | :--------------------------------------------------------------------------------- |
| `PRIMARY KEY` / `FOREIGN KEY` / `UNIQUE`   | La regla es estructural pura: integridad referencial, unicidad, claves compuestas. |
| `CHECK`                                    | La regla depende solo de columnas que NO participan de una FK en la misma fila.    |
| `TRIGGER` (`BEFORE`/`AFTER` INSERT/UPDATE) | La regla depende de otra tabla, de una consulta, o de una columna que sí es FK.    |

_Nota técnica:_ Al ejecutar el DDL original con `CHECK` sobre columnas que son clave foránea (ej. `id_origen <> id_destino`), MariaDB devolvió el `Error 1901: Function or expression cannot be used in the CHECK clause`. Esas reglas se reimplementaron como triggers para garantizar compatibilidad total entre MariaDB y MySQL.

---

### 5.3. Restricciones implementadas - Bloque 1

| Restricción (según DER)                     | Implementación                                | Objeto SQL                  |
| :------------------------------------------ | :-------------------------------------------- | :-------------------------- |
| `nombre_usuario` único y no nulo            | `UNIQUE` + `NOT NULL`                         | Constraint (DDL)            |
| `email` único y con formato válido          | `UNIQUE` + `CHECK` con `REGEXP`               | `chk_usuario_email_formato` |
| `contrasena_hash` nunca en texto plano      | Se genera en la aplicación antes del `INSERT` | Capa de Aplicación          |
| Solo asignar roles con `activo = true`      | Trigger valida `ROL.activo` antes de insertar | `trg_usuariorol_rol_activo` |
| Invalidar sesiones al desactivar un usuario | Manejado en la capa de sesión de la app       | Capa de Aplicación          |
| Usuario inactivo no puede iniciar sesión    | Validado durante el login                     | Capa de Aplicación          |

---

### 5.4. Restricciones implementadas - Bloque 2

| Restricción (según DER)                                     | Implementación                                                       | Objeto SQL                      |
| :---------------------------------------------------------- | :------------------------------------------------------------------- | :------------------------------ |
| Respuesta válida solo si `fecha_inicio <= hoy <= fecha_fin` | Trigger valida el rango de vigencia antes de insertar                | `trg_respencuesta_reglas`       |
| Si `es_anonima = true`, `ci_paciente` es `NULL`             | Validado en trigger de sesión de respuesta                           | `trg_respencuesta_reglas`       |
| Si `es_anonima = false`, `ci_paciente` `NOT NULL`           | Validado en trigger de sesión de respuesta                           | `trg_respencuesta_reglas`       |
| `id_opcion` y `texto_libre` mutuamente exclusivos           | Trigger (reemplaza `CHECK` por involucrar FK)                        | `trg_resppregunta_exclusividad` |
| Pregunta `requerida = true` obliga a responderla            | La sesión se completa en varios INSERT; se valida al cerrar el envío | Capa de Aplicación              |
| Orden único por encuesta / pregunta                         | `UNIQUE (id_encuesta, orden)` / `UNIQUE (id_pregunta, orden)`        | Constraint (DDL)                |
| Un paciente no responde la encuesta dos veces               | `UNIQUE (id_encuesta, ci_paciente)`                                  | Constraint (DDL)                |

---

### 5.5. Restricciones implementadas - Bloque 3

| Restricción (según DER)                                                         | Implementación                                                                        | Objeto SQL                                                            |
| :------------------------------------------------------------------------------ | :------------------------------------------------------------------------------------ | :-------------------------------------------------------------------- |
| `id_origen <> id_destino` en `RUTA`                                             | Trigger (reemplaza `CHECK` por columna FK)                                            | `trg_ruta_origen_destino`                                             |
| `id_origen <> id_destino` en `SOLICITUD_TRASLADO`                               | Trigger (reemplaza `CHECK` por columna FK)                                            | `trg_solicitud_origen_destino`                                        |
| Vehículo asignado debe estar `DISPONIBLE`                                       | Trigger; además lo marca `EN_USO` al asignarlo                                        | `trg_traslado_validaciones`                                           |
| Tipo de vehículo compatible con elemento (`COMPATIBILIDAD`)                     | Trigger valida la tabla intermedia                                                    | `trg_traslado_validaciones`                                           |
| Ruta del traslado coincide con origen/destino de la solicitud                   | Trigger de validación integral de traslado                                            | `trg_traslado_validaciones`                                           |
| Proveedor externo y personal propio son excluyentes                             | Triggers en asignación de personal y proveedor                                        | `trg_personaltraslado_exclusion` / `trg_traslado_exclusion_proveedor` |
| Horas en orden cronológico correcto                                             | `CHECK (hora_llegada_real >= hora_salida_real AND hora_retorno >= hora_llegada_real)` | `chk_traslado_horas`                                                  |
| Flujo de `estado_solicitud` (`PENDIENTE` &rarr; `ASIGNADA` &rarr; `COMPLETADA`) | Trigger `BEFORE UPDATE`                                                               | `trg_solicitud_secuencia_estado`                                      |
| Cambio en `HISTORIAL_ESTADO` se propaga a la solicitud                          | Trigger `AFTER INSERT`; libera vehículo al finalizar o cancelar                       | `trg_historial_propaga_estado`                                        |
| Personal no asignado a dos traslados activos a la vez                           | No registra horario planificado previo, solo hora real ejecutada                      | Mejora Futura                                                         |

---

### 5.6. Evidencia de funcionamiento

Se ejecutó `04_pruebas_restricciones.sql` sobre la base cargada. Las 8 pruebas fallaron devolviendo el **Error 1644 (SQLSTATE '45000')**, confirmando que los triggers bloquean las operaciones inválidas:

| Pr. | Caso probado                                         | Mensaje de error obtenido (ERROR 1644)                                     |
| :-- | :--------------------------------------------------- | :------------------------------------------------------------------------- |
| 1   | Asignar un rol inactivo                              | _No se puede asignar un rol inactivo a un usuario._                        |
| 2   | Ruta con mismo origen y destino                      | _Una ruta no puede tener el mismo origen y destino._                       |
| 3   | Respuesta sin `id_opcion` ni `texto_libre`           | _Debe indicarse exactamente uno: id_opcion o texto_libre._                 |
| 4   | Respuesta con `id_opcion` y `texto_libre` a la vez   | _Debe indicarse exactamente uno: id_opcion o texto_libre._                 |
| 5   | Vehículo en `MANTENIMIENTO` asignado a traslado      | _El vehículo asignado no está DISPONIBLE._                                 |
| 6   | Vehículo incompatible con el elemento solicitado     | _El tipo de vehículo no es compatible con el tipo de elemento solicitado._ |
| 7   | `ci_paciente` cargado en encuesta anónima            | _La encuesta es anónima: ci_paciente debe ser NULL._                       |
| 8   | Modificar el estado de una solicitud ya `COMPLETADA` | _No se puede modificar el estado de una solicitud ya COMPLETADA._          |

#### Propagación automática de estados (Camino Correcto)

Al registrar en `HISTORIAL_ESTADO` la secuencia completa de un traslado (`ASIGNADO` &rarr; `EN_CAMINO` &rarr; `COMPLETADO`):

```sql
SELECT estado_solicitud FROM SOLICITUD_TRASLADO WHERE id_solicitud = 1;
-- Resultado: COMPLETADA

SELECT estado_disponibilidad FROM VEHICULO WHERE id_vehiculo = 1;
-- Resultado: DISPONIBLE
```
