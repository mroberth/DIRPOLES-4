// dist/js/core/documentos.js
// ------------------------------------------------------------------
// DocumentosPDF — diálogo común para generar la Constancia de Atención y
// la Referencia a otra área (tercera puerta PDF) desde la columna
// Acciones de las consultas de Medicina, Orientación, Discapacidad,
// Psicología y Trabajo Social.
//
// Uso desde cualquier consultar.js:
//
//   window.DocumentosPDF.abrir({
//       tipo:    'constancia' | 'referencia',
//       ruta:    'medicina/constancia/15',   // relativa a BASE_URL
//       registro: { ... },                   // fila de la tabla (prellenado)
//       tramite: 'Texto inicial del trámite', // opcional
//   });
//
// Reglas:
//   - Trámite: opcional, máx. 120 caracteres (se recorta en backend).
//   - Hora: opcional, formato HH:MM; se prellena con la hora propia del
//     registro si el timestamp la trae, o con la hora actual si el
//     registro es de hoy; si no, queda vacía.
//   - Área: OBLIGATORIA solo para la referencia; lista desplegable con
//     las áreas del catálogo `servicio` pero también acepta texto libre.
//   - El PDF se abre en pestaña nueva (window.open), igual que los PDFs
//     de trabajo social.
// ------------------------------------------------------------------
window.DocumentosPDF = (function () {
    'use strict';

    // Áreas sugeridas (catálogo `servicio`, estatus 1). El usuario puede
    // escribir cualquier otra: el input es de texto con datalist.
    const AREAS = [
        'Psicologia', 'Medicina', 'Orientacion', 'Trabajo Social',
        'Discapacidad', 'General', 'Comedor', 'Gerente', 'Transporte',
    ];

    const escaparAtributo = (valor) => String(valor ?? '')
        .replace(/&/g, '&amp;').replace(/</g, '&lt;')
        .replace(/>/g, '&gt;').replace(/"/g, '&quot;');

    /** Hora inicial (HH:MM) del diálogo según la fila seleccionada. */
    function horaPrellenada(registro) {
        const fecha = String(registro?.fecha_creacion || '');
        const partes = fecha.split(' ');
        const dia = partes[0] || '';
        const reloj = partes[1] || '';
        if (reloj) {
            return Formato.hora(reloj); // timestamp propio del registro
        }
        if (dia) {
            const ahora = new Date();
            const dos = (n) => String(n).padStart(2, '0');
            const hoy = `${ahora.getFullYear()}-${dos(ahora.getMonth() + 1)}-${dos(ahora.getDate())}`;
            if (dia === hoy) {
                return Formato.hora(`${dos(ahora.getHours())}:${dos(ahora.getMinutes())}:00`);
            }
        }
        return '';
    }

    function abrir({ tipo, ruta, registro = {}, tramite = '' }) {
        if (!ruta) return Promise.resolve();
        const esReferencia = tipo === 'referencia';
        const preTramite = String(tramite ?? '');
        const preHora = horaPrellenada(registro);

        return Swal.fire({
            title: esReferencia ? 'Generar referencia' : 'Generar constancia de atención',
            html: `
                <div class="text-start">
                    <label for="doc-tramite" class="form-label">Trámite</label>
                    <input type="text" id="doc-tramite" class="swal2-input" maxlength="120"
                           placeholder="Motivo del trámite (opcional)"
                           value="${escaparAtributo(preTramite)}">
                    <label for="doc-hora" class="form-label">Hora</label>
                    <input type="time" id="doc-hora" class="swal2-input" value="${escaparAtributo(preHora)}">
                    ${esReferencia ? `
                    <label for="doc-area" class="form-label">Área de destino</label>
                    <input type="text" id="doc-area" class="swal2-input" list="doc-areas" maxlength="80"
                           placeholder="Ej.: Psicologia">
                    <datalist id="doc-areas">
                        ${AREAS.map((area) => `<option value="${escaparAtributo(area)}"></option>`).join('')}
                    </datalist>` : ''}
                </div>`,
            showCancelButton: true,
            confirmButtonText: 'Abrir PDF',
            cancelButtonText: 'Cancelar',
            focusConfirm: false,
            preConfirm: () => {
                const valorTramite = document.getElementById('doc-tramite')?.value.trim() ?? '';
                const hora = document.getElementById('doc-hora')?.value ?? '';
                const area = esReferencia ? (document.getElementById('doc-area')?.value.trim() ?? '') : '';
                if (hora && !/^\d{1,2}:\d{2}$/.test(hora)) {
                    Swal.showValidationMessage('La hora debe tener el formato HH:MM.');
                    return false;
                }
                if (esReferencia && !area) {
                    Swal.showValidationMessage('El área de destino es obligatoria.');
                    return false;
                }
                return { tramite: valorTramite, hora, area };
            },
        }).then((resultado) => {
            if (!resultado.isConfirmed || !resultado.value) return;
            const { tramite: texto, hora, area } = resultado.value;
            const parametros = new URLSearchParams();
            if (texto) parametros.set('tramite', texto);
            if (hora) parametros.set('hora', hora);
            if (area) parametros.set('area', area);
            window.open(`${BASE_URL}${ruta}?${parametros.toString()}`, '_blank', 'noopener');
        });
    }

    return { abrir };
})();
