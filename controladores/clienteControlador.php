<?php

require_once "modelos/cliente.php";

class clienteControlador {

    public function index() {
        $modelo = new cliente();
        $datos = $modelo->obtenerClientes();

        $contenido = "vistas/clientes.php";
        require "vistas/diseños.php";
    }

    public function soloVista() {
        $modelo = new cliente();
        $datos = $modelo->obtenerClientes();

        require "vistas/clientes.php";
    }

    public function eliminar() {
        header("Content-Type: text/plain; charset=utf-8");
        $id = filter_var($_POST["id"] ?? null, FILTER_VALIDATE_INT);
        if (!$id) {
            http_response_code(400);
            echo "ID de cliente inválido.";
            return;
        }
        try {
            (new Cliente())->eliminarCliente($id);
            echo "ok";
        } catch (Throwable $error) {
            http_response_code(409);
            error_log("Error al eliminar cliente: " . $error->getMessage());
            echo "No se pudo eliminar el cliente. Puede tener ventas asociadas.";
        }
    }

    public function guardar() {
        header("Content-Type: application/json; charset=utf-8");
        try {
            $datos = json_decode(file_get_contents("php://input"), true, 512, JSON_THROW_ON_ERROR);
            if (!is_array($datos)) throw new InvalidArgumentException("Los datos del cliente no son válidos.");
            $modelo = new Cliente();
            if (!empty($datos["editar"])) {
                $modelo->editarCliente($datos);
                $id = (int)$datos["id"];
            } else {
                $id = $modelo->crearCliente($datos);
            }
            echo json_encode(["ok" => true, "id" => $id]);
        } catch (JsonException $error) {
            http_response_code(400);
            echo json_encode(["ok" => false, "mensaje" => "No se recibieron datos válidos."]);
        } catch (InvalidArgumentException $error) {
            http_response_code(422);
            echo json_encode(["ok" => false, "mensaje" => $error->getMessage()]);
        } catch (Throwable $error) {
            http_response_code(500);
            error_log("Error al guardar cliente: " . $error->getMessage());
            echo json_encode(["ok" => false, "mensaje" => "No se pudo guardar el cliente. Revisá la conexión e intentá de nuevo."]);
        }
    }

}
