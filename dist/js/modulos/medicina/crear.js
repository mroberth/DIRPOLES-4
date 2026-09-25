// dist/js/modulos/medicina/crear.js
// Solo la lógica de ESTA pantalla: catálogos, filas de insumos, enviar y
// refrescar. Las validaciones viven en validaciones.js.
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('form-medicina');
    if (!form) return;

    // Tour guiado (Driver.js).
    const btnAyuda = document.getElementById('btn-ayuda');
    if (btnAyuda && window.MedicinaTour) {
        btnAyuda.addEventListener('click', () => window.MedicinaTour.iniciar());
    }

    // Validador reutilizable (si todavía no existe el archivo, seguimos sin él).
    const validador = window.MedicinaValidaciones
        ? window.MedicinaValidaciones.configurar(form)
        : null;

    const tbodyInsumos = document.getElementById('lista_insumos');
    const btnAgregarInsumo = document.getElementById('btnAgregarInsumo');

    // -----------------------------------------------------------------
    // 1) Catálogos (beneficiarios + patologías + insumos) en UNA llamada.
    //    GET api/medicina/catalogos
    //    → { exito:true, datos:{ patologias:[...], beneficiarios:[...], insumos:[...] } }
    // -----------------------------------------------------------------
    function pintarSelect(selectId, items, placeholder, mapear) {
        const sel = document.getElementById(selectId);
        if (!sel) return;

        sel.innerHTML = `<option value="">${placeholder}</option>`;
        items.forEach((it) => {
            const opt = document.createElement('option');
            const { value, texto } = mapear(it);
            opt.value = value;
            opt.textContent = texto;
            sel.appendChild(opt);
        });
    }

    (async () => {
        try {
            const catalogo = await apiFetch(BASE_URL + 'api/medicina/catalogos');

            pintarSelect(
                'id_beneficiario',
                catalogo.beneficiarios || [],
                'Seleccione…',
                (b) => ({
                    value: b.id_beneficiario,
                    texto: `${b.tipo_cedula || ''}-${b.cedula || ''} ${b.nombres || ''} ${b.apellidos || ''}`.trim(),
                })
            );

            pintarSelect(
                'id_patologia',
                catalogo.patologias || [],
                'Seleccione…',
                (p) => ({
                    value: p.id_patologia,
                    texto: p.nombre_patologia || p.nombre || '',
                })
            );

            // El catálogo de insumos alimenta las filas dinámicas y el validador.
            const insumos = catalogo.insumos || [];
            const contenedor = document.getElementById('insumosDisponibles');
            if (contenedor) contenedor.dataset.insumos = JSON.stringify(insumos);
            if (validador && validador.setInsumos) validador.setInsumos(insumos);

            // Recién después de insertar las opciones inicializamos Select2.
            if (window.initSelect2) window.initSelect2(form);
        } catch (e) {
            console.error('No se cargaron los catálogos de medicina:', e);
            AlertManager.error('Catálogos', 'No se pudieron cargar beneficiarios, patologías ni insumos.');
        }
    })();

    // -----------------------------------------------------------------
    // 2) Filas dinámicas de insumos (opcional).
    //    Cada fila: select de insumo (Select2) + cantidad + botón quitar.
    //    Sin name en los inputs: FormData no los envía; el JS arma el array.
    // -----------------------------------------------------------------
    function insumosCatalogo() {
        const contenedor = document.getElementById('insumosDisponibles');
        try {
            return JSON.parse(contenedor ? contenedor.dataset.insumos : '[]') || [];
        } catch (e) {
            return [];
        }
    }

    function agregarFila() {
        const insumos = insumosCatalogo();
        if (!insumos.length) {
            AlertManager.warning('Sin insumos', 'No hay insumos disponibles en el inventario médico.');
            return;
        }

        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>
                <select class="form-select select2 select-insumo" data-placeholder="Seleccione el insumo…">
                    <option value="">Seleccione…</option>
                </select>
            </td>
            <td>
                <input type="number" class="form-control input-cantidad" min="1" step="1" value="1">
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-outline-danger btn-quitar-insumo" title="Quitar insumo">
                    <i class="fas fa-trash"></i>
                </button>
            </td>`;

        const select = tr.querySelector('.select-insumo');
        insumos.forEach((i) => {
            const opt = document.createElement('option');
            opt.value = i.id_insumo;
            opt.textContent = `${i.nombre_insumo} (disponible: ${i.cantidad})`;
            select.appendChild(opt);
        });

        tbodyInsumos.appendChild(tr);
        if (window.initSelect2) window.initSelect2(tr);
        // No validamos al agregar: la fila empieza vacía a propósito; la
        // validación ocurre al elegir insumo, cambiar cantidades y al enviar.
    }

    function destruirSelect2De(tr) {
        const select = tr.querySelector('.select-insumo');
        if (select && typeof jQuery !== 'undefined' && $.fn && $.fn.select2 && $(select).data('select2')) {
            $(select).select2('destroy');
        }
    }

    function vaciarFilasInsumos() {
        if (!tbodyInsumos) return;
        Array.from(tbodyInsumos.querySelectorAll('tr')).forEach(destruirSelect2De);
        tbodyInsumos.innerHTML = '';
    }

    btnAgregarInsumo && btnAgregarInsumo.addEventListener('click', agregarFila);

    tbodyInsumos && tbodyInsumos.addEventListener('click', function (ev) {
        const btn = ev.target.closest('.btn-quitar-insumo');
        if (!btn) return;
        const tr = btn.closest('tr');
        if (tr) {
            destruirSelect2De(tr);
            tr.remove();
        }
        if (validador) validador.validarInsumos();
    });

    // El botón "Limpiar" (reset) también descarta las filas de insumos.
    form.addEventListener('reset', () => setTimeout(vaciarFilasInsumos, 0));

    // -----------------------------------------------------------------
    // 3) Enviar.
    // -----------------------------------------------------------------
    form.addEventListener('submit', async function (ev) {
        ev.preventDefault();

        if (validador) {
            const ok = await validador.validarTodo();
            if (!ok) {
                AlertManager.error('Formulario incompleto', 'Corrige los campos resaltados antes de continuar.');
                return;
            }
        }

        const datos = Object.fromEntries(new FormData(form).entries());
        datos.id_beneficiario = parseInt(datos.id_beneficiario, 10);
        datos.id_patologia = parseInt(datos.id_patologia, 10);

        // El detalle de insumos se arma desde las filas de la tabla.
        datos.insumos = Array.from(tbodyInsumos ? tbodyInsumos.querySelectorAll('tr') : [])
            .map((tr) => ({
                id_insumo: parseInt(tr.querySelector('.select-insumo').value, 10),
                cantidad: parseInt(tr.querySelector('.input-cantidad').value, 10),
            }));

        try {
            await apiFetch(BASE_URL + 'api/medicina/crear', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(datos),
            });

            AlertManager.success('¡Registrada!', 'La consulta médica se guardó correctamente.');

            form.reset();
            vaciarFilasInsumos();

            // form.reset() no dispara 'change' de Select2 por sí solo: el helper
            // global lo hace y aquí lo reforzamos tras el reset.
            if (window.initSelect2) window.initSelect2(form);

            if (window.MedicinaStats) window.MedicinaStats.cargar();
        } catch (error) {
            if (error.codigo === 'ALREADY_EXISTS' || error.codigo === 'VALIDATION_ERROR') {
                AlertManager.warning('Revisa los datos', error.mensaje);
            } else if (error.codigo === 'NOT_FOUND') {
                AlertManager.warning('Registro no encontrado', error.mensaje);
            } else {
                AlertManager.error('Error', error.mensaje);
            }
        }
    });
});
