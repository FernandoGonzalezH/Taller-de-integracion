<h2 class="titulo-metal">Ventas</h2>


<div class="tabla-header">
    <button class="btn-gold" onclick="abrirModalVenta()">➕ Añadir</button>
</div>

<div class="card">

<div class="filtros-container">
    <button class="btn-metal" type="button" onclick="filtrarVentas('todas')">Todas las ventas</button>
    <button class="btn-metal" type="button" onclick="filtrarVentas('mesActual')">Ventas del mes actual</button>
    <button class="btn-metal" type="button" onclick="filtrarVentas('anteriores')">Ventas anteriores</button>
</div>
<br><br>

<table id="miTabla" class="display tabla-ventas" style="width:100%">
    <thead>
        <tr>
            <th>ID</th>
            <th>Cliente</th>
            <th>Vehículo</th>
            <th>Vendedor</th>
            <th>Precio</th>
            <th>Fecha</th>
        </tr>
    </thead>
    <tbody>

    <?php foreach ($datos as $v): ?>
        <tr>
            <td><?= $v["id"]; ?></td>
            <td><?= htmlspecialchars($v["cliente"], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></td>
            <td><?= htmlspecialchars($v["vehiculo"], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></td>
            <td><?= htmlspecialchars($v["vendedor"], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></td>
            <td>u$s <?= number_format((float)$v["precio"], 2, ',', '.'); ?></td>
            <td><?= htmlspecialchars($v["fecha"], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></td>
        </tr>
    <?php endforeach; ?>

    </tbody>
</table>

</div>
