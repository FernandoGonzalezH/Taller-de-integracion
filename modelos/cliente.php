<?php
require_once __DIR__ . "/../config/conexion.php";


class cliente {
    private function conectar() {
        $conexion = conectarBaseDatos();
        if ($conexion->connect_error) throw new RuntimeException("No se pudo conectar con la base de datos.");
        $conexion->set_charset("utf8mb4");
        return $conexion;
    }

    public function obtenerClientes() {
        $conexion = $this->conectar();
        $resultado = $conexion->query("SELECT c.id_cliente, p.nombre, p.apellido, p.dni, p.nacimiento, p.localidad, p.direccion, p.telefono, p.email FROM clientes c INNER JOIN personas p ON c.id_persona = p.id_persona");
        $datos = [];
        while ($fila = $resultado->fetch_assoc()) $datos[] = $fila;
        $conexion->close();
        return $datos;
    }

    public function obtenerClientesParaVenta() {
        $conexion = $this->conectar();
        $resultado = $conexion->query("SELECT c.id_cliente, p.nombre, p.apellido FROM clientes c INNER JOIN personas p ON c.id_persona = p.id_persona ORDER BY p.apellido, p.nombre");
        $datos = [];
        while ($fila = $resultado->fetch_assoc()) $datos[] = $fila;
        $conexion->close();
        return $datos;
    }

    public function eliminarCliente($id) {
        $id = filter_var($id, FILTER_VALIDATE_INT);
        if (!$id) throw new InvalidArgumentException("ID de cliente inválido.");
        $conexion = $this->conectar();
        $stmt = $conexion->prepare("DELETE FROM clientes WHERE id_cliente = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $conexion->close();
    }

    public function crearCliente($d) {
        if (!is_array($d)) throw new InvalidArgumentException("Los datos del cliente no son válidos.");
        $campos = [];
        foreach (["nombre", "apellido", "dni", "nacimiento", "localidad", "direccion", "telefono", "email"] as $campo) {
            $campos[$campo] = trim((string)($d[$campo] ?? ""));
        }
        $this->validarDatosPersona($campos);
        $conexion = $this->conectar();
        $conexion->begin_transaction();
        try {
            $stmt = $conexion->prepare("INSERT INTO personas (nombre, apellido, dni, nacimiento, localidad, direccion, telefono, email) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssss", $campos["nombre"], $campos["apellido"], $campos["dni"], $campos["nacimiento"], $campos["localidad"], $campos["direccion"], $campos["telefono"], $campos["email"]);
            $stmt->execute();
            $idPersona = $conexion->insert_id;
            $stmt = $conexion->prepare("INSERT INTO clientes (id_persona) VALUES (?)");
            $stmt->bind_param("i", $idPersona);
            $stmt->execute();
            $idCliente = (int)$conexion->insert_id;
            $conexion->commit();
        } catch (Throwable $error) {
            $conexion->rollback();
            throw $error;
        } finally {
            $conexion->close();
        }
        return $idCliente;
    }

    public function editarCliente($d) {
        if (!is_array($d)) throw new InvalidArgumentException("Los datos del cliente no son válidos.");
        $id = filter_var($d["id"] ?? null, FILTER_VALIDATE_INT);
        if (!$id) throw new InvalidArgumentException("ID de cliente inválido.");
        $campos = [];
        foreach (["nombre", "apellido", "dni", "nacimiento", "localidad", "direccion", "telefono", "email"] as $campo) {
            $campos[$campo] = trim((string)($d[$campo] ?? ""));
        }
        $this->validarDatosPersona($campos);
        $conexion = $this->conectar();
        $stmt = $conexion->prepare("UPDATE personas p JOIN clientes c ON c.id_persona = p.id_persona SET p.nombre=?, p.apellido=?, p.dni=?, p.nacimiento=?, p.localidad=?, p.direccion=?, p.telefono=?, p.email=? WHERE c.id_cliente=?");
        $stmt->bind_param("ssssssssi", $campos["nombre"], $campos["apellido"], $campos["dni"], $campos["nacimiento"], $campos["localidad"], $campos["direccion"], $campos["telefono"], $campos["email"], $id);
        $stmt->execute();
        $conexion->close();
    }

    private function validarDatosPersona(array $campos): void {
        if ($campos["nombre"] === "" || $campos["apellido"] === "") {
            throw new InvalidArgumentException("Ingresá el nombre y el apellido del cliente.");
        }
        if ($campos["email"] !== "" && filter_var($campos["email"], FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidArgumentException("Ingresá un correo electrónico válido.");
        }
        if ($campos["nacimiento"] !== "") {
            $fecha = DateTime::createFromFormat("!Y-m-d", $campos["nacimiento"]);
            if (!$fecha || $fecha->format("Y-m-d") !== $campos["nacimiento"]) {
                throw new InvalidArgumentException("La fecha de nacimiento debe tener el formato AAAA-MM-DD.");
            }
        }
    }
}
