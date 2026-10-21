<h2 class="titulo-metal">Vehículos vendidos</h2>

<div class="tabla-header">
    <button class="btn-gold" onclick="abrirModalVehiculo()">➕ Añadir</button>
</div>

<div class="card">

<div class="filtros-container">

    <div class="filtro-item">
        <label>Tipo</label>
        <select id="filtroTipo">
            <option value="">Todos</option>
            <option value="Auto">Auto</option>
            <option value="Moto">Moto</option>
        </select>
    </div>

    <div class="filtro-item">
        <label>Estado</label>
        <select id="filtroEstado">
            <option value="">Todos</option>
            <option value="Nuevo">Nuevo</option>
            <option value="Usado">Usado</option>
        </select>
    </div>
</div>

<div class="filtro-item">
        <div>
            <button class="btn-metal" onclick="cargarVista('vehiculos')">Ver disponibles </button>
        </div>
    </div>

<table id="miTabla" class="display" style="width:100%">
    <thead>
    <tr>
        <th>ID</th>
        <th>Tipo</th>
        <th>Marca</th>
        <th>Modelo</th>
        <th>Precio</th>
        <th>Kilómetros</th>
        <th>Año</th>
        <th>Estado</th>
        <th>Acciones</th> <!-- 👈 NUEVA -->
    </tr>
</thead>
<tbody>

<?php foreach ($datos as $v): ?>
        <tr>
            <td><?= $v["id"]; ?></td>
            <td><?= htmlspecialchars($v["tipo"], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></td>
            <td><?= htmlspecialchars($v["marca"], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></td>
            <td><?= htmlspecialchars($v["modelo"], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></td>
            <td>u$s <?= number_format((float)$v["precio"], 2, ',', '.'); ?></td>
            <td><?= $v["km"]; ?> km</td>
            <td><?= $v["anio"]; ?></td>
            <td><?= htmlspecialchars(ucfirst($v["estado"]), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></td>

            <!-- 🔥 BOTONES -->
            <td class="acciones">
                <button class="btn-metal" onclick='verMas(<?= json_encode($v, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>)'>...</button>
                <button class="btn-metal" onclick='editarVehiculo(<?= json_encode($v, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>)'>✏️</button>
                <button class="btn-metal" onclick="borrarVehiculo(<?= $v['id'] ?>)">🗑</button>
            </td>

        </tr>
<?php endforeach; ?>

</tbody>

</table>

</div>
