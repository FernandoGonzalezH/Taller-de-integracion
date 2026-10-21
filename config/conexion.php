<?php

/**
 * Abre la base FG Motors y crea las tablas que todavía no existen.
 * La cuenta configurada necesita permiso CREATE DATABASE solo cuando la base
 * aún no existe, y permiso CREATE para inicializar tablas faltantes.
 */
function conectarBaseDatos(): mysqli {
    static $inicializado = false;

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    $host = getenv("FGMOTORS_DB_HOST") ?: "127.0.0.1";
    $usuario = getenv("FGMOTORS_DB_USER") ?: "root";
    $clave = getenv("FGMOTORS_DB_PASSWORD");
    $clave = $clave === false ? "" : $clave;
    $base = getenv("FGMOTORS_DB_NAME") ?: "fgmotors";

    if (!preg_match('/^[a-zA-Z0-9_]+$/', $base)) {
        throw new RuntimeException("El nombre de la base de datos configurado no es válido.");
    }

    if (!$inicializado) {
        try {
            $conexion = new mysqli($host, $usuario, $clave, $base);
        } catch (mysqli_sql_exception $error) {
            // MySQL/MariaDB devuelve 1049 cuando todavía no existe la base.
            if ((int)$error->getCode() !== 1049) {
                throw new RuntimeException("No se pudo conectar con MySQL/MariaDB. Revisá la configuración de la base de datos.", 0, $error);
            }

            $conexion = new mysqli($host, $usuario, $clave);
            $conexion->query("CREATE DATABASE IF NOT EXISTS `{$base}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $conexion->select_db($base);
        }

        $conexion->set_charset("utf8mb4");
        $tablasExistentes = [];
        $resultado = $conexion->query("SHOW TABLES");
        while ($fila = $resultado->fetch_row()) {
            $tablasExistentes[$fila[0]] = true;
        }

        $definiciones = [
            "personas" => "CREATE TABLE personas (
                id_persona INT UNSIGNED NOT NULL AUTO_INCREMENT,
                nombre VARCHAR(100) NOT NULL,
                apellido VARCHAR(100) NOT NULL,
                dni VARCHAR(30) NOT NULL DEFAULT '',
                nacimiento VARCHAR(10) NOT NULL DEFAULT '',
                localidad VARCHAR(100) NOT NULL DEFAULT '',
                direccion VARCHAR(180) NOT NULL DEFAULT '',
                telefono VARCHAR(40) NOT NULL DEFAULT '',
                email VARCHAR(190) NOT NULL DEFAULT '',
                PRIMARY KEY (id_persona)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "empleados" => "CREATE TABLE empleados (
                legajo INT UNSIGNED NOT NULL AUTO_INCREMENT,
                id_persona INT UNSIGNED NOT NULL,
                sueldo DECIMAL(14,2) NOT NULL DEFAULT 0,
                area VARCHAR(120) NOT NULL DEFAULT '',
                rol VARCHAR(120) NOT NULL DEFAULT '',
                ingreso VARCHAR(10) NOT NULL DEFAULT '',
                aoi CHAR(1) NOT NULL DEFAULT 'a',
                PRIMARY KEY (legajo),
                UNIQUE KEY uq_empleados_persona (id_persona),
                CONSTRAINT fk_empleados_persona FOREIGN KEY (id_persona) REFERENCES personas (id_persona)
                    ON UPDATE CASCADE ON DELETE RESTRICT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "clientes" => "CREATE TABLE clientes (
                id_cliente INT UNSIGNED NOT NULL AUTO_INCREMENT,
                id_persona INT UNSIGNED NOT NULL,
                PRIMARY KEY (id_cliente),
                UNIQUE KEY uq_clientes_persona (id_persona),
                CONSTRAINT fk_clientes_persona FOREIGN KEY (id_persona) REFERENCES personas (id_persona)
                    ON UPDATE CASCADE ON DELETE RESTRICT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "vendedores" => "CREATE TABLE vendedores (
                legajo INT UNSIGNED NOT NULL,
                porcentaje DECIMAL(6,2) NOT NULL DEFAULT 0,
                comision DECIMAL(14,2) NOT NULL DEFAULT 0,
                total DECIMAL(14,2) NOT NULL DEFAULT 0,
                PRIMARY KEY (legajo),
                CONSTRAINT fk_vendedores_empleado FOREIGN KEY (legajo) REFERENCES empleados (legajo)
                    ON UPDATE CASCADE ON DELETE RESTRICT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "marcas" => "CREATE TABLE marcas (
                id_marca INT UNSIGNED NOT NULL AUTO_INCREMENT,
                nombre VARCHAR(100) NOT NULL,
                PRIMARY KEY (id_marca),
                UNIQUE KEY uq_marcas_nombre (nombre)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "modelos" => "CREATE TABLE modelos (
                id_modelo INT UNSIGNED NOT NULL AUTO_INCREMENT,
                id_marca INT UNSIGNED NOT NULL,
                nombre VARCHAR(120) NOT NULL,
                PRIMARY KEY (id_modelo),
                UNIQUE KEY uq_modelos_marca_nombre (id_marca, nombre),
                CONSTRAINT fk_modelos_marca FOREIGN KEY (id_marca) REFERENCES marcas (id_marca)
                    ON UPDATE CASCADE ON DELETE RESTRICT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "vehiculos" => "CREATE TABLE vehiculos (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                id_modelo INT UNSIGNED NOT NULL,
                tipo VARCHAR(20) NOT NULL,
                precio DECIMAL(14,2) NOT NULL,
                km INT UNSIGNED NOT NULL DEFAULT 0,
                anio SMALLINT UNSIGNED NOT NULL,
                estado VARCHAR(20) NOT NULL,
                transmision VARCHAR(100) NOT NULL DEFAULT '',
                combustible VARCHAR(60) NOT NULL DEFAULT '',
                motor VARCHAR(100) NOT NULL DEFAULT '',
                sov CHAR(1) NOT NULL DEFAULT 's',
                PRIMARY KEY (id),
                KEY idx_vehiculos_modelo (id_modelo),
                KEY idx_vehiculos_tipo_estado (tipo, estado),
                KEY idx_vehiculos_disponibilidad (sov),
                CONSTRAINT fk_vehiculos_modelo FOREIGN KEY (id_modelo) REFERENCES modelos (id_modelo)
                    ON UPDATE CASCADE ON DELETE RESTRICT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "autos" => "CREATE TABLE autos (
                id INT UNSIGNED NOT NULL,
                puertas INT UNSIGNED NULL,
                traccion VARCHAR(60) NOT NULL DEFAULT '',
                pasajeros INT UNSIGNED NULL,
                carroceria VARCHAR(100) NOT NULL DEFAULT '',
                PRIMARY KEY (id),
                CONSTRAINT fk_autos_vehiculo FOREIGN KEY (id) REFERENCES vehiculos (id)
                    ON UPDATE CASCADE ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "motos" => "CREATE TABLE motos (
                id INT UNSIGNED NOT NULL,
                cilindros INT UNSIGNED NULL,
                tipomoto VARCHAR(100) NOT NULL DEFAULT '',
                PRIMARY KEY (id),
                CONSTRAINT fk_motos_vehiculo FOREIGN KEY (id) REFERENCES vehiculos (id)
                    ON UPDATE CASCADE ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "ventas" => "CREATE TABLE ventas (
                id_venta INT UNSIGNED NOT NULL AUTO_INCREMENT,
                id_cliente INT UNSIGNED NOT NULL,
                id_vehiculo INT UNSIGNED NOT NULL,
                legajo INT UNSIGNED NOT NULL,
                precio DECIMAL(14,2) NOT NULL,
                fecha INT NOT NULL,
                PRIMARY KEY (id_venta),
                UNIQUE KEY uq_ventas_vehiculo (id_vehiculo),
                KEY idx_ventas_cliente (id_cliente),
                KEY idx_ventas_legajo (legajo),
                KEY idx_ventas_fecha (fecha),
                CONSTRAINT fk_ventas_cliente FOREIGN KEY (id_cliente) REFERENCES clientes (id_cliente)
                    ON UPDATE CASCADE ON DELETE RESTRICT,
                CONSTRAINT fk_ventas_vehiculo FOREIGN KEY (id_vehiculo) REFERENCES vehiculos (id)
                    ON UPDATE CASCADE ON DELETE RESTRICT,
                CONSTRAINT fk_ventas_vendedor FOREIGN KEY (legajo) REFERENCES empleados (legajo)
                    ON UPDATE CASCADE ON DELETE RESTRICT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "clave_acceso" => "CREATE TABLE clave_acceso (
                clave_hash VARCHAR(255) NOT NULL,
                PRIMARY KEY (clave_hash)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        ];

        foreach ($definiciones as $nombre => $sql) {
            if (!isset($tablasExistentes[$nombre])) {
                $conexion->query($sql);
            }
        }

        $inicializado = true;
        return $conexion;
    }

    $conexion = new mysqli($host, $usuario, $clave, $base);
    $conexion->set_charset("utf8mb4");
    return $conexion;
}
