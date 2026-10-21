<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        "httponly" => true,
        "secure" => !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off",
        "samesite" => "Lax",
    ]);
    session_start();
}

require_once __DIR__ . "/config/conexion.php";
try {
    $conexionInicial = conectarBaseDatos();
    $conexionInicial->close();
} catch (Throwable $error) {
    error_log("No se pudo inicializar la base de datos: " . $error->getMessage());
    http_response_code(503);
    exit("No se pudo preparar la base de datos. Revisá la configuración y los permisos de MySQL/MariaDB.");
}

$accion = $_GET['accion'] ?? 'inicio';
$esAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']);
$tiempoMaximoInactividad = 60 * 60;

if (!empty($_SESSION["autenticado"])) {
    if (isset($_SESSION["ultima_actividad"]) && time() - (int)$_SESSION["ultima_actividad"] > $tiempoMaximoInactividad) {
        $_SESSION = [];
        session_regenerate_id(true);
        $_SESSION["token_acceso"] = bin2hex(random_bytes(32));
        $_SESSION["aviso_acceso"] = "La sesión se cerró por inactividad. Ingresá de nuevo.";
        if ($esAjax) {
            http_response_code(401);
            exit("SESSION_EXPIRED");
        }
        header("Location: index.php");
        exit;
    }
    $_SESSION["ultima_actividad"] = time();
}

if ($accion === "salir") {
    $tokenRecibido = $_POST["token"] ?? "";
    if ($_SERVER["REQUEST_METHOD"] !== "POST" || empty($_SESSION["token_sesion"]) || !hash_equals($_SESSION["token_sesion"], $tokenRecibido)) {
        http_response_code(403);
        exit("Solicitud no válida");
    }
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $parametrosCookie = session_get_cookie_params();
        setcookie(session_name(), "", time() - 42000, $parametrosCookie["path"], $parametrosCookie["domain"], $parametrosCookie["secure"], $parametrosCookie["httponly"]);
    }
    session_destroy();
    header("Location: index.php");
    exit;
}

if (empty($_SESSION["autenticado"])) {
    if ($esAjax) {
        http_response_code(401);
        exit("SESSION_REQUIRED");
    }
    if (empty($_SESSION["token_acceso"])) {
        $_SESSION["token_acceso"] = bin2hex(random_bytes(32));
    }

    $errorAcceso = $_SESSION["aviso_acceso"] ?? null;
    unset($_SESSION["aviso_acceso"]);
    if ($accion === "ingresar" && $_SERVER["REQUEST_METHOD"] === "POST") {
        $tokenRecibido = $_POST["token"] ?? "";
        if (!hash_equals($_SESSION["token_acceso"], $tokenRecibido)) {
            $errorAcceso = "La solicitud venció. Actualizá la página e intentá de nuevo.";
        } else {
            require_once "modelos/acceso.php";
            try {
                if (verificarClaveAcceso((string)($_POST["clave"] ?? ""))) {
                    session_regenerate_id(true);
                    $_SESSION["autenticado"] = true;
                    unset($_SESSION["token_acceso"]);
                    header("Location: index.php");
                    exit;
                }
                $errorAcceso = "La clave ingresada no es correcta.";
            } catch (Throwable $error) {
                error_log("No se pudo verificar la clave de acceso: " . $error->getMessage());
                $errorAcceso = "No se pudo verificar la clave. Revisá que MySQL esté iniciado e intentá de nuevo.";
            }
        }
    }

    http_response_code($errorAcceso ? 401 : 200);
    require "vistas/acceso.php";
    exit;
}

if (empty($_SESSION["token_sesion"])) {
    $_SESSION["token_sesion"] = bin2hex(random_bytes(32));
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && !in_array($accion, ["ingresar", "salir"], true)) {
    $tokenCsrf = $_SERVER["HTTP_X_CSRF_TOKEN"] ?? "";
    if (!hash_equals($_SESSION["token_sesion"], $tokenCsrf)) {
        http_response_code(403);
        header("Content-Type: application/json; charset=utf-8");
        echo json_encode(["ok" => false, "mensaje" => "La solicitud no es válida. Actualizá la página e intentá de nuevo."]);
        exit;
    }
}

if ($accion === "cambiarClave") {
    header("Content-Type: application/json; charset=utf-8");
    $datosClave = json_decode(file_get_contents("php://input"), true);
    $datosClave = is_array($datosClave) ? $datosClave : [];
    if (!hash_equals($_SESSION["token_sesion"], (string)($datosClave["token"] ?? ""))) {
        http_response_code(403);
        echo json_encode(["ok" => false, "mensaje" => "La sesión venció. Actualizá la página e intentá de nuevo."]);
        exit;
    }
    $claveNueva = (string)($datosClave["claveNueva"] ?? "");
    if (strlen($claveNueva) < 8) {
        http_response_code(422);
        echo json_encode(["ok" => false, "mensaje" => "La nueva clave debe tener al menos 8 caracteres."]);
        exit;
    }
    if ($claveNueva !== (string)($datosClave["confirmacion"] ?? "")) {
        http_response_code(422);
        echo json_encode(["ok" => false, "mensaje" => "La confirmación no coincide con la nueva clave."]);
        exit;
    }

    require_once "modelos/acceso.php";
    try {
        if (!actualizarClaveAcceso((string)($datosClave["claveActual"] ?? ""), $claveNueva)) {
            http_response_code(422);
            echo json_encode(["ok" => false, "mensaje" => "La clave actual no es correcta."]);
            exit;
        }
        echo json_encode(["ok" => true, "mensaje" => "La clave se cambió correctamente."]);
    } catch (Throwable $error) {
        error_log("No se pudo cambiar la clave: " . $error->getMessage());
        http_response_code(500);
        echo json_encode(["ok" => false, "mensaje" => "No se pudo cambiar la clave. Revisá la conexión e intentá de nuevo."]);
    }
    exit;
}

if ($accion == "inicio") {

    if ($esAjax) {
        require "vistas/inicio.php";
    } else {
        require_once "modelos/vehiculo.php";
        $catalogosMarcaModelo = (new vehiculo())->obtenerCatalogosMarcaModelo();
        $contenido = "vistas/inicio.php";
        require "vistas/diseños.php";
    }
}


elseif ($accion == "vehiculos") {

    require_once "controladores/vehiculoControlador.php";
    $controller = new vehiculoControlador();

    if ($esAjax) $controller->soloVista();
    else $controller->index();
}

elseif ($accion == "clientes") {

    require_once "controladores/clienteControlador.php";
    $controller = new clienteControlador();

    if ($esAjax) $controller->soloVista();
    else $controller->index();
}

elseif ($accion == "empleados") {

    require_once "controladores/empleadoControlador.php";
    $controller = new empleadoControlador();

    if ($esAjax) $controller->soloVista();
    else $controller->index();
}

elseif ($accion == "ventas") {

    require_once "controladores/ventaControlador.php";
    $controller = new ventaControlador();

    if ($esAjax) $controller->soloVista();
    else $controller->index();
}

elseif ($accion == "informes") {

    require_once "controladores/informeControlador.php";
    $controller = new informeControlador();

    if ($esAjax) $controller->soloVista();
    else $controller->index();
}

elseif ($accion == "guardarEmpleado") {

    require_once "controladores/empleadoControlador.php";
    $controller = new empleadoControlador();
    $controller->guardar();
}

elseif ($accion == "eliminarEmpleado") {

    require_once "controladores/empleadoControlador.php";
    $controller = new empleadoControlador();
    $controller->eliminar();
}

elseif ($accion == "empleadosInactivos") {

    require_once "controladores/empleadoControlador.php";
    $controller = new empleadoControlador();

    $controller->inactivos();
}


// 🔥 GUARDAR VEHICULO
elseif ($accion == "guardarVehiculo") {

    require_once "controladores/vehiculoControlador.php";
    $controller = new vehiculoControlador();
    $controller->guardar();
}

// 🔥 ACTUALIZAR VEHICULO
elseif ($accion == "actualizarVehiculo") {
    require_once "controladores/vehiculoControlador.php";
    $controller = new vehiculoControlador();
    $controller->actualizar();
}


// Eliminar vehículo del inventario
elseif ($accion == "eliminarVehiculo") {

    require_once "controladores/vehiculoControlador.php";
    $controller = new vehiculoControlador();
    $controller->eliminar();
}

elseif ($accion == "reactivarEmpleado") {

    require_once "controladores/empleadoControlador.php";
    $controller = new empleadoControlador();
    $controller->reactivar();
}

elseif ($accion === "ayuda") {
    require "vistas/ayuda.php";
}

// 🔥 VER VEHICULOS VENDIDOS
elseif ($accion == "vehiculosVendidos") {

    require_once "controladores/vehiculoControlador.php";
    $controller = new vehiculoControlador();
    $controller->vendidos();
}

elseif ($accion == "eliminarCliente") {

    require_once "controladores/clienteControlador.php";
    $controller = new clienteControlador();
    $controller->eliminar();
}

elseif ($accion == "guardarCliente") {

    require_once "controladores/clienteControlador.php";
    $controller = new clienteControlador();
    $controller->guardar();
}

elseif ($accion == "guardarVenta") {

    require_once "controladores/ventaControlador.php";
    $controller = new ventaControlador();
    $controller->guardar();
}

elseif ($accion === "opcionesVenta") {

    require_once "controladores/ventaControlador.php";
    $controller = new ventaControlador();
    $controller->opciones();
}

elseif ($accion == "obtenerInforme") {

    $tipo = $_GET['tipo'] ?? null;

    require_once "controladores/informeControlador.php";

    $controller = new informeControlador();
    $controller->soloVista($tipo);
}


elseif ($accion === "pdfInforme") {
    require_once "controladores/informeControlador.php";
    $controller = new informeControlador();
    $controller->pdf();
}




