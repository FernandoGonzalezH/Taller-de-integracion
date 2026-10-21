<?php
require_once __DIR__ . "/../config/conexion.php";


require_once "modelos/vehiculo.php";

class Informe {

    private function conectar() {
        return conectarBaseDatos();
    }

    // 🔹 1. Ventas totales
    public function ventasTotales() {

    $conexion = $this->conectar();

    $res = $conexion->query("
        SELECT 
            COUNT(*) as cantidad,
            SUM(precio) as total
        FROM ventas
    ");

    $row = $res->fetch_assoc();

    // 🔥 DEVOLVER COMO ARRAY (IMPORTANTE)
    return [$row];
}


// 🔹 2. Ventas por vendedor
public function ventasPorVendedor() {

    $conexion = $this->conectar();

    $res = $conexion->query("
        SELECT 
            CONCAT(p.nombre, ' ', p.apellido) as vendedor,
            COUNT(v.id_venta) as cantidad,
            COALESCE(SUM(v.precio), 0) as total,
            COALESCE(vend.comision, 0) as comision
        FROM vendedores vend
        JOIN empleados e ON vend.legajo = e.legajo
        JOIN personas p ON e.id_persona = p.id_persona
        LEFT JOIN ventas v ON v.legajo = vend.legajo
        GROUP BY vend.legajo, p.nombre, p.apellido, vend.comision
    ");

    $datos = [];

    while ($row = $res->fetch_assoc()) {
        $datos[] = $row;
    }

    return $datos;
}


// 🔹 3. Ventas por mes
public function ventasPorMes($mesSeleccionado = null) {

    $conexion = $this->conectar();

    $sql = "SELECT
                DATE_FORMAT(STR_TO_DATE(LPAD(CAST(fecha AS CHAR), 8, '0'), '%d%m%Y'), '%Y-%m') as mes,
                COUNT(*) as cantidad,
                SUM(precio) as total
            FROM ventas";
    if ($mesSeleccionado !== null) {
        $sql .= " WHERE DATE_FORMAT(STR_TO_DATE(LPAD(CAST(fecha AS CHAR), 8, '0'), '%d%m%Y'), '%Y-%m') = ?";
        $stmt = $conexion->prepare($sql . " GROUP BY mes ORDER BY mes DESC");
        $stmt->bind_param("s", $mesSeleccionado);
        $stmt->execute();
        $res = $stmt->get_result();
    } else {
        $res = $conexion->query($sql . " GROUP BY mes ORDER BY mes DESC");
    }

    $datos = [];

    while ($row = $res->fetch_assoc()) {
        $datos[] = $row;
    }

    return $datos;
}


// 🔹 4. Stock disponible
public function stockDisponible($tipoVehiculo = null, $condicion = null) {

    $conexion = $this->conectar();

    $sql = "
        SELECT
            ve.id,
            ve.tipo,
            ma.nombre AS marca,
            mo.nombre AS modelo,
            ve.anio,
            ve.km,
            ve.estado,
            ve.combustible,
            ve.precio
        FROM vehiculos ve
        JOIN modelos mo ON mo.id_modelo = ve.id_modelo
        JOIN marcas ma ON ma.id_marca = mo.id_marca
        WHERE ve.sov = 's'
    ";
    $tiposValidos = ["Auto", "Moto"];
    $estadosValidos = ["Nuevo", "Usado"];
    if ($tipoVehiculo !== null && in_array($tipoVehiculo, $tiposValidos, true)) {
        $sql .= " AND ve.tipo = '" . $tipoVehiculo . "'";
    }
    if ($condicion !== null && in_array($condicion, $estadosValidos, true)) {
        $sql .= " AND ve.estado = '" . $condicion . "'";
    }
    $sql .= " ORDER BY ve.tipo, ma.nombre, mo.nombre, ve.anio DESC";
    $res = $conexion->query($sql);

    $datos = [];

    while ($row = $res->fetch_assoc()) {
        $datos[] = $row;
    }

    return $datos;
}

public function listaEmpleados() {
    $conexion = $this->conectar();
    $res = $conexion->query("SELECT
            e.legajo, p.nombre, p.apellido, p.dni, p.nacimiento,
            p.localidad, p.direccion, p.telefono, p.email,
            e.sueldo, e.area, e.rol, e.ingreso
        FROM empleados e
        JOIN personas p ON p.id_persona = e.id_persona
        WHERE e.aoi = 'a'
        ORDER BY p.apellido, p.nombre, e.legajo");
    $datos = [];
    while ($row = $res->fetch_assoc()) {
        $datos[] = $row;
    }
    return $datos;
}

public function vehiculosVendidos() {
    $conexion = $this->conectar();
    $res = $conexion->query("SELECT
            ve.id,
            ve.tipo,
            ma.nombre AS marca,
            mo.nombre AS modelo,
            ve.anio,
            ve.km,
            ve.estado,
            v.precio,
            DATE_FORMAT(STR_TO_DATE(LPAD(CAST(v.fecha AS CHAR), 8, '0'), '%d%m%Y'), '%d/%m/%Y') AS fecha,
            CONCAT(p.nombre, ' ', p.apellido) AS vendedor
        FROM vehiculos ve
        JOIN modelos mo ON mo.id_modelo = ve.id_modelo
        JOIN marcas ma ON ma.id_marca = mo.id_marca
        JOIN ventas v ON v.id_vehiculo = ve.id
        JOIN empleados e ON e.legajo = v.legajo
        JOIN personas p ON p.id_persona = e.id_persona
        WHERE ve.sov = 'v'
        ORDER BY v.fecha DESC, v.id_venta DESC");
    $datos = [];
    while ($row = $res->fetch_assoc()) $datos[] = $row;
    $conexion->close();
    return $datos;
}

public function listaEmpleadosInactivos() {
    $conexion = $this->conectar();
    $res = $conexion->query("SELECT
            e.legajo, p.nombre, p.apellido, p.dni, p.nacimiento,
            p.localidad, p.direccion, p.telefono, p.email,
            e.sueldo, e.area, e.rol, e.ingreso
        FROM empleados e
        JOIN personas p ON p.id_persona = e.id_persona
        WHERE e.aoi = 'i'
        ORDER BY p.apellido, p.nombre, e.legajo");
    $datos = [];
    while ($row = $res->fetch_assoc()) {
        $datos[] = $row;
    }
    return $datos;
}

public function listaVendedores() {
    $conexion = $this->conectar();
    $res = $conexion->query("SELECT
            e.legajo,
            CONCAT(p.nombre, ' ', p.apellido) AS vendedor,
            e.sueldo,
            COALESCE(v.porcentaje, 0) AS porcentaje,
            COALESCE(v.comision, 0) AS comision,
            (e.sueldo + COALESCE(v.comision, 0)) AS total
        FROM vendedores v
        JOIN empleados e ON e.legajo = v.legajo
        JOIN personas p ON p.id_persona = e.id_persona
        ORDER BY p.apellido, p.nombre, e.legajo");
    $datos = [];
    while ($row = $res->fetch_assoc()) {
        $datos[] = $row;
    }
    return $datos;
}

public function ventasDetalleVendedor($legajo) {
    $conexion = $this->conectar();
    $stmt = $conexion->prepare("SELECT
            v.id_venta AS id,
            CONCAT(pc.nombre, ' ', pc.apellido) AS cliente,
            CONCAT(ma.nombre, ' ', mo.nombre, ' ', ve.anio) AS vehiculo,
            v.precio,
            DATE_FORMAT(STR_TO_DATE(LPAD(CAST(v.fecha AS CHAR), 8, '0'), '%d%m%Y'), '%d/%m/%Y') AS fecha,
            v.precio * COALESCE(vend.porcentaje, 0) / 100 AS comision
        FROM ventas v
        JOIN vendedores vend ON vend.legajo = v.legajo
        JOIN clientes c ON c.id_cliente = v.id_cliente
        JOIN personas pc ON pc.id_persona = c.id_persona
        JOIN vehiculos ve ON ve.id = v.id_vehiculo
        JOIN modelos mo ON mo.id_modelo = ve.id_modelo
        JOIN marcas ma ON ma.id_marca = mo.id_marca
        WHERE v.legajo = ?
        ORDER BY v.fecha DESC, v.id_venta DESC");
    $stmt->bind_param("i", $legajo);
    $stmt->execute();
    $res = $stmt->get_result();
    $datos = [];
    while ($row = $res->fetch_assoc()) $datos[] = $row;
    $stmt->close();
    $conexion->close();
    return $datos;
}

public function listaClientes() {
    $conexion = $this->conectar();
    $res = $conexion->query("SELECT
            c.id_cliente AS id,
            p.nombre, p.apellido, p.dni, p.nacimiento,
            p.localidad, p.direccion, p.telefono, p.email
        FROM clientes c
        JOIN personas p ON p.id_persona = c.id_persona
        ORDER BY p.apellido, p.nombre, c.id_cliente");
    $datos = [];
    while ($row = $res->fetch_assoc()) {
        $datos[] = $row;
    }
    return $datos;
}

public function ventasDetalle($mesSeleccionado = null) {

    $conexion = $this->conectar();

    $sql = "SELECT
            v.id_venta as id,
            CONCAT(p.nombre, ' ', p.apellido) as cliente,
            CONCAT(ma.nombre, ' ', mo.nombre, ' ', ve.anio) as vehiculo,
            CONCAT(pe.nombre, ' ', pe.apellido) as vendedor,
            v.precio,
            DATE_FORMAT(STR_TO_DATE(LPAD(CAST(v.fecha AS CHAR), 8, '0'), '%d%m%Y'), '%d/%m/%Y') as fecha
        FROM ventas v
        JOIN clientes c ON v.id_cliente = c.id_cliente
        JOIN personas p ON c.id_persona = p.id_persona
        JOIN vehiculos ve ON v.id_vehiculo = ve.id
        JOIN modelos mo ON mo.id_modelo = ve.id_modelo
        JOIN marcas ma ON ma.id_marca = mo.id_marca
        JOIN empleados e ON v.legajo = e.legajo
        JOIN personas pe ON e.id_persona = pe.id_persona";
    if ($mesSeleccionado !== null) {
        $sql .= " WHERE DATE_FORMAT(STR_TO_DATE(LPAD(CAST(v.fecha AS CHAR), 8, '0'), '%d%m%Y'), '%Y-%m') = ?";
        $stmt = $conexion->prepare($sql . " ORDER BY v.fecha DESC, v.id_venta DESC");
        $stmt->bind_param("s", $mesSeleccionado);
        $stmt->execute();
        $res = $stmt->get_result();
    } else {
        $res = $conexion->query($sql . " ORDER BY v.fecha DESC, v.id_venta DESC");
    }

    $datos = [];

    while ($row = $res->fetch_assoc()) {
        $datos[] = $row;
    }

    return $datos;
}
}
