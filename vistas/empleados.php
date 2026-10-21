<h2 class="titulo-metal">Empleados</h2>

<div class="tabla-header">
    <button class="btn-gold" onclick="abrirModalEmpleado()">➕ Añadir</button>
</div>

<div class="card">

<div class="filtros-container">
<button class="btn-metal" onclick="cargarVista('empleadosInactivos')">
    Ver inactivos
</button>
</div>

<br><br>

<table id="miTabla" class="display" style="width:100%">
<thead>
    <tr>
        <th>Legajo</th>
        <th>Nombre</th>
        <th>Apellido</th>
        <th>DNI</th>
        <th>Rol</th>
        <th>Acciones</th>
    </tr>
</thead>

<tbody>

<?php foreach ($datos as $e): ?>
        <tr>
            <td><?= $e["legajo"]; ?></td>
            <td><?= htmlspecialchars($e["nombre"], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></td>
            <td><?= htmlspecialchars($e["apellido"], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></td>
            <td><?= htmlspecialchars($e["dni"], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></td>
            <td><?= htmlspecialchars($e["rol"], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></td>

            <td class="acciones">
                <button class="btn-metal" onclick='verMasPersona(<?= json_encode($e, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>)'>...</button>
                <button class="btn-metal" onclick='editarEmpleado(<?= json_encode($e, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>)'>✏️</button>
                <button class="btn-metal" onclick="borrarEmpleado(<?= $e['legajo'] ?>)">🗑</button>
            </td>
        </tr>
<?php endforeach; ?>

</tbody>
</table>

</div>
