let filtroVentasActual = "todas";

function normalizarCatalogo(valor) {
    return String(valor ?? "")
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "")
        .trim()
        .toLocaleLowerCase();
}

function actualizarModelosPorMarca() {
    const marcaInput = document.getElementById("marca");
    const datalist = document.getElementById("catalogoModelos");
    const fuente = document.getElementById("catalogoModelosFuente");
    const modeloInput = document.getElementById("modelo");
    if (!marcaInput || !datalist || !fuente || !modeloInput) return;

    const marca = normalizarCatalogo(marcaInput.value);
    const opciones = Array.from(fuente.options)
        .filter(opcion => normalizarCatalogo(opcion.dataset.marca) === marca);

    datalist.replaceChildren(...opciones.map(opcion => {
        const sugerencia = document.createElement("option");
        sugerencia.value = opcion.value;
        return sugerencia;
    }));

    if (modeloInput.value && !opciones.some(opcion => normalizarCatalogo(opcion.value) === normalizarCatalogo(modeloInput.value))) {
        modeloInput.value = "";
    }
}

function agregarMarcaModeloAlCatalogo(marca, modelo) {
    const listaMarcas = document.getElementById("catalogoMarcas");
    const fuenteModelos = document.getElementById("catalogoModelosFuente");
    if (!listaMarcas || !fuenteModelos) return;

    const marcaNormalizada = normalizarCatalogo(marca);
    const modeloNormalizado = normalizarCatalogo(modelo);
    const existeMarca = Array.from(listaMarcas.options)
        .some(opcion => normalizarCatalogo(opcion.value) === marcaNormalizada);
    if (!existeMarca) {
        const opcionMarca = document.createElement("option");
        opcionMarca.value = marca.trim();
        listaMarcas.appendChild(opcionMarca);
    }

    const existeModelo = Array.from(fuenteModelos.options).some(opcion =>
        normalizarCatalogo(opcion.value) === modeloNormalizado &&
        normalizarCatalogo(opcion.dataset.marca) === marcaNormalizada
    );
    if (!existeModelo) {
        const opcionModelo = document.createElement("option");
        opcionModelo.value = modelo.trim();
        opcionModelo.dataset.marca = marca.trim();
        fuenteModelos.appendChild(opcionModelo);
    }
    actualizarModelosPorMarca();
}

function escaparHTML(valor) {
    const elemento = document.createElement("span");
    elemento.textContent = String(valor ?? "—");
    return elemento.innerHTML;
}

const fetchOriginalConCsrf = window.fetch.bind(window);
window.fetch = (recurso, opciones = {}) => {
    const metodo = (opciones.method || (recurso instanceof Request ? recurso.method : "GET")).toUpperCase();
    if (metodo !== "GET" && metodo !== "HEAD") {
        const encabezados = new Headers(opciones.headers || (recurso instanceof Request ? recurso.headers : undefined));
        const token = document.querySelector('meta[name="csrf-token"]')?.content;
        if (token) encabezados.set("X-CSRF-Token", token);
        encabezados.set("X-Requested-With", "XMLHttpRequest");
        opciones = { ...opciones, headers: encabezados };
    }
    return fetchOriginalConCsrf(recurso, opciones).then(async respuesta => {
        if (respuesta.status === 401) {
            const mensaje = (await respuesta.clone().text()).trim();
            if (["SESSION_EXPIRED", "SESSION_REQUIRED"].includes(mensaje)) {
                window.location.href = "index.php";
            }
        }
        return respuesta;
    });
};

if ($.fn && $.fn.dataTable) {
    $.fn.dataTable.ext.search.push(function (settings, data) {
        if (!settings.nTable.classList.contains("tabla-ventas") || filtroVentasActual === "todas") return true;

        const partes = (data[5] || "").split("/");
        if (partes.length !== 3) return false;
        const fechaVenta = new Date(Number(partes[2]), Number(partes[1]) - 1, Number(partes[0]));
        if (Number.isNaN(fechaVenta.getTime())) return false;
        const ahora = new Date();
        const mesActual = new Date(ahora.getFullYear(), ahora.getMonth(), 1);
        const inicioMesVenta = new Date(fechaVenta.getFullYear(), fechaVenta.getMonth(), 1);

        return filtroVentasActual === "mesActual"
            ? inicioMesVenta.getTime() === mesActual.getTime()
            : inicioMesVenta < mesActual;
    });
}

function filtrarVentas(filtro) {
    filtroVentasActual = filtro;
    if ($.fn.DataTable.isDataTable('#miTabla')) {
        $('#miTabla').DataTable().draw();
    }
}

function inicializarTablas() {
    if ($('#miTabla').length) {

        if ($.fn.DataTable.isDataTable('#miTabla')) {
            $('#miTabla').DataTable().destroy();
        }

        let tabla = $('#miTabla').DataTable({
            language: {
                url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
            }
        });

        // 🔥 FILTRO POR ESTADO
        $('#filtroEstado').off().on('change', function () {

    let valor = this.value;

    if (valor === "") {
        tabla.column(7).search('').draw();
    } else {
        tabla.column(7).search('^' + valor + '$', true, false).draw();
    }

});

$('#filtroTipo').off().on('change', function () {

    let valor = this.value.toLowerCase();

    if (valor === "") {
        tabla.column(1).search('').draw();
    } else {
        tabla.column(1).search(valor, false, true).draw();
    }

});


    }
}


function cargarVista(vista) {
    fetch("index.php?accion=" + vista, {
        headers: { "X-Requested-With": "XMLHttpRequest" }
    })
    .then(res => res.text())
    .then(data => {
        document.getElementById("contenido").innerHTML = data;

        setTimeout(() => {
            inicializarTablas();
        }, 50);
    });
}

window.onload = function () {
    inicializarTablas();
};

function verMas(vehiculo) {

    let html = `
        <p><b>Marca:</b> ${escaparHTML(vehiculo.marca)}</p>
        <p><b>Modelo:</b> ${escaparHTML(vehiculo.modelo)}</p>
        <p><b>Precio:</b> $${escaparHTML(vehiculo.precio)}</p>
        <p><b>Estado:</b> ${escaparHTML(vehiculo.estado)}</p>
        <p><b>Kilometros:</b> ${escaparHTML(vehiculo.km)}</p>
        <p><b>Año:</b> ${escaparHTML(vehiculo.anio)}</p>
        <p><b>Motor:</b> ${escaparHTML(vehiculo.motor)}</p>
        <p><b>Transmision:</b> ${escaparHTML(vehiculo.transmision)}</p>
        <p><b>Combustible:</b> ${escaparHTML(vehiculo.combustible)}</p>
    `;

    // 🔥 SI ES AUTO
    if (vehiculo.tipo === "Auto") {
        html += `
            <p><b>Puertas:</b> ${escaparHTML(vehiculo.puertas)}</p>
            <p><b>Traccion:</b> ${escaparHTML(vehiculo.traccion)}</p>
            <p><b>Pasajeros:</b> ${escaparHTML(vehiculo.pasajeros)}</p>
            <p><b>Carroceria:</b> ${escaparHTML(vehiculo.carroceria)}</p>
        `;
    }

    // 🔥 SI ES MOTO
    if (vehiculo.tipo === "Moto") {
        html += `
            <p><b>Cilindros:</b> ${escaparHTML(vehiculo.cilindros)}</p>
            <p><b>Tipo:</b> ${escaparHTML(vehiculo.tipomoto)}</p>
        `;
    }

    document.getElementById("infoContenido").innerHTML = html;
    document.getElementById("modalInfo").style.display = "block";

    document.body.classList.add("modal-abierto");
}

function verMasPersona(persona) {

    let html = `
        <p><b>Nombre:</b> ${escaparHTML(persona.nombre)}</p>
        <p><b>Apellido:</b> ${escaparHTML(persona.apellido)}</p>
        <p><b>DNI:</b> ${escaparHTML(persona.dni)}</p>
        <p><b>Fecha de nacimiento:</b> ${escaparHTML(persona.nacimiento)}</p>
        <p><b>Localidad:</b> ${escaparHTML(persona.localidad)}</p>
        <p><b>Dirección:</b> ${escaparHTML(persona.direccion)}</p>
        <p><b>Teléfono:</b> ${escaparHTML(persona.telefono)}</p>
        <p><b>Email:</b> ${escaparHTML(persona.email)}</p>
    `;

    // 🔥 SI ES EMPLEADO
    if (persona.legajo !== undefined) {

        html += `
            <hr>
            <p><b>Sueldo (USD):</b> u$s ${escaparHTML(persona.sueldo)}</p>
            <p><b>Area:</b> ${escaparHTML(persona.area)}</p>
            <p><b>Rol:</b> ${escaparHTML(persona.rol)}</p>
            <p><b>Fecha ingreso:</b> ${escaparHTML(persona.ingreso)}</p>
        `;

        // 🔥 SI ES VENDEDOR
        if (Number(persona.es_vendedor) === 1) {

            html += `
                <hr>
                
                <p><b>Comisión acumulada (USD):</b> u$s ${escaparHTML(persona.comision ?? 0)}</p>
                <p><b>Porcentaje de comisión:</b> ${escaparHTML(persona.porcentaje ?? 0)}%</p>
                <p><b>Sueldo total (USD):</b> u$s ${escaparHTML(persona.total ?? persona.sueldo ?? 0)}</p>
            `;
        }
    }

    document.getElementById("infoContenido").innerHTML = html;
    document.getElementById("modalInfo").style.display = "block";
    document.body.classList.add("modal-abierto");
}




function cerrarModal() {
    document.getElementById("modalInfo").style.display = "none";
    document.body.classList.add("modal-cerrado");
}

window.onclick = function(e) {
    let modal = document.getElementById("modalInfo");
    if (e.target === modal) {
        modal.style.display = "none";
    }
};

let legajoAEliminar = null;

// ABRIR MODAL
function borrarEmpleado(legajo) {

    legajoAEliminar = legajo;

    document.getElementById("textoConfirm").innerText =
        "¿Seguro que querés desactivar el empleado " + legajo + "?";

    document.getElementById("btnConfirmar").onclick = confirmarEliminar;
    document.getElementById("modalConfirm").style.display = "block";
    document.body.classList.add("modal-abierto");
}

// CONFIRMAR
function confirmarEliminar() {

    fetch("index.php?accion=eliminarEmpleado", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: "legajo=" + legajoAEliminar
    })
    .then(async res => {
        const mensaje = (await res.text()).trim();
        if (!res.ok || mensaje !== "ok") throw new Error(mensaje || "No se pudo desactivar el empleado.");
        cerrarConfirmEmpleado();
        cargarVista('empleados');
    })
    .catch(error => alert(error.message || "No se pudo desactivar el empleado."));
}

// CERRAR
function cerrarConfirmEmpleado() {
    cerrarConfirmacion();
    legajoAEliminar = null;
}

function reactivarEmpleado(legajo) {
    fetch("index.php?accion=reactivarEmpleado", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "legajo=" + encodeURIComponent(legajo)
    })
    .then(async res => {
        const mensaje = (await res.text()).trim();
        if (!res.ok || mensaje !== "ok") throw new Error(mensaje || "No se pudo reactivar el empleado.");
        cargarVista("empleadosInactivos");
    })
    .catch(error => alert(error.message || "No se pudo reactivar el empleado."));
}

document.addEventListener("input", function (evento) {
    const campo = evento.target;
    if (!(campo instanceof HTMLInputElement) || campo.type !== "text") return;
    if (!campo.closest("#modalEmpleado, #modalVehiculo, #modalCliente")) return;

    const caracteres = Array.from(campo.value);
    if (!caracteres.length) return;
    const primeraMayuscula = caracteres[0].toLocaleUpperCase("es-AR");
    if (primeraMayuscula !== caracteres[0]) {
        const posicion = campo.selectionStart;
        caracteres[0] = primeraMayuscula;
        campo.value = caracteres.join("");
        if (posicion !== null) campo.setSelectionRange(posicion, posicion);
    }
});

function alternarPasosAyuda(id, boton) {
    const pasos = document.getElementById(id);
    if (!pasos) return;
    const mostrar = pasos.hidden;
    pasos.hidden = !mostrar;
    boton.setAttribute("aria-expanded", mostrar ? "true" : "false");
    boton.textContent = mostrar ? "Ocultar pasos" : "Ver pasos";
}

function abrirModalOpciones() {
    document.getElementById("accionesOpciones").style.display = "flex";
    document.getElementById("formCambiarClave").style.display = "none";
    document.getElementById("modalOpciones").style.display = "block";
    document.body.classList.add("modal-abierto");
}

function cerrarModalOpciones() {
    document.getElementById("modalOpciones").style.display = "none";
    document.body.classList.remove("modal-abierto");
}

function mostrarFormularioCambioClave() {
    document.getElementById("accionesOpciones").style.display = "none";
    document.getElementById("formCambiarClave").style.display = "block";
    const mensaje = document.getElementById("mensajeCambioClave");
    mensaje.textContent = "";
    mensaje.style.display = "none";
}

function volverOpciones() {
    document.getElementById("formCambiarClave").reset();
    document.getElementById("formCambiarClave").style.display = "none";
    document.getElementById("accionesOpciones").style.display = "flex";
}

async function cambiarClave(evento) {
    evento.preventDefault();
    const actual = document.getElementById("claveActual").value;
    const nueva = document.getElementById("claveNueva").value;
    const confirmacion = document.getElementById("confirmarClaveNueva").value;
    const mensaje = document.getElementById("mensajeCambioClave");
    const token = document.querySelector("#modalOpciones input[name='token']")?.value || "";

    if (nueva.length < 8 || nueva !== confirmacion) {
        mensaje.textContent = nueva.length < 8 ? "La nueva clave debe tener al menos 8 caracteres." : "La confirmación no coincide con la nueva clave.";
        mensaje.style.color = "#ffb7b7";
        mensaje.style.display = "block";
        return;
    }

    try {
        const respuesta = await fetch("index.php?accion=cambiarClave", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ token, claveActual: actual, claveNueva: nueva, confirmacion })
        });
        const resultado = await respuesta.json();
        if (!respuesta.ok || !resultado.ok) throw new Error(resultado.mensaje || "No se pudo cambiar la clave.");
        mensaje.textContent = resultado.mensaje;
        mensaje.style.color = "#b9f6ca";
        mensaje.style.display = "block";
        document.getElementById("formCambiarClave").reset();
    } catch (error) {
        mensaje.textContent = error.message || "No se pudo cambiar la clave.";
        mensaje.style.color = "#ffb7b7";
        mensaje.style.display = "block";
    }
}

function cerrarConfirmacion() {
    document.getElementById("modalConfirm").style.display = "none";
    document.body.classList.remove("modal-abierto");
    legajoAEliminar = null;
    idVehiculoAEliminar = null;
}

function abrirModalEmpleado() {

    const error = document.getElementById("errorEmpleado");
    if (error) {
        error.textContent = "";
        error.style.display = "none";
    }

    if (!modoEditar) {
        limpiarFormulario();
        document.querySelector("#modalEmpleado h3").innerText = "Nuevo Empleado";
    } else {
        document.querySelector("#modalEmpleado h3").innerText = "Editar Empleado";
    }

    document.getElementById("modalEmpleado").style.display = "block";
    document.body.classList.add("modal-abierto");
}

function limpiarFormulario() {

    let inputs = document.querySelectorAll("#modalEmpleado input");

    inputs.forEach(i => i.value = "");

    establecerVendedor(false);
}



function cerrarModalEmpleado() {
    document.getElementById("modalEmpleado").style.display = "none";
    document.body.classList.remove("modal-abierto");
}

// mostrar campos vendedor
function toggleVendedor() {
    establecerVendedor(document.getElementById("tipoEmpleado").checked);
}

function establecerVendedor(esVendedor) {
    const tipo = document.getElementById("tipoEmpleado");
    if (!tipo) return;
    tipo.checked = esVendedor;
    document.getElementById("datosVendedor").style.display = esVendedor ? "block" : "none";
}

function guardarEmpleado() {

    let datos = {
        nombre: document.getElementById("nombre").value,
        apellido: document.getElementById("apellido").value,
        dni: document.getElementById("dni").value,
        nacimiento: document.getElementById("nacimiento").value,
        localidad: document.getElementById("localidad").value,
        direccion: document.getElementById("direccion").value,
        telefono: document.getElementById("telefono").value,
        email: document.getElementById("email").value,

        sueldo: document.getElementById("sueldo").value,
        area: document.getElementById("area").value,
        rol: document.getElementById("rol").value,
        ingreso: document.getElementById("ingreso").value,

        tipo: document.getElementById("tipoEmpleado").checked ? "vendedor" : "otro",
        porcentaje: document.getElementById("porcentaje").value,

        editar: modoEditar,
        legajo: legajoEditando
    };

    const error = document.getElementById("errorEmpleado");
    fetch("index.php?accion=guardarEmpleado", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify(datos)
    })
    .then(async res => {
        const resultado = await res.json();
        if (!res.ok || !resultado.ok) {
            throw new Error(resultado.mensaje || "No se pudo guardar el empleado.");
        }
        return resultado;
    })
    .then(() => {
        cerrarModalEmpleado();
        modoEditar = false;
        legajoEditando = null;
        cargarVista('empleados');
    })
    .catch(err => {
        if (error) {
            error.textContent = err.message || "No se pudo guardar el empleado.";
            error.style.display = "block";
        }
    });
}


document.addEventListener("click", function(e) {
    if (e.target.classList.contains("modal")) {
        if (e.target.id === "modalCliente") {
            cerrarModalCliente();
            return;
        }
        e.target.style.display = "none";
        if (e.target.id !== "modalVenta" || document.getElementById("modalCliente")?.style.display !== "block") {
            document.body.classList.remove("modal-abierto");
        }
    }
});

let modoEditar = false;
let legajoEditando = null;

function editarEmpleado(emp) {

    modoEditar = true;
    legajoEditando = emp.legajo;

    abrirModalEmpleado();

    // PERSONA
    document.getElementById("nombre").value = emp.nombre || "";
    document.getElementById("apellido").value = emp.apellido || "";
    document.getElementById("dni").value = emp.dni || "";
    document.getElementById("nacimiento").value = emp.nacimiento || "";
    document.getElementById("localidad").value = emp.localidad || "";
    document.getElementById("direccion").value = emp.direccion || "";
    document.getElementById("telefono").value = emp.telefono || "";
    document.getElementById("email").value = emp.email || "";

    // EMPLEADO
    document.getElementById("sueldo").value = emp.sueldo || "";
    document.getElementById("area").value = emp.area || "";
    document.getElementById("rol").value = emp.rol || "";
    document.getElementById("ingreso").value = emp.ingreso || "";

    // VENDEDOR
    if (emp.porcentaje !== null && emp.porcentaje !== undefined) {
        document.getElementById("porcentaje").value = emp.porcentaje;
        establecerVendedor(true);
    } else {
        establecerVendedor(false);
    }

    // TITULO
    document.querySelector("#modalEmpleado h3").innerText = "Editar Empleado";
}



function limpiarFormulario() {

    document.querySelectorAll("#modalEmpleado input").forEach(i => i.value = "");

    establecerVendedor(false);
}

let modoEditarVehiculo = false;
let idVehiculo = null;

function editarVehiculo(v) {

    modoEditarVehiculo = true;
    idVehiculo = v.id;

    limpiarFormularioVehiculo(); // 🔥 LIMPIAR PRIMERO

    // 🔥 BASE
    document.getElementById("marca").value = v.marca || "";
    document.getElementById("modelo").value = v.modelo || "";
    actualizarModelosPorMarca();
    document.getElementById("precio").value = v.precio || "";
    document.getElementById("km").value = v.km || "";
    document.getElementById("anio").value = v.anio || "";
    document.getElementById("motor").value = v.motor || "";
    document.getElementById("transmision").value = v.transmision || "";
    document.getElementById("combustible").value = v.combustible || "";
    document.getElementById("estado").value = v.estado || "";

    let tipo = v.tipo || "";

    // 🔥 SETEAR TIPO
    document.getElementById("tipoVehiculo").value = tipo;

    // 🔥 MOSTRAR CAMPOS CORRECTOS
    toggleTipoVehiculo();

    // 🔥 AUTO
    if (tipo === "Auto") {
        document.getElementById("puertas").value = v.puertas || "";
        document.getElementById("traccion").value = v.traccion || "";
        document.getElementById("pasajeros").value = v.pasajeros || "";
        document.getElementById("carroceria").value = v.carroceria || "";
    }

    // 🔥 MOTO
    if (tipo === "Moto") {
        document.getElementById("cilindros").value = v.cilindros || "";
        document.getElementById("tipomoto").value = v.tipomoto || "";
    }

    // 🔥 TITULO
    document.querySelector("#modalVehiculo h3").innerText = "Editar Vehículo";

    // 🔥 ABRIR MODAL (SIN RESETEAR)
    document.getElementById("modalVehiculo").style.display = "block";
    document.body.classList.add("modal-abierto");
}

function abrirModalVehiculo() {

    modoEditarVehiculo = false;
    idVehiculo = null;
    limpiarFormularioVehiculo();

    // 🔥 FORZAR ACTUALIZACIÓN VISUAL
    toggleTipoVehiculo();

    document.querySelector("#modalVehiculo h3").innerText = "Nuevo Vehículo";

    document.getElementById("modalVehiculo").style.display = "block";
    document.body.classList.add("modal-abierto");
}


function cerrarModalVehiculo() {
    document.getElementById("modalVehiculo").style.display = "none";
    document.body.classList.remove("modal-abierto");
    modoEditarVehiculo = false;
    idVehiculo = null;
}

function limpiarFormularioVehiculo() {

    document.querySelectorAll("#modalVehiculo input").forEach(i => i.value = "");
    actualizarModelosPorMarca();

    document.getElementById("tipoVehiculo").value = "Auto";

    // ❌ BORRÁ ESTO SI LO TENÉS:
    // display = block/none manual

    // ✅ USÁ SOLO:
    toggleTipoVehiculo();
}


function guardarVehiculo() {
    const editando = modoEditarVehiculo;
    const datos = {
        id: editando ? idVehiculo : null,
        tipo: document.getElementById("tipoVehiculo").value
    };

    document.querySelectorAll("#modalVehiculo input").forEach(i => {
        datos[i.id] = i.value;
    });

    const accion = editando ? "actualizarVehiculo" : "guardarVehiculo";

    fetch("index.php?accion=" + accion, {
        method: "POST",
        headers: {"Content-Type": "application/json"},
        body: JSON.stringify(datos)
    })
    .then(async res => {
        const texto = (await res.text()).trim();
        if (!res.ok || texto !== "ok") {
            throw new Error(texto || "No se pudo guardar el vehículo.");
        }
        agregarMarcaModeloAlCatalogo(datos.marca, datos.modelo);
        cerrarModalVehiculo();
        cargarVista('vehiculos');
    })
    .catch(error => alert("Error al guardar: " + error.message));
}

function toggleTipoVehiculo() {

    let tipo = document.getElementById("tipoVehiculo").value;

    if (tipo === "Auto") {
        document.getElementById("camposAuto").style.display = "block";
        document.getElementById("camposMoto").style.display = "none";
    } 
    else if (tipo === "Moto") {
        document.getElementById("camposAuto").style.display = "none";
        document.getElementById("camposMoto").style.display = "block";
    }
}



let idVehiculoAEliminar = null;

function borrarVehiculo(id) {

    idVehiculoAEliminar = id;

    document.getElementById("textoConfirm").innerText =
        "¿Seguro que querés borrar el vehículo ID " + id + "? Esta acción no se puede deshacer.";

    // 🔥 asignar acción al botón SI
    document.getElementById("btnConfirmar").onclick = confirmarEliminarVehiculo;

    document.getElementById("modalConfirm").style.display = "block";
    document.body.classList.add("modal-abierto");
}





function confirmarEliminarVehiculo() {

    fetch("index.php?accion=eliminarVehiculo", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: "id=" + idVehiculoAEliminar
    })
    .then(async res => {
        const texto = (await res.text()).trim();
        if (!res.ok || texto !== "ok") {
            throw new Error(texto || "No se pudo borrar el vehículo.");
        }
        cerrarConfirmVehiculo();
        cargarVista('vehiculos');
    })
    .catch(error => alert("Error: " + error.message));
}

function cerrarConfirmVehiculo() {
    cerrarConfirmacion();
}

// =========================
// CLIENTES
// =========================
let idClienteAEliminar = null;
let modoEditarCliente = false;
let idClienteEditando = null;
let clienteDesdeVenta = false;

function editarCliente(c) {


    modoEditarCliente = true;
    idClienteEditando = c.id_cliente;

    abrirModalCliente();

    // 🔥 CARGAR DATOS
    document.getElementById("nombreC").value = c.nombre || "";
    document.getElementById("apellidoC").value = c.apellido || "";
    document.getElementById("dniC").value = c.dni || "";
    document.getElementById("nacimientoC").value = c.nacimiento || "";
    document.getElementById("localidadC").value = c.localidad || "";
    document.getElementById("direccionC").value = c.direccion || "";
    document.getElementById("telefonoC").value = c.telefono || "";
    document.getElementById("emailC").value = c.email || "";

    // 🔥 TITULO
    document.querySelector("#modalCliente h3").innerText = "Editar Cliente";
}



// 🔹 ABRIR MODAL
function abrirModalCliente() {

    if (!modoEditarCliente) {
        limpiarFormularioCliente();
        document.querySelector("#modalCliente h3").innerText = "Nuevo Cliente";
    } else {
        document.querySelector("#modalCliente h3").innerText = "Editar Cliente";
    }

    document.getElementById("modalCliente").style.display = "block";
    document.getElementById("modalCliente").style.zIndex = clienteDesdeVenta ? "1001" : "999";
    document.body.classList.add("modal-abierto");
}

function nuevoClienteDesdeVenta() {
    clienteDesdeVenta = true;
    nuevoCliente();
}



// 🔹 CERRAR MODAL
function cerrarModalCliente() {
    document.getElementById("modalCliente").style.display = "none";
    document.getElementById("modalCliente").style.zIndex = "";
    clienteDesdeVenta = false;
    if (document.getElementById("modalVenta")?.style.display !== "block") {
        document.body.classList.remove("modal-abierto");
    }
    modoEditarCliente = false;
}


// 🔹 LIMPIAR FORM
function limpiarFormularioCliente() {
    document.querySelectorAll("#modalCliente input").forEach(i => i.value = "");
}



// 🔹 GUARDAR
function guardarCliente() {

    let datos = {
        id: idClienteEditando,
        nombre: document.getElementById("nombreC").value,
        apellido: document.getElementById("apellidoC").value,
        dni: document.getElementById("dniC").value,
        nacimiento: document.getElementById("nacimientoC").value,
        localidad: document.getElementById("localidadC").value,
        direccion: document.getElementById("direccionC").value,
        telefono: document.getElementById("telefonoC").value,
        email: document.getElementById("emailC").value,
        editar: modoEditarCliente
    };



    const nombreCliente = `${datos.nombre} ${datos.apellido}`.trim();
    fetch("index.php?accion=guardarCliente", {
        method: "POST",
        headers: {"Content-Type": "application/json"},
        body: JSON.stringify(datos)
    })
    .then(async respuesta => {
        const resultado = await respuesta.json();
        if (!respuesta.ok || !resultado.ok) throw new Error(resultado.mensaje || "No se pudo guardar el cliente.");
        return resultado;
    })
    .then(resultado => {
        if (clienteDesdeVenta && !datos.editar) {
            const selector = document.getElementById("clienteVenta");
            const opcion = new Option(nombreCliente, resultado.id, true, true);
            selector.add(opcion);
            selector.value = String(resultado.id);
            cerrarModalCliente();
        } else {
            cerrarModalCliente();
            cargarVista('clientes');
        }
    })
    .catch(error => {
        alert(error.message || "No se pudo guardar el cliente.");
    });
}


// 🔹 BORRAR (ABRE MODAL)
function borrarCliente(id) {

    idClienteAEliminar = id;

    document.getElementById("textoConfirmCliente").innerText =
        "¿Seguro que querés eliminar el cliente ID " + id + "?";

    document.getElementById("btnConfirmarCliente").onclick = confirmarEliminarCliente;

    document.getElementById("modalConfirmCliente").style.display = "block";
}


// 🔹 CONFIRMAR BORRADO
function confirmarEliminarCliente() {

    fetch("index.php?accion=eliminarCliente", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: "id=" + idClienteAEliminar
    })
    .then(async res => {
        const mensaje = (await res.text()).trim();
        if (!res.ok || mensaje !== "ok") throw new Error(mensaje || "No se pudo eliminar el cliente.");
        cerrarConfirmCliente();
        cargarVista('clientes');
    })
    .catch(error => alert(error.message || "No se pudo eliminar el cliente."));
}


// 🔹 CERRAR CONFIRM
function cerrarConfirmCliente() {
    document.getElementById("modalConfirmCliente").style.display = "none";
    idClienteAEliminar = null;
}

function nuevoCliente() {

    modoEditarCliente = false;
    idClienteEditando = null;

    abrirModalCliente();
}

//----------------ventas-------------------------
async function abrirModalVenta() {
    const error = document.getElementById("errorVenta");
    if (error) {
        error.textContent = "";
        error.style.display = "none";
    }

    const modal = document.getElementById("modalVenta");
    const [clientes, vendedores, vehiculos] = [
        document.getElementById("clienteVenta"),
        document.getElementById("vendedorVenta"),
        document.getElementById("vehiculoVenta")
    ];
    [clientes, vendedores, vehiculos].forEach(selector => { selector.disabled = true; });
    modal.style.display = "block";
    document.body.classList.add("modal-abierto");

    try {
        const respuesta = await fetch("index.php?accion=opcionesVenta", {
            cache: "no-store",
            headers: { "X-Requested-With": "XMLHttpRequest" }
        });
        const opciones = await respuesta.json();
        if (!respuesta.ok || opciones.ok === false) {
            throw new Error(opciones.mensaje || "No se pudieron actualizar las opciones de venta.");
        }

        actualizarOpcionesVenta(clientes, opciones.clientes, "Seleccionar cliente", cliente => ({
            valor: cliente.id_cliente,
            texto: `${cliente.nombre} ${cliente.apellido}`
        }));
        actualizarOpcionesVenta(vendedores, opciones.vendedores, "Seleccionar vendedor", vendedor => ({
            valor: vendedor.legajo,
            texto: `${vendedor.nombre} ${vendedor.apellido}`
        }));
        actualizarOpcionesVenta(vehiculos, opciones.vehiculos, "Seleccionar vehículo", vehiculo => ({
            valor: vehiculo.id,
            texto: `${vehiculo.marca} ${vehiculo.modelo} ${vehiculo.anio}`,
            precio: vehiculo.precio
        }));
        cargarPrecioVehiculo();
        [clientes, vendedores, vehiculos].forEach(selector => { selector.disabled = false; });
    } catch (err) {
        if (error) {
            error.textContent = err.message || "No se pudieron actualizar las opciones de venta.";
            error.style.display = "block";
        }
    }
}

function actualizarOpcionesVenta(selector, registros, textoInicial, mapear) {
    const valorAnterior = selector.value;
    selector.replaceChildren(new Option(textoInicial, ""));
    (Array.isArray(registros) ? registros : []).forEach(registro => {
        const datos = mapear(registro);
        const opcion = new Option(datos.texto, datos.valor);
        if (datos.precio !== undefined) opcion.dataset.precio = datos.precio;
        selector.add(opcion);
    });
    selector.value = [...selector.options].some(opcion => opcion.value === valorAnterior)
        ? valorAnterior
        : "";
}

function cerrarModalVenta() {
    document.getElementById("modalVenta").style.display = "none";
    document.body.classList.remove("modal-abierto");
}

function cargarPrecioVehiculo() {
    let select = document.getElementById("vehiculoVenta");
    let precio = select.options[select.selectedIndex].getAttribute("data-precio");

    document.getElementById("precioVenta").value = precio || "";
}

function guardarVenta() {

    let datos = {
        cliente: document.getElementById("clienteVenta").value,
        vehiculo: document.getElementById("vehiculoVenta").value,
        vendedor: document.getElementById("vendedorVenta").value,
        precio: document.getElementById("precioVenta").value,
    };

    const error = document.getElementById("errorVenta");
    if (!datos.cliente || !datos.vehiculo || !datos.vendedor || !datos.precio || Number(datos.precio) <= 0) {
        error.textContent = "Completá cliente, vehículo, vendedor y un precio válido.";
        error.style.display = "block";
        return;
    }

    fetch("index.php?accion=guardarVenta", {
        method: "POST",
        headers: {"Content-Type": "application/json"},
        body: JSON.stringify(datos)
    })
    .then(async res => {
        const resultado = await res.json();
        if (!res.ok || !resultado.ok) {
            throw new Error(resultado.mensaje || "No se pudo guardar la venta.");
        }
        return resultado;
    })
    .then(() => {
        cerrarModalVenta();
        cargarVista('ventas');
    })
    .catch(err => {
        error.textContent = err.message || "No se pudo guardar la venta.";
        error.style.display = "block";
    });
}

function verInforme(tipo, legajo = null) {
    const parametros = tipo === "ventasVendedorDetalle" && legajo ? "&legajo=" + encodeURIComponent(legajo) : "";
    fetch("index.php?accion=obtenerInforme&tipo=" + encodeURIComponent(tipo) + parametros, {
        headers: { "X-Requested-With": "XMLHttpRequest" }
    })
    .then(res => {
        if (!res.ok) throw new Error("No se pudo cargar el informe.");
        return res.text();
    })
    .then(html => {
        document.getElementById("contenidoInforme").innerHTML = html;
        document.getElementById("modalInforme").style.display = "block";
        document.body.classList.add("modal-abierto");
    })
    .catch(error => alert(error.message));
}

function abrirSelectorVendedorInforme() {
    const modal = document.getElementById("modalVendedorInforme");
    if (!modal) return;
    modal.style.display = "block";
    document.body.classList.add("modal-abierto");
}

function cerrarSelectorVendedorInforme() {
    const modal = document.getElementById("modalVendedorInforme");
    if (modal) modal.style.display = "none";
    document.body.classList.remove("modal-abierto");
}

function verVentasVendedorInforme() {
    const legajo = document.getElementById("vendedorInforme")?.value;
    if (!legajo) {
        alert("Elegí un vendedor para ver sus ventas.");
        return;
    }
    cerrarSelectorVendedorInforme();
    generarPDF("ventasVendedorDetalle", legajo);
}



function cerrarModalInforme() {
    document.getElementById("modalInforme").style.display = "none";
    document.body.classList.remove("modal-abierto");
}


let tipoInformeMesActual = "ventasMes";

function abrirSelectorMesInforme(tipo = "ventasMes") {
    const modal = document.getElementById("modalMesInforme");
    if (!modal) return;
    tipoInformeMesActual = tipo;
    const esDetalle = tipo === "ventasMesDetalle";
    document.getElementById("tituloModalMesInforme").textContent = esDetalle ? "Ventas del mes" : "Ventas por mes";
    document.getElementById("btnVerInformeMes").textContent = esDetalle ? "Ver ventas" : "Ver resumen";
    modal.style.display = "block";
    document.body.classList.add("modal-abierto");
}

function cerrarSelectorMesInforme() {
    const modal = document.getElementById("modalMesInforme");
    if (modal) modal.style.display = "none";
    document.body.classList.remove("modal-abierto");
}

function generarPDFMes() {
    const selector = document.getElementById("mesVentasInforme");
    const mes = selector ? selector.value : "";
    if (!mes) {
        alert("Elegí un mes para generar el informe.");
        return;
    }
    cerrarSelectorMesInforme();
    generarPDF(tipoInformeMesActual, mes);
}

function abrirSelectorStockInforme() {
    const modal = document.getElementById("modalStockInforme");
    if (!modal) return;
    modal.style.display = "block";
    document.body.classList.add("modal-abierto");
}

function cerrarSelectorStockInforme() {
    const modal = document.getElementById("modalStockInforme");
    if (modal) modal.style.display = "none";
    document.body.classList.remove("modal-abierto");
}

function generarPDFStockInforme() {
    const tipoVehiculo = document.getElementById("tipoStockInforme")?.value || "todos";
    const condicion = document.getElementById("condicionStockInforme")?.value || "todos";
    cerrarSelectorStockInforme();
    generarPDF("stockFiltro", null, { tipoVehiculo, condicion });
}

function generarPDF(tipo, mes = null, filtros = null) {
    const tiposPermitidos = [
        "ventasTotales",
        "ventasVendedor",
        "ventasVendedorDetalle",
        "ventasMes",
        "ventasMesDetalle",
        "stock",
        "stockFiltro",
        "vehiculosVendidos",
        "ventasDetalle",
        "listaEmpleados",
        "listaEmpleadosInactivos",
        "listaVendedores",
        "listaClientes"
    ];

    if (!tiposPermitidos.includes(tipo)) {
        alert("Tipo de informe inválido");
        return;
    }

    if (["ventasMes", "ventasMesDetalle"].includes(tipo) && !mes) {
        alert("Elegí un mes para generar el informe.");
        return;
    }

    if (tipo === "ventasVendedorDetalle" && !mes) {
        alert("Elegí un vendedor para generar el informe.");
        return;
    }

    let url = "index.php?accion=pdfInforme&tipo=" + encodeURIComponent(tipo);
    if (["ventasMes", "ventasMesDetalle"].includes(tipo)) url += "&mes=" + encodeURIComponent(mes);
    else if (tipo === "ventasVendedorDetalle") url += "&legajo=" + encodeURIComponent(mes);
    else if (tipo === "stockFiltro" && filtros) url += "&tipoVehiculo=" + encodeURIComponent(filtros.tipoVehiculo) + "&condicion=" + encodeURIComponent(filtros.condicion);
    window.open(url, "_blank");
}
