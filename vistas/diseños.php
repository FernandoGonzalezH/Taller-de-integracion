<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
if (empty($_SESSION["autenticado"]) || (isset($_SESSION["ultima_actividad"]) && time() - (int)$_SESSION["ultima_actividad"] > 3600)) {
    $_SESSION = [];
    session_destroy();
    header("Location: ../index.php");
    exit;
}
$_SESSION["ultima_actividad"] = time();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="<?= htmlspecialchars($_SESSION['token_sesion'], ENT_QUOTES, 'UTF-8') ?>">
    <title>FG Motors</title>

    <link rel="icon" href="public/imagenes/logo.png">

    <link rel="stylesheet" href="public/css/estilo.css?v=20261008-9">

    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

    <!-- jQuery + DataTables -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <script src="public/js/app.js?v=20261021-1"></script>

    
</head>

<body>

<header class="navbar">
    <img src="public/imagenes/logo.png" class="logo"
         onclick="cargarVista('inicio')">

    <div class="menu">
        <button onclick="cargarVista('ventas')">Ventas</button>
        <button onclick="cargarVista('vehiculos')">Vehículos</button>
        <button onclick="cargarVista('clientes')">Clientes</button>
        <button onclick="cargarVista('empleados')">Empleados</button>
        <button onclick="cargarVista('informes')">Informes</button>
        <button class="btn-ayuda" onclick="cargarVista('ayuda')">Ayuda</button>
        <button class="btn-gold" onclick="abrirModalOpciones()">Opciones</button>
    </div>
</header>

<div id="contenido">
    <?php require_once $contenido; ?>
</div>

<!-- MODAL -->
<div id="modalInfo" class="modal">
    <div class="modal-contenido">
        <h3 class="titulo-metal">Detalles</h3>
        <div id="infoContenido"></div>
        <button class="btn-metal" onclick="cerrarModal()">Cerrar</button>
    </div>
</div>


<!-- MODAL EMPLEADO -->

<div id="modalEmpleado" class="modal">
    <div class="modal-contenido modal-grande">

        <h3 class="titulo-metal">Nuevo Empleado</h3>

        <p id="errorEmpleado" role="alert" style="display:none; color:#b00020;"></p>

        <!-- TIPO -->
        <div class="form-grupo">
            <label class="etiqueta-metal" for="tipoEmpleado"><input id="tipoEmpleado" type="checkbox" onchange="toggleVendedor()"> Vendedor</label>
        </div>

        <hr>

        <!-- PERSONA -->
        <div class="form-grid">
            <input type="text" id="nombre" placeholder="Nombre">
            <input type="text" id="apellido" placeholder="Apellido">
            <input type="text" id="dni" placeholder="DNI">
            <input type="text" id="nacimiento" placeholder="Nacimiento:AAAA-MM-DD">
            <input type="text" id="localidad" placeholder="Localidad">
            <input type="text" id="direccion" placeholder="Dirección">
            <input type="text" id="telefono" placeholder="Teléfono">
            <input type="email" id="email" placeholder="Email">

        </div>

        <hr>

        <!-- EMPLEADO -->
        <div class="form-grid">
            <input type="number" id="sueldo" placeholder="Sueldo (USD)" step="1">
            <select id="area" required aria-label="Área">
                <option value="">Área</option>
                <option value="Ventas">Ventas</option>
                <option value="Administrativa">Administrativa</option>
                <option value="Contable">Contable</option>
                <option value="Legal">Legal</option>
            </select>
            <input type="text" id="rol" placeholder="Rol" required>
            <input type="text" id="ingreso" placeholder="Ingreso:AAAA-MM-DD">
        </div>

        <!-- VENDEDOR -->
        <div id="datosVendedor" style="display:none;">
            <hr>
            <div class="form-grid">
                <input type="number" id="porcentaje" placeholder="Porcentaje">
            </div>
        </div>

        <br>

        <div class="botones-form">
            <button class="btn-gold" onclick="guardarEmpleado()">Guardar</button>
            <button class="btn-gold" onclick="cerrarModalEmpleado()">Cancelar</button>
        </div>

    </div>
</div>

<div id="modalVehiculo" class="modal">
    <div class="modal-contenido modal-grande">

        <h3 class="titulo-metal">Nuevo Vehículo</h3>

        <!-- TIPO -->
        <div class="form-grupo">
            <label>Tipo de vehículo</label>
            <select id="tipoVehiculo" onchange="toggleTipoVehiculo()">
                <option value="Auto">Auto</option>
                <option value="Moto">Moto</option>
            </select>
        </div>

        <hr>

        <!-- BASE -->
        <div class="form-grid">
            <input type="text" id="marca" list="catalogoMarcas" oninput="actualizarModelosPorMarca()" placeholder="Marca (escribí para ver sugerencias)">
            <datalist id="catalogoMarcas">
                <?php foreach (($catalogosMarcaModelo["marcas"] ?? []) as $marcaCatalogo): ?>
                    <option value="<?= htmlspecialchars($marcaCatalogo, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                <?php endforeach; ?>
            </datalist>
            <input type="text" id="modelo" list="catalogoModelos" placeholder="Modelo">
            <datalist id="catalogoModelos"></datalist>
            <datalist id="catalogoModelosFuente">
                <?php foreach (($catalogosMarcaModelo["modelos"] ?? []) as $modeloCatalogo): ?>
                    <option value="<?= htmlspecialchars($modeloCatalogo["nombre"], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" data-marca="<?= htmlspecialchars($modeloCatalogo["marca"], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                <?php endforeach; ?>
            </datalist>
            <input type="number" id="precio" placeholder="Precio">
            <input type="number" id="km" placeholder="KM">
            <input type="number" id="anio" placeholder="Año">
            <input type="text" id="motor" placeholder="Motor">
            <input type="text" id="transmision" placeholder="Transmisión">
            <input type="text" id="combustible" placeholder="Combustible">
            <input type="text" id="estado" placeholder="Estado (nuevo/usado)">
        </div>

        <hr>

        <!-- AUTO -->
        <div id="camposAuto">
            <div class="form-grid">
                <input type="number" id="puertas" placeholder="Puertas">
                <input type="text" id="traccion" placeholder="Tracción">
                <input type="number" id="pasajeros" placeholder="Pasajeros">
                <input type="text" id="carroceria" placeholder="Carrocería">
            </div>
        </div>

        <!-- MOTO -->
        <div id="camposMoto" style="display:none;">
            <div class="form-grid">
                <input type="number" id="cilindros" placeholder="Cilindros">
                <input type="text" id="tipomoto" placeholder="Tipo">
            </div>
        </div>

        <br>

        <div class="botones-form">
            <button class="btn-gold" onclick="guardarVehiculo()">Guardar</button>
            <button class="btn-gold" onclick="cerrarModalVehiculo()">Cancelar</button>
        </div>

    </div>
</div>

<div id="modalConfirm" class="modal">
    <div class="modal-contenido">

        <h3 class="titulo-metal">Confirmar acción</h3>

        <p id="textoConfirm"></p>

        <div style="margin-top:15px; display:flex; justify-content:center; gap:10px;">
            <button id="btnConfirmar" class="btn-metal">Sí</button>
            <button class="btn-metal" onclick="cerrarConfirmacion()">No</button>
        </div>

    </div>
</div>

<!-- MODAL CLIENTE -->
<div id="modalCliente" class="modal">
    <div class="modal-contenido modal-grande">

        <h3 class="titulo-metal">Nuevo Cliente</h3>

        <div class="form-grid">
            <input type="text" id="nombreC" placeholder="Nombre">
            <input type="text" id="apellidoC" placeholder="Apellido">
            <input type="text" id="dniC" placeholder="DNI">
            <input type="text" id="nacimientoC" placeholder="Nacimiento: AAAA-MM-DD">
            <input type="text" id="localidadC" placeholder="Localidad">
            <input type="text" id="direccionC" placeholder="Dirección">
            <input type="text" id="telefonoC" placeholder="Teléfono">
            <input type="email" id="emailC" placeholder="Email">
        </div>

        <br>

        <div class="botones-form">
            <button class="btn-gold" onclick="guardarCliente()">Guardar</button>
            <button class="btn-gold" onclick="cerrarModalCliente()">Cancelar</button>
        </div>

    </div>
</div>

<!-- MODAL CONFIRMAR CLIENTE -->
<div id="modalConfirmCliente" class="modal">
    <div class="modal-contenido">

        <h3 class="titulo-metal">Confirmar acción</h3>

        <p id="textoConfirmCliente"></p>

        <div style="margin-top:15px; display:flex; justify-content:center; gap:10px;">
            <button id="btnConfirmarCliente" class="btn-metal">Sí</button>
            <button class="btn-metal" onclick="cerrarConfirmCliente()">No</button>
        </div>

    </div>
</div>

<div id="modalVenta" class="modal">
    <div class="modal-contenido modal-grande">

        <h3 class="titulo-metal">Nueva Venta</h3>

        <p id="errorVenta" role="alert" style="display:none; color:#b00020;"></p>

        <div class="form-grid">

            <!-- CLIENTE -->
            <div class="form-grupo">
                <label>Cliente</label>
                <select id="clienteVenta">
                    <option value="">Seleccionar cliente</option>
                </select>
                <button type="button" class="btn-metal btn-nuevo-cliente" onclick="nuevoClienteDesdeVenta()">＋ Nuevo cliente</button>
            </div>

            <!-- VENDEDOR -->
            <div class="form-grupo">
                <label>Vendedor</label>
                <select id="vendedorVenta">
                    <option value="">Seleccionar vendedor</option>
                </select>
            </div>

            <!-- VEHICULO -->
            <div class="form-grupo">
                <label>Vehículo</label>
                <select id="vehiculoVenta" onchange="cargarPrecioVehiculo()">
                    <option value="">Seleccionar vehículo</option>
                </select>
            </div>

            <!-- PRECIO -->
            <div class="form-grupo">
                <label>Precio</label>
                <input type="number" id="precioVenta" placeholder="Precio">
            </div>

        </div>

        <br>

        <div class="botones-form">
            <button class="btn-gold" onclick="guardarVenta()">Guardar</button>
            <button class="btn-gold" onclick="cerrarModalVenta()">Cancelar</button>
        </div>

    </div>
</div>

<div id="modalInforme" class="modal">
    <div class="modal-contenido modal-grande">

        <h3 class="titulo-metal">Vista previa</h3>

        <div id="contenidoInforme" ></div>

        <br>
        <button class="btn-metal" onclick="cerrarModalInforme()">Cerrar</button>

    </div>
</div>



<div id="modalOpciones" class="modal">
    <div class="modal-contenido opciones-contenido">
        <h3 class="titulo-metal">Opciones</h3>
        <div id="accionesOpciones" class="botones-form">
            <button class="btn-gold" type="button" onclick="mostrarFormularioCambioClave()">Cambiar clave</button>
            <form method="post" action="index.php?accion=salir">
                <input type="hidden" name="token" value="<?= htmlspecialchars($_SESSION['token_sesion'], ENT_QUOTES, 'UTF-8') ?>">
                <button class="btn-gold" type="submit">Cerrar sesión</button>
            </form>
        </div>
        <form id="formCambiarClave" style="display:none" autocomplete="off" onsubmit="cambiarClave(event)">
            <div class="form-grupo">
                <label for="claveActual">Clave actual</label>
                <input id="claveActual" type="password" autocomplete="current-password" required>
            </div>
            <div class="form-grupo">
                <label for="claveNueva">Nueva clave (mínimo 8 caracteres)</label>
                <input id="claveNueva" type="password" autocomplete="new-password" minlength="8" required>
            </div>
            <div class="form-grupo">
                <label for="confirmarClaveNueva">Confirmar nueva clave</label>
                <input id="confirmarClaveNueva" type="password" autocomplete="new-password" minlength="8" required>
            </div>
            <p id="mensajeCambioClave" role="status" style="display:none"></p>
            <div class="botones-form">
                <button class="btn-gold" type="submit">Guardar clave</button>
                <button class="btn-gold" type="button" onclick="volverOpciones()">Volver</button>
            </div>
        </form>
        <div class="opciones-pie">
            <button class="btn-metal opciones-cerrar" type="button" onclick="cerrarModalOpciones()">Cerrar</button>
        </div>
    </div>
</div>

</body>
</html>
