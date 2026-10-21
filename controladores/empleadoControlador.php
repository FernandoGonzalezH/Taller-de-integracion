<?php

require_once "modelos/empleado.php";

class empleadoControlador {

    public function index() {

        $modelo = new empleado();
        $datos = $modelo->obtenerEmpleados();

        $contenido = "vistas/empleados.php";
        require "vistas/diseños.php";
    }

    public function soloVista() {

        $modelo = new empleado();
        $datos = $modelo->obtenerEmpleados();

        require "vistas/empleados.php";
    }

    public function eliminar() {
        header("Content-Type: text/plain; charset=utf-8");
        $legajo = filter_var($_POST["legajo"] ?? null, FILTER_VALIDATE_INT);
        if (!$legajo) {
            http_response_code(400);
            echo "Legajo inválido.";
            return;
        }
        try {
            (new empleado())->eliminarEmpleado($legajo);
            echo "ok";
        } catch (Throwable $error) {
            http_response_code(500);
            error_log("Error al desactivar empleado: " . $error->getMessage());
            echo "No se pudo desactivar el empleado.";
        }
    }

    public function reactivar() {
        header("Content-Type: text/plain; charset=utf-8");
        $legajo = filter_var($_POST["legajo"] ?? null, FILTER_VALIDATE_INT);
        if (!$legajo) {
            http_response_code(400);
            echo "Legajo inválido.";
            return;
        }
        try {
            (new empleado())->reactivarEmpleado($legajo);
            echo "ok";
        } catch (Throwable $error) {
            http_response_code(500);
            error_log("Error al reactivar empleado: " . $error->getMessage());
            echo "No se pudo reactivar el empleado.";
        }
    }


public function guardar() {
    header("Content-Type: application/json; charset=utf-8");
    try {
        $datos = json_decode(file_get_contents("php://input"), true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($datos)) {
            throw new InvalidArgumentException("Los datos del empleado no son válidos.");
        }

        $modelo = new empleado();
        if (!empty($datos["editar"])) {
            $modelo->editarEmpleado($datos);
        } else {
            $modelo->guardarEmpleado($datos);
        }
        echo json_encode(["ok" => true, "mensaje" => "Empleado guardado."]);
    } catch (JsonException $error) {
        http_response_code(400);
        echo json_encode(["ok" => false, "mensaje" => "No se recibieron datos válidos."]);
    } catch (InvalidArgumentException $error) {
        http_response_code(422);
        echo json_encode(["ok" => false, "mensaje" => $error->getMessage()]);
    } catch (Throwable $error) {
        http_response_code(500);
        error_log("Error al guardar empleado: " . $error->getMessage());
        echo json_encode(["ok" => false, "mensaje" => "No se pudo guardar el empleado. Revisá la conexión y los datos e intentá de nuevo."]);
    }
}

public function inactivos() {

    $modelo = new empleado();
    $datos = $modelo->obtenerInactivos();

    require "vistas/empleados_inactivos.php";
}



}
