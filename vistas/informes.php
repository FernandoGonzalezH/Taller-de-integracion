
<h2 class="titulo-metal">Informes</h2>

<div class="card">

<style>
    #tablaInformes thead th { text-align: left !important; }
</style>

<table id="tablaInformes" class="display tabla-ayuda" style="width:90%; margin: 0 auto">
    <thead>
        <tr>
            <th>Informe</th>
            <th>Ver</th>
        </tr>
    </thead>
    <tbody>

        <tr>
            <td>Ventas totales</td>
            <td>
                <button class="btn-metal" aria-label="Ver ventas totales" title="Ver informe" onclick="generarPDF('ventasTotales')">👁</button>
            </td>
        </tr>

        <tr>
            <td>Ventas por mes</td>
            <td>
                <button class="btn-metal" aria-label="Elegir mes para el informe de ventas" title="Elegir mes para el informe" onclick="abrirSelectorMesInforme('ventasMes')">👁</button>
            </td>
        </tr>

        <tr>
            <td>Ventas del mes</td>
            <td>
                <button class="btn-metal" aria-label="Elegir mes para ver las ventas" title="Elegir mes para ver las ventas" onclick="abrirSelectorMesInforme('ventasMesDetalle')">👁</button>
            </td>
        </tr>

        <tr>
            <td>Ventas por vendedor</td>
            <td>
                <button class="btn-metal" aria-label="Ver ventas por vendedor" title="Ver informe" onclick="generarPDF('ventasVendedor')">👁</button>
            </td>
        </tr>

        <tr>
            <td>Ventas del vendedor</td>
            <td>
                <button class="btn-metal" aria-label="Elegir vendedor y ver sus ventas" title="Ver informe" onclick="abrirSelectorVendedorInforme()">👁</button>
            </td>
        </tr>

        <tr>
            <td>Todas las ventas</td>
            <td>
                <button class="btn-metal" aria-label="Ver detalle de ventas" title="Ver informe" onclick="generarPDF('ventasDetalle')">👁</button>
            </td>
        </tr>

        <tr>
            <td>Vehículos disponibles</td>
            <td>
                <button class="btn-metal" aria-label="Elegir filtros de vehículos disponibles" title="Filtrar vehículos" onclick="abrirSelectorStockInforme()">👁</button>
            </td>
        </tr>

        <tr>
            <td>Vehículos vendidos</td>
            <td>
                <button class="btn-metal" aria-label="Ver vehículos vendidos" title="Ver informe" onclick="generarPDF('vehiculosVendidos')">👁</button>
            </td>
        </tr>

        <tr>
            <td>Clientes</td>
            <td><button class="btn-metal" aria-label="Ver clientes" title="Ver informe" onclick="generarPDF('listaClientes')">👁</button></td>
        </tr>

        <tr>
            <td>Vendedores</td>
            <td><button class="btn-metal" aria-label="Ver vendedores" title="Ver informe" onclick="generarPDF('listaVendedores')">👁</button></td>
        </tr>

        <tr>
            <td>Empleados</td>
            <td><button class="btn-metal" aria-label="Ver empleados" title="Ver informe" onclick="generarPDF('listaEmpleados')">👁</button></td>
        </tr>

        <tr>
            <td>Empleados inactivos</td>
            <td><button class="btn-metal" aria-label="Ver empleados inactivos" title="Ver informe" onclick="generarPDF('listaEmpleadosInactivos')">👁</button></td>
        </tr>

    </tbody>
</table>


</div>

<div id="modalStockInforme" class="modal">
    <div class="modal-contenido">
        <h3 class="titulo-metal">Vehículos disponibles</h3>
        <div class="form-grupo">
            <label for="tipoStockInforme">Tipo de vehículo</label>
            <select id="tipoStockInforme">
                <option value="todos">Todos</option>
                <option value="Auto">Auto</option>
                <option value="Moto">Moto</option>
            </select>
        </div>
        <div class="form-grupo">
            <label for="condicionStockInforme">Condición</label>
            <select id="condicionStockInforme">
                <option value="todos">Todos</option>
                <option value="Nuevo">Nuevos</option>
                <option value="Usado">Usados</option>
            </select>
        </div>
        <div class="botones-form">
            <button class="btn-gold" onclick="generarPDFStockInforme()">Ver informe</button>
            <button class="btn-gold" onclick="cerrarSelectorStockInforme()">Cancelar</button>
        </div>
    </div>
</div>

<div id="modalVendedorInforme" class="modal">
    <div class="modal-contenido">
        <h3 class="titulo-metal">Ventas del vendedor</h3>
        <div class="form-grupo">
            <label for="vendedorInforme">Elegí el vendedor</label>
            <select id="vendedorInforme">
                <option value="">Seleccionar vendedor</option>
                <?php foreach (($vendedoresInforme ?? []) as $vendedorInforme): ?>
                    <option value="<?= (int)$vendedorInforme['legajo'] ?>"><?= htmlspecialchars($vendedorInforme['vendedor'], ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="botones-form">
            <button class="btn-gold" onclick="verVentasVendedorInforme()" <?= empty($vendedoresInforme) ? 'disabled' : '' ?>>Ver ventas</button>
            <button class="btn-gold" onclick="cerrarSelectorVendedorInforme()">Cancelar</button>
        </div>
    </div>
</div>

<div id="modalMesInforme" class="modal">
    <div class="modal-contenido">
        <h3 id="tituloModalMesInforme" class="titulo-metal">Ventas por mes</h3>
        <div class="form-grupo">
            <label for="mesVentasInforme">Elegí el mes del informe</label>
            <select id="mesVentasInforme" aria-label="Elegir mes para el informe de ventas">
                <?php if (empty($mesesDisponibles)): ?>
                    <option value="">No hay meses con ventas</option>
                <?php else: ?>
                    <?php foreach ($mesesDisponibles as $mesDisponible): ?>
                        <?php $fechaMes = DateTime::createFromFormat('!Y-m', $mesDisponible); ?>
                        <option value="<?= htmlspecialchars($mesDisponible, ENT_QUOTES, 'UTF-8') ?>" <?= $mesDisponible === ($mesActualInforme ?? null) ? 'selected' : '' ?>>
                            <?= $fechaMes ? htmlspecialchars($fechaMes->format('m/Y'), ENT_QUOTES, 'UTF-8') : htmlspecialchars($mesDisponible, ENT_QUOTES, 'UTF-8') ?><?= $mesDisponible === ($mesActualInforme ?? null) ? ' (actual)' : '' ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
        <div class="botones-form">
            <button id="btnVerInformeMes" class="btn-gold" onclick="generarPDFMes()" <?= empty($mesesDisponibles) ? 'disabled' : '' ?>>Ver informe</button>
            <button class="btn-gold" onclick="cerrarSelectorMesInforme()">Cancelar</button>
        </div>
    </div>
</div>
