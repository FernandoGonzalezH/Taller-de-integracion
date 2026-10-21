<?php
require_once __DIR__ . "/../config/conexion.php";


class empleado {

    public function obtenerEmpleados() {

        $conexion = conectarBaseDatos();

        $sql = "SELECT e.legajo,
               p.nombre,
               p.apellido,
               p.dni,
               p.nacimiento,
               p.localidad,
               p.direccion,
               p.telefono,
               p.email,
               e.sueldo,
               e.area,
               e.rol,
               e.ingreso,
               v.comision,
               v.porcentaje,
               (e.sueldo + COALESCE(v.comision, 0)) AS total,
               (v.legajo IS NOT NULL) AS es_vendedor,
               e.aoi
        FROM empleados e
        INNER JOIN personas p 
            ON e.id_persona = p.id_persona
        LEFT JOIN vendedores v 
            ON e.legajo = v.legajo
            WHERE e.aoi = 'a' ";


        $resultado = $conexion->query($sql);

        $datos = [];

        while ($fila = $resultado->fetch_assoc()) {
            $datos[] = $fila;
        }

        return $datos;
    }

    public function obtenerVendedoresActivos() {
        $conexion = conectarBaseDatos();
        if ($conexion->connect_error) throw new RuntimeException("No se pudo conectar con la base de datos.");
        $conexion->set_charset("utf8mb4");
        $sql = "SELECT v.legajo, p.nombre, p.apellido
                FROM vendedores v
                INNER JOIN empleados e ON e.legajo = v.legajo
                INNER JOIN personas p ON p.id_persona = e.id_persona
                WHERE e.aoi = 'a'
                ORDER BY p.apellido, p.nombre";
        $resultado = $conexion->query($sql);
        if (!$resultado) {
            $conexion->close();
            throw new RuntimeException("No se pudieron cargar los vendedores.");
        }
        $datos = [];
        while ($fila = $resultado->fetch_assoc()) $datos[] = $fila;
        $conexion->close();
        return $datos;
    }

    public function eliminarEmpleado($legajo) {
        $legajo = filter_var($legajo, FILTER_VALIDATE_INT);
        if (!$legajo) throw new InvalidArgumentException("Legajo inválido.");
        $conexion = conectarBaseDatos();
        $stmt = $conexion->prepare("UPDATE empleados SET aoi = 'i' WHERE legajo = ?");
        $stmt->bind_param("i", $legajo);
        $stmt->execute();
        $conexion->close();
}

    public function reactivarEmpleado($legajo) {
        $legajo = filter_var($legajo, FILTER_VALIDATE_INT);
        if (!$legajo) throw new InvalidArgumentException("Legajo inválido.");
        $conexion = conectarBaseDatos();
        if ($conexion->connect_error) throw new RuntimeException("No se pudo conectar con la base de datos.");
        $stmt = $conexion->prepare("UPDATE empleados SET aoi = 'a' WHERE legajo = ? AND aoi = 'i'");
        $stmt->bind_param("i", $legajo);
        $stmt->execute();
        $actualizado = $stmt->affected_rows > 0;
        $stmt->close();
        $conexion->close();
        if (!$actualizado) throw new RuntimeException("No se encontró un empleado inactivo con ese legajo.");
    }

public function guardarEmpleado($d) {
    if (!is_array($d)) throw new InvalidArgumentException("Los datos del empleado no son válidos.");
    $conexion = conectarBaseDatos();
    if ($conexion->connect_error) throw new RuntimeException("No se pudo conectar con la base de datos.");
    $conexion->set_charset("utf8mb4");
    $persona = [];
    foreach (["nombre", "apellido", "dni", "nacimiento", "localidad", "direccion", "telefono", "email"] as $campo) $persona[$campo] = (string)($d[$campo] ?? "");
    $sueldo = filter_var($d["sueldo"] ?? null, FILTER_VALIDATE_FLOAT);
    $tipo = $d["tipo"] ?? "otro";
    $porcentajeIngresado = $d["porcentaje"] ?? "";
    $porcentaje = ($tipo !== "vendedor" && $porcentajeIngresado === "")
        ? 0
        : filter_var($porcentajeIngresado === "" ? 0 : $porcentajeIngresado, FILTER_VALIDATE_FLOAT);
    $area = trim((string)($d["area"] ?? "")); $rol = trim((string)($d["rol"] ?? ""));
    if (trim($persona["nombre"]) === "" || trim($persona["apellido"]) === "" || ($persona["email"] !== "" && filter_var($persona["email"], FILTER_VALIDATE_EMAIL) === false) || $sueldo === false || $sueldo < 0 || $porcentaje === false || $porcentaje < 0 || $porcentaje > 100 || !in_array($tipo, ["otro", "vendedor"], true) || !in_array($area, ["Ventas", "Administrativa", "Contable", "Legal"], true) || $rol === "") {
        $conexion->close();
        throw new InvalidArgumentException("Ingresá nombre y apellido, seleccioná un área, indicá el puesto y verificá el correo, sueldo, porcentaje y tipo.");
    }
    $ingreso = (string)($d["ingreso"] ?? "");
    $conexion->begin_transaction();
    try {
        $stmt = $conexion->prepare("INSERT INTO personas (nombre, apellido, dni, nacimiento, localidad, direccion, telefono, email) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $persona["nombre"], $persona["apellido"], $persona["dni"], $persona["nacimiento"], $persona["localidad"], $persona["direccion"], $persona["telefono"], $persona["email"]);
        $stmt->execute();
        $idPersona = $conexion->insert_id;
        $stmt = $conexion->prepare("INSERT INTO empleados (id_persona, sueldo, area, rol, ingreso, aoi) VALUES (?, ?, ?, ?, ?, 'a')");
        $stmt->bind_param("idsss", $idPersona, $sueldo, $area, $rol, $ingreso);
        $stmt->execute();
        $legajo = $conexion->insert_id;
        if ($tipo === "vendedor") {
            $stmt = $conexion->prepare("INSERT INTO vendedores (legajo, porcentaje, comision, total) VALUES (?, ?, 0, ?)");
            $stmt->bind_param("idd", $legajo, $porcentaje, $sueldo);
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

public function editarEmpleado($d) {
    if (!is_array($d)) throw new InvalidArgumentException("Los datos del empleado no son válidos.");
    $conexion = conectarBaseDatos();
    if ($conexion->connect_error) throw new RuntimeException("No se pudo conectar con la base de datos.");
    $conexion->set_charset("utf8mb4");
    $legajo = filter_var($d["legajo"] ?? null, FILTER_VALIDATE_INT);
    $sueldo = filter_var($d["sueldo"] ?? null, FILTER_VALIDATE_FLOAT);
    $tipo = $d["tipo"] ?? "otro";
    $porcentajeIngresado = $d["porcentaje"] ?? "";
    $porcentaje = ($tipo !== "vendedor" && $porcentajeIngresado === "")
        ? 0
        : filter_var($porcentajeIngresado === "" ? 0 : $porcentajeIngresado, FILTER_VALIDATE_FLOAT);
    $persona = [];
    foreach (["nombre", "apellido", "dni", "nacimiento", "localidad", "direccion", "telefono", "email"] as $campo) $persona[$campo] = (string)($d[$campo] ?? "");
    $area = trim((string)($d["area"] ?? "")); $rol = trim((string)($d["rol"] ?? ""));
    if (!$legajo || trim($persona["nombre"]) === "" || trim($persona["apellido"]) === "" || ($persona["email"] !== "" && filter_var($persona["email"], FILTER_VALIDATE_EMAIL) === false) || $sueldo === false || $sueldo < 0 || $porcentaje === false || $porcentaje < 0 || $porcentaje > 100 || !in_array($tipo, ["otro", "vendedor"], true) || !in_array($area, ["Ventas", "Administrativa", "Contable", "Legal"], true) || $rol === "") {
        $conexion->close();
        throw new InvalidArgumentException("Ingresá nombre y apellido, seleccioná un área, indicá el puesto y verificá el correo, legajo, sueldo, porcentaje y tipo.");
    }
    $ingreso = (string)($d["ingreso"] ?? "");
    $conexion->begin_transaction();
    try {
        $stmt = $conexion->prepare("UPDATE personas p JOIN empleados e ON e.id_persona = p.id_persona SET p.nombre=?, p.apellido=?, p.dni=?, p.nacimiento=?, p.localidad=?, p.direccion=?, p.telefono=?, p.email=? WHERE e.legajo=?");
        $stmt->bind_param("ssssssssi", $persona["nombre"], $persona["apellido"], $persona["dni"], $persona["nacimiento"], $persona["localidad"], $persona["direccion"], $persona["telefono"], $persona["email"], $legajo);
        $stmt->execute();
        $stmt = $conexion->prepare("UPDATE empleados SET sueldo=?, area=?, rol=?, ingreso=? WHERE legajo=?");
        $stmt->bind_param("dsssi", $sueldo, $area, $rol, $ingreso, $legajo);
        $stmt->execute();
        if ($tipo === "vendedor") {
            $stmt = $conexion->prepare("INSERT INTO vendedores (legajo, porcentaje, comision, total) VALUES (?, ?, 0, ?) ON DUPLICATE KEY UPDATE porcentaje=VALUES(porcentaje), total=VALUES(total)+comision");
            $stmt->bind_param("idd", $legajo, $porcentaje, $sueldo);
            $stmt->execute();
        } else {
            $stmt = $conexion->prepare("SELECT 1 FROM ventas WHERE legajo = ? LIMIT 1");
            $stmt->bind_param("i", $legajo);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                throw new InvalidArgumentException("No se puede quitar el rol de vendedor porque tiene ventas registradas.");
            }
            $stmt = $conexion->prepare("DELETE FROM vendedores WHERE legajo = ?");
            $stmt->bind_param("i", $legajo);
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

public function obtenerInactivos() {

    $conexion = conectarBaseDatos();

    $sql = "SELECT e.legajo,
               p.nombre,
               p.apellido,
               p.dni,
               p.nacimiento,
               p.localidad,
               p.direccion,
               p.telefono,
               p.email,
               e.sueldo,
               e.area,
               e.rol,
               e.ingreso,
               v.comision,
               v.porcentaje,
               (e.sueldo + COALESCE(v.comision, 0)) AS total,
               (v.legajo IS NOT NULL) AS es_vendedor,
               e.aoi
        FROM empleados e
        INNER JOIN personas p 
            ON e.id_persona = p.id_persona
        LEFT JOIN vendedores v 
            ON e.legajo = v.legajo
            WHERE e.aoi = 'i' ";

    $resultado = $conexion->query($sql);

    $datos = [];

    while ($fila = $resultado->fetch_assoc()) {
        $datos[] = $fila;
    }

    return $datos;
}

    

}
