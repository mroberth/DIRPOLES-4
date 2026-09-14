// dist/js/core/apiFetch.js
// ------------------------------------------------------------------
// Helper único para consumir la API del sistema (contrato Respuesta).
// Cópialo en cualquier módulo; todos usan el mismo patrón:
//
//   const datos = await apiFetch(BASE_URL + 'api/productos/listar');
//   await apiFetch(BASE_URL + 'api/productos/crear', {
//       method: 'POST',
//       headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
//       body: JSON.stringify({ nombre: 'X' }),
//   });
//
// Garantías:
//   - Envía Accept: application/json y las cookies de sesión (same-origin).
//   - 401 → navega a la redirección que manda el middleware (datos.redireccion).
//   - Si la respuesta no es exito, LANZA el objeto error del contrato
//     ({ codigo, estado, mensaje }) para que el módulo programe contra error.codigo.
// ------------------------------------------------------------------
async function apiFetch(url, opciones = {}) {
    const resp = await fetch(url, {
        headers: { 'Accept': 'application/json' },
        credentials: 'same-origin', // envía sesión + cookies JWT
        cache: 'no-store',          // la API nunca debe servirse de caché
        ...opciones,
    });

    const contentType = resp.headers.get('Content-Type') || '';
    const esJson = contentType.includes('json');

    // Sesión expirada → el middleware responde 401 con datos.redireccion
    if (resp.status === 401 && esJson) {
        const cuerpo = await resp.json();
        window.location.href = cuerpo.datos?.redireccion ?? BASE_URL + 'login';
        return;
    }

    // Respuesta NO JSON (HTML de error fatal, timeout del proxy, etc.):
    // no intentar parsear; lanzar el contrato de error mínimo.
    if (!esJson) {
        throw {
            codigo: 'INTERNAL_SERVER_ERROR',
            estado: resp.status,
            mensaje: resp.ok
                ? 'El servidor devolvió una respuesta no válida.'
                : 'Error del servidor (' + resp.status + ').',
        };
    }

    const cuerpo = await resp.json();

    if (!resp.ok || !cuerpo.exito) {
        // CONTRATO DE ERROR: { exito:false, error:{ codigo, estado, mensaje } }
        throw cuerpo.error ?? {
            codigo: 'INTERNAL_SERVER_ERROR',
            estado: resp.status,
            mensaje: 'Error inesperado',
        };
    }
    return cuerpo.datos;
}