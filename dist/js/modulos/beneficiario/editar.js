// dist/js/modulos/beneficiario/editar.js
// Edición de beneficiario en un MODAL. Reutiliza BeneficiarioValidaciones.
window.BeneficiarioEditar = (function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('form-editar');
        const modalEl = document.getElementById('modalEditar');
        if (!form || !modalEl) return;

        const modal = (typeof bootstrap !== 'undefined') ? new bootstrap.Modal(modalEl) : null;
        const opciones = { idExcluir: 0 };
        const validador = window.BeneficiarioValidaciones.configurar(form, opciones);

        const setValor = (id, valor) => {
            const el = document.getElementById(id);
            if (el) el.value = valor == null ? '' : valor;
        };

        // Catálogo de PNF (una vez)
        (async () => {
            try {
                const pnfs = await apiFetch(BASE_URL + 'api/beneficiarios/pnfs');
                const sel = document.getElementById('id_pnf');
                sel.innerHTML = '<option value="">Seleccione…</option>';
                pnfs.forEach((p) => {
                    const opt = document.createElement('option');
                    opt.value = p.id_pnf;
                    opt.textContent = p.nombre_pnf;
                    sel.appendChild(opt);
                });
                if (window.initSelect2) window.initSelect2(form);
            } catch (e) {
                console.error('No se cargaron los PNF:', e);
            }
        })();

        modalEl.addEventListener('shown.bs.modal', function () {
            if (window.initSelect2) window.initSelect2(modalEl);
        });

        window.BeneficiarioEditar = {
            async abrir(id) {
                try {
                    const b = await apiFetch(BASE_URL + 'api/beneficiarios/obtener/' + encodeURIComponent(id));

                    setValor('id_beneficiario', b.id_beneficiario);
                    setValor('tipo_cedula', b.tipo_cedula);
                    setValor('cedula', b.cedula);
                    setValor('nombres', b.nombres);
                    setValor('apellidos', b.apellidos);
                    setValor('correo', b.correo);
                    setValor('telefono', b.telefono);
                    setValor('genero', b.genero);
                    setValor('id_pnf', b.id_pnf);
                    const partsSec = (b.seccion || '').split('-');
                    setValor('seccion_numero', partsSec[0] || '');
                    setValor('seccion_sede', partsSec[1] || '');
                    setValor('seccion', b.seccion || '');
                    setValor('fecha_nac', b.fecha_nac);
                    setValor('direccion', b.direccion);
                    setValor('estatus', String(b.estatus));

                    const codigo = document.getElementById('beneficiarioCodigo');
                    if (codigo) {
                        codigo.textContent = `${b.nombres || ''} ${b.apellidos || ''} · ${b.tipo_cedula || ''}-${b.cedula || ''}`;
                    }

                    opciones.idExcluir = Number(b.id_beneficiario);
                    validador.limpiar();

                    if (modal) modal.show();
                    if (window.initSelect2) window.initSelect2(modalEl);
                } catch (error) {
                    console.error('Abrir edición:', error);
                    if (error.codigo === 'NOT_FOUND') {
                        AlertManager.warning('Beneficiario no encontrado',
                            'Puede haber sido eliminado. Actualizando la lista…');
                        if (window.BeneficiarioConsultar) window.BeneficiarioConsultar.recargar();
                    } else {
                        AlertManager.error('Error', error.mensaje || 'No se pudo cargar el beneficiario.');
                    }
                }
            }
        };

        form.addEventListener('submit', async function (ev) {
            ev.preventDefault();

            const ok = await validador.validarTodo();
            if (!ok) {
                AlertManager.error('Formulario incompleto', 'Corrige los campos resaltados antes de continuar.');
                return;
            }

            const datos = Object.fromEntries(new FormData(form).entries());
            datos.id_beneficiario = parseInt(datos.id_beneficiario, 10);
            datos.id_pnf = parseInt(datos.id_pnf, 10);
            datos.estatus = parseInt(datos.estatus, 10);

            try {
                await apiFetch(BASE_URL + 'api/beneficiarios/actualizar', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify(datos),
                });

                if (modal) modal.hide();
                AlertManager.success('¡Actualizado!', 'Los datos del beneficiario se guardaron correctamente.');

                if (window.BeneficiarioConsultar) window.BeneficiarioConsultar.recargar();
                if (window.BeneficiarioStats) window.BeneficiarioStats.cargar();
            } catch (error) {
                if (error.codigo === 'ALREADY_EXISTS' || error.codigo === 'VALIDATION_ERROR') {
                    AlertManager.warning('Revisa los datos', error.mensaje);
                } else {
                    AlertManager.error('Error', error.mensaje);
                }
            }
        });
    });

    return {}; // abrir() se define en DOMContentLoaded
})();
