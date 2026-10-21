-- Esquema de FG Motors para MySQL/MariaDB.
-- Importar en phpMyAdmin o ejecutar: mysql -u usuario -p < database.sql
CREATE DATABASE IF NOT EXISTS fgmotors
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE fgmotors;

CREATE TABLE IF NOT EXISTS personas (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS empleados (
  legajo INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_persona INT UNSIGNED NOT NULL,
  sueldo DECIMAL(14,2) NOT NULL DEFAULT 0,
  area VARCHAR(120) NOT NULL DEFAULT '',
  rol VARCHAR(120) NOT NULL DEFAULT '',
  ingreso VARCHAR(10) NOT NULL DEFAULT '',
  aoi CHAR(1) NOT NULL DEFAULT 'a',
  PRIMARY KEY (legajo),
  UNIQUE KEY uq_empleados_persona (id_persona),
  CONSTRAINT fk_empleados_persona FOREIGN KEY (id_persona) REFERENCES personas (id_persona) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS clientes (
  id_cliente INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_persona INT UNSIGNED NOT NULL,
  PRIMARY KEY (id_cliente),
  UNIQUE KEY uq_clientes_persona (id_persona),
  CONSTRAINT fk_clientes_persona FOREIGN KEY (id_persona) REFERENCES personas (id_persona) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS vendedores (
  legajo INT UNSIGNED NOT NULL,
  porcentaje DECIMAL(6,2) NOT NULL DEFAULT 0,
  comision DECIMAL(14,2) NOT NULL DEFAULT 0,
  total DECIMAL(14,2) NOT NULL DEFAULT 0,
  PRIMARY KEY (legajo),
  CONSTRAINT fk_vendedores_empleado FOREIGN KEY (legajo) REFERENCES empleados (legajo) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS marcas (
  id_marca INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nombre VARCHAR(100) NOT NULL,
  PRIMARY KEY (id_marca),
  UNIQUE KEY uq_marcas_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS modelos (
  id_modelo INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_marca INT UNSIGNED NOT NULL,
  nombre VARCHAR(120) NOT NULL,
  PRIMARY KEY (id_modelo),
  UNIQUE KEY uq_modelos_marca_nombre (id_marca, nombre),
  CONSTRAINT fk_modelos_marca FOREIGN KEY (id_marca) REFERENCES marcas (id_marca)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS vehiculos (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS autos (
  id INT UNSIGNED NOT NULL,
  puertas INT UNSIGNED NULL,
  traccion VARCHAR(60) NOT NULL DEFAULT '',
  pasajeros INT UNSIGNED NULL,
  carroceria VARCHAR(100) NOT NULL DEFAULT '',
  PRIMARY KEY (id),
  CONSTRAINT fk_autos_vehiculo FOREIGN KEY (id) REFERENCES vehiculos (id) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS motos (
  id INT UNSIGNED NOT NULL,
  cilindros INT UNSIGNED NULL,
  tipomoto VARCHAR(100) NOT NULL DEFAULT '',
  PRIMARY KEY (id),
  CONSTRAINT fk_motos_vehiculo FOREIGN KEY (id) REFERENCES vehiculos (id) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ventas (
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
  CONSTRAINT fk_ventas_cliente FOREIGN KEY (id_cliente) REFERENCES clientes (id_cliente) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_ventas_vehiculo FOREIGN KEY (id_vehiculo) REFERENCES vehiculos (id) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_ventas_vendedor FOREIGN KEY (legajo) REFERENCES empleados (legajo) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS clave_acceso (
  clave_hash VARCHAR(255) NOT NULL,
  PRIMARY KEY (clave_hash)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
