// dist/js/core/formato.js
// ------------------------------------------------------------------
// Helpers de FORMATO compartidos por todos los módulos (frontend).
//
//   Formato.fechaHora('2026-09-20 14:54:09') → '20/09/2026 14:54'
//   Formato.fecha('2026-09-20 14:54:09')     → '20/09/2026'
//   Formato.hora('14:54:09')                 → '14:54'
//
// Acepta tanto 'YYYY-MM-DD HH:MM:SS' (MySQL) como ISO 'YYYY-MM-DDTHH:MM:SS'.
// Si el valor no es una fecha válida, devuelve el texto original tal cual.
// ------------------------------------------------------------------
window.Formato = (function () {
    'use strict';

    const dos = (n) => String(n).padStart(2, '0');

    function aFecha(valor) {
        if (!valor) return null;
        const fecha = new Date(String(valor).replace(' ', 'T'));
        return isNaN(fecha.getTime()) ? null : fecha;
    }

    /** Fecha y hora sin segundos: '20/09/2026 14:54'. */
    function fechaHora(valor) {
        const fecha = aFecha(valor);
        if (!fecha) return valor == null ? '' : String(valor);
        return `${dos(fecha.getDate())}/${dos(fecha.getMonth() + 1)}/${fecha.getFullYear()} ${dos(fecha.getHours())}:${dos(fecha.getMinutes())}`;
    }

    /** Solo fecha: '20/09/2026'. */
    function fecha(valor) {
        const fecha = aFecha(valor);
        if (!fecha) return valor == null ? '' : String(valor);
        return `${dos(fecha.getDate())}/${dos(fecha.getMonth() + 1)}/${fecha.getFullYear()}`;
    }

    /** Solo hora sin segundos: '14:54'. */
    function hora(valor) {
        if (!valor) return '';
        const partes = String(valor).split(':');
        return partes.length >= 2 ? `${partes[0]}:${partes[1]}` : String(valor);
    }

    return { fechaHora, fecha, hora };
})();
