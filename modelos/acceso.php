<?php
require_once __DIR__ . "/../config/conexion.php";


function verificarClaveAcceso(string $clave): bool {
    $conexion = conectarBaseDatos();
    if ($conexion->connect_error) {
        throw new RuntimeException("No se pudo conectar con la base de datos.");
    }
    $conexion->set_charset("utf8mb4");

    try {
        if (!$conexion->query("CREATE TABLE IF NOT EXISTS clave_acceso (clave_hash VARCHAR(255) NOT NULL PRIMARY KEY)")) {
            throw new RuntimeException("No se pudo preparar la tabla de acceso.");
        }

        $resultado = $conexion->query("SELECT clave_hash FROM clave_acceso LIMIT 1");
        if (!$resultado) {
            throw new RuntimeException("No se pudo leer la clave de acceso.");
        }
        $registro = $resultado->fetch_assoc();

        if (!$registro) {
            $hashInicial = '$2y$10$HH6Yv6eFiy02NIkuDCSJIuNFKKyodZwwtfND/E.OBi2VYxLKuMkGO';
            $stmt = $conexion->prepare("INSERT INTO clave_acceso (clave_hash) VALUES (?)");
            $stmt->bind_param("s", $hashInicial);
            if (!$stmt->execute()) {
                throw new RuntimeException("No se pudo guardar la clave de acceso.");
            }
            $registro = ["clave_hash" => $hashInicial];
        }

        return password_verify($clave, $registro["clave_hash"]);
    } finally {
        $conexion->close();
    }
}

function actualizarClaveAcceso(string $claveActual, string $claveNueva): bool {
    $conexion = conectarBaseDatos();
    if ($conexion->connect_error) {
        throw new RuntimeException("No se pudo conectar con la base de datos.");
    }
    $conexion->set_charset("utf8mb4");

    try {
        $resultado = $conexion->query("SELECT clave_hash FROM clave_acceso LIMIT 1");
        $registro = $resultado ? $resultado->fetch_assoc() : null;
        if (!$registro || !password_verify($claveActual, $registro["clave_hash"])) {
            return false;
        }

        $nuevoHash = password_hash($claveNueva, PASSWORD_DEFAULT);
        $stmt = $conexion->prepare("UPDATE clave_acceso SET clave_hash = ? LIMIT 1");
        $stmt->bind_param("s", $nuevoHash);
        if (!$stmt->execute()) {
            throw new RuntimeException("No se pudo actualizar la clave.");
        }
        return true;
    } finally {
        $conexion->close();
    }
}
