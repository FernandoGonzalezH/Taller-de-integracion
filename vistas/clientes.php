<h2 class="titulo-metal">Clientes</h2>

<div class="tabla-header">
    <button class="btn-gold" onclick="nuevoCliente()">➕ Añadir</button>
</div>

<div class="card">
<br><br>

<table id="miTabla" class="display" style="width:100%">
<thead>
    <tr>
        <th>ID</th>
        <th>Nombre</th>
        <th>Apellido</th>
        <th>DNI</th>
        <th>Acciones</th>
    </tr>
</thead>

<tbody>

<?php foreach ($datos as $c): ?>
        <tr>
            <td><?= $c["id_cliente"]; ?></td>
            <td><?= htmlspecialchars($c["nombre"], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></td>
            <td><?= htmlspecialchars($c["apellido"], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></td>
            <td><?= htmlspecialchars($c["dni"], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></td>

            <td class="acciones">
                <button class="btn-metal" onclick='verMasPersona(<?= json_encode($c, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>)'>...</button>
                <button class="btn-metal" onclick='editarCliente(<?= json_encode($c, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>)'>✏️</button>
                <button class="btn-metal" onclick="borrarCliente(<?= $c['id_cliente'] ?>)">🗑</button>
            </td>
        </tr>
<?php endforeach; ?>

</tbody>
</table>

</div>
