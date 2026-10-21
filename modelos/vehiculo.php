<?php
require_once __DIR__ . "/../config/conexion.php";


class vehiculo {

    public function obtenerVehiculos() {

        $conexion = conectarBaseDatos();

        $sql = "SELECT v.*, ma.nombre AS marca, mo.nombre AS modelo,
                       a.puertas, a.traccion, a.pasajeros, a.carroceria,
                       m.cilindros, m.tipomoto
                FROM vehiculos v
                JOIN modelos mo ON mo.id_modelo = v.id_modelo
                JOIN marcas ma ON ma.id_marca = mo.id_marca
                LEFT JOIN autos a ON v.id = a.id
                LEFT JOIN motos m ON v.id = m.id
                WHERE v.sov = 's'";

        $res = $conexion->query($sql);

        $datos = [];
        while ($fila = $res->fetch_assoc()) {
            $datos[] = $fila;
        }

        return $datos;
    }

    public function obtenerVehiculosDisponiblesParaVenta() {
        $conexion = conectarBaseDatos();
        if ($conexion->connect_error) throw new RuntimeException("No se pudo conectar con la base de datos.");
        $conexion->set_charset("utf8mb4");
        $sql = "SELECT v.id, ma.nombre AS marca, mo.nombre AS modelo, v.anio, v.precio
                FROM vehiculos v
                JOIN modelos mo ON mo.id_modelo = v.id_modelo
                JOIN marcas ma ON ma.id_marca = mo.id_marca
                WHERE v.sov = 's'
                ORDER BY ma.nombre, mo.nombre, v.anio";
        $resultado = $conexion->query($sql);
        if (!$resultado) {
            $conexion->close();
            throw new RuntimeException("No se pudieron cargar los vehículos disponibles.");
        }
        $datos = [];
        while ($fila = $resultado->fetch_assoc()) $datos[] = $fila;
        $conexion->close();
        return $datos;
    }

    public function obtenerCatalogosMarcaModelo(): array {
        $conexion = conectarBaseDatos();
        $marcas = [];
        $modelos = [];
        $resultado = $conexion->query("SELECT nombre FROM marcas ORDER BY nombre");
        while ($fila = $resultado->fetch_assoc()) $marcas[] = $fila["nombre"];
        $resultado = $conexion->query("SELECT mo.nombre, ma.nombre AS marca FROM modelos mo JOIN marcas ma ON ma.id_marca = mo.id_marca ORDER BY ma.nombre, mo.nombre");
        while ($fila = $resultado->fetch_assoc()) $modelos[] = $fila;
        $conexion->close();
        return ["marcas" => $marcas, "modelos" => $modelos];
    }

    private function resolverIdModelo(mysqli $conexion, string $marca, string $nombreModelo): int {
        $stmt = $conexion->prepare("INSERT INTO marcas (nombre) VALUES (?) ON DUPLICATE KEY UPDATE id_marca = LAST_INSERT_ID(id_marca)");
        $stmt->bind_param("s", $marca);
        $stmt->execute();
        $idMarca = (int)$conexion->insert_id;

        $stmt = $conexion->prepare("INSERT INTO modelos (id_marca, nombre) VALUES (?, ?) ON DUPLICATE KEY UPDATE id_modelo = LAST_INSERT_ID(id_modelo)");
        $stmt->bind_param("is", $idMarca, $nombreModelo);
        $stmt->execute();
        return (int)$conexion->insert_id;
    }

public function guardarVehiculo($d) {
    if (!is_array($d)) throw new InvalidArgumentException("Los datos del vehículo no son válidos.");
    $conexion = conectarBaseDatos();
    if ($conexion->connect_error) throw new RuntimeException("No se pudo conectar con la base de datos.");
    $conexion->set_charset("utf8mb4");
    $tipo = $d["tipo"] ?? "";
    $precio = filter_var($d["precio"] ?? null, FILTER_VALIDATE_FLOAT);
    $km = filter_var($d["km"] ?? null, FILTER_VALIDATE_INT);
    $anio = filter_var($d["anio"] ?? null, FILTER_VALIDATE_INT);
    $marca = trim((string)($d["marca"] ?? ""));
    $modelo = trim((string)($d["modelo"] ?? ""));
    $estado = ucfirst(strtolower(trim((string)($d["estado"] ?? ""))));
    if (!in_array($tipo, ["Auto", "Moto"], true) || $marca === "" || $modelo === "" || $precio === false || $precio <= 0 || $km === false || $km < 0 || $anio === false || $anio < 1886 || $anio > (int)date("Y") + 2 || !in_array($estado, ["Nuevo", "Usado"], true)) {
        $conexion->close();
        throw new InvalidArgumentException("Completá marca, modelo, tipo, precio, kilómetros, año y estado válidos.");
    }
    $transmision = (string)($d["transmision"] ?? "");
    $combustible = (string)($d["combustible"] ?? ""); $motor = (string)($d["motor"] ?? "");
    $conexion->begin_transaction();
    try {
        $idModelo = $this->resolverIdModelo($conexion, $marca, $modelo);
        $stmt = $conexion->prepare("INSERT INTO vehiculos (id_modelo, tipo, precio, km, anio, estado, transmision, combustible, motor, sov) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 's')");
        $stmt->bind_param("isdiissss", $idModelo, $tipo, $precio, $km, $anio, $estado, $transmision, $combustible, $motor);
        $stmt->execute();
        $id = $conexion->insert_id;
        if ($tipo === "Auto") {
            $puertas = ($d["puertas"] ?? "") !== "" ? (int)$d["puertas"] : null;
            $traccion = (string)($d["traccion"] ?? "");
            $pasajeros = ($d["pasajeros"] ?? "") !== "" ? (int)$d["pasajeros"] : null;
            $carroceria = (string)($d["carroceria"] ?? "");
            $stmt = $conexion->prepare("INSERT INTO autos (id, puertas, traccion, pasajeros, carroceria) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("iisis", $id, $puertas, $traccion, $pasajeros, $carroceria);
            $stmt->execute();
        } else {
            $cilindros = ($d["cilindros"] ?? "") !== "" ? (int)$d["cilindros"] : null;
            $tipomoto = (string)($d["tipomoto"] ?? "");
            $stmt = $conexion->prepare("INSERT INTO motos (id, cilindros, tipomoto) VALUES (?, ?, ?)");
            $stmt->bind_param("iis", $id, $cilindros, $tipomoto);
            $stmt->execute();
        }
        $conexion->commit();
    } catch (Throwable $error) {
        $conexion->rollback();
        throw $error;
    } finally {
        $conexion->close();
    }
}

public function actualizarVehiculo($d) {
    if (!is_array($d)) throw new InvalidArgumentException("Los datos del vehículo no son válidos.");
    $conexion = conectarBaseDatos();
    if ($conexion->connect_error) {
        throw new RuntimeException("Error de conexión: " . $conexion->connect_error);
    }
    $conexion->set_charset("utf8mb4");

    $id = filter_var($d["id"] ?? null, FILTER_VALIDATE_INT);
    $tipo = $d["tipo"] ?? "";
    $marca = trim((string)($d["marca"] ?? ""));
    $modelo = trim((string)($d["modelo"] ?? ""));
    $precio = filter_var($d["precio"] ?? null, FILTER_VALIDATE_FLOAT);
    $km = filter_var($d["km"] ?? null, FILTER_VALIDATE_INT);
    $anio = filter_var($d["anio"] ?? null, FILTER_VALIDATE_INT);
    $estado = ucfirst(strtolower(trim((string)($d["estado"] ?? ""))));
    $motor = (string)($d["motor"] ?? "");
    $transmision = (string)($d["transmision"] ?? "");
    $combustible = (string)($d["combustible"] ?? "");
    if (!$id || !in_array($tipo, ["Auto", "Moto"], true) || $marca === "" || $modelo === "" || $precio === false || $precio <= 0 || $km === false || $km < 0 || $anio === false || $anio < 1886 || $anio > (int)date("Y") + 2 || !in_array($estado, ["Nuevo", "Usado"], true)) {
        $conexion->close();
        throw new InvalidArgumentException("Completá vehículo, marca, modelo, tipo, precio, kilómetros, año y estado válidos.");
    }

    $conexion->begin_transaction();
    try {
        // Leer y conservar explícitamente el estado actual (disponible/vendido).
        $stmt = $conexion->prepare("SELECT sov FROM vehiculos WHERE id=? FOR UPDATE");
        $stmt->bind_param("i", $id);
        if (!$stmt->execute()) {
            throw new RuntimeException("No se pudo leer el estado actual del vehículo: " . $stmt->error);
        }
        $actual = $stmt->get_result()->fetch_assoc();
        if (!$actual) {
            throw new RuntimeException("No se encontró el vehículo para editar.");
        }
        $estadoVenta = $actual["sov"];

        $idModelo = $this->resolverIdModelo($conexion, $marca, $modelo);
        $stmt = $conexion->prepare(
            "UPDATE vehiculos SET id_modelo=?, tipo=?, precio=?, km=?, anio=?,
             estado=?, motor=?, transmision=?, combustible=?, sov=? WHERE id=?"
        );
        $stmt->bind_param(
            "isdiisssssi",
            $idModelo, $tipo, $precio, $km, $anio,
            $estado, $motor, $transmision, $combustible, $estadoVenta, $id
        );
        if (!$stmt->execute()) {
            throw new RuntimeException("No se pudieron actualizar los datos del vehículo: " . $stmt->error);
        }

        $stmt = $conexion->prepare("DELETE FROM autos WHERE id=?");
        $stmt->bind_param("i", $id);
        if (!$stmt->execute()) {
            throw new RuntimeException("No se pudieron actualizar los datos del auto: " . $stmt->error);
        }
        $stmt = $conexion->prepare("DELETE FROM motos WHERE id=?");
        $stmt->bind_param("i", $id);
        if (!$stmt->execute()) {
            throw new RuntimeException("No se pudieron actualizar los datos de la moto: " . $stmt->error);
        }

        if ($tipo === "Auto") {
            $puertas = ($d["puertas"] ?? "") !== "" ? (int)$d["puertas"] : null;
            $traccion = $d["traccion"] ?? "";
            $pasajeros = ($d["pasajeros"] ?? "") !== "" ? (int)$d["pasajeros"] : null;
            $carroceria = $d["carroceria"] ?? "";
            $stmt = $conexion->prepare(
                "INSERT INTO autos (id, puertas, traccion, pasajeros, carroceria) VALUES (?, ?, ?, ?, ?)"
            );
            $stmt->bind_param("iisis", $id, $puertas, $traccion, $pasajeros, $carroceria);
        } else {
            $cilindros = ($d["cilindros"] ?? "") !== "" ? (int)$d["cilindros"] : null;
            $tipomoto = $d["tipomoto"] ?? "";
            $stmt = $conexion->prepare(
                "INSERT INTO motos (id, cilindros, tipomoto) VALUES (?, ?, ?)"
            );
            $stmt->bind_param("iis", $id, $cilindros, $tipomoto);
        }
        if (!$stmt->execute()) {
            throw new RuntimeException("No se pudieron guardar los detalles del vehículo: " . $stmt->error);
        }

        $conexion->commit();
    } catch (Throwable $e) {
        $conexion->rollback();
        throw $e;
    } finally {
        $conexion->close();
    }
}

    public function obtenerVendidos() {

        $conexion = conectarBaseDatos();

        $sql = "SELECT v.*, ma.nombre AS marca, mo.nombre AS modelo,
                       a.puertas, a.traccion, a.pasajeros, a.carroceria,
                       m.cilindros, m.tipomoto
                FROM vehiculos v
                JOIN modelos mo ON mo.id_modelo = v.id_modelo
                JOIN marcas ma ON ma.id_marca = mo.id_marca
                LEFT JOIN autos a ON v.id = a.id
                LEFT JOIN motos m ON v.id = m.id
                WHERE v.sov = 'v'";

        $res = $conexion->query($sql);

        $datos = [];
        while ($fila = $res->fetch_assoc()) {
            $datos[] = $fila;
        }

        return $datos;
    }
    public function eliminarVehiculo($id) {
        $conexion = conectarBaseDatos();
        if ($conexion->connect_error) {
            throw new RuntimeException("Error de conexión: " . $conexion->connect_error);
        }

        $conexion->begin_transaction();
        try {
            // No borrar vehículos del historial de ventas.
            $stmt = $conexion->prepare("SELECT id_venta FROM ventas WHERE id_vehiculo=? LIMIT 1");
            $stmt->bind_param("i", $id);
            if (!$stmt->execute()) {
                throw new RuntimeException("No se pudo comprobar si el vehículo tiene ventas.");
            }
            if ($stmt->get_result()->num_rows > 0) {
                throw new RuntimeException("No se puede borrar: el vehículo tiene una venta asociada.");
            }

            $stmt = $conexion->prepare("DELETE FROM autos WHERE id=?");
            $stmt->bind_param("i", $id);
            if (!$stmt->execute()) {
                throw new RuntimeException("No se pudieron borrar los datos del auto: " . $stmt->error);
            }

            $stmt = $conexion->prepare("DELETE FROM motos WHERE id=?");
            $stmt->bind_param("i", $id);
            if (!$stmt->execute()) {
                throw new RuntimeException("No se pudieron borrar los datos de la moto: " . $stmt->error);
            }

            $stmt = $conexion->prepare("DELETE FROM vehiculos WHERE id=?");
            $stmt->bind_param("i", $id);
            if (!$stmt->execute()) {
                throw new RuntimeException("No se pudo borrar el vehículo: " . $stmt->error);
            }
            if ($stmt->affected_rows !== 1) {
                throw new RuntimeException("No se encontró el vehículo.");
            }

            $conexion->commit();
            return true;
        } catch (Throwable $e) {
            $conexion->rollback();
            throw $e;
        } finally {
            $conexion->close();
        }
    }

}
