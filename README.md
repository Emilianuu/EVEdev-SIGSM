# S.I.G.S.M. - Sistema de Información

S.I.G.S.M. es un sistema de gestión desarrollado para optimizar la administración y los flujos de trabajo de los módulos del proyecto. El sistema está diseñado poniendo un fuerte énfasis en la seguridad del lado del servidor, la eficiencia en el manejo de datos y una integración limpia entre la lógica de negocio y la interfaz de usuario.

---

## Tecnologías Utilizadas

El núcleo del sistema se ha construido seleccionando tecnologías robustas que garantizan estabilidad y un rendimiento óptimo en un entorno web:

- **Backend:** PHP (Apache)
- **Base de Datos:** MySQL / MariaDB
- **Frontend:** HTML5, CSS3 y JavaScript (ES6+)
- **Infraestructura y Contenedores:** Docker, Docker Compose y Bash Scripting
- **Control de Versiones:** Git

---

## Justificación Tecnológica (Arquitectura)

La selección de la arquitectura de este proyecto se basa en la robustez, seguridad y portabilidad, integrando tanto el desarrollo FullStack como la Administración de Sistemas:

### Procesamiento del Lado del Servidor (Backend con PHP)

A diferencia de los scripts del lado del cliente, **el código de S.I.G.S.M. se ejecuta íntegramente en el servidor**. Esto aporta ventajas críticas para el sistema:

- **Seguridad Absoluta del Código:** El servidor procesa la lógica y genera el HTML que se envía al cliente. El usuario final solo recibe el resultado del script, sin tener ningún medio de acceso al código fuente que produjo dicho resultado.
- **Gestión Eficiente:** Permite una integración nativa y fluida con el sistema de bases de datos para la persistencia de información y un manejo seguro de las sesiones de usuario.

### Entorno Containerizado y Sistema Operativo (DevOps / SysAdmin)

El sistema está empaquetado utilizando Docker para garantizar la paridad entre entornos (desarrollo, pruebas y producción) y preparar el sistema para su exportación como Appliance Virtual (.ova) para el Hospital de Clínicas.

- **Aislamiento:** Servicios de PHP/Apache y MariaDB separados y orquestados mediante `docker-compose.yml`.
- **Configuración Optimizada:** Archivos `php.ini` a medida para permitir la carga de documentos pesados y control del OPcache.
- **Administración del SO:** Scripts automatizados en Bash para gestionar respaldos, auditoría de logs, usuarios, grupos y reglas de firewall en el servidor anfitrión.

---

## Estructura del Proyecto

A continuación se detalla la organización de archivos y directorios en la raíz del repositorio, la cual refleja tanto el código fuente como la infraestructura:

```text
.
├── docker-compose.yml           # Orquestación de contenedores y servicios
├── menu.sh                      # Script principal de gestión del SO anfitrión
├── README.md                    # Documentación principal del proyecto
├── .env                         # Variables de entorno (Plantilla local)
├── mariadb/                     # INFRAESTRUCTURA DE BASE DE DATOS
│   ├── Dockerfile               # Imagen personalizada de MariaDB
│   ├── scripts_sql/             # DDL, Triggers y DML para inicialización automática
│   └── documentacion_BD/        # Diagramas DER y documentación de datos
├── php/                         # APLICACIÓN FULLSTACK (Backend/Frontend)
│   ├── Dockerfile               # Imagen de PHP-Apache con extensiones requeridas
│   ├── php.ini                  # Configuración personalizada del motor PHP
│   ├── index.php                # Punto de entrada de la aplicación
│   ├── modulo_documentacion/    # Módulo de gestión documental (MVC)
│   ├── modulo_traslados/        # Módulo de gestión de traslados (MVC)
│   └── servicios_comunes/       # Lógica global compartida y vistas generales
└── scripts/                     # SCRIPTS DE ADMINISTRACIÓN DE SISTEMAS (Linux)
    ├── firewall.sh              # Reglas de iptables/ufw
    ├── grupos.sh                # Gestión de grupos del sistema
    ├── logs.sh                  # Rotación y análisis de bitácoras
    ├── respaldos.sh             # Automatización de copias de seguridad
    └── usuarios.sh              # Gestión de usuarios del SO
```

---

## Instalación y Configuración con Docker (Recomendada)

El uso de Docker es el método oficial para desplegar S.I.G.S.M., ya que automatiza la configuración del servidor, la base de datos y la persistencia de archivos.

### Pre-requisitos

1. **Docker** y **Docker Compose** instalados en el sistema anfitrión.
2. **Git** instalado.

### Pasos de Despliegue

1. **Clonar el repositorio:**

   ```bash
   git clone https://github.com/tu-usuario/nombre-repositorio.git
   cd nombre-repositorio
   ```

2. **Configurar Variables de Entorno:**
   Asegúrate de configurar el archivo `.env` en la raíz del proyecto basándote en la plantilla `.env.example` proporcionada. Debe contener las credenciales seguras para MariaDB.

3. **Levantar los contenedores:**
   Ejecuta el siguiente comando en la raíz del proyecto para construir y levantar los servicios en segundo plano:

   ```bash
   docker-compose up -d --build
   ```

4. **Acceso al Sistema:**
   Una vez que los contenedores estén en ejecución, la base de datos se inicializará automáticamente consumiendo los scripts en `mariadb/scripts_sql/`.
   Accede a la aplicación a través de tu navegador apuntando al servidor.

### Notas sobre Persistencia

El archivo `docker-compose.yml` está configurado con volúmenes físicos para garantizar la retención de datos. La base de datos y los archivos adjuntos no se perderán al reiniciar o destruir los contenedores.

---

## Instalación Local Tradicional (Alternativa manual)

Si necesitas instalar el proyecto de forma nativa en un servidor LAMP/XAMPP/Laragon.

1. Clona el repositorio y apunta el _Document Root_ de tu servidor Apache a la carpeta `php/`.
2. Accede a tu gestor de base de datos local y crea la base `sigsm` (cotejamiento `utf8mb4_unicode_ci`).
3. Importa los scripts SQL ubicados en `mariadb/scripts_sql/` respetando su orden numérico (01, 02, 03...).
4. Configura `php/servicios_comunes/conexion_BD/conexion.php` con tus credenciales locales.
5. **Importante:** Asegúrate de configurar tu `php.ini` local aumentando `upload_max_filesize` y `post_max_size`.

---

## Convenciones de Commits

Para mantener un historial de versiones limpio, legible y estructurado, adoptamos la especificación de **Conventional Commits**.

| Tipo       | Descripción                                                             | Ejemplo                                                       |
| :--------- | :---------------------------------------------------------------------- | :------------------------------------------------------------ |
| `feat`     | Incorporación de una nueva funcionalidad o script.                      | `feat(scripts): agregar automatización de respaldos`          |
| `fix`      | Corrección de un error o bug en el código.                              | `fix(conexion_BD): corregir parámetro de conexión a MySQL`    |
| `docs`     | Cambios exclusivamente en la documentación.                             | `docs: actualizar estructura del proyecto en README`          |
| `style`    | Cambios que no afectan la lógica (espacios, formato, punto y coma).     | `style(traslados): ajustar estilos CSS en módulo traslados`   |
| `refactor` | Reestructuración de código sin cambiar comportamiento ni corregir bugs. | `refactor(autenticacion): optimizar verificación de sesiones` |
| `perf`     | Cambios orientados a mejorar el rendimiento.                            | `perf(docker): optimizar caché de capas en Dockerfile`        |
| `test`     | Añadir o corregir pruebas unitarias/integración.                        | `test(validaciones): agregar pruebas para entrada de datos`   |
| `chore`    | Tareas de mantenimiento, actualización de scripts o herramientas.       | `chore: integrar estructura completa de infraestructura`      |
