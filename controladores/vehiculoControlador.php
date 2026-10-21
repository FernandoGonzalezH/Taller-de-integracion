<?php

require_once "modelos/vehiculo.php";

class vehiculoControlador {

    public function index() {
        $modelo = new vehiculo();
        $datos = $modelo->obtenerVehiculos();
        $catalogosMarcaModelo = $modelo->obtenerCatalogosMarcaModelo();

        $contenido = "vistas/vehiculos.php";
        require "vistas/diseños.php";
    }

    public function soloVista() {
        $modelo = new vehiculo();
        $datos = $modelo->obtenerVehiculos();

        require "vistas/vehiculos.php";
    }

public function eliminar() {
    header("Content-Type: text/plain; charset=utf-8");

    $id = filter_var($_POST["id"] ?? null, FILTER_VALIDATE_INT);
    if (!$id) {
        http_response_code(400);
        echo "ID de vehículo inválido.";
        return;
    }

    try {
        $modelo = new vehiculo();
        $modelo->eliminarVehiculo($id);
        echo "ok";
    } catch (Throwable $e) {
        http_response_code(409);
        echo $e->getMessage();
    }
}


// 🔥 GUARDAR VEHICULO
public function guardar() {
    header("Content-Type: text/plain; charset=utf-8");
    try {
        $datos = json_decode(file_get_contents("php://input"), true, 512, JSON_THROW_ON_ERROR);
        (new vehiculo())->guardarVehiculo($datos);
        echo "ok";
    } catch (JsonException $error) {
        http_response_code(400);
        echo "Los datos recibidos no son válidos.";
    } catch (InvalidArgumentException $error) {
        http_response_code(422);
        echo $error->getMessage();
    } catch (Throwable $error) {
        http_response_code(500);
        error_log("Error al guardar vehículo: " . $error->getMessage());
        echo "No se pudo guardar el vehículo. Revisá la conexión y los datos e intentá de nuevo.";
    }
}


// 🔥 ACTUALIZAR VEHICULO



// 🔥 VER VENDIDOS
public function vendidos() {

    $modelo = new vehiculo();
    $datos = $modelo->obtenerVendidos();

    require "vistas/vehiculos_vendidos.php";
}

public function actualizar() {
    header("Content-Type: text/plain; charset=utf-8");
    try {
        $datos = json_decode(file_get_contents("php://input"), true);
        if (!is_array($datos)) {
            http_response_code(400);
            echo "Los datos recibidos no son válidos.";
            return;
        }

        $modelo = new vehiculo();
        $modelo->actualizarVehiculo($datos);
        echo "ok";
    } catch (Throwable $e) {
        if ($e instanceof InvalidArgumentException) {
            http_response_code(422);
            echo $e->getMessage();
        } else {
            http_response_code(500);
            error_log("Error al actualizar vehículo: " . $e->getMessage());
            echo "No se pudo actualizar el vehículo. Revisá la conexión e intentá de nuevo.";
        }
    }
}


}

