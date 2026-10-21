<?php

require_once "modelos/informe.php";

class informeControlador {

    private function mesesDelSelector() {
        $zonaHoraria = new DateTimeZone("America/Argentina/Cordoba");
        $mesActual = new DateTimeImmutable("first day of this month", $zonaHoraria);
        $meses = [];
        for ($desfase = 0; $desfase < 6; $desfase++) {
            $meses[] = $mesActual->modify("-{$desfase} months")->format("Y-m");
        }
        return [$meses, $mesActual->format("Y-m")];
    }

    public function index() {
        [$mesesDisponibles, $mesActualInforme] = $this->mesesDelSelector();
        $vendedoresInforme = (new Informe())->listaVendedores();

        $contenido = "vistas/informes.php";
        require "vistas/diseños.php";
    }

    public function soloVista() {

    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Pragma: no-cache");

    $tipo = $_GET['tipo'] ?? null;

    if (!$tipo) {
        [$mesesDisponibles, $mesActualInforme] = $this->mesesDelSelector();
        $vendedoresInforme = (new Informe())->listaVendedores();
        require "vistas/informes.php";
        return;
    }

    $modelo = new Informe();

    switch ($tipo) {

        case "ventasTotales":
            $datos = $modelo->ventasTotales();
            break;

        case "ventasVendedor":
            $datos = $modelo->ventasPorVendedor();
            break;

        case "ventasVendedorDetalle":
            $legajoVendedorInforme = filter_var($_GET["legajo"] ?? null, FILTER_VALIDATE_INT);
            $vendedoresInforme = $modelo->listaVendedores();
            $nombreVendedorInforme = null;
            foreach ($vendedoresInforme as $vendedorInforme) {
                if ((int)$vendedorInforme["legajo"] === (int)$legajoVendedorInforme) {
                    $nombreVendedorInforme = $vendedorInforme["vendedor"];
                    break;
                }
            }
            if (!$legajoVendedorInforme || $nombreVendedorInforme === null) {
                http_response_code(400);
                echo "Elegí un vendedor válido";
                return;
            }
            $datos = $modelo->ventasDetalleVendedor($legajoVendedorInforme);
            break;

        case "ventasMes":
            $mes = $_GET["mes"] ?? null;
            if ($mes !== null && !preg_match('/^\\d{4}-(0[1-9]|1[0-2])$/', $mes)) {
                http_response_code(400);
                echo "Mes no válido";
                return;
            }
            $datos = $modelo->ventasPorMes($mes);
            $mesSeleccionado = $mes;
            break;

        case "ventasMesDetalle":
            $mes = $_GET["mes"] ?? null;
            if ($mes === null || !preg_match('/^\\d{4}-(0[1-9]|1[0-2])$/', $mes)) {
                http_response_code(400);
                echo "Elegí un mes válido";
                return;
            }
            $datos = $modelo->ventasDetalle($mes);
            $mesSeleccionado = $mes;
            $tipoPresentacion = "ventasDetalle";
            break;

        case "stock":
        case "stockFiltro":
            [$filtroTipoStock, $filtroEstadoStock] = $this->filtrosStock();
            if ($filtroTipoStock === false || $filtroEstadoStock === false) {
                http_response_code(400);
                echo "Elegí filtros de stock válidos";
                return;
            }
            $datos = $modelo->stockDisponible($filtroTipoStock, $filtroEstadoStock);
            if ($tipo === "stockFiltro") $tipoPresentacion = "stock";
            break;

        case "vehiculosVendidos":
            $datos = $modelo->vehiculosVendidos();
            break;

        case "listaEmpleados":
            $datos = $modelo->listaEmpleados();
            break;

        case "listaEmpleadosInactivos":
            $datos = $modelo->listaEmpleadosInactivos();
            break;

        case "listaVendedores":
            $datos = $modelo->listaVendedores();
            break;

        case "listaClientes":
            $datos = $modelo->listaClientes();
            break;

        case "ventasDetalle":
            $datos = $modelo->ventasDetalle();
            break;

        default:
            echo "Informe no válido";
            return;
    }

    require "vistas/verInforme.php";
}

public function pdf() {
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Pragma: no-cache");

    $tipo = $_GET["tipo"] ?? "";

    $metodos = [
        "ventasTotales"  => "ventasTotales",
        "ventasVendedor" => "ventasPorVendedor",
        "ventasMes"      => "ventasPorMes",
        "ventasMesDetalle" => "ventasDetalle",
        "stock"          => "stockDisponible",
        "stockFiltro"    => "stockDisponible",
        "vehiculosVendidos" => "vehiculosVendidos",
        "listaEmpleados" => "listaEmpleados",
        "listaEmpleadosInactivos" => "listaEmpleadosInactivos",
        "listaVendedores" => "listaVendedores",
        "listaClientes" => "listaClientes",
        "ventasDetalle"  => "ventasDetalle",
        "ventasVendedorDetalle" => "ventasDetalleVendedor",
    ];

    if (!isset($metodos[$tipo])) {
        http_response_code(400);
        exit("Informe no válido");
    }

    $modelo = new Informe();
    $mesSeleccionado = null;
    if (in_array($tipo, ["stock", "stockFiltro"], true)) {
        [$filtroTipoStock, $filtroEstadoStock] = $this->filtrosStock();
        if ($filtroTipoStock === false || $filtroEstadoStock === false) {
            http_response_code(400);
            exit("Elegí filtros de stock válidos");
        }
        $datos = $modelo->stockDisponible($filtroTipoStock, $filtroEstadoStock);
        if ($tipo === "stockFiltro") $tipoPresentacion = "stock";
        $filtrosStockTitulo = [$filtroTipoStock ?? "Todos", $filtroEstadoStock ?? "Todos"];
    } elseif ($tipo === "vehiculosVendidos") {
        $datos = $modelo->vehiculosVendidos();
    } elseif ($tipo === "ventasVendedorDetalle") {
        $legajoVendedorInforme = filter_var($_GET["legajo"] ?? null, FILTER_VALIDATE_INT);
        $vendedoresInforme = $modelo->listaVendedores();
        $nombreVendedorInforme = null;
        foreach ($vendedoresInforme as $vendedorInforme) {
            if ((int)$vendedorInforme["legajo"] === (int)$legajoVendedorInforme) {
                $nombreVendedorInforme = $vendedorInforme["vendedor"];
                break;
            }
        }
        if (!$legajoVendedorInforme || $nombreVendedorInforme === null) {
            http_response_code(400);
            exit("Elegí un vendedor válido");
        }
        $datos = $modelo->ventasDetalleVendedor($legajoVendedorInforme);
    } elseif (in_array($tipo, ["ventasMes", "ventasMesDetalle"], true)) {
        $mesSeleccionado = $_GET["mes"] ?? null;
        if (($tipo === "ventasMesDetalle" && $mesSeleccionado === null) || ($mesSeleccionado !== null && !preg_match('/^\\d{4}-(0[1-9]|1[0-2])$/', $mesSeleccionado))) {
            http_response_code(400);
            exit("Mes no válido");
        }
        if ($tipo === "ventasMesDetalle") {
            $datos = $modelo->ventasDetalle($mesSeleccionado);
            $tipoPresentacion = "ventasDetalle";
        } else {
            $datos = $modelo->ventasPorMes($mesSeleccionado);
        }
    } else {
        $datos = $modelo->{$metodos[$tipo]}();
    }

    require "vistas/pdfInforme.php";
}

private function filtrosStock() {
    $tipo = $_GET["tipoVehiculo"] ?? "todos";
    $estado = $_GET["condicion"] ?? "todos";
    $tiposValidos = ["todos" => null, "Auto" => "Auto", "Moto" => "Moto"];
    $estadosValidos = ["todos" => null, "Nuevo" => "Nuevo", "Usado" => "Usado"];
    return [array_key_exists($tipo, $tiposValidos) ? $tiposValidos[$tipo] : false,
        array_key_exists($estado, $estadosValidos) ? $estadosValidos[$estado] : false];
}


}
