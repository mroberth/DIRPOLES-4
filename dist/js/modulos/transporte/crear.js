/**
 * dist/js/modulos/transporte/crear.js
 * ---------------------------------------------------------------
 * Lógica de pantalla de creación para Transporte (Rutas, Vehículos, Proveedores, Repuestos, Asignaciones, Mantenimientos).
 */
document.addEventListener('DOMContentLoaded', async function () {
    'use strict';

    function escapar(txt) {
        if (txt === null || txt === undefined) return '';
        const div = document.createElement('div');
        div.textContent = txt;
        return div.innerHTML;
    }

    const tabCrear = document.getElementById('tabTransporteCrear');
    if (!tabCrear) return;

    // Conectar Botón Ayuda
    const btnAyuda = document.getElementById('btn-ayuda');
    if (btnAyuda && window.TransporteTour) {
        btnAyuda.addEventListener('click', () => window.TransporteTour.iniciar());
    }

    // Inicializar Select2 en los formularios de crear
    if (window.initSelect2) {
        window.initSelect2(document.getElementById('tabTransporteCrearContent'));
    }

    // Listas globales de catálogo para repuestos y opciones
    let listaProveedores = [];
    let listaRepuestos = [];

    // Cargar catálogos iniciales
    async function cargarCatalogos() {
        try {
            // Cargar Proveedores
            listaProveedores = await apiFetch(BASE_URL + 'api/transporte/proveedores/listar');
            const selectProv = document.getElementById('id_proveedor_repuesto');
            if (selectProv) {
                selectProv.innerHTML = '<option value="">Sin proveedor asignado</option>' +
                    listaProveedores.map(p => `<option value="${escapar(p.id_proveedor)}">${escapar(p.nombre)} (${escapar(p.tipo_documento)}-${escapar(p.num_documento)})</option>`).join('');
                if (window.initSelect2) window.initSelect2(selectProv.parentElement);
            }

            // Cargar Repuestos
            listaRepuestos = await apiFetch(BASE_URL + 'api/transporte/repuestos/listar');

            // Cargar Opciones de Asignaciones (rutas, vehículos, choferes)
            const opcAsig = await apiFetch(BASE_URL + 'api/transporte/asignaciones/opciones');
            const selRutasAsig = document.getElementById('id_ruta_asig');
            const selVehAsig   = document.getElementById('id_vehiculo_asig');
            const selEmpAsig   = document.getElementById('id_empleado_asig');
            const selVehMant   = document.getElementById('id_vehiculo_mant');

            if (selRutasAsig) {
                selRutasAsig.innerHTML = '<option value="">Seleccione ruta...</option>' +
                    opcAsig.rutas.map(r => `<option value="${escapar(r.id_ruta)}">${escapar(r.nombre_ruta)} (${escapar(r.tipo_ruta)})</option>`).join('');
            }
            if (selVehAsig) {
                selVehAsig.innerHTML = '<option value="">Seleccione vehículo...</option>' +
                    opcAsig.vehiculos.map(v => `<option value="${escapar(v.id_vehiculo)}">${escapar(v.placa)} - ${escapar(v.modelo || v.tipo)}</option>`).join('');
            }
            if (selVehMant) {
                selVehMant.innerHTML = '<option value="">Seleccione vehículo...</option>' +
                    opcAsig.vehiculos.map(v => `<option value="${escapar(v.id_vehiculo)}">${escapar(v.placa)} - ${escapar(v.modelo || v.tipo)}</option>`).join('');
            }
            if (selEmpAsig) {
                selEmpAsig.innerHTML = '<option value="">Seleccione chofer...</option>' +
                    opcAsig.empleados.map(e => `<option value="${escapar(e.id_empleado)}">${escapar(e.nombres)} ${escapar(e.apellidos)} (${escapar(e.tipo_cedula)}-${escapar(e.cedula)})</option>`).join('');
            }

            if (window.initSelect2) {
                window.initSelect2(document.getElementById('tabTransporteCrearContent'));
            }
        } catch (err) {
            console.error('Error al cargar catálogos de transporte:', err);
        }
    }

    await cargarCatalogos();

    // ==================== SUBMIT DE CADA FORMULARIO ====================

    // 1. FORMULARIO RUTAS
    const formRuta = document.getElementById('form-ruta');
    if (formRuta) {
        const valRuta = window.TransporteValidaciones.configurar(formRuta);
        formRuta.addEventListener('submit', async function (ev) {
            ev.preventDefault();
            if (!(await valRuta.validarTodo())) {
                AlertManager.warning('Formulario incompleto', 'Revisa los campos destacados de la ruta.');
                return;
            }
            const payload = Object.fromEntries(new FormData(formRuta));
            try {
                const res = await apiFetch(BASE_URL + 'api/transporte/rutas/crear', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify(payload),
                });
                AlertManager.success('¡Ruta Registrada!', `La ruta "${res.nombre_ruta}" se creó con éxito.`);
                formRuta.reset();
                if (window.initSelect2) window.initSelect2(formRuta);
                if (window.TransporteStats) window.TransporteStats.cargar();
                await cargarCatalogos();
            } catch (err) {
                AlertManager.warning('Atención', err.mensaje || 'Error al guardar la ruta.');
            }
        });
    }

    // 2. FORMULARIO VEHÍCULOS
    const formVeh = document.getElementById('form-vehiculo');
    if (formVeh) {
        const valVeh = window.TransporteValidaciones.configurar(formVeh);
        formVeh.addEventListener('submit', async function (ev) {
            ev.preventDefault();
            if (!(await valVeh.validarTodo())) {
                AlertManager.warning('Formulario incompleto', 'Revisa los campos del vehículo.');
                return;
            }
            const payload = Object.fromEntries(new FormData(formVeh));
            try {
                const res = await apiFetch(BASE_URL + 'api/transporte/vehiculos/crear', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify(payload),
                });
                AlertManager.success('¡Vehículo Registrado!', `El vehículo con placa "${res.placa}" se creó con éxito.`);
                formVeh.reset();
                if (window.initSelect2) window.initSelect2(formVeh);
                if (window.TransporteStats) window.TransporteStats.cargar();
                await cargarCatalogos();
            } catch (err) {
                AlertManager.warning('Atención', err.mensaje || 'Error al guardar el vehículo.');
            }
        });
    }

    // 3. FORMULARIO PROVEEDORES
    const formProv = document.getElementById('form-proveedor');
    if (formProv) {
        const valProv = window.TransporteValidaciones.configurar(formProv);
        formProv.addEventListener('submit', async function (ev) {
            ev.preventDefault();
            if (!(await valProv.validarTodo())) {
                AlertManager.warning('Formulario incompleto', 'Revisa los campos del proveedor.');
                return;
            }
            const payload = Object.fromEntries(new FormData(formProv));
            try {
                const res = await apiFetch(BASE_URL + 'api/transporte/proveedores/crear', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify(payload),
                });
                AlertManager.success('¡Proveedor Registrado!', `El proveedor "${res.nombre}" se creó con éxito.`);
                formProv.reset();
                if (window.TransporteStats) window.TransporteStats.cargar();
                await cargarCatalogos();
            } catch (err) {
                AlertManager.warning('Atención', err.mensaje || 'Error al guardar el proveedor.');
            }
        });
    }

    // 4. FORMULARIO REPUESTOS
    const formRep = document.getElementById('form-repuesto');
    if (formRep) {
        const valRep = window.TransporteValidaciones.configurar(formRep);
        formRep.addEventListener('submit', async function (ev) {
            ev.preventDefault();
            if (!(await valRep.validarTodo())) {
                AlertManager.warning('Formulario incompleto', 'Revisa los campos del repuesto.');
                return;
            }
            const payload = Object.fromEntries(new FormData(formRep));
            payload.id_proveedor = parseInt(payload.id_proveedor, 10) || 0;

            try {
                const res = await apiFetch(BASE_URL + 'api/transporte/repuestos/crear', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify(payload),
                });
                AlertManager.success('¡Repuesto Registrado!', `El repuesto "${res.nombre}" se creó con stock inicial 0.`);
                formRep.reset();
                if (window.initSelect2) window.initSelect2(formRep);
                if (window.TransporteStats) window.TransporteStats.cargar();
                await cargarCatalogos();
            } catch (err) {
                AlertManager.warning('Atención', err.mensaje || 'Error al guardar el repuesto.');
            }
        });
    }

    // 5. FORMULARIO ASIGNACIONES
    const formAsig = document.getElementById('form-asignacion');
    if (formAsig) {
        const valAsig = window.TransporteValidaciones.configurar(formAsig);
        formAsig.addEventListener('submit', async function (ev) {
            ev.preventDefault();
            if (!(await valAsig.validarTodo())) {
                AlertManager.warning('Formulario incompleto', 'Selecciona la ruta, vehículo y chofer.');
                return;
            }
            const payload = Object.fromEntries(new FormData(formAsig));
            payload.id_ruta = parseInt(payload.id_ruta, 10);
            payload.id_vehiculo = parseInt(payload.id_vehiculo, 10);
            payload.id_empleado = parseInt(payload.id_empleado, 10);

            try {
                const res = await apiFetch(BASE_URL + 'api/transporte/asignaciones/crear', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify(payload),
                });
                AlertManager.success('¡Asignación Creada!', `Vehículo ${res.placa} asignado a la ruta "${res.nombre_ruta}".`);
                formAsig.reset();
                if (window.initSelect2) window.initSelect2(formAsig);
                if (window.TransporteStats) window.TransporteStats.cargar();
            } catch (err) {
                AlertManager.warning('Atención', err.mensaje || 'Error al crear la asignación.');
            }
        });
    }

    // 6. FORMULARIO MANTENIMIENTO + REPUESTOS DINÁMICOS
    const formMant = document.getElementById('form-mantenimiento');
    const contenedorRep = document.getElementById('contenedor-repuestos-mant');
    const btnAgregarRep = document.getElementById('btn-agregar-repuesto-mant');
    const msgSinRep     = document.getElementById('msg-sin-repuestos');

    if (btnAgregarRep && contenedorRep) {
        btnAgregarRep.addEventListener('click', function () {
            if (msgSinRep) msgSinRep.style.display = 'none';

            const itemIndex = contenedorRep.querySelectorAll('.item-repuesto-row').length;
            const opcRepuestos = listaRepuestos.map(r => `<option value="${escapar(r.id_repuesto)}">${escapar(r.nombre)} (Stock: ${escapar(r.cantidad)})</option>`).join('');

            const rowHtml = `
                <div class="row g-2 align-items-center mb-2 item-repuesto-row">
                    <div class="col-md-7">
                        <select class="form-select form-select-sm sel-repuesto-item" required>
                            <option value="">Seleccione repuesto...</option>
                            ${opcRepuestos}
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="number" class="form-control form-control-sm inp-repuesto-cant" min="1" value="1" placeholder="Cant" required>
                    </div>
                    <div class="col-md-2 text-center">
                        <button type="button" class="btn btn-sm btn-outline-danger btn-quitar-repuesto"><i class="fas fa-trash"></i></button>
                    </div>
                </div>
            `;
            contenedorRep.insertAdjacentHTML('beforeend', rowHtml);
        });

        contenedorRep.addEventListener('click', function (e) {
            const btnQuitar = e.target.closest('.btn-quitar-repuesto');
            if (btnQuitar) {
                const row = btnQuitar.closest('.item-repuesto-row');
                if (row) row.remove();
                if (contenedorRep.querySelectorAll('.item-repuesto-row').length === 0 && msgSinRep) {
                    msgSinRep.style.display = 'block';
                }
            }
        });
    }

    if (formMant) {
        const valMant = window.TransporteValidaciones.configurar(formMant);
        formMant.addEventListener('submit', async function (ev) {
            ev.preventDefault();
            if (!(await valMant.validarTodo())) {
                AlertManager.warning('Formulario incompleto', 'Selecciona el vehículo, tipo y fecha de mantenimiento.');
                return;
            }

            const payload = {
                id_vehiculo: parseInt(document.getElementById('id_vehiculo_mant').value, 10),
                tipo: document.getElementById('tipo_mantenimiento').value,
                fecha: document.getElementById('fecha_mantenimiento').value,
                descripcion: document.getElementById('descripcion_mantenimiento').value,
                repuestos: [],
            };

            // Extraer repuestos consumidos
            const rows = contenedorRep.querySelectorAll('.item-repuesto-row');
            rows.forEach(r => {
                const idR = parseInt(r.querySelector('.sel-repuesto-item').value, 10);
                const cant = parseInt(r.querySelector('.inp-repuesto-cant').value, 10);
                if (idR > 0 && cant > 0) {
                    payload.repuestos.push({ id_repuesto: idR, cantidad: cant });
                }
            });

            try {
                const res = await apiFetch(BASE_URL + 'api/transporte/mantenimientos/crear', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify(payload),
                });
                AlertManager.success('¡Mantenimiento Registrado!', `Servicio registrado para el vehículo ${res.placa}.`);
                formMant.reset();
                if (contenedorRep) {
                    contenedorRep.innerHTML = '';
                    if (msgSinRep) {
                        contenedorRep.appendChild(msgSinRep);
                        msgSinRep.style.display = 'block';
                    }
                }
                if (window.initSelect2) window.initSelect2(formMant);
                if (window.TransporteStats) window.TransporteStats.cargar();
                await cargarCatalogos();
            } catch (err) {
                AlertManager.warning('Atención', err.mensaje || 'Error al registrar el mantenimiento.');
            }
        });
    }
});
