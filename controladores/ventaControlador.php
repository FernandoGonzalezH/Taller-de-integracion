<?php

require_once "modelos/venta.php";

class ventaControlador {

    public function opciones() {
        header("Content-Type: application/json; charset=utf-8");
        try {
            require_once "modelos/cliente.php";
            require_once "modelos/empleado.php";
            require_once "modelos/vehiculo.php";
            echo json_encode([
                "clientes" => (new cliente())->obtenerClientesParaVenta(),
                "vendedores" => (new empleado())->obtenerVendedoresActivos(),
                "vehiculos" => (new vehiculo())->obtenerVehiculosDisponiblesParaVenta(),
            ], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        } catch (Throwable $error) {
            http_response_code(500);
            error_log("Error al cargar opciones de venta: " . $error->getMessage());
            echo json_encode(["ok" => false, "mensaje" => "No se pudieron actualizar las opciones de venta."]);
        }
    }

    public function index() {

        $modelo = new venta();
        $datos = $modelo->obtenerVentas();

        $contenido = "vistas/ventas.php";
        require "vistas/diseños.php";
    }

    public function soloVista() {

        $modelo = new venta();
        $datos = $modelo->obtenerVentas();

        require "vistas/ventas.php";
    }

    public function guardar() {
        header("Content-Type: application/json; charset=utf-8");
        try {
            $datos = json_decode(file_get_contents("php://input"), true, 512, JSON_THROW_ON_ERROR);
            $modelo = new venta();
            $modelo->guardarVenta($datos);
            echo json_encode(["ok" => true, "mensaje" => "Venta guardada."]);
        } catch (JsonException $error) {
            http_response_code(400);
            echo json_encode(["ok" => false, "mensaje" => "No se recibieron datos válidos."]);
        } catch (InvalidArgumentException $error) {
            http_response_code(422);
            echo json_encode(["ok" => false, "mensaje" => $error->getMessage()]);
        } catch (Throwable $error) {
            http_response_code(500);
            error_log("Error al guardar venta: " . $error->getMessage());
            echo json_encode(["ok" => false, "mensaje" => "No se pudo guardar la venta. Revisá la conexión y los datos e intentá de nuevo."]);
        }
    }
}
