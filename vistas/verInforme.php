<div id="contenidoInforme">

<h2 class="titulo-informe">
<?php
$esDetalleInforme = in_array($tipo, ["ventasDetalle", "ventasMesDetalle"], true);
if ($tipo == "ventasTotales") echo "Ventas Totales";
if ($tipo == "ventasVendedor") echo "Ventas por Vendedor";
if ($tipo == "ventasVendedorDetalle") echo "Ventas de " . htmlspecialchars($nombreVendedorInforme ?? "vendedor", ENT_QUOTES, 'UTF-8');
if ($tipo == "ventasMes") {
    $fechaMesTitulo = !empty($mesSeleccionado) ? DateTime::createFromFormat('!Y-m', $mesSeleccionado) : false;
    echo $fechaMesTitulo ? "Ventas de " . htmlspecialchars($fechaMesTitulo->format('m/Y'), ENT_QUOTES, 'UTF-8') : "Ventas por Mes";
}
if ($tipo == "ventasMesDetalle") {
    $fechaMesTitulo = !empty($mesSeleccionado) ? DateTime::createFromFormat('!Y-m', $mesSeleccionado) : false;
    echo $fechaMesTitulo ? "Ventas del mes " . htmlspecialchars($fechaMesTitulo->format('m/Y'), ENT_QUOTES, 'UTF-8') : "Ventas del Mes";
}
if ($tipo == "stock") echo "Stock Disponible";
if ($tipo == "ventasDetalle") echo "Todas las Ventas";
if ($tipo == "listaEmpleados") echo "Empleados";
if ($tipo == "listaEmpleadosInactivos") echo "Empleados Inactivos";
if ($tipo == "listaVendedores") echo "Vendedores";
if ($tipo == "listaClientes") echo "Clientes";
?>
</h2>

<?php
$columnasListaPersonas = [
    "legajo" => "Legajo", "nombre" => "Nombre", "apellido" => "Apellido",
    "dni" => "DNI", "nacimiento" => "Nacimiento", "localidad" => "Localidad",
    "direccion" => "Dirección", "telefono" => "Teléfono", "email" => "Email",
    "sueldo" => "Sueldo (USD)", "area" => "Área", "rol" => "Rol", "ingreso" => "Fecha de ingreso"
];
$fechaLista = static function ($valor) {
    $fecha = DateTime::createFromFormat('!Y-m-d', (string)$valor);
    return $fecha ? $fecha->format('d/m/Y') : (string)$valor;
};
$escaparInforme = static function ($valor) {
    return htmlspecialchars((string)$valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
};
$esListaEmpleados = in_array($tipo, ["listaEmpleados", "listaEmpleadosInactivos"], true);
$cantidadTotalLista = 0;
$importeTotalLista = 0;
if ($tipo === "ventasTotales" && !empty($datos)) {
    $cantidadTotalLista = (int)($datos[0]["cantidad"] ?? 0);
    $importeTotalLista = (float)($datos[0]["total"] ?? 0);
} elseif ($esDetalleInforme) {
    foreach ($datos as $filaTotal) {
        $cantidadTotalLista++;
        $importeTotalLista += (float)($filaTotal["precio"] ?? 0);
    }
}
?>

<div class="tabla-informe-wrap">
<table class="tabla-informe <?= $esDetalleInforme ? "tabla-informe-detalle" : ($tipo === "ventasVendedor" ? "tabla-informe-vendedor" : ($esListaEmpleados ? "tabla-informe-empleados" : ($tipo === "listaClientes" ? "tabla-informe-clientes" : ""))) ?>">

<thead>
<tr>

<?php
if ($tipo == "ventasTotales") {
    echo "<th class='numerico'>Cantidad</th><th class='numerico'>Total</th>";
}

if ($tipo == "ventasVendedor") {
    echo "<th>Vendedor</th><th class='numerico'>Cantidad</th><th class='numerico'>Total vendido</th><th class='numerico'>Comisión acumulada</th>";
}

if ($tipo == "ventasVendedorDetalle") {
    echo "<th class='numerico'>ID</th><th>Cliente</th><th>Vehículo</th><th class='numerico'>Precio</th><th class='numerico'>Comisión</th><th class='fecha'>Fecha</th>";
}

if ($tipo == "ventasMes") {
    echo "<th class='fecha'>Mes</th><th class='numerico'>Cantidad</th><th class='numerico'>Total</th>";
}

if ($tipo == "stock") {
    echo "<th class='numerico'>ID</th><th>Tipo</th><th>Marca</th><th>Modelo</th><th class='numerico'>Año</th><th class='numerico'>Kilómetros</th><th>Combustible</th><th class='numerico'>Precio</th>";
}

if ($esListaEmpleados) {
    foreach ($columnasListaPersonas as $campo => $etiqueta) {
        $clase = in_array($campo, ["legajo", "dni", "sueldo"], true) ? " class='numerico'" : ($campo === "ingreso" || $campo === "nacimiento" ? " class='fecha'" : "");
        echo "<th{$clase}>" . $escaparInforme($etiqueta) . "</th>";
    }
}

if ($tipo == "listaVendedores") {
    echo "<th class='numerico'>Legajo</th><th>Vendedor</th><th class='numerico'>Sueldo (USD)</th><th class='numerico'>Porcentaje de comisión</th><th class='numerico'>Comisión acumulada (USD)</th><th class='numerico'>Sueldo total (USD)</th>";
}

if ($tipo == "listaClientes") {
    echo "<th class='numerico'>ID</th><th>Nombre</th><th>Apellido</th><th class='numerico'>DNI</th><th class='fecha'>Nacimiento</th><th>Localidad</th><th>Dirección</th><th>Teléfono</th><th>Email</th>";
}

if ($esDetalleInforme) {
    echo "<th class='numerico'>ID</th><th>Cliente</th><th>Vehículo</th><th>Vendedor</th><th class='numerico'>Precio</th><th class='fecha'>Fecha</th>";
}
?>

</tr>
</thead>

<tbody>

<?php foreach ($datos as $d): ?>
<tr>

<?php
if ($tipo == "ventasTotales") {
    echo "<td class='numerico'>{$d['cantidad']}</td><td class='numerico'>" . number_format((float)$d['total'], 0, ',', '.') . "</td>";
}

if ($esDetalleInforme) {
    echo "<td class='numerico'>" . (int)$d['id'] . "</td>"
        . "<td>" . $escaparInforme($d['cliente']) . "</td>"
        . "<td>" . $escaparInforme($d['vehiculo']) . "</td>"
        . "<td>" . $escaparInforme($d['vendedor']) . "</td>"
        . "<td class='numerico'>" . number_format((float)$d['precio'], 0, ',', '.') . "</td>"
        . "<td class='fecha'>" . $escaparInforme($d['fecha']) . "</td>";
}

if ($tipo == "ventasVendedor") {
    echo "<td>" . $escaparInforme($d['vendedor']) . "</td><td class='numerico'>" . (int)$d['cantidad'] . "</td><td class='numerico'>" . number_format((float)$d['total'], 0, ',', '.') . "</td><td class='numerico'>" . number_format((float)$d['comision'], 0, ',', '.') . "</td>";
}

if ($tipo == "ventasVendedorDetalle") {
    echo "<td class='numerico'>" . $escaparInforme($d['id']) . "</td><td>" . $escaparInforme($d['cliente']) . "</td><td>" . $escaparInforme($d['vehiculo']) . "</td><td class='numerico'>" . number_format((float)$d['precio'], 0, ',', '.') . "</td><td class='numerico'>" . number_format((float)$d['comision'], 0, ',', '.') . "</td><td class='fecha'>" . $escaparInforme($d['fecha']) . "</td>";
}

if ($tipo == "ventasMes") {
    echo "<td class='fecha'>" . $escaparInforme($d['mes']) . "</td><td class='numerico'>" . (int)$d['cantidad'] . "</td><td class='numerico'>" . number_format((float)$d['total'], 0, ',', '.') . "</td>";
}

if ($tipo == "stock") {
    echo "<td class='numerico'>" . (int)$d['id'] . "</td>"
        . "<td>" . $escaparInforme($d['tipo']) . "</td>"
        . "<td>" . $escaparInforme($d['marca']) . "</td>"
        . "<td>" . $escaparInforme($d['modelo']) . "</td>"
        . "<td class='numerico'>" . (int)$d['anio'] . "</td>"
        . "<td class='numerico'>" . number_format((float)$d['km'], 0, ',', '.') . " km</td>"
        . "<td>" . $escaparInforme($d['combustible']) . "</td>"
        . "<td class='numerico'>" . number_format((float)$d['precio'], 0, ',', '.') . "</td>";
}

if ($esListaEmpleados) {
    foreach ($columnasListaPersonas as $campo => $_etiqueta) {
        $valor = $d[$campo] ?? "";
        if ($campo === "sueldo") {
            $valor = number_format((float)$valor, 0, ',', '.');
        } elseif (in_array($campo, ["ingreso", "nacimiento"], true) && $valor !== "") {
            $valor = $fechaLista($valor);
        }
        $clase = in_array($campo, ["legajo", "dni", "sueldo"], true) ? " class='numerico'" : (in_array($campo, ["ingreso", "nacimiento"], true) ? " class='fecha'" : "");
        echo "<td{$clase}>" . $escaparInforme($valor) . "</td>";
    }
}

if ($tipo == "listaVendedores") {
    echo "<td class='numerico'>" . $escaparInforme($d['legajo'] ?? "") . "</td><td>" . $escaparInforme($d['vendedor'] ?? "") . "</td><td class='numerico'>" . number_format((float)($d['sueldo'] ?? 0), 0, ',', '.') . "</td><td class='numerico'>" . $escaparInforme($d['porcentaje'] ?? 0) . "%</td><td class='numerico'>" . number_format((float)($d['comision'] ?? 0), 0, ',', '.') . "</td><td class='numerico'>" . number_format((float)($d['total'] ?? 0), 0, ',', '.') . "</td>";
}

if ($tipo == "listaClientes") {
    foreach (["id", "nombre", "apellido", "dni", "nacimiento", "localidad", "direccion", "telefono", "email"] as $campo) {
        $valor = $d[$campo] ?? "";
        if ($campo === "nacimiento" && $valor !== "") $valor = $fechaLista($valor);
        $clase = in_array($campo, ["id", "dni"], true) ? " class='numerico'" : ($campo === "nacimiento" ? " class='fecha'" : "");
        echo "<td{$clase}>" . $escaparInforme($valor) . "</td>";
    }
}
?>

</tr>
<?php endforeach; ?>

</tbody>

<?php if (in_array($tipo, ["ventasDetalle", "ventasMesDetalle"], true)): ?>
<tfoot>
    <tr><th colspan="4">Total de la lista: <?= number_format($cantidadTotalLista, 0, ',', '.') ?> ventas</th><th class="numerico">$<?= number_format($importeTotalLista, 0, ',', '.') ?></th><th></th></tr>
</tfoot>
<?php endif; ?>

<?php if ($tipo === "ventasVendedorDetalle"): ?>
<tfoot>
    <?php $comisionAcumuladaInforme = array_sum(array_map(static fn($ventaInforme) => (float)$ventaInforme['comision'], $datos)); ?>
    <tr><th colspan="4">Total: <?= number_format(count($datos), 0, ',', '.') ?> ventas</th><th class="numerico">Comisión acumulada: $<?= number_format($comisionAcumuladaInforme, 0, ',', '.') ?></th><th></th></tr>
</tfoot>
<?php endif; ?>

</table>
</div>

</div>

