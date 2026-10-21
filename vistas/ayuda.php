<h2 class="titulo-metal">Ayuda</h2>

<div class="card ayuda-card">
    <p class="ayuda-intro">Elegí una tarea y tocá <strong>Ver pasos</strong> para consultar cómo hacerla.</p>
    <div class="tabla-ayuda-wrap">
        <table id="tablaAyuda" class="display tabla-ayuda" style="width:100%">
            <thead>
                <tr><th>Qué querés hacer</th><th>Pasos</th></tr>
            </thead>
            <tbody>
                <tr>
                    <td>Agregar empleado</td>
                    <td><button class="btn-metal" type="button" aria-expanded="false" onclick="alternarPasosAyuda('ayuda-empleado', this)">Ver pasos</button><ol id="ayuda-empleado" hidden><li>Abrí <strong>Empleados</strong>.</li><li>Tocá <strong>Añadir</strong>.</li><li>Completá los datos personales y laborales.</li><li>Si corresponde, marcá la casilla <strong>Vendedor</strong> e ingresá el porcentaje de comisión.</li><li>Tocá <strong>Guardar</strong>.</li></ol></td>
                </tr>
                <tr>
                    <td>Editar o eliminar empleado</td>
                    <td><button class="btn-metal" type="button" aria-expanded="false" onclick="alternarPasosAyuda('ayuda-editar-empleado', this)">Ver pasos</button><ol id="ayuda-editar-empleado" hidden><li>En <strong>Empleados</strong>, buscá a la persona en la tabla.</li><li>Tocá el lápiz para editar y guardá los cambios, o tocá el tacho para eliminarla.</li><li>Confirmá la eliminación cuando aparezca el aviso.</li></ol></td>
                </tr>
                <tr>
                    <td>Ver empleados inactivos</td>
                    <td><button class="btn-metal" type="button" aria-expanded="false" onclick="alternarPasosAyuda('ayuda-inactivos', this)">Ver pasos</button><ol id="ayuda-inactivos" hidden><li>Abrí <strong>Empleados</strong>.</li><li>Tocá <strong>Ver inactivos</strong> para consultar la lista.</li></ol></td>
                </tr>
                <tr>
                    <td>Agregar, editar o eliminar vehículo</td>
                    <td><button class="btn-metal" type="button" aria-expanded="false" onclick="alternarPasosAyuda('ayuda-vehiculos', this)">Ver pasos</button><ol id="ayuda-vehiculos" hidden><li>Abrí <strong>Vehículos</strong>.</li><li>Tocá <strong>Añadir</strong> y completá los datos para cargar uno.</li><li>En la fila correspondiente, usá el lápiz para editar o el tacho para eliminar y confirmá la acción.</li></ol></td>
                </tr>
                <tr>
                    <td>Consultar vehículos vendidos</td>
                    <td><button class="btn-metal" type="button" aria-expanded="false" onclick="alternarPasosAyuda('ayuda-vendidos', this)">Ver pasos</button><ol id="ayuda-vendidos" hidden><li>Abrí <strong>Vehículos</strong>.</li><li>Tocá <strong>Ver vendidos</strong>.</li><li>Para volver, tocá <strong>Ver disponibles</strong>.</li></ol></td>
                </tr>
                <tr>
                    <td>Agregar, editar o eliminar cliente</td>
                    <td><button class="btn-metal" type="button" aria-expanded="false" onclick="alternarPasosAyuda('ayuda-clientes', this)">Ver pasos</button><ol id="ayuda-clientes" hidden><li>Abrí <strong>Clientes</strong>.</li><li>Tocá <strong>Añadir</strong> y completá los datos.</li><li>Para cambiar o borrar un cliente, usá el lápiz o el tacho de su fila.</li><li>Confirmá la eliminación cuando se solicite.</li></ol></td>
                </tr>
                <tr>
                    <td>Registrar una venta</td>
                    <td><button class="btn-metal" type="button" aria-expanded="false" onclick="alternarPasosAyuda('ayuda-venta', this)">Ver pasos</button><ol id="ayuda-venta" hidden><li>Asegurate de que el cliente, el vehículo disponible y el empleado vendedor ya estén cargados.</li><li>Abrí <strong>Ventas</strong> y tocá <strong>Añadir</strong>.</li><li>Elegí cliente, vehículo y vendedor; verificá el precio.</li><li>Tocá <strong>Guardar</strong>. El vehículo pasará a la lista de vendidos.</li></ol></td>
                </tr>
                <tr>
                    <td>Filtrar ventas</td>
                    <td><button class="btn-metal" type="button" aria-expanded="false" onclick="alternarPasosAyuda('ayuda-filtro-ventas', this)">Ver pasos</button><ol id="ayuda-filtro-ventas" hidden><li>Abrí <strong>Ventas</strong>.</li><li>Elegí <strong>Todas las ventas</strong>, <strong>Ventas del mes actual</strong> o <strong>Ventas anteriores</strong>.</li></ol></td>
                </tr>
                <tr>
                    <td>Ver o descargar un informe</td>
                    <td><button class="btn-metal" type="button" aria-expanded="false" onclick="alternarPasosAyuda('ayuda-informes', this)">Ver pasos</button><ol id="ayuda-informes" hidden><li>Abrí <strong>Informes</strong>.</li><li>Tocá el botón del ojo del informe que necesitás.</li><li>Si el informe pide un mes o un vendedor, elegilo y tocá <strong>Ver informe</strong> o <strong>Ver ventas</strong>.</li><li>En la vista previa podés imprimir o descargar el informe.</li></ol></td>
                </tr>
                <tr>
                    <td>Consultar ventas de un vendedor y sus comisiones</td>
                    <td><button class="btn-metal" type="button" aria-expanded="false" onclick="alternarPasosAyuda('ayuda-ventas-vendedor', this)">Ver pasos</button><ol id="ayuda-ventas-vendedor" hidden><li>Abrí <strong>Informes</strong>.</li><li>En <strong>Ventas del vendedor</strong>, tocá el botón del ojo.</li><li>Elegí al vendedor y tocá <strong>Ver ventas</strong>.</li><li>La vista previa muestra la comisión por venta, la cantidad y la comisión acumulada.</li></ol></td>
                </tr>
                <tr>
                    <td>Cambiar la clave</td>
                    <td><button class="btn-metal" type="button" aria-expanded="false" onclick="alternarPasosAyuda('ayuda-cambiar-clave', this)">Ver pasos</button><ol id="ayuda-cambiar-clave" hidden><li>Tocá <strong>Opciones</strong> en el menú.</li><li>Elegí <strong>Cambiar clave</strong>.</li><li>Ingresá tu clave actual, escribí la nueva clave y volvé a ingresarla para confirmarla.</li><li>La nueva clave debe tener al menos 8 caracteres. Tocá <strong>Guardar clave</strong>.</li></ol></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
