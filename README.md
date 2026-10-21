# FG Motors

Sistema web de gestión para una concesionaria. Permite administrar vehículos, clientes, empleados, vendedores y ventas, y consultar informes descargables en PDF.

## Requisitos

- PHP 8 o superior, con la extensión `mysqli` habilitada.
- MySQL o MariaDB.
- Servidor web compatible con PHP, como Apache o Nginx con PHP-FPM.
- Acceso a Internet desde el navegador para cargar las bibliotecas de generación de PDF.

## Instalación

1. Copiá o descargá el proyecto en un servidor con PHP 8 o superior, extensión `mysqli`, y MySQL o MariaDB. Puede ser Apache, Nginx con PHP-FPM, el servidor integrado de PHP para desarrollo, o un hosting compatible.
2. Creá la base y las tablas importando [`database.sql`](database.sql) con la herramienta disponible en tu entorno, por ejemplo phpMyAdmin o el cliente `mysql`.
3. Si querés registros de muestra, importá [`datos_ejemplo.sql`](datos_ejemplo.sql) después de `database.sql`. Este paso es opcional.
4. Configurá las variables de conexión a MySQL/MariaDB indicadas abajo en el entorno del servidor.
5. Configurá el servidor web para que el directorio del proyecto sea accesible y abrí su URL. Ingresá con la clave inicial `fgmotors` y cambiala desde **Opciones → Cambiar clave**.

Para desarrollo local, XAMPP es una alternativa que incluye Apache y MySQL/MariaDB. Con XAMPP, copiá el proyecto a `htdocs`, iniciá ambos servicios e importá los dos archivos desde phpMyAdmin. La configuración predeterminada (`127.0.0.1`, usuario `root`, contraseña vacía) funciona en una instalación local típica de XAMPP; otros entornos requieren sus propios valores.

Los archivos SQL están separados: `database.sql` crea la estructura y `datos_ejemplo.sql` agrega los registros. Importá los datos después de crear las tablas y solo una vez en una base nueva.

## Configuración de la base de datos

La conexión se configura mediante variables de entorno:

| Variable | Valor predeterminado |
| --- | --- |
| `FGMOTORS_DB_HOST` | `127.0.0.1` |
| `FGMOTORS_DB_USER` | `root` |
| `FGMOTORS_DB_PASSWORD` | vacío |
| `FGMOTORS_DB_NAME` | `fgmotors` |

La configuración central está en `config/conexion.php` y es utilizada por los modelos.

En una instalación publicada, usá un usuario específico de MySQL/MariaDB con los permisos necesarios para la aplicación. No uses la cuenta administrativa del motor ni una contraseña vacía.

## Acceso

La clave inicial es `fgmotors`. Cambiala desde **Opciones → Cambiar clave** después del primer ingreso. La clave se almacena como hash en la tabla `clave_acceso`.

## Informes PDF

Los informes se generan en el navegador mediante bibliotecas externas cargadas desde CDN. El navegador debe tener acceso a Internet para descargar los PDF.

## Estructura del proyecto

| Directorio | Contenido |
| --- | --- |
| `controladores/` | Gestión de solicitudes y coordinación de la aplicación |
| `modelos/` | Consultas y operaciones sobre los datos |
| `vistas/` | Pantallas e informes |
| `config/` | Configuración de conexión |
| `public/` | CSS, JavaScript e imágenes |

## Datos

La aplicación también puede crear la base y las tablas vacías automáticamente si el usuario de MySQL tiene permisos para ello. Para cargar los datos de ejemplo, importá `datos_ejemplo.sql` después de crear las tablas.
