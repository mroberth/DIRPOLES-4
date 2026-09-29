// dist/js/modulos/jornadas/detalle.js
// ------------------------------------------------------------------
// Página de DETALLE de una jornada: cabecera (aforo/estatus), tabla de
// asistentes con DataTables, alta manual con autocompletar por cédula y
// eliminación de asistentes. Los diagnósticos viven en diagnosticos.js.
//
// Reglas visibles (las aplica el backend igualmente):
//  - solo una jornada 'Activa' y vigente por fecha admite escrituras;
//  - una cédula no se repite en la MISMA jornada;
//  - el aforo no se puede rebasar (bloqueo FOR UPDATE);
//  - no se elimina un asistente con diagnóstico.
// ------------------------------------------------------------------
document.addEventListener('DOMContentLoaded', function () {
    const tbody = document.getElementById('tbodyAsistentes');
    if (!tbody) return;

    const idJornada = Number(window.JORNADAS_ID || 0);
    const puedeCrear = window.JORNADAS_PUEDE_CREAR === true;
    const puedeEliminar = window.JORNADAS_PUEDE_ELIMINAR === true;

    const V = window.JornadasValidaciones;

    let asistentes = [];
    let tabla = null;

    const escapar = (txt) => {
        const div = document.createElement('div');
        div.textContent = txt == null ? '' : String(txt);
        return div.innerHTML;
    };

    // ---------- Tour ----------
    const btnAyuda = document.getElementById('btn-ayuda');
    if (btnAyuda && window.JornadasTour) {
        btnAyuda.addEventListener('click', () => window.JornadasTour.iniciarDetalle());
    }

    // ---------- Cabecera ----------
    function pintarCabecera(j) {
        const estatus = document.getElementById('jornadaEstatus');
        if (estatus) {
            estatus.textContent = j.estatus;
            estatus.className = 'badge align-middle ms-2 '
                + (j.estatus === 'Activa' ? 'bg-success'
                    : (j.estatus === 'Cancelada' ? 'bg-danger' : 'bg-secondary'));
        }

        const personas = document.getElementById('jornadaPersonas');
        if (personas) personas.textContent = j.personas;

        const ocupacion = document.getElementById('jornadaOcupacion');
        if (ocupacion) ocupacion.textContent = Number(j.ocupacion).toFixed(1) + '%';

        const barra = document.getElementById('jornadaBarra');
        if (barra) {
            const pct = Math.min(100, Number(j.ocupacion) || 0);
            barra.style.width = pct + '%';
            barra.classList.toggle('bg-danger', pct >= 100);
        }

        const diag = document.getElementById('jornadaDiagnosticos');
        if (diag) diag.textContent = j.diagnosticos;
    }

    async function cargarCabecera() {
        try {
            const j = await apiFetch(BASE_URL + 'api/jornadas/obtener/' + encodeURIComponent(idJornada));
            pintarCabecera(j);
        } catch (error) {
            console.error('Cabecera de la jornada:', error);
            if (error.codigo === 'NOT_FOUND') {
                AlertManager.warning('Jornada no encontrada', 'La jornada ya no existe.');
            }
        }
    }

    // ---------- Asistentes ----------
    function fila(a) {
        // Ver diagnósticos solo requiere 'leer'; el ALTA dentro del modal
        // está condicionada a 'crear' (diagnosticos.js).
        const btnDiagnosticos = `
            <button type="button" class="btn btn-sm btn-outline-success btn-diagnosticos"
                    data-id="${escapar(a.id_jornada_beneficiario)}"
                    data-nombre="${escapar(a.nombres + ' ' + a.apellidos)}"
                    title="${puedeCrear ? 'Ver y registrar diagnósticos' : 'Ver diagnósticos'}">
                <i class="fas fa-notes-medical"></i>
            </button>`;

        const tieneDiag = Number(a.diagnosticos) > 0;
        const btnEliminar = puedeEliminar
            ? `<button type="button" class="btn btn-sm btn-outline-dark btn-eliminar-asistente"
                       data-id="${escapar(a.id_jornada_beneficiario)}"
                       data-nombre="${escapar(a.nombres + ' ' + a.apellidos)}"
                       title="${tieneDiag ? 'No se puede eliminar: ya tiene diagnósticos' : 'Eliminar asistente'}"
                       ${tieneDiag ? 'disabled' : ''}>
                   <i class="fas fa-trash"></i>
               </button>`
            : '';

        return `
            <tr>
                <td data-order="${escapar(a.cedula)}">${escapar(a.tipo_cedula)}-${escapar(a.cedula)}</td>
                <td>${escapar(a.nombres)} ${escapar(a.apellidos)}</td>
                <td>${escapar(a.tipo_paciente)}</td>
                <td class="text-center">${a.edad == null ? '—' : escapar(a.edad)}</td>
                <td data-order="${escapar(a.fecha_atencion)}">${escapar(Formato.fechaHora(a.fecha_atencion))}</td>
                <td class="text-center">
                    <span class="badge ${Number(a.diagnosticos) > 0 ? 'bg-success' : 'bg-secondary'}">
                        ${Number(a.diagnosticos) || 0}
                    </span>
                </td>
                <td class="text-center text-nowrap">${btnDiagnosticos} ${btnEliminar}</td>
            </tr>`;
    }

    async function cargarAsistentes() {
        try {
            asistentes = await apiFetch(BASE_URL + 'api/jornadas/asistentes/' + encodeURIComponent(idJornada));

            if (tabla) {
                tabla.destroy();
                tabla = null;
            }

            tbody.innerHTML = asistentes.map(fila).join('');

            tabla = DataTableHelper.inicializar('#tablaAsistentes', {
                titulo: 'Asistentes de la jornada',
                orden: [[4, 'desc']],
                pageLength: 10,
                columnasExport: [0, 1, 2, 3, 4, 5],
                columnDefs: [
                    { targets: 2, width: '150px' },
                    { targets: 3, width: '70px', className: 'text-center' },
                    { targets: 5, width: '110px', className: 'text-center' },
                    { targets: 6, width: '120px', orderable: false, className: 'text-center text-nowrap' },
                ],
            });
        } catch (error) {
            console.error('Asistentes:', error);
            if (error.codigo === 'NOT_FOUND') {
                AlertManager.warning('Jornada no encontrada', 'La jornada ya no existe.');
            } else if (error.codigo === 'ACCESS_DENIED') {
                AlertManager.warning('Sin permiso', error.mensaje);
            } else {
                AlertManager.error('Error', error.mensaje);
            }
        }
    }

    function recargar() {
        cargarCabecera();
        cargarAsistentes();
    }

    // ---------- Formulario de asistente ----------
    const formAsistente = document.getElementById('form-asistente');
    const validadorAsistente = formAsistente
        ? V.configurar(formAsistente, { tipo: 'asistente', prefijo: 'asis_' })
        : null;

    // Catálogo de tipos de paciente.
    if (formAsistente) {
        apiFetch(BASE_URL + 'api/jornadas/catalogos')
            .then((catalogos) => {
                const sel = document.getElementById('asis_tipo_paciente');
                sel.innerHTML = '<option value="">Seleccione…</option>';
                (catalogos.tipo_paciente || []).forEach((tipo) => {
                    const opcion = document.createElement('option');
                    opcion.value = tipo;
                    opcion.textContent = tipo;
                    sel.appendChild(opcion);
                });
                if (window.initSelect2) window.initSelect2(formAsistente);
            })
            .catch((error) => {
                console.error('Catálogos:', error);
                AlertManager.error('No se cargaron los datos', error.mensaje || 'Intenta nuevamente.');
            });
    }

    function rellenar(campoId, valor) {
        const campo = document.getElementById(campoId);
        if (!campo) return;
        campo.value = valor == null ? '' : valor;
        campo.dispatchEvent(new Event(campo.tagName === 'SELECT' ? 'change' : 'input', { bubbles: true }));
    }

    /** Autocompletado: la API solo SUGIERE; quien confirma es el usuario. */
    async function buscarPersona() {
        const tipo = document.getElementById('asis_tipo_cedula').value;
        const cedula = document.getElementById('asis_cedula').value.replace(/\D+/g, '');

        if (cedula.length < 5 || cedula.length > 12) {
            V.mostrarError(document.getElementById('asis_cedula'), 'La cédula debe tener entre 5 y 12 dígitos.');
            return;
        }

        try {
            const r = await apiFetch(
                BASE_URL + 'api/jornadas/buscar_persona?tipo=' + encodeURIComponent(tipo)
                + '&cedula=' + encodeURIComponent(cedula)
            );

            if (!r.encontrado) {
                AlertManager.info('Sin coincidencias',
                    'Esa cédula no está en beneficiarios ni en empleados: escribe los datos a mano.');
                document.getElementById('asis_nombres').focus();
                return;
            }

            rellenar('asis_tipo_cedula', r.tipo_cedula || tipo);
            rellenar('asis_cedula', r.cedula || cedula);
            rellenar('asis_nombres', r.nombres || '');
            rellenar('asis_apellidos', r.apellidos || '');
            rellenar('asis_fecha_nacimiento', (r.fecha_nacimiento || '').slice(0, 10));
            if (r.genero) rellenar('asis_genero', r.genero);
            rellenar('asis_telefono', r.telefono || '');
            rellenar('asis_correo', r.correo || '');
            rellenar('asis_direccion', r.direccion || '');

            AlertManager.success('Datos autocompletados',
                `Se rellenaron los datos de ${r.nombres} ${r.apellidos} (origen: ${r.origen}). Revísalos antes de guardar.`);
        } catch (error) {
            V.mostrarError(document.getElementById('asis_cedula'), 'No se pudo verificar con el servidor. Intenta de nuevo.');
            AlertManager.error('Error', error.mensaje);
        }
    }

    const btnBuscar = document.getElementById('btn-buscar-persona');
    if (btnBuscar) btnBuscar.addEventListener('click', buscarPersona);

    function manejarErrorAsistente(error) {
        if (error.codigo === 'ALREADY_EXISTS' || error.codigo === 'VALIDATION_ERROR') {
            AlertManager.warning('No se pudo registrar', error.mensaje);
        } else if (error.codigo === 'ACCESS_DENIED') {
            AlertManager.warning('Sin permiso', error.mensaje);
        } else if (error.codigo === 'NOT_FOUND') {
            AlertManager.warning('Jornada no disponible', error.mensaje);
            recargar();
        } else {
            AlertManager.error('Error', error.mensaje);
        }
    }

    if (formAsistente) {
        formAsistente.addEventListener('submit', async function (ev) {
            ev.preventDefault();

            if (!validadorAsistente.validarTodo()) {
                AlertManager.warning('Formulario incompleto', 'Corrige los campos resaltados antes de continuar.');
                return;
            }

            const datos = {
                id_jornada: idJornada,
                tipo_cedula:       document.getElementById('asis_tipo_cedula').value,
                cedula:            document.getElementById('asis_cedula').value,
                nombres:           document.getElementById('asis_nombres').value.trim(),
                apellidos:         document.getElementById('asis_apellidos').value.trim(),
                fecha_nacimiento:  document.getElementById('asis_fecha_nacimiento').value,
                genero:            document.getElementById('asis_genero').value,
                tipo_paciente:     document.getElementById('asis_tipo_paciente').value,
                telefono:          document.getElementById('asis_telefono').value,
                correo:            document.getElementById('asis_correo').value.trim(),
                direccion:         document.getElementById('asis_direccion').value.trim(),
            };

            try {
                const nueva = await apiFetch(BASE_URL + 'api/jornadas/agregar_asistente', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify(datos),
                });

                AlertManager.success('Asistente registrado',
                    `${nueva.nombres} ${nueva.apellidos} quedó en la jornada (${nueva.personas} de ${nueva.aforo_maximo}).`);

                formAsistente.reset();
                validadorAsistente.limpiar();
                if (window.initSelect2) window.initSelect2(formAsistente);

                const colapso = document.getElementById('colapsoAsistente');
                if (colapso && typeof bootstrap !== 'undefined' && colapso.classList.contains('show')) {
                    bootstrap.Collapse.getOrCreateInstance(colapso, { toggle: false }).hide();
                }

                recargar();
            } catch (error) {
                manejarErrorAsistente(error);
                recargar();
            }
        });
    }

    async function eliminarAsistente(id, nombre) {
        const confirmacion = await AlertManager.confirm(
            '¿Eliminar asistente?',
            `Se quitará a "${nombre}" de esta jornada. No es posible si ya tiene diagnósticos registrados.`,
            'Sí, eliminar',
            'Cancelar'
        );
        if (!confirmacion.isConfirmed) return;

        try {
            await apiFetch(BASE_URL + 'api/jornadas/eliminar_asistente', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ id_jornada_beneficiario: Number(id) }),
            });
            AlertManager.success('Asistente eliminado', 'La persona fue quitada de la jornada.');
            recargar();
        } catch (error) {
            manejarErrorAsistente(error);
            recargar();
        }
    }

    // ---------- Acciones (delegado) ----------
    document.addEventListener('click', function (ev) {
        const diag = ev.target.closest('.btn-diagnosticos');
        if (diag && window.JornadasDiagnosticos) {
            window.JornadasDiagnosticos.abrir(diag.getAttribute('data-id'), diag.getAttribute('data-nombre'));
            return;
        }

        const borrar = ev.target.closest('.btn-eliminar-asistente');
        if (borrar && !borrar.disabled) {
            eliminarAsistente(borrar.getAttribute('data-id'), borrar.getAttribute('data-nombre'));
        }
    });

    // ---------- Recargar ----------
    const btnRecargar = document.getElementById('btn-recargar');
    if (btnRecargar) btnRecargar.addEventListener('click', recargar);

    // Expuesto para diagnosticos.js.
    window.JornadasDetalle = { recargar };

    cargarCabecera();
    cargarAsistentes();
});
