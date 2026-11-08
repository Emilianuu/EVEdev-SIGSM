# S.I.G.S.M.

## Programación Fullstack

**EVEdev**

| ROL                | C.I.      | APELLIDO | NOMBRE    | E-MAIL                       |
| :----------------- | :-------- | :------- | :-------- | :--------------------------- |
| **Coordinador**    | 5742262-3 | Chiessa  | Emiliano  | emiliano603099@gmail.com     |
| **Subcoordinador** | 5745443-2 | Gomez    | Ezequiel  | ezequielg000mez23@gmail.com  |
| **Integrante**     | 5771900-0 | Burgueño | Valentino | valentinoburgeno16@gmail.com |

**I.S.B.O.** **Docente:** Acosta, Fabián  
**Fecha de culminación:** 02/09/26  
**SEGUNDA ENTREGA** | **3°MJ**

[Descargar Manual de Entorno de Desarrollo (PDF)](./Configuracion_Entorno_Desarrollo.pdf)

---

## Índice

1. [Introducción y objetivos](#1-introducción-y-objetivos)
2. [Requisitos de software](#2-requisitos-de-software)
3. [Proceso de instalación paso a paso](#3-proceso-de-instalación-paso-a-paso)
   - 3.1. [XAMPP (Apache, PHP, MySQL)](#31-xampp-apache-php-mysql)
     - [Paso 1: Descarga](#paso-1-descarga)
     - [Paso 2: Instalación](#paso-2-instalación)
     - [Paso 3: Configuración Inicial](#paso-3-configuración-inicial)
     - [Paso 3.1: Habilitar extensiones de PHP](#paso-31-habilitar-extensiones-de-php)
     - [Paso 3.2: Configurar puertos en Apache (Opcional - Solución de Errores)](#paso-32-configurar-puertos-en-apache-opcional---solución-de-errores)
     - [Paso 4: Verificación](#paso-4-verificación)
       - [Paso 4.1: Abrir http://localhost](#paso-41-abrir-httplocalhost)
       - [Paso 4.2: Comprobar la versión de PHP](#paso-42-comprobar-la-versión-de-php)
       - [Paso 4.3: Comprobar funcionamiento de MySQL](#paso-43-comprobar-funcionamiento-de-mysql)
   - 3.2. [VirtualBox](#32-virtualbox)
     - [Paso 1: Descarga](#paso-1-descarga-1)
     - [Paso 2: Instalación](#paso-2-instalación-1)
     - [Paso 3: Configuración Inicial](#paso-3-configuración-inicial-1)
     - [Paso 4: Verificación](#paso-4-verificación-1)
   - 3.3. [Git](#33-git)
     - [Paso 1: Descarga](#paso-1-descarga-2)
     - [Paso 2: Instalación](#paso-2-instalación-2)
     - [Paso 3: Configuración Inicial](#paso-3-configuración-inicial-2)
     - [Paso 4: Verificación](#paso-4-verificación-2)
   - 3.4. [Visual Studio Code](#34-visual-studio-code)
     - [Paso 1: Descarga](#paso-1-descarga-3)
     - [Paso 2: Instalación](#paso-2-instalación-3)
     - [Paso 3: Configuración Inicial](#paso-3-configuración-inicial-3)
     - [Paso 4: Verificación](#paso-4-verificación-3)
4. [Configuración de VS Code y extensiones](#4-configuración-de-vs-code-y-extensiones)
   - 4.1. [PHP Intelephense](#41-php-intelephense)
     - 4.1.1. [Configuración](#411-configuración)
   - 4.2. [Live Server](#42-live-server)
   - 4.3. [GitLens](#43-gitlens)
   - 4.4. [Prettier](#44-prettier)
     - 4.4.1. [Configuración](#441-configuración-formatear-al-guardar)
   - 4.5. [MySQL](#45-mysql)
     - 4.5.1. [Configuración](#451-configuración-de-la-conexión)
5. [Instrucciones verificables](#5-instrucciones-verificables)

---

## 1. Introducción y objetivos

Este manual contiene las instrucciones detalladas paso a paso para replicar desde cero el entorno de desarrollo local del proyecto. El cumplimiento estricto de las versiones y configuraciones aquí descritas garantiza la homogeneidad del entorno entre desarrolladores y evaluadores, previniendo errores de compatibilidad.

---

## 2. Requisitos de software

| Componente               | Herramienta        | Versión Requerida       | Propósito en el proyecto                                     | URL Oficial de Descarga                                 |
| :----------------------- | :----------------- | :---------------------- | :----------------------------------------------------------- | :------------------------------------------------------ |
| **XAMPP**                | XAMPP              | 8.2.12                  | Servidor web local, PHP y motor de Base de Datos.            | [apachefriends.org](https://www.apachefriends.org/)     |
| **Backend**              | PHP                | 8.2.12                  | Lenguaje de programación Backend.                            | _(Incluido en XAMPP)_                                   |
| **Base de Datos**        | MariaDB (MySQL)    | 8.2.12                  | Gestión de Bases de Datos.                                   | _(Incluido en XAMPP)_                                   |
| **Virtualización**       | VirtualBox         | 7.2.8                   | Sistema de Virtualización del servidor de pruebas GNU/Linux. | [virtualbox.org](https://www.virtualbox.org/)           |
| **Control de Versiones** | Git                | Última estable (2.54.0) | Control de versiones local y sincronización.                 | [git-scm.com](https://git-scm.com/)                     |
| **Editor de Código**     | Visual Studio Code | Última estable          | Editor de código fuente principal.                           | [code.visualstudio.com](https://code.visualstudio.com/) |
| **Navegador**            | Navegador Web      | Chrome / Firefox / Edge | Pruebas de interfaz y herramientas de desarrollo.            | N/A                                                     |

---

## 3. Proceso de instalación paso a paso

### 3.1. XAMPP (Apache, PHP, MySQL)

#### Paso 1: Descarga

Dirigirse a [https://www.apachefriends.org/](https://www.apachefriends.org/) y descargar el instalador de XAMPP para su sistema operativo (Windows, Linux o macOS). En este caso, utilizaremos la versión **8.2.12 (PHP 8.2.12)**.

#### Paso 2: Instalación

Ejecutar el instalador descargado (`xampp-windows-x64-8.2.12-0-VS16-installer.exe`).

> **¡ACLARACIÓN IMPORTANTE!** Si aparece un mensaje de advertencia sobre el Control de Cuentas de Usuario (UAC): _"Important! Because an activated User Account Control (UAC) on your system some functions of XAMPP are possibly restricted..."_, se debe evitar instalar XAMPP en `C:\Program Files`. Se recomienda encarecidamente instalarlo en la ruta por defecto: **`C:\xampp`**.

1. En la ventana de **Select Components**, dejar seleccionados los componentes predeterminados (Apache, MySQL, phpMyAdmin, etc.) y presionar **Next**.
2. En **Installation folder**, asegurar que la ruta sea **`C:\xampp`**.
3. Seleccionar el idioma (**English**) y continuar haciendo clic en **Next** hasta completar el asistente.
4. Al finalizar, dejar marcada la casilla _Do you want to start the Control Panel now?_ y presionar **Finish**.

#### Paso 3: Configuración Inicial

Este es el panel de control de XAMPP. Se recomienda que a la hora de ejecutarlo, se realice **con permisos de administrador** para evitar conflictos de escritura o bloqueos de servicios.

#### Paso 3.1: Habilitar extensiones de PHP

Para la versión actual del proyecto, se requiere únicamente la configuración base que XAMPP incluye por defecto. No es necesario modificar el archivo `php.ini` ni habilitar extensiones adicionales en esta etapa.

#### Paso 3.2: Configurar puertos en Apache (Opcional - Solución de Errores)

Por defecto, Apache utiliza el puerto 80. Si al hacer clic en **Start** el módulo de Apache se apaga inmediatamente o muestra un error en color rojo, significa que el puerto está ocupado por otra aplicación.

**Pasos para cambiar el puerto al 8080:**

1. En el panel de XAMPP, haz clic en el botón **Config** (en la línea de Apache) y selecciona **Apache (httpd.conf)**.
2. Presiona `Ctrl + B` (o `Ctrl + F`) y busca la línea: `Listen 80`.
3. Cámbiala por: `Listen 8080`.
4. Busca un poco más abajo la línea: `ServerName localhost:80` y cámbiala por: `ServerName localhost:8080`.
5. Guarda el archivo (`Ctrl + S` o `Ctrl + G` según el editor), cierra el bloc de notas y vuelve a hacer clic en **Start** en el panel de XAMPP.

_Nota: Si realizas este cambio, para acceder al proyecto en el navegador deberás usar la dirección `http://localhost:8080` en lugar de `http://localhost`._

#### Paso 4: Verificación

Lo primero que haremos para verificar que todo funcione correctamente será activar los módulos de **Apache** y **MySQL** haciendo clic en sus respectivos botones **Start** en el panel de control de XAMPP. Una vez que se muestren en fondo verde y con sus respectivos PIDs y puertos asignados, procedemos con las siguientes comprobaciones:

##### Paso 4.1: Abrir http://localhost

Ingresar a [http://localhost](http://localhost) (o `http://localhost:8080` si se cambió el puerto) en el navegador web mientras el módulo de Apache se encuentra activo. Esto desplegará el dashboard de bienvenida de XAMPP, confirmando su correcta instalación.

##### Paso 4.2: Comprobar la versión de PHP

Dentro del panel de control de XAMPP, haz clic en el botón **Shell** (a la derecha) para abrir la terminal integrada de XAMPP. Ejecuta el comando:

```bash
php -v
```
