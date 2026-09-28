// dist/js/modulos/trabajo-social/consultar.js
// ------------------------------------------------------------------
// Consulta de los 4 sub-registros de Trabajo Social: una pestaña por
// tipo (becas, exoneraciones, FAMES, embarazadas) con su DataTable,
// detalle en SweetAlert, y acciones de editar/eliminar por fila.
// ------------------------------------------------------------------
document.addEventListener('DOMContentLoaded', () => {
    const escapar = (valor) => String(valor ?? '').replace(/[&<>'"]/g, (caracter) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;' }[caracter]));

    const TABS = {
        becas: {
            tabla: '#tablaBecas',
            tbody: 'tbodyBecas',
            titulo: 'Becas',
            clave: 'id_becas',
            columnasExport: [0, 1, 2, 3],
            columnDefs: [
                { targets: 0, width: '105px' },
                { targets: 2, width: '230px' },
                { targets: 4, width: '130px', orderable: false, className: 'text-center text-nowrap' },
            ],
        },
        exoneraciones: {
            tabla: '#tablaExoneraciones',
            tbody: 'tbodyExoneraciones',
            titulo: 'Exoneraciones',
            clave: 'id_exoneracion',
            columnasExport: [0, 1, 2, 3],
            columnDefs: [
                { targets: 0, width: '105px' },
                { targets: 2, width: '230px' },
                { targets: 4, width: '170px', orderable: false, className: 'text-center text-nowrap' },
            ],
        },
        fames: {
            tabla: '#tablaFames',
            tbody: 'tbodyFames',
            titulo: 'FAMES',
            clave: 'id_fames',
            columnasExport: [0, 1, 2, 3],
            columnDefs: [
                { targets: 0, width: '105px' },
                { targets: 2, width: '200px' },
                { targets: 4, width: '130px', orderable: false, className: 'text-center text-nowrap' },
            ],
        },
        embarazadas: {
            tabla: '#tablaEmbarazadas',
            tbody: 'tbodyEmbarazadas',
            titulo: 'Embarazadas',
            clave: 'id_gestion',
            columnasExport: [0, 1, 2, 3, 4],
            columnDefs: [
                { targets: 0, width: '105px' },
                { targets: 2, width: '180px' },
                { targets: 3, width: '90px' },
                { targets: 4, width: '120px' },
                { targets: 5, width: '130px', orderable: false, className: 'text-center text-nowrap' },
            ],
        },
    };

    /** Estado por tipo: filas, instancia DataTable y bandera de carga. */
    const estado = {};
    Object.keys(TABS).forEach((tipo) => {
        estado[tipo] = { registros: [], tabla: null, cargada: false };
    });

    function celdaAcciones(tipo, id) {
        // Solo exoneraciones tienen estudio socioeconómico: se añade el
        // botón de ver PDF junto al detalle/edición/eliminación.
        const verEstudio = tipo === 'exoneraciones'
            ? `<button class="btn btn-sm btn-outline-warning js-ver-estudio" data-tipo="${tipo}" data-id="${id}" title="Ver estudio socioeconómico"><i class="fas fa-file-pdf"></i></button> `
            : '';
        return `<td class="text-center text-nowrap">
            <button class="btn btn-sm btn-outline-primary js-detalle" data-tipo="${tipo}" data-id="${id}" title="Detalle"><i class="fas fa-eye"></i></button>
            ${verEstudio}<button class="btn btn-sm btn-outline-secondary js-editar" data-tipo="${tipo}" data-id="${id}" title="Editar"><i class="fas fa-pen"></i></button>
            <button class="btn btn-sm btn-outline-danger js-eliminar" data-tipo="${tipo}" data-id="${id}" title="Eliminar"><i class="fas fa-trash"></i></button>
        </td>`;
    }

    function celdaBeneficiario(registro) {
        return `${escapar(registro.beneficiario)}<br><small class="text-muted">${escapar(registro.cedula_beneficiario)}</small>`;
    }

    function fila(tipo, registro) {
        const id = registro[TABS[tipo].clave];
        const fecha = registro.fecha_creacion || '';
        const celdaFecha = `<td data-order="${escapar(fecha)}">${escapar(Formato.fecha(fecha))}</td>`;

        if (tipo === 'becas') {
            return `<tr>${celdaFecha}
                <td>${celdaBeneficiario(registro)}</td>
                <td>${escapar(registro.nombre_banco || 'Banco no identificado')}<br><small class="text-muted">Código ${escapar(registro.tipo_banco || '')}</small></td>
                <td>${escapar(registro.cta_bcv || '')}</td>
                ${celdaAcciones(tipo, id)}</tr>`;
        }

        if (tipo === 'exoneraciones') {
            const detalle = registro.motivo === 'Otro' && registro.otro_motivo
                ? `<br><small class="text-muted">${escapar(registro.otro_motivo)}</small>` : '';
            return `<tr>${celdaFecha}
                <td>${celdaBeneficiario(registro)}</td>
                <td>${escapar(registro.motivo || '')}${detalle}</td>
                <td>${escapar(registro.carnet_discapacidad || '')}</td>
                ${celdaAcciones(tipo, id)}</tr>`;
        }

        if (tipo === 'fames') {
            const ayuda = registro.tipo_ayuda === 'Otros' && registro.otro_tipo
                ? `<br><small class="text-muted">${escapar(registro.otro_tipo)}</small>` : '';
            return `<tr>${celdaFecha}
                <td>${celdaBeneficiario(registro)}</td>
                <td>${escapar(registro.nombre_patologia || 'Sin patología')}</td>
                <td>${escapar(registro.tipo_ayuda || '')}${ayuda}</td>
                ${celdaAcciones(tipo, id)}</tr>`;
        }

        // embarazadas
        const estados = { 'En Proceso': 'bg-primary', 'Aprobado': 'bg-success', 'Rechazado': 'bg-danger' };
        const clase = estados[registro.estado] || 'bg-secondary';
        return `<tr>${celdaFecha}
            <td>${celdaBeneficiario(registro)}</td>
            <td>${escapar(registro.nombre_patologia || 'Sin patología')}</td>
            <td class="text-center">${escapar(registro.semanas_gest)}</td>
            <td><span class="badge ${clase}">${escapar(registro.estado || '')}</span></td>
            ${celdaAcciones(tipo, id)}</tr>`;
    }

    function pintar(tipo) {
        const config = TABS[tipo];
        const tbody = document.getElementById(config.tbody);
        if (tbody) tbody.innerHTML = estado[tipo].registros.map((registro) => fila(tipo, registro)).join('');
    }

    function iniciarTabla(tipo) {
        if (!window.DataTableHelper) return;
        const config = TABS[tipo];
        estado[tipo].tabla = DataTableHelper.inicializar(config.tabla, {
            titulo: config.titulo,
            orden: [[0, 'desc']],
            pageLength: 10,
            columnasExport: config.columnasExport,
            columnDefs: config.columnDefs,
        });
    }

    async function cargar(tipo) {
        const est = estado[tipo];
        const config = TABS[tipo];
        if (!config) return;
        try {
            if (est.tabla) { est.tabla.destroy(); est.tabla = null; }
            est.registros = await apiFetch(`${BASE_URL}api/trabajo-social/listar?tipo=${tipo}`);
            pintar(tipo);
            iniciarTabla(tipo);
            est.cargada = true;
        } catch (error) {
            AlertManager.error('No se pudo cargar', error.mensaje || 'Error inesperado.');
        }
    }

    function htmlDetalle(tipo, registro) {
        const base = `
            <p><b>Fecha:</b> ${escapar(Formato.fecha(registro.fecha_creacion || ''))}</p>
            <p><b>Beneficiario:</b> ${escapar(registro.beneficiario)} (${escapar(registro.cedula_beneficiario)})</p>
            <p><b>Atendió:</b> ${escapar(registro.empleado)} (${escapar(registro.cedula_empleado || 'sin cédula')})</p>`;

        if (tipo === 'becas') {
            return base + `
                <p><b>Banco:</b> ${escapar(registro.nombre_banco || 'No identificado')} (${escapar(registro.tipo_banco || '')})</p>
                <p><b>Cuenta BCV:</b> ${escapar(registro.cta_bcv || '')}</p>
                <p><b>Planilla:</b> ${escapar(registro.direccion_pdf || 'Sin archivo')}</p>`;
        }
        if (tipo === 'exoneraciones') {
            return base + `
                <p><b>Motivo:</b> ${escapar(registro.motivo || '')}${registro.motivo === 'Otro' ? ' — ' + escapar(registro.otro_motivo || '') : ''}</p>
                <p><b>Carnet de discapacidad:</b> ${escapar(registro.carnet_discapacidad || '')}</p>
                <p><b>Carta:</b> ${escapar(registro.direccion_carta || 'Sin archivo')}</p>
                <p><b>Estudio socioeconómico:</b> ${registro.direccion_estudiose ? escapar(registro.direccion_estudiose) : '<span class="text-warning">Pendiente</span>'}</p>`;
        }
        if (tipo === 'fames') {
            return base + `
                <p><b>Patología:</b> ${escapar(registro.nombre_patologia || 'Sin patología')}</p>
                <p><b>Tipo de ayuda:</b> ${escapar(registro.tipo_ayuda || '')}${registro.tipo_ayuda === 'Otros' ? ' — ' + escapar(registro.otro_tipo || '') : ''}</p>`;
        }
        // embarazadas
        return base + `
            <p><b>Patología:</b> ${escapar(registro.nombre_patologia || 'Sin patología')}</p>
            <p><b>Semanas de gestación:</b> ${escapar(registro.semanas_gest)}</p>
            <p><b>Estado:</b> ${escapar(registro.estado || '')}</p>
            <p><b>Código Patria:</b> ${escapar(registro.codigo_patria ?? 'No registrado')}</p>
            <p><b>Serial Patria:</b> ${escapar(registro.serial_patria ?? 'No registrado')}</p>`;
    }

    const PESTANAS = {
        'cons-becas-tab': 'becas',
        'cons-exoneracion-tab': 'exoneraciones',
        'cons-fames-tab': 'fames',
        'cons-embarazadas-tab': 'embarazadas',
    };

    // Las pestañas se cargan perezosamente: DataTables necesita el
    // contenedor visible para dimensionar bien sus columnas.
    document.querySelectorAll('#tsConsultaTabs [data-bs-toggle="tab"]').forEach((boton) => {
        boton.addEventListener('shown.bs.tab', () => {
            const tipo = PESTANAS[boton.id];
            if (tipo && !estado[tipo].cargada) cargar(tipo);
        });
    });

    // Delegación de acciones (detalle / editar / eliminar) en las filas.
    document.getElementById('tsConsultaContenido')?.addEventListener('click', async (evento) => {
        const boton = evento.target.closest('button[data-tipo][data-id]');
        if (!boton) return;
        const tipo = boton.dataset.tipo;
        const config = TABS[tipo];
        if (!config) return;
        const registro = estado[tipo].registros.find(
            (item) => String(item[config.clave]) === boton.dataset.id
        );
        if (!registro) return;

        if (boton.classList.contains('js-detalle')) {
            const titulos = {
                becas: 'Beca',
                exoneraciones: 'Exoneración',
                fames: 'FAMES',
                embarazadas: 'Gestión de embarazo',
            };
            Swal.fire({
                title: titulos[tipo] || config.titulo,
                html: `<div class="text-start">${htmlDetalle(tipo, registro)}</div>`,
                confirmButtonText: 'Cerrar',
            });
            return;
        }

        if (boton.classList.contains('js-editar')) {
            window.TrabajoSocialEditar?.abrir(tipo, registro);
            return;
        }

        // Ver estudio socioeconómico (solo exoneraciones): abre el PDF en
        // pestaña nueva o avisa que todavía no se ha realizado.
        if (boton.classList.contains('js-ver-estudio')) {
            const ruta = registro.direccion_estudiose;
            if (!ruta) {
                AlertManager.warning(
                    'Estudio no realizado',
                    `Aún no se ha realizado el estudio socioeconómico de ${registro.beneficiario || 'esta beneficiaria'}.`
                );
                return;
            }
            window.open(BASE_URL + ruta, '_blank', 'noopener');
            return;
        }

        const etiqueta = {
            becas: 'la beca',
            exoneraciones: 'la exoneración',
            fames: 'el FAMES',
            embarazadas: 'la gestión de embarazo',
        }[tipo];
        const confirmacion = await AlertManager.confirm(
            `¿Eliminar ${etiqueta}?`,
            `Se eliminará ${etiqueta} de ${registro.beneficiario || 'este beneficiario'} junto con su solicitud de servicio. Esta acción no se puede deshacer.`,
            'Sí, eliminar',
            'Cancelar'
        );
        if (confirmacion.isConfirmed) {
            try {
                await apiFetch(BASE_URL + 'api/trabajo-social/eliminar', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ tipo, id: Number(registro[config.clave]) }),
                });
                AlertManager.success('Registro eliminado', `Se eliminó ${etiqueta} correctamente.`);
                cargar(tipo);
                if (window.TrabajoSocialStats) window.TrabajoSocialStats.cargar();
            } catch (error) {
                if (error.codigo === 'IN_USE') {
                    AlertManager.warning('No se puede eliminar', error.mensaje);
                } else if (error.codigo === 'NOT_FOUND') {
                    AlertManager.warning('Registro no encontrado', 'Puede haber sido eliminado. Actualizando la lista…');
                    cargar(tipo);
                } else {
                    AlertManager.error('No se pudo eliminar', error.mensaje || 'Error inesperado.');
                }
            }
        }
    });

    document.getElementById('btn-recargar-trabajo-social')?.addEventListener('click', () => {
        const cargadas = Object.keys(estado).filter((tipo) => estado[tipo].cargada);
        (cargadas.length ? cargadas : ['becas']).forEach(cargar);
    });

    window.TrabajoSocialConsultar = { cargar };
    cargar('becas');
});
