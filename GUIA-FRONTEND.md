# GUIA-FRONTEND.md — Cómo construir el frontend de un módulo en DIRPOLES-4

> Esta guía es el **contrato de frontend** del sistema. Léela COMPLETA antes de
> crear o editar cualquier vista o JavaScript de módulo. Su objetivo es que el
> código nuevo sea **idéntico en estilo y calidad** al módulo canónico
> (**Empleados**), no código genérico inventado.
>
> Regla de oro: **primero copia el patrón real, luego adapta los nombres.**
> Los archivos de Empleados son la fuente de verdad:
>
> - `app/Views/empleados/crear.php` · `app/Views/empleados/consultar.php`
> - `app/Views/empleados/components/stats.php`
> - `dist/js/modulos/empleado/{stats,validaciones,tour,crear,editar,consultar}.js`
>
> Módulos que ya replican este patrón (más ejemplos): `beneficiario`, `cita`,
> `horario`, `psicologia`, `bitacora`, `configuracion`.

---

## 1. Lo que YA existe (NO lo recrees)

`app/Views/template/script.php` carga estos helpers en **todas** las páginas
(excepto login). Nunca los copies a un módulo, nunca los reinicialices a mano,
nunca escribas `fetch()`, `Swal.fire()` de utilidad, `$(...).DataTable()` ni
`$(...).select2()` directos:

| Helper global | Archivo | Uso |
|---|---|---|
| `apiFetch(url, opciones)` | `dist/js/core/apiFetch.js` | Única forma de llamar a la API. Devuelve `cuerpo.datos` en éxito y **lanza** `{ codigo, estado, mensaje }` en fallo. El 401 lo redirige solo. |
| `AlertManager.success/error/warning/info/confirm/loading/close` | `dist/js/core/AlertManager.js` | Todo feedback al usuario. `confirm()` devuelve la promesa de SweetAlert2 (`isConfirmed`). Los 429 ya los intercepta él solo. |
| `DataTableHelper.inicializar(selector, opciones)` | `dist/js/core/datatable.js` | Única forma de crear una tabla: idioma local, `autoWidth:false`, botones Excel/PDF con `columnasExport` (sin Acciones) y `limpiarHtml`. |
| `window.initSelect2(scope)` | `dist/js/core/select-2-init.js` | Única forma de inicializar/re-inicializar `.select2` (destruye y recrea, ponle `dropdownParent` en modales y ya escucha el `reset` del formulario). |
| `window.Formato.fecha/fechaHora/hora` | `dist/js/core/formato.js` | Fechas MySQL **SIEMPRE** por aquí en tablas, detalle y modales; nunca la fecha cruda en la interfaz (ver regla 7 de §11). |
| `window.BASE_URL` | inline en `script.php` | Prefijo de toda URL: `BASE_URL + 'api/...'`. |
| Driver.js (IIFE) | `plugins/driver.js/` | Tour guiado; se expone como `window.driver.js.driver(...)`. |

> **Ojo**: `dist/js/core/modalManager.js` existe pero **NO está en
> `script.php`**. El patrón canónico de modal es el de `consultar.php` de
> Empleados (modal Bootstrap propio + `new bootstrap.Modal(el)`). No referenciar
> `ModalManager` en módulos nuevos.

---

## 2. Estructura de archivos de un módulo CRUD

```
app/Views/<modulo>s/                       ← carpeta en PLURAL (como 'empleados')
  crear.php                                ← página del formulario
  consultar.php                            ← página de la tabla + modal de edición
  components/
    stats.php                              ← tarjetas [data-stat] (si aplica)

dist/js/modulos/<modulo>/                  ← carpeta en SINGULAR (como 'empleado')
  stats.js        window.<Modulo>Stats
  validaciones.js window.<Modulo>Validaciones
  tour.js         window.<Modulo>Tour
  crear.js        (solo lógica de la pantalla crear)
  editar.js       window.<Modulo>Editar   (solo lógica del modal)
  consultar.js    window.<Modulo>Consultar (solo lógica de la tabla)
```

- **Un namespace por archivo** en `window` con PascalCase + nombre de parte.
  Ninguna variable global suelta, ningún `const` global compartido entre archivos.
- Si la pantalla no tiene tabla → no existe `consultar.js`. Si no tiene
  formulario → no existen `validaciones.js` ni `tour.js`.
- Orden de inclusión en la vista (siempre con `defer`, después de `script.php`):

```html
<script src="<?= BASE_URL ?>dist/js/modulos/<modulo>/stats.js" defer></script>
<script src="<?= BASE_URL ?>dist/js/modulos/<modulo>/validaciones.js" defer></script>
<script src="<?= BASE_URL ?>dist/js/modulos/<modulo>/tour.js" defer></script>
<script src="<?= BASE_URL ?>dist/js/modulos/<modulo>/crear.js" defer></script>   <!-- o editar.js -->
<script src="<?= BASE_URL ?>dist/js/modulos/<modulo>/consultar.js" defer></script>
```

- Toda pantalla debe funcionar aunque **no** esté el archivo que no le toca:
  por eso cada JS arranca con guardas `if (!elemento) return;` y las llamadas
  entre archivos son `if (window.<X>) window.<X>.y();`.

---

## 3. Anatomía de `crear.php`

Fuente: `app/Views/empleados/crear.php`. Estructura fija:

1. `$titulo = '...';` **ANTES** de `include 'app/Views/template/head.php';`.
2. `body#wrapper` → `sidebar.php` → `content-wrapper` → `header.php`.
3. Cabecera: `<h1 class="h3 mb-0">` + botones (`#btn-ayuda` con
   `btn-info`, enlace a `.../consultar` con `btn-secondary`).
4. `include '.../components/stats.php'` (si el módulo tiene tarjetas).
5. `.card.shadow` → `<form id="form-<modulo>" novalidate>` → `row g-3` de campos.
6. Botones: `submit` (`btn-primary`) + `reset` (`btn-outline-secondary`).
7. `footer.php` → `script.php` → scripts del módulo con `defer`.

**Contrato de cada campo validable** (obligatorio, identidad `name` = `id`):

```html
<div class="col-md-4">
    <label class="form-label">Correo electrónico *</label>   <!-- * si es obligatorio -->
    <input type="email" name="correo" id="correo" class="form-control" maxlength="50" required>
    <div id="correoError" class="form-text text-danger"></div>
</div>

<div class="col-md-4">
    <label class="form-label">Tipo de empleado *</label>
    <select name="id_tipo_empleado" id="id_tipo_empleado"
            class="form-select select2" data-placeholder="Seleccione…" required>
        <option value="">Cargando…</option>                 <!-- se llena por API -->
    </select>
    <div id="id_tipo_empleadoError" class="form-text text-danger"></div>
</div>
```

Reglas del formulario:
- `novalidate` siempre (la validación es nuestra, no la del navegador).
- `id="<campo>Error"` **exactamente igual** al `id` del campo + `Error`
  (`validaciones.js` lo busca con `campo.id + 'Error'`).
- Select2: clase `select2` + `data-placeholder`; los `<select>` estáticos
  normales van sin la clase (p. ej. `estatus`).
- Inputs numéricos: `inputmode="numeric"` y `maxlength` real de BD.
- Contraseña: `input-group` con `#btnTogglePassword` + `#icon-eye`/`#icon-eye-slash`
  (el toggle lo implementa `validaciones.js`, solo hace falta el HTML).
- Campos de catálogo vacíos con `<option value="">Cargando…</option>`:
  los llena `crear.js` por API **antes** de `initSelect2`.

---

## 4. Anatomía de `consultar.php`

Fuente: `app/Views/empleados/consultar.php`:

1. Igual que crear, pero los botones son `#btn-recargar` (outline) + enlace a
   `.../crear` (primary).
2. `include components/stats.php`.
3. Tabla **con `thead` completo y `tbody` VACÍO** (sin filas y sin `colspan`;
   el estado vacío lo dibuja DataTables):

```html
<table id="tabla<Modulos>" class="table table-bordered table-hover align-middle" width="100%">
    <thead class="table-light">
        <tr>
            <th>Columna</th> … 
            <th class="text-center">Acciones</th>      <!-- SIEMPRE la última -->
        </tr>
    </thead>
    <tbody id="tbody<Modulos>"></tbody>
</table>
```

4. Después del cierre de `content`, el **modal de edición** (`#modalEditar`):
   - `modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable`,
     `data-bs-backdrop="static"`, header con degradado (`bg-gradient-primary`)
     y subtítulo `<small id="<modulo>Codigo">`.
   - `<form id="form-editar" novalidate>` con
     `<input type="hidden" name="id_<id>" id="id_<id>">`.
   - **Mismos campos, mismos `id` y mismos `<div id="...Error">` que el
     formulario de crear** (por eso `validaciones.js` sirve para ambas
     pantallas; solo cambia que la contraseña es opcional y no lleva `*`).
   - Footer: Cancelar (`data-bs-dismiss`) + `submit` Guardar cambios.
5. `script.php` + scripts del módulo (`stats`, `validaciones`, `editar`,
   `consultar`).

---

## 5. `components/stats.php`

Plantilla: `app/Views/empleados/components/stats.php`. Array `$cards` con
`color`, `titulo`, `stat` e `icon`; cada tarjeta imprime
`<span data-stat="<?= htmlspecialchars($card['stat']) ?>">0</span>`.
La clave `stat` debe coincidir **exactamente** con una clave del JSON de
`api/<modulo>/stats`. Nunca imprimir el número en PHP.

---

## 6. `validaciones.js` — el archivo más delicado

Plantilla: `dist/js/modulos/empleado/validaciones.js`. Patrón obligatorio:

```javascript
window.<Modulo>Validaciones = (function () {
    'use strict';

    const RX = { /* regex de nombre, correo, teléfono, clave… */ };

    function marcarSelect2(campo, conError) { /* pinta .select2-selection */ }
    function mostrarError(campo, msg)  { /* <id>Error + is-invalid  -is-valid */ }
    function limpiarError(campo)       { /* limpia <id>Error + is-valid -is-invalid */ }
    function limpiarTodo(form)         { /* quita TODOS los estados del form */ }

    function configurar(form, opciones = {}) {
        const c = { /* form.querySelector('#campo') por cada campo */ };

        async function consultar(url, datos) { /* apiFetch POST JSON */ }

        function validarCampo() { /* return true|false; SIEMPRE empieza con
                                      'if (!c.campo) return true;' */ }
        async function validarCampoRemoto() { /* local + API con id_excluir */ }

        async function validarTodo() { /* todos los validadores, .every() */ }

        // eventos en vivo: input/change + debounce SOLO en los remotos
        // eventos Select2 por jQuery: 'change select2:select select2:clear'
        // form 'reset' → limpiarTodo
        // toggle de contraseña si #btnTogglePassword existe

        return { validarTodo, limpiar: () => limpiarTodo(form), campos: c };
    }

    return { configurar, mostrarError, limpiarError };
})();
```

Reglas duras (todas están implementadas en Empleados, respétalas):

1. **Omisión por campo ausente**: cada validador empieza con
   `if (!c.campo) return true;`. Así UN MISMO archivo sirve para crear y para
   editar aunque los formularios no tengan exactamente los mismos campos.
2. **`opciones` se leen en cada validación** (no se capturan al inicio):
   `const idExcluirActual = () => Number(opciones.idExcluir || 0);`
   para que `editar.js` pueda cambiar `idExcluir` al abrir el modal con otro
   registro sin re-inicializar el validador.
3. **Validación remota** (unicidad/disponibilidad): POST a
   `api/<modulo>/validar_<campo>` con `{ ..., id_excluir: idExcluirActual() }`;
   si `r.existe` → `mostrarError` y `return false`. Envuelve en `try/catch` y
   aplica la regla de oro: **si la API falla (caída de red, 500, 429) el campo
   va en ROJO** con un mensaje tipo "No se pudo verificar … Intenta de nuevo."
   y `return false`; **jamás verde**: si no se verificó, no se afirma que el
   valor es válido. El backend sigue siendo la última barrera, y como
   `validarTodo()` se ejecuta en cada `submit`, reintentar guardar vuelve a
   consultar. Si `validarTodo()` incluye la llamada remota, debe ser `async`
   y sus llamadores deben usar `await`.
   En los errores de NEGOCIO del backend (400/404/409, p. ej. "ya está
   registrada" o "fuera del horario") se muestra `error.mensaje` tal cual.
4. **Debounce de 450 ms** solo en los validadores remotos (`cedula`, `correo`,
   `telefono`); los locales validan en cada `input`.
5. **Sanitizar antes de validar**: quitar no-dígitos en cédula/teléfono y
   anti-XSS (`v.replace(/<[^>]*>?/gm, '')`) en textos libres.
6. **Los `<select class="select2">` NO emiten `change` nativo**: además del
   `addEventListener('change', ...)`, engancha por jQuery
   `$(campo).on('change select2:select select2:clear', fn)`.
7. `mostrarError`/`limpiarError` siempre pintan también el `.select2-selection`
   vía `marcarSelect2()` (el `<select>` está oculto a 1px).
8. En el `submit` del formulario **siempre** se llama a `validarTodo()` otra
   vez, aunque en vivo ya se haya validado.
9. Nunca un `alert()` ni un `console.log` como feedback de usuario.
10. **Nunca reescribir `campo.value` en la validación EN VIVO**: si haces
    `campo.value = v.trim()` en cada `input`, el usuario no puede escribir
    espacios finales ("nada que agregar" se queda en "nadaqueagregar" porque
    cada tecla recorta el espacio final). Patrón: `validarTexto(campo, escribir = true)`
    y llamarlo con `false` desde el listener `input`; el trim/sanitizado se
    aplica SOLO en `validarTodo()` (al enviar). Igual para `serial` y
    cualquier otro valor normalizado.

> **Estado (2026-09-24)**: la regla 3 ya está aplicada en todos los módulos
> con validación remota: `empleado` (y `perfil`, que lo reutiliza),
> `beneficiario`, `cita` y `configuracion`.

---

## 7. `tour.js`

Plantilla: `dist/js/modulos/empleado/tour.js`.

- IIFE `window.<Modulo>Tour` con `{ iniciar }` y un array `PASOS` de
  `{ element: '#id', title, description }` describiendo **las reglas reales**
  del formulario (máx. de caracteres, formatos, obligatoriedad), no texto
  genérico.
- `iniciar()` construye el driver con textos en español
  (`nextBtnText/prevBtnText/doneBtnText`), `showProgress: true` y llama
  `driverObj.drive()`.
- **Siempre** mapea cada paso con la función `objetivo(selector)`: si el
  elemento es `SELECT.select2`, devuelve `el.nextElementSibling`
  (`.select2-container`); si el elemento no existe, devuelve el selector tal
  cual (Driver.js lo omite con gracia).
- `crear.js`/`editar.js` solo conectan el botón:
  `btnAyuda.addEventListener('click', () => window.<Modulo>Tour.iniciar());`

---

## 8. `stats.js`

Plantilla: `dist/js/modulos/empleado/stats.js` (28 líneas, se copia casi
literal):

- IIFE `window.<Modulo>Stats` con `cargar()`: si no hay `[data-stat]` en el
  DOM, `return` (no llama a la API para nada).
- **UNA sola** llamada a `apiFetch(BASE_URL + 'api/<modulo>/stats')` y rellena
  cada nodo por `data-stat` + `classList.add('fade-in')`.
- Se auto-ejecuta en `DOMContentLoaded` y se expone `cargar()` para que
  `crear.js`/`editar.js`/`consultar.js` la refresquen tras cada escritura.
- Errores: `console.error` y nada más (las stats son decorativas).

---

## 9. `crear.js`

Plantilla: `dist/js/modulos/empleado/crear.js`. Flujo exacto:

```
DOMContentLoaded
 ├─ const form = getElementById('form-<modulo>'); if (!form) return;
 ├─ #btn-ayuda → window.<Modulo>Tour.iniciar()
 ├─ const validador = window.<Modulo>Validaciones.configurar(form, { idExcluir: 0 })
 ├─ (async) cargar catálogos por apiFetch → pintar <option> → initSelect2(form)
 └─ form 'submit'
      ├─ ev.preventDefault()
      ├─ if (!await validador.validarTodo()) → AlertManager.error('Formulario incompleto', ...) y PARAR
      ├─ datos = Object.fromEntries(new FormData(form))
      ├─ castear números: datos.x = parseInt(datos.x, 10)
      ├─ try: apiFetch POST 'api/<modulo>/crear' (JSON body)
      │    ├─ AlertManager.success('¡Registrado!', '<msg con datos devueltos>')
      │    ├─ form.reset() → initSelect2(form)   ← reset no limpia Select2 a ojo
      │    └─ window.<Modulo>Stats.cargar()
      └─ catch: por error.codigo → warning para VALIDATION_ERROR/ALREADY_EXISTS, error para el resto
```

- El `catch` **nunca** muestra `error.mensaje` genérico para todo: los códigos
  de validación/duplicado van a `warning` (son culpa del usuario), el resto a `error`.
- El mensaje de éxito usa los datos **devueltos por el backend**, no los locales.

---

## 10. `editar.js` (modal)

Plantilla: `dist/js/modulos/empleado/editar.js`. Diferencias con crear:

- Guarda `if (!form || !modalEl) return;` y crea `const modal = new bootstrap.Modal(modalEl)`.
- `const opciones = { idExcluir: 0, claveOpcional: true };` y las pasa al
  validador (mutables: cambian en cada apertura).
- Expone `window.<Modulo>Editar = { async abrir(id) {...} }`:
  1. `apiFetch('api/<modulo>/obtener/' + id)` (recuerda: `{id}` llega como
     `$_GET['id']` en el backend).
  2. Rellena con un helper `setValor(id, valor)` (`''` si es null).
  3. Rellena el subtítulo del modal.
  4. `opciones.idExcluir = Number(registro.id_<pk>); validador.limpiar();`
  5. `modal.show();` y **después** `initSelect2(modalEl)`.
  6. `catch NOT_FOUND` → `AlertManager.warning(...)` + `window.<Modulo>Consultar.recargar()`
     (fila obsoleta); resto → `AlertManager.error`.
- `modalEl 'shown.bs.modal'` → `initSelect2(modalEl)` (el dropdown del modal
  necesita el elemento visible para calcular ancho/posición).
- `submit` → mismo flujo que crear contra `api/<modulo>/actualizar`, y al
  terminar: `modal.hide()` → success → `Consultar.recargar()` + `Stats.cargar()`.
  Campos opcionales vacíos se eliminan del payload:
  `if (!datos.clave) delete datos.clave;`

---

## 11. `consultar.js` (tabla)

Plantilla: `dist/js/modulos/empleado/consultar.js`. Flujo:

```
DOMContentLoaded
 ├─ tbody = getElementById('tbody<Modulos>'); if (!tbody) return;
 ├─ escapar(txt)      ← div.textContent → innerHTML (SIEMPRE al inyectar datos)
 ├─ fila(registro)    ← template string con badges de estado y botones
 │                       .btn-detalle / .btn-editar / .btn-eliminar + data-id
 ├─ cargar()
 │    ├─ registros = await apiFetch('api/<modulo>/listar')
 │    ├─ if (tabla) tabla.destroy()      ← ANTES de repintar (siempre)
 │    ├─ tbody.innerHTML = registros.map(fila).join('')
 │    └─ tabla = DataTableHelper.inicializar('#tabla<Modulos>', {
 │           titulo, orden, pageLength, columnasExport /* sin Acciones */, columnDefs
 │       })
 ├─ porId(id) / verDetalle(id)  ← Swal.fire con html escapado (detalle rico)
 ├─ eliminar(id)  ← AlertManager.confirm(...) → POST eliminar → cargar() + Stats.cargar()
 │                    catch: IN_USE → warning | NOT_FOUND → warning + recargar() | resto → error
 ├─ #btn-recargar → cargar()
 ├─ document 'click' con ev.target.closest('.btn-x')   ← DELEGACIÓN (la tabla se repinta)
 └─ window.<Modulo>Consultar = { recargar: cargar };  ← lo usa editar.js
     cargar() al final
```

Reglas duras:

1. **Nunca** un `id` dentro de las filas generadas (se repetiría): solo clases
   + `data-id` + delegación de eventos.
2. **Nunca** una fila `<tr><td colspan=N>` de "sin datos": tbody vacío y
   DataTables pinta su estado vacío.
3. **Destruir antes de repintar**: `tabla.destroy()` y luego `inicializar()`
   otra vez; si no, DataTables revienta con "Cannot reinitialise" o restaura
   DOM viejo.
4. `columnasExport` **excluye** la columna Acciones (índices 0..n-2).
5. Todo texto de BD pasa por `escapar()` antes de entrar en un template string.
6. El HTML rico (iconos, badges) se construye en el template; los **datos**
   siempre escapados.
7. **Fechas SIEMPRE con el helper `Formato`**, jamás la cruda de MySQL
   (`2026-09-20`): en la celda de la tabla, en el detalle de Swal y en el
   subtítulo del modal. Patrón canónico (psicología → medicina):

   ```js
   const fecha = registro.fecha_creacion || '';
   const hora  = Formato.hora((fecha.split(' ')[1]) || '');   // '' si es solo DATE
   // celda:  <div>${Formato.fecha(fecha)}</div>  + hora en <small> si existe
   // data-order conserva la fecha ISO/MySQL para que DataTables ordene bien
   ```

   - Campo `date` → solo `Formato.fecha(fecha)` (no pintes la línea de hora vacía).
   - Campo `datetime` → `Formato.fecha(fecha)` + `Formato.hora(...)` en `<small>`.
   - Unicidad de la celda de fecha: `<td data-order="...">` con el valor crudo
     para la ordenación, y el texto visible formateado.

---

## 12. Manejo de errores en el frontend (por `error.codigo`)

`apiFetch` lanza el objeto de error del contrato. Programa SIEMPRE contra
`error.codigo`, nunca contra `error.mensaje`:

| `error.codigo` | Acción típica |
|---|---|
| `VALIDATION_ERROR` | `AlertManager.warning('Revisa los datos', error.mensaje)` |
| `ALREADY_EXISTS` | `AlertManager.warning('Revisa los datos', error.mensaje)` |
| `NOT_FOUND` (en tabla/modal) | `AlertManager.warning(...)` + recargar la lista |
| `IN_USE` | `AlertManager.warning('No se puede eliminar', error.mensaje)` |
| `ACCESS_DENIED` | `AlertManager.warning('Sin permiso', error.mensaje)` |
| `RATE_LIMIT_EXCEEDED` | no lo manejes: el interceptor de `AlertManager` ya lo muestra |
| 401 / `UNAUTHENTICATED` | no lo manejes: `apiFetch` redirige solo |
| cualquier otro | `AlertManager.error('Error', error.mensaje)` |

---

## 13. Prohibiciones (esto es "código basura" en este sistema)

- ❌ `fetch()` directo, `axios`, `XMLHttpRequest` → solo `apiFetch`.
- ❌ `Swal.fire()` para mensajes de éxito/error/confirmación estándar → solo
  `AlertManager` (Swall directo SOLO para contenido rico, p. ej. `verDetalle`).
- ❌ `alert()` / `confirm()` nativos.
- ❌ `$('#tabla').DataTable({ language: {...} })` a mano → `DataTableHelper`.
- ❌ `$(...).select2({...})` a mano → `window.initSelect2(scope)`.
- ❌ Cargar jQuery/Select2/DataTables/SweetAlert2 en la vista → ya están en `script.php`.
- ❌ Hardcodear cifras de stats o imprimirlas desde PHP.
- ❌ `<td colspan>` como estado vacío en tablas con DataTables.
- ❌ `id` repetido en filas dinámicas; `querySelector` con ids que solo existen
  una vez fuera del formulario.
- ❌ Inventar `error.codigo` nuevos desde el JS (usa los de `ErrorCodes`).
- ❌ Módulo con una sola función gigante: respeta el split por archivos.
- ❌ Validación solo en el cliente (el backend siempre repite la validación)
  y validación solo en el backend (el usuario necesita feedback en vivo).
- ❌ Variables/funciones globales fuera del namespace `window.<Modulo><Parte>`.

---

## 14. Secuencia de creación de un módulo (frontend)

1. Leer el módulo canónico equivalente (`empleado` para CRUD+modal).
2. `app/Views/<modulo>s/components/stats.php` (si hay stats) → copiar de empleados y cambiar claves/títulos.
3. `crear.php` → copiar estructura, adaptar campos **respetando el contrato** (`name`=`id`, `id`+`Error`, `select2`+`data-placeholder`, `novalidate`, `#btn-ayuda`).
4. `consultar.php` → thead + tbody vacío + modal con los MISMOS ids que crear.
5. `stats.js` → casi literal.
6. `validaciones.js` → copiar el esqueleto de empleados, cambiar `RX`, campos y endpoints `validar_*`.
7. `tour.js` → PASOS con las reglas reales del formulario.
8. `crear.js` / `editar.js` / `consultar.js` → copiar el flujo, cambiar rutas y campos.
9. Incluir los scripts con `defer` en el orden de la sección 2.
10. Recorrer el checklist de la sección 15 y entregar los comandos al usuario.

## 15. Checklist por pantalla (antes de dar un módulo por terminado)

**Formulario (crear/editar)**
- [ ] `novalidate`, `*` en obligatorios, `<div id="<campo>Error">` en todos los campos validables.
- [ ] Validación en vivo (`input`/`change` + `select2:select select2:clear`) y de nuevo en `submit`.
- [ ] Remotas con `apiFetch` + `debounce` + `id_excluir`; si la API falla → rojo "No se pudo verificar…" + `return false` (nunca verde).
- [ ] Select2: clase + placeholder + `initSelect2` tras cargar opciones y tras `reset`/`modal.show`.
- [ ] `#btn-ayuda` → tour; los pasos apuntan a `objetivo()`; Select2 usa `nextElementSibling`.
- [ ] Éxito → `AlertManager.success` + reset/rellenado + `Stats.cargar()` (y `Consultar.recargar()` si venía de la tabla).

**Tabla (consultar)**
- [ ] thead completo, tbody vacío, sin `colspan`, sin `id` en filas.
- [ ] Fechas formateadas con `Formato.fecha`/`Formato.hora` (nunca la cruda de MySQL); `data-order` conserva el valor ISO para ordenar.
- [ ] `destroy()` antes de repintar; `DataTableHelper` con `columnasExport` sin Acciones.
- [ ] Todo dato escapado; botones por delegación.
- [ ] Eliminar con `AlertManager.confirm`; `IN_USE`/`NOT_FOUND` manejados.
- [ ] Estados vacíos y de permiso (`ACCESS_DENIED`) probados.

**General**
- [ ] Sin `fetch`/`Swal`/`DataTable()`/`select2()` directos; sin librerías nuevas en la vista.
- [ ] Namespace único por archivo; guardas `if (!el) return`.
- [ ] Textos en español, nombres de variables en español.

## 16. Comandos de verificación (entregarlos al usuario, no ejecutarlos)

```text
node --check dist/js/modulos/<modulo>/stats.js
node --check dist/js/modulos/<modulo>/validaciones.js
node --check dist/js/modulos/<modulo>/tour.js
node --check dist/js/modulos/<modulo>/crear.js
node --check dist/js/modulos/<modulo>/editar.js
node --check dist/js/modulos/<modulo>/consultar.js
php -l app/Views/<modulo>s/crear.php
php -l app/Views/<modulo>s/consultar.php
php -l app/Views/<modulo>s/components/stats.php
```

Y listar los escenarios de prueba manual de navegador (validar duplicado,
reset del Select2, tour, recarga de tabla, estado vacío, exportar Excel/PDF).
La IA **no** afirma que la interfaz fue probada si no hay entorno de navegador.
