<?php
$titulos = [
    "ventasTotales"  => "Ventas totales",
    "ventasVendedor" => "Ventas por vendedor",
    "ventasVendedorDetalle" => "Ventas del vendedor",
    "ventasMes"      => "Ventas por mes",
    "stock"          => "Vehículos disponibles",
    "vehiculosVendidos" => "Vehículos vendidos",
    "ventasDetalle"  => "Detalle de ventas",
    "listaEmpleados" => "Empleados",
    "listaEmpleadosInactivos" => "Empleados inactivos",
    "listaVendedores" => "Vendedores",
    "listaClientes" => "Clientes",
];

$columnas = [
    "ventasTotales"  => ["cantidad" => "Cantidad de ventas", "total" => "Importe total"],
    "ventasVendedor" => ["vendedor" => "Vendedor", "cantidad" => "Ventas", "total" => "Total vendido", "comision" => "Comisión acumulada"],
    "ventasVendedorDetalle" => ["id" => "N.º", "cliente" => "Cliente", "vehiculo" => "Vehículo", "precio" => "Precio", "comision" => "Comisión", "fecha" => "Fecha"],
    "ventasMes"      => ["mes" => "Mes", "cantidad" => "Ventas", "total" => "Importe total"],
    "stock"          => ["id" => "ID", "tipo" => "Tipo", "marca" => "Marca", "modelo" => "Modelo", "anio" => "Año", "km" => "Kilómetros", "estado" => "Estado", "combustible" => "Combustible", "precio" => "Precio"],
    "vehiculosVendidos" => ["id" => "ID", "tipo" => "Tipo", "marca" => "Marca", "modelo" => "Modelo", "anio" => "Año", "km" => "Kilómetros", "estado" => "Estado", "precio" => "Precio vendido", "fecha" => "Fecha de venta", "vendedor" => "Vendedor"],
    "ventasDetalle"  => ["id" => "N.º", "cliente" => "Cliente", "vehiculo" => "Vehículo", "vendedor" => "Vendedor", "precio" => "Precio", "fecha" => "Fecha"],
    "listaEmpleados" => ["legajo" => "Legajo", "nombre" => "Nombre", "apellido" => "Apellido", "dni" => "DNI", "nacimiento" => "Nacimiento", "localidad" => "Localidad", "direccion" => "Dirección", "telefono" => "Teléfono", "email" => "Email", "sueldo" => "Sueldo (USD)", "area" => "Área", "rol" => "Rol", "ingreso" => "Fecha de ingreso"],
    "listaEmpleadosInactivos" => ["legajo" => "Legajo", "nombre" => "Nombre", "apellido" => "Apellido", "dni" => "DNI", "nacimiento" => "Nacimiento", "localidad" => "Localidad", "direccion" => "Dirección", "telefono" => "Teléfono", "email" => "Email", "sueldo" => "Sueldo (USD)", "area" => "Área", "rol" => "Rol", "ingreso" => "Fecha de ingreso"],
    "listaVendedores" => ["legajo" => "Legajo", "vendedor" => "Vendedor", "sueldo" => "Sueldo (USD)", "porcentaje" => "Porcentaje de comisión", "comision" => "Comisión (USD)", "total" => "Sueldo total (USD)"],
    "listaClientes" => ["id" => "ID", "nombre" => "Nombre", "apellido" => "Apellido", "dni" => "DNI", "nacimiento" => "Nacimiento", "localidad" => "Localidad", "direccion" => "Dirección", "telefono" => "Teléfono", "email" => "Email"],
];

function h($valor) {
    return htmlspecialchars((string)$valor, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
}

$tipoPresentacion = $tipoPresentacion ?? $tipo;
$titulo = $titulos[$tipoPresentacion] ?? "Informe";
if ($tipoPresentacion === "ventasVendedorDetalle" && !empty($nombreVendedorInforme)) {
    $titulo .= " · " . $nombreVendedorInforme;
}
if ($tipo === "stockFiltro" && isset($filtrosStockTitulo)) {
    [$tipoStockTitulo, $estadoStockTitulo] = $filtrosStockTitulo;
    $nombreTipoStock = $tipoStockTitulo === "Auto" ? "Autos" : ($tipoStockTitulo === "Moto" ? "Motos" : "Vehículos");
    $nombreEstadoStock = $estadoStockTitulo === "Nuevo" ? "nuevos" : ($estadoStockTitulo === "Usado" ? "usados" : "");
    $titulo = trim($nombreTipoStock . " " . $nombreEstadoStock . " disponibles");
    if ($tipoStockTitulo === "Todos" && $estadoStockTitulo !== "Todos") {
        $titulo = ucfirst($nombreEstadoStock) . " disponibles";
    }
    if ($filtrosStockTitulo[0] !== "Todos") unset($columnas["stock"]["tipo"]);
    if ($filtrosStockTitulo[1] !== "Todos") unset($columnas["stock"]["estado"]);
}
if (in_array($tipo, ["ventasMes", "ventasMesDetalle"], true) && !empty($mesSeleccionado)) {
    $fechaMesTitulo = DateTime::createFromFormat('!Y-m', $mesSeleccionado);
    if ($fechaMesTitulo) {
        $prefijoTitulo = $tipo === "ventasMesDetalle" ? "Ventas del mes " : "Ventas de ";
        $titulo = $prefijoTitulo . $fechaMesTitulo->format('m/Y');
    }
}
$campos = $columnas[$tipoPresentacion] ?? [];
$fechaEmision = (new DateTimeImmutable("now", new DateTimeZone("America/Argentina/Buenos_Aires")))->format("d/m/Y H:i");
$cantidadRegistros = count($datos);
$logo = "public/imagenes/logo.png";
$estilosColumnas = [];
$alineacionesEncabezado = [];
foreach (array_keys($campos) as $indice => $campo) {
    if (in_array($campo, ["cantidad", "total", "precio", "comision", "sueldo", "id", "anio", "km", "legajo", "dni", "porcentaje"], true)) {
        $estilosColumnas[$indice] = ["halign" => "right"];
        $alineacionesEncabezado[] = "right";
    } elseif (in_array($campo, ["fecha", "mes"], true)) {
        $estilosColumnas[$indice] = ["halign" => "center"];
        $alineacionesEncabezado[] = "center";
    } else {
        $alineacionesEncabezado[] = "left";
    }
}
$pieInforme = [];
if ($tipoPresentacion === "ventasDetalle") {
    $cantidadTotalLista = 0;
    $importeTotalLista = 0;
    foreach ($datos as $filaTotal) {
        $cantidadTotalLista++;
        $importeTotalLista += (float)($filaTotal["precio"] ?? 0);
    }
    $pieInforme = [["Total de la lista: " . number_format($cantidadTotalLista, 0, ',', '.') . " ventas", "", "", "", "u$d " . number_format($importeTotalLista, 0, ',', '.'), ""]];
} elseif ($tipoPresentacion === "ventasVendedorDetalle") {
    $comisionAcumuladaInforme = array_sum(array_map(static fn($fila) => (float)($fila["comision"] ?? 0), $datos));
    $pieInforme = [["Total: " . number_format($cantidadRegistros, 0, ',', '.') . " ventas", "", "", "", "u$d " . number_format($comisionAcumuladaInforme, 0, ',', '.'), ""]];
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($titulo) ?> | FG Motors</title>
    <style>
        :root {
            color-scheme: light;
            --ink: #202329;
            --muted: #69717c;
            --line: #d9dde2;
            --gold: #b58a37;
            --gold-pale: #f4eddd;
            --paper: #fff;
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: #eef0f2;
            color: var(--ink);
            font: 14px/1.45 Arial, Helvetica, sans-serif;
        }
        .sheet {
            width: min(100% - 40px, 980px);
            margin: 28px auto;
            padding: 34px 42px 28px;
            background: var(--paper);
            box-shadow: 0 8px 28px rgba(20, 25, 32, .12);
        }
        .masthead {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
            padding-bottom: 20px;
            border-bottom: 3px solid var(--gold);
            text-align: left;
        }
        .logo {
            display: block;
            width: 92px;
            height: 92px;
            object-fit: contain;
        }
        .brand-name {
            margin: 0;
            font-size: 24px;
            font-weight: 800;
            letter-spacing: .18em;
        }
        .brand-caption {
            margin: 4px 0 0;
            color: var(--muted);
            font-size: 11px;
            letter-spacing: .2em;
            text-transform: uppercase;
        }
        .report-heading {
            display: flex;
            align-items: end;
            justify-content: space-between;
            gap: 20px;
            margin: 28px 0 20px;
        }
        .eyebrow {
            margin: 0 0 5px;
            color: var(--gold);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .16em;
            text-transform: uppercase;
        }
        h1 {
            margin: 0;
            font-size: 25px;
            line-height: 1.2;
        }
        .emission {
            color: var(--muted);
            font-size: 11px;
            text-align: right;
            white-space: nowrap;
        }
        .table-wrap { overflow-x: auto; }
        .numeric { text-align: right !important; font-variant-numeric: tabular-nums; }
        .centered { text-align: center !important; }
        table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid var(--line);
        }
        thead { display: table-header-group; }
        th {
            padding: 11px 12px;
            background: #272b31;
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .08em;
            text-align: left;
            text-transform: uppercase;
        }
        th:first-child { border-top: 3px solid var(--gold); }
        tfoot th {
            border-top: 2px solid var(--gold);
            background: var(--gold-pale);
            color: var(--ink);
            letter-spacing: 0;
            text-transform: none;
        }
        tfoot .summary-total { text-align: left; }
        td {
            padding: 10px 12px;
            border-bottom: 1px solid var(--line);
            vertical-align: top;
        }
        tbody tr:nth-child(even) { background: #f6f7f8; }
        tbody tr { break-inside: avoid; }
        .numeric { text-align: right; font-variant-numeric: tabular-nums; }
        .empty {
            padding: 24px 12px;
            color: var(--muted);
            text-align: center;
        }
        .report-footer {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin-top: 22px;
            padding-top: 12px;
            border-top: 1px solid var(--line);
            color: var(--muted);
            font-size: 10px;
        }
        .print-actions { margin: 18px auto 32px; text-align: center; }
        .print-button {
            padding: 10px 20px;
            border: 0;
            border-radius: 5px;
            background: #292d33;
            color: #fff;
            cursor: pointer;
            font-size: 13px;
        }
        .print-button:hover { background: #41464e; }

        @page {
            size: A4 portrait;
            margin: 14mm;
        }
        @media print {
            body { background: #fff; font-size: 11px; }
            .sheet {
                width: auto;
                margin: 0;
                padding: 0;
                box-shadow: none;
            }
            .masthead { padding-bottom: 12px; }
            .logo { width: 70px; height: 70px; }
            .report-heading { margin: 20px 0 14px; }
            th { background: #272b31 !important; color: #fff !important; print-color-adjust: exact; }
            tbody tr:nth-child(even) { background: #f6f7f8 !important; print-color-adjust: exact; }
            .print-actions { display: none; }
            .report-footer { margin-top: 14px; }
        }
    </style>
</head>
<body>
    <main class="sheet">
        <header class="masthead">
            <img class="logo" src="<?= h($logo) ?>" alt="Logo de FG Motors">
            <div>
                <p class="brand-name">FG MOTORS</p>
                <p class="brand-caption">Automotores · Informes de gestión</p>
            </div>
        </header>

        <section class="report-heading">
            <div>
                <p class="eyebrow">Informe de gestión</p>
                <h1><?= h($titulo) ?></h1>
            </div>
            <div class="emission">
                Emitido<br><strong><?= h($fechaEmision) ?></strong>
            </div>
        </section>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <?php foreach ($campos as $campo => $etiqueta): ?>
                            <?php
                                $alineacion = in_array($campo, ["cantidad", "total", "precio", "comision", "sueldo", "id", "anio", "km", "legajo", "dni", "porcentaje"], true)
                                    ? "right"
                                    : (in_array($campo, ["fecha", "mes"], true) ? "center" : "left");
                                $claseAlineacion = $alineacion === "right" ? "numeric" : ($alineacion === "center" ? "centered" : "");
                            ?>
                            <th<?= $claseAlineacion ? ' class="' . $claseAlineacion . '"' : "" ?>><?= h($etiqueta) ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($datos)): ?>
                        <tr><td class="empty" colspan="<?= max(count($campos), 1) ?>">No hay datos para mostrar en este informe.</td></tr>
                    <?php else: ?>
                        <?php foreach ($datos as $fila): ?>
                            <tr>
                                <?php foreach ($campos as $campo => $etiqueta): ?>
                                    <?php $esNumero = in_array($campo, ["cantidad", "total", "precio", "comision", "sueldo", "id", "anio", "km", "legajo", "dni", "porcentaje"], true); ?>
                                    <td class="<?= $esNumero ? "numeric" : "" ?>">
                                        <?php if (in_array($campo, ["precio", "total", "comision", "sueldo"], true) && isset($fila[$campo]) && $fila[$campo] !== ""): ?>
                                            u$d <?= h(number_format((float)$fila[$campo], 0, ',', '.')) ?>
                                        <?php elseif ($campo === "porcentaje" && isset($fila[$campo]) && $fila[$campo] !== ""): ?>
                                            <?= h($fila[$campo]) ?>%
                                        <?php elseif ($campo === "km" && isset($fila[$campo]) && $fila[$campo] !== ""): ?>
                                            <?= h(number_format((float)$fila[$campo], 0, ',', '.')) ?> km
                                        <?php elseif (in_array($campo, ["nacimiento", "ingreso"], true) && !empty($fila[$campo])): ?>
                                            <?php $fechaLista = DateTime::createFromFormat('!Y-m-d', (string)$fila[$campo]); ?>
                                            <?= h($fechaLista ? $fechaLista->format('d/m/Y') : $fila[$campo]) ?>
                                        <?php else: ?>
                                            <?= h($fila[$campo] ?? "—") ?>
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <?php if (in_array($tipoPresentacion, ["ventasDetalle", "ventasVendedorDetalle"], true)): ?>
                    <tfoot>
                        <?php if ($tipoPresentacion === "ventasDetalle"): ?>
                            <tr>
                                <th colspan="4">Total de la lista: <?= number_format($cantidadTotalLista, 0, ',', '.') ?> ventas</th>
                                <th class="numeric">u$d <?= number_format($importeTotalLista, 0, ',', '.') ?></th>
                                <th></th>
                            </tr>
                        <?php else: ?>
                            <tr>
                                <th colspan="4">Total: <?= number_format($cantidadRegistros, 0, ',', '.') ?> ventas</th>
                                <th class="numeric">u$d <?= number_format($comisionAcumuladaInforme, 0, ',', '.') ?></th>
                                <th></th>
                            </tr>
                        <?php endif; ?>
                    </tfoot>
                <?php endif; ?>
            </table>
        </div>

        <footer class="report-footer">
            <span>FG Motors · Documento generado por el sistema</span>
            <span><?= count($datos) ?> registro<?= count($datos) === 1 ? "" : "s" ?></span>
        </footer>
    </main>

    <div class="print-actions">
        <button class="print-button" id="download-pdf" onclick="descargarPDFInforme()">Descargar PDF</button>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.4/jspdf.plugin.autotable.min.js"></script>
    <script>
        async function descargarPDFInforme() {
            const boton = document.getElementById("download-pdf");
            const textoOriginal = boton.textContent;
            boton.disabled = true;
            boton.textContent = "Generando PDF…";

            try {
                if (!window.jspdf || !window.jspdf.jsPDF) {
                    throw new Error("No se pudo cargar el generador de PDF. Revisá tu conexión a Internet e intentá de nuevo.");
                }

                const { jsPDF } = window.jspdf;
                const doc = new jsPDF({ orientation: "portrait", unit: "mm", format: "a4" });
                const titulo = <?= json_encode($titulo, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
                const fechaEmision = <?= json_encode($fechaEmision) ?>;
                const logoResponse = await fetch(<?= json_encode($logo) ?>);
                if (!logoResponse.ok) throw new Error("No se pudo cargar el logo.");
                const logoBlob = await logoResponse.blob();
                const logoData = await new Promise((resolve, reject) => {
                    const reader = new FileReader();
                    reader.onload = () => resolve(reader.result);
                    reader.onerror = () => reject(new Error("No se pudo preparar el logo."));
                    reader.readAsDataURL(logoBlob);
                });

                const encabezados = Array.from(document.querySelectorAll("table thead th"))
                    .map(celda => celda.textContent.trim());
                const filas = Array.from(document.querySelectorAll("table tbody tr"))
                    .map(fila => Array.from(fila.querySelectorAll("td"))
                        .map(celda => celda.textContent.trim()));

                const margen = 14;
                const anchoPagina = doc.internal.pageSize.getWidth();
                const altoPagina = doc.internal.pageSize.getHeight();
                const anchoLogo = 22;
                const centroX = anchoPagina / 2;
                const cantidadRegistros = <?= (int)$cantidadRegistros ?>;
                doc.addImage(logoData, "PNG", centroX - anchoLogo / 2, 8, anchoLogo, anchoLogo);
                doc.setTextColor(32, 35, 41);
                doc.setFont("helvetica", "bold");
                doc.setFontSize(15);
                doc.text("FG MOTORS", centroX, 36, { align: "center" });
                doc.setFont("helvetica", "normal");
                doc.setFontSize(8);
                doc.setTextColor(105, 113, 124);
                doc.text("AUTOMOTORES  ·  INFORMES DE GESTIÓN", centroX, 41, { align: "center" });
                doc.setDrawColor(181, 138, 55);
                doc.setLineWidth(1.2);
                doc.line(margen, 47, anchoPagina - margen, 47);
                doc.setTextColor(181, 138, 55);
                doc.setFont("helvetica", "bold");
                doc.setFontSize(8);
                doc.text("INFORME DE GESTIÓN", centroX, 55, { align: "center" });
                doc.setTextColor(32, 35, 41);
                doc.setFontSize(16);
                doc.text(titulo, centroX, 63, { align: "center" });
                doc.setFont("helvetica", "normal");
                doc.setFontSize(8);
                doc.setTextColor(105, 113, 124);
                doc.text("Emitido: " + fechaEmision + "   ·   " + cantidadRegistros + " registro" + (cantidadRegistros === 1 ? "" : "s"), centroX, 69, { align: "center" });

                if (typeof doc.autoTable !== "function") {
                    throw new Error("No se pudo cargar el complemento de tablas PDF.");
                }
                doc.autoTable({
                    head: [encabezados],
                    body: filas,
                    foot: <?= json_encode($pieInforme, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>,
                    showFoot: "lastPage",
                    startY: 75,
                    // startY reserva el encabezado grande solo en la primera página.
                    // En las siguientes, el margen corto permite aprovechar el alto disponible.
                    margin: { top: 18, right: margen, bottom: 18, left: margen },
                    theme: "grid",
                    columnStyles: <?= json_encode($estilosColumnas) ?>,
                    headStyles: { fillColor: [39, 43, 49], textColor: [255, 255, 255], fontStyle: "bold" },
                    footStyles: { fillColor: [244, 237, 221], textColor: [32, 35, 41], fontStyle: "bold" },
                    alternateRowStyles: { fillColor: [246, 247, 248] },
                    styles: { font: "helvetica", fontSize: 8, cellPadding: 3, overflow: "linebreak", valign: "middle", textColor: [32, 35, 41], lineColor: [217, 221, 226] },
                    didParseCell: data => {
                        if (data.section === "head") {
                            const alignments = <?= json_encode($alineacionesEncabezado) ?>;
                            data.cell.styles.halign = alignments[data.column.index] || "left";
                        }
                    },
                    willDrawPage: data => {
                        if (data.pageNumber > 1) {
                            doc.setFont("helvetica", "bold");
                            doc.setFontSize(8);
                            doc.setTextColor(105, 113, 124);
                            doc.text(titulo + " · continuación", margen, 10);
                            doc.setDrawColor(181, 138, 55);
                            doc.setLineWidth(0.35);
                            doc.line(margen, 13, anchoPagina - margen, 13);
                        }
                    },
                    didDrawPage: () => {
                        const pagina = doc.internal.getCurrentPageInfo().pageNumber;
                        doc.setFontSize(8);
                        doc.setTextColor(105, 113, 124);
                        doc.text("FG Motors · " + cantidadRegistros + " registro" + (cantidadRegistros === 1 ? "" : "s"), margen, altoPagina - 8);
                        doc.text("Página " + pagina, anchoPagina - margen, altoPagina - 8, { align: "right" });
                    }
                });

                const nombre = titulo.normalize("NFD").replace(/[\u0300-\u036f]/g, "")
                    .toLowerCase().replace(/[^a-z0-9]+/g, "-").replace(/^-|-$/g, "");
                doc.save("fg-motors-" + nombre + ".pdf");
            } catch (error) {
                alert(error.message || "No se pudo generar el PDF.");
            } finally {
                boton.disabled = false;
                boton.textContent = textoOriginal;
            }
        }
    </script>
</body>
</html>
