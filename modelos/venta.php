<?php
require_once __DIR__ . "/../config/conexion.php";


class venta {

    public function obtenerVentas() {
        $conexion = conectarBaseDatos();
        if ($conexion->connect_error) {
            throw new RuntimeException("No se pudo conectar con la base de datos.");
        }
        $conexion->set_charset("utf8mb4");

        $sql = "SELECT
                    v.id_venta AS id,
                    CONCAT(p.nombre, ' ', p.apellido) AS cliente,
                    CONCAT(ma.nombre, ' ', mo.nombre, ' ', ve.anio) AS vehiculo,
                    CONCAT(pe.nombre, ' ', pe.apellido) AS vendedor,
                    v.precio,
                    DATE_FORMAT(STR_TO_DATE(LPAD(CAST(v.fecha AS CHAR), 8, '0'), '%d%m%Y'), '%d/%m/%Y') AS fecha
                FROM ventas v
                JOIN clientes c ON v.id_cliente = c.id_cliente
                JOIN personas p ON c.id_persona = p.id_persona
                JOIN vehiculos ve ON v.id_vehiculo = ve.id
                JOIN modelos mo ON mo.id_modelo = ve.id_modelo
                JOIN marcas ma ON ma.id_marca = mo.id_marca
                JOIN empleados e ON v.legajo = e.legajo
                JOIN personas pe ON e.id_persona = pe.id_persona";
        $res = $conexion->query($sql);
        if (!$res) {
            throw new RuntimeException("No se pudieron cargar las ventas.");
        }

        $ventas = [];
        while ($row = $res->fetch_assoc()) {
            $ventas[] = $row;
        }
        $conexion->close();
        return $ventas;
    }

    public function guardarVenta($datos) {
        if (!is_array($datos)) {
            throw new InvalidArgumentException("Los datos de la venta no son válidos.");
        }

        $cliente = filter_var($datos["cliente"] ?? null, FILTER_VALIDATE_INT);
        $vehiculo = filter_var($datos["vehiculo"] ?? null, FILTER_VALIDATE_INT);
        $vendedor = filter_var($datos["vendedor"] ?? null, FILTER_VALIDATE_INT);
        $precio = filter_var($datos["precio"] ?? null, FILTER_VALIDATE_FLOAT);
        if (!$cliente || !$vehiculo || !$vendedor || $precio === false || $precio <= 0) {
            throw new InvalidArgumentException("Completá cliente, vehículo, vendedor y un precio válido.");
        }

        $conexion = conectarBaseDatos();
        if ($conexion->connect_error) {
            throw new RuntimeException("No se pudo conectar con la base de datos.");
        }
        $conexion->set_charset("utf8mb4");
        $conexion->begin_transaction();

        try {
            // Reservar el vehículo durante la transacción evita venderlo dos veces.
            $stmt = $conexion->prepare("SELECT sov FROM vehiculos WHERE id=? FOR UPDATE");
            $stmt->bind_param("i", $vehiculo);
            $stmt->execute();
            $estadoVehiculo = $stmt->get_result()->fetch_assoc();
            if (!$estadoVehiculo || $estadoVehiculo["sov"] !== "s") {
                throw new InvalidArgumentException("El vehículo seleccionado ya no está disponible.");
            }

            $stmt = $conexion->prepare("SELECT porcentaje, comision, sueldo FROM vendedores v JOIN empleados e ON e.legajo=v.legajo WHERE v.legajo=? AND e.aoi='a' FOR UPDATE");
            $stmt->bind_param("i", $vendedor);
            $stmt->execute();
            $datosVendedor = $stmt->get_result()->fetch_assoc();
            if (!$datosVendedor) {
                throw new InvalidArgumentException("El empleado seleccionado no está registrado como vendedor.");
            }

            $stmt = $conexion->prepare("SELECT id_cliente FROM clientes WHERE id_cliente=?");
            $stmt->bind_param("i", $cliente);
            $stmt->execute();
            if (!$stmt->get_result()->fetch_assoc()) {
                throw new InvalidArgumentException("El cliente seleccionado no existe.");
            }

            // La columna fecha es INT; se almacena como DDMMAAAA.
            $stmt = $conexion->prepare("INSERT INTO ventas (id_cliente, id_vehiculo, legajo, precio, fecha) VALUES (?, ?, ?, ?, DATE_FORMAT(NOW(), '%d%m%Y'))");
            $stmt->bind_param("iiid", $cliente, $vehiculo, $vendedor, $precio);
            $stmt->execute();

            $comisionTotal = (float)$datosVendedor["comision"] + $precio * ((float)$datosVendedor["porcentaje"] / 100);
            $sueldoTotal = (float)$datosVendedor["sueldo"] + $comisionTotal;
            $stmt = $conexion->prepare("UPDATE vendedores SET comision=?, total=? WHERE legajo=?");
            $stmt->bind_param("ddi", $comisionTotal, $sueldoTotal, $vendedor);
            $stmt->execute();

            $stmt = $conexion->prepare("UPDATE vehiculos SET sov='v' WHERE id=? AND sov='s'");
            $stmt->bind_param("i", $vehiculo);
            $stmt->execute();
            if ($stmt->affected_rows !== 1) {
                throw new RuntimeException("No se pudo actualizar la disponibilidad del vehículo.");
            }

            $conexion->commit();
        } catch (Throwable $error) {
            $conexion->rollback();
            throw $error;
        } finally {
            $conexion->close();
        }
    }
}
