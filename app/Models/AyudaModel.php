<?php
// app/Models/AyudaModel.php

namespace App\Models;

use App\Core\ExcepcionApi;

class AyudaModel extends BusinessModel
{
    private array $atributos = [];

    public function __set(string $nombre, mixed $valor): void
    {
        $this->atributos[$nombre] = $valor;
    }

    public function __get(string $atributo): mixed
    {
        return $this->atributos[$atributo] ?? null;
    }

    public function manejarAccion(string $accion): mixed
    {
        return match ($accion) {
            'obtener_guias' => $this->obtenerGuias(),
            default => throw ExcepcionApi::errorInterno("Acción no válida en AyudaModel: {$accion}"),
        };
    }

    /**
     * Retorna el catálogo de guías filtrado dinámicamente según los permisos
     * reales del rol del usuario (RBAC modulosPermitidos y es_admin).
     */
    private function obtenerGuias(): array
    {
        $modulosPermitidos = (array) ($this->__get('modulos_permitidos') ?? []);
        $esAdmin = (bool) ($this->__get('es_admin') ?? false);
        
        $catalogoCompleto = $this->getCatalogoCompleto();

        $guiasFiltradas = [];
        foreach ($catalogoCompleto['guias'] as $key => $guia) {
            // Si la guía es solo para administradores y el usuario no lo es, excluirla
            if (!empty($guia['solo_admin']) && !$esAdmin) {
                continue;
            }

            // Verificar si el usuario tiene acceso al id_modulo de la guía
            if ($this->tienePermisoModulo($guia['id_modulo'] ?? null, $modulosPermitidos)) {
                $guiasFiltradas[$key] = $guia;
            }
        }

        $categoriasFiltradas = [];
        foreach ($catalogoCompleto['categorias'] as $catKey => $cat) {
            if (!empty($cat['solo_admin']) && !$esAdmin) {
                continue;
            }

            if (!$this->tienePermisoModulo($cat['id_modulo'] ?? null, $modulosPermitidos)) {
                continue;
            }

            // La categoría debe tener al menos una guía permitida
            $tieneGuias = false;
            foreach ($guiasFiltradas as $g) {
                if (($g['categoria_key'] ?? '') === $catKey) {
                    $tieneGuias = true;
                    break;
                }
            }

            if ($tieneGuias) {
                $categoriasFiltradas[$catKey] = $cat;
            }
        }

        return [
            'categorias' => $categoriasFiltradas,
            'guias' => $guiasFiltradas,
        ];
    }

    private function tienePermisoModulo(int|array|null $idModulo, array $modulosPermitidos): bool
    {
        if ($idModulo === null) {
            return true;
        }

        if (is_array($idModulo)) {
            foreach ($idModulo as $id) {
                if (isset($modulosPermitidos[$id])) {
                    return true;
                }
            }
            return false;
        }

        return isset($modulosPermitidos[$idModulo]);
    }

    private function getCatalogoCompleto(): array
    {
        return [
            'categorias' => [
                'inicio' => [
                    'id_modulo' => null,
                    'titulo' => 'Inicio de Sesión y Dashboard',
                    'descripcion' => 'Acceso al sistema, panel principal e indicadores.',
                    'icono' => 'fa-sign-in-alt',
                    'color' => 'primary',
                    'grupo' => 'general',
                    'keywords' => 'login sesion acceso contraseña correo dashboard calendario inicio',
                ],
                'empleados' => [
                    'id_modulo' => 1,
                    'titulo' => 'Gestión de Empleados',
                    'descripcion' => 'Registro de personal, asignación de roles y permisos.',
                    'icono' => 'fa-users',
                    'color' => 'success',
                    'grupo' => 'admin',
                    'keywords' => 'empleados personal nomina registro crear editar eliminar rol',
                ],
                'beneficiarios' => [
                    'id_modulo' => 2,
                    'titulo' => 'Gestión de Beneficiarios',
                    'descripcion' => 'Registro de estudiantes, datos PNF y ficha personal.',
                    'icono' => 'fa-user-graduate',
                    'color' => 'info',
                    'grupo' => 'general',
                    'keywords' => 'beneficiarios estudiantes cedula pnf registro ficha',
                ],
                'citas' => [
                    'id_modulo' => 3,
                    'titulo' => 'Gestión de Citas',
                    'descripcion' => 'Programación y seguimiento de citas de Psicología.',
                    'icono' => 'fa-calendar-check',
                    'color' => 'warning',
                    'grupo' => 'psicologia',
                    'keywords' => 'citas calendario agenda psicologo horario agendar disponibilidad',
                ],
                'horarios' => [
                    'id_modulo' => 18,
                    'solo_admin' => true,
                    'titulo' => 'Horarios de Psicología',
                    'descripcion' => 'Gestión de turnos y agendas médicas de especialistas.',
                    'icono' => 'fa-clock',
                    'color' => 'info',
                    'grupo' => 'psicologia',
                    'keywords' => 'horarios turnos psicologo disponibilidad agenda',
                ],
                'diagnosticos' => [
                    'id_modulo' => null,
                    'titulo' => 'Diagnósticos y Atenciones',
                    'descripcion' => 'Atención especializada (Psicología, Medicina, Orientación, Trabajo Social, Discapacidad).',
                    'icono' => 'fa-file-medical',
                    'color' => 'danger',
                    'grupo' => 'salud',
                    'keywords' => 'diagnostico psicologia medicina orientacion trabajo social discapacidad constancia recipe',
                ],
                'inventario' => [
                    'id_modulo' => 9,
                    'titulo' => 'Inventario Médico',
                    'descripcion' => 'Control de medicamentos, insumos, entradas y kardex.',
                    'icono' => 'fa-pills',
                    'color' => 'info',
                    'grupo' => 'salud',
                    'keywords' => 'inventario medico insumos medicamentos stock farmacia entrada salida kardex',
                ],
                'referencias' => [
                    'id_modulo' => 10,
                    'titulo' => 'Referencias entre Áreas',
                    'descripcion' => 'Derivaciones interdepartamentales y seguimiento de casos.',
                    'icono' => 'fa-share-nodes',
                    'color' => 'secondary',
                    'grupo' => 'general',
                    'keywords' => 'referencias derivadas traslado canalizar modulo area origen destino',
                ],
                'jornadas' => [
                    'id_modulo' => 11,
                    'titulo' => 'Jornadas Médicas',
                    'descripcion' => 'Eventos especiales, control de aforo y atención masiva.',
                    'icono' => 'fa-briefcase-medical',
                    'color' => 'success',
                    'grupo' => 'salud',
                    'keywords' => 'jornadas medicina eventos masiva asistencia operativo insumos aforo',
                ],
                'mobiliario' => [
                    'id_modulo' => 12,
                    'titulo' => 'Mobiliario y Equipos',
                    'descripcion' => 'Censo de activos fijos, seriales y Fichas Técnicas.',
                    'icono' => 'fa-chair',
                    'color' => 'warning',
                    'grupo' => 'admin',
                    'keywords' => 'mobiliario equipos fichas tecnicas seriales asignacion activos bienes',
                ],
                'transporte' => [
                    'id_modulo' => 13,
                    'titulo' => 'Gestión de Transporte',
                    'descripcion' => 'Flota vehicular, choferes, rutas, repuestos y mantenimientos.',
                    'icono' => 'fa-truck',
                    'color' => 'dark',
                    'grupo' => 'admin',
                    'keywords' => 'transporte vehiculo ruta chofer proveedor repuestos mantenimiento asignaciones',
                ],
                'reportes' => [
                    'id_modulo' => null, // Contenedor multi-módulo (se filtran sus reportes específicos)
                    'titulo' => 'Reportes Estadísticos',
                    'descripcion' => 'Análisis de datos, filtros por fecha/área y exportación.',
                    'icono' => 'fa-chart-pie',
                    'color' => 'primary',
                    'grupo' => 'general',
                    'keywords' => 'reportes graficos estadisticas filtros general medicina psicologia excel pdf',
                ],
                'seguridad' => [
                    'id_modulo' => [14, 16, 17],
                    'titulo' => 'Seguridad y Auditoría',
                    'descripcion' => 'Bitácora de eventos, matriz de permisos y respaldos BD.',
                    'icono' => 'fa-shield-halved',
                    'color' => 'dark',
                    'grupo' => 'admin',
                    'keywords' => 'seguridad bitacora auditoria permisos roles respaldo base de datos',
                ],
            ],
            'guias' => [
                'login_acceso' => [
                    'id_modulo' => null,
                    'category' => 'Inicio de Sesión',
                    'categoria_key' => 'inicio',
                    'title' => '¿Cómo iniciar sesión en el sistema?',
                    'route' => 'inicio',
                    'steps' => [
                        ['icon' => 'fa-envelope', 'text' => 'Ingrese su <b>correo electrónico</b> institucional registrado.'],
                        ['icon' => 'fa-lock', 'text' => 'Introduzca su <b>contraseña</b>. El sistema protege las credenciales mediante encriptación RSA y bcrypt.'],
                        ['icon' => 'fa-right-to-bracket', 'text' => 'Haga clic en el botón <b>"Iniciar Sesión"</b> para acceder al panel principal.'],
                    ],
                    'tip' => 'Tras 3 intentos fallidos consecutivos, el sistema bloqueará temporalmente el acceso por seguridad.',
                ],
                'login_dashboard' => [
                    'id_modulo' => null,
                    'category' => 'Inicio de Sesión',
                    'categoria_key' => 'inicio',
                    'title' => '¿Cómo funciona el Panel de Inicio?',
                    'route' => 'inicio',
                    'steps' => [
                        ['icon' => 'fa-gauge-high', 'text' => 'El panel muestra <b>tarjetas de acceso rápido</b> según los permisos asignados a su rol.'],
                        ['icon' => 'fa-chart-line', 'text' => 'Las tarjetas de estadísticas muestran contadores en tiempo real de su departamento.'],
                        ['icon' => 'fa-calendar-days', 'text' => 'En la parte inferior dispone de su <b>Calendario Personal</b> para programar tareas y recordatorios.'],
                    ],
                    'tip' => 'Puede hacer clic en cualquiera de las tarjetas de módulo disponibles para ir directamente a la pantalla de gestión.',
                ],
                'empleados_crear' => [
                    'id_modulo' => 1,
                    'category' => 'Gestión de Empleados',
                    'categoria_key' => 'empleados',
                    'title' => '¿Cómo registrar un nuevo empleado?',
                    'route' => 'empleados/crear',
                    'steps' => [
                        ['icon' => 'fa-user-plus', 'text' => 'Diríjase a <b>Gestionar Empleados >> Crear</b> en el menú lateral.'],
                        ['icon' => 'fa-id-card', 'text' => 'Ingrese la Cédula, Nombres, Apellidos, Correo, Teléfono y Dirección.'],
                        ['icon' => 'fa-user-shield', 'text' => 'Seleccione el <b>Tipo de Empleado (Rol)</b>: Administrador, Médico, Psicólogo, Orientador, Trabajador Social, etc.'],
                        ['icon' => 'fa-floppy-disk', 'text' => 'Haga clic en <b>"Registrar Empleado"</b>. La validación comprobará la unicidad global de cédula y correo.'],
                    ],
                    'tip' => 'El rol asignado determinará automáticamente los accesos a los módulos y las funciones en el menú lateral.',
                ],
                'empleados_consultar' => [
                    'id_modulo' => 1,
                    'category' => 'Gestión de Empleados',
                    'categoria_key' => 'empleados',
                    'title' => '¿Cómo consultar y administrar empleados?',
                    'route' => 'empleados/consultar',
                    'steps' => [
                        ['icon' => 'fa-table-list', 'text' => 'Acceda a <b>Gestionar Empleados >> Consultar</b>.'],
                        ['icon' => 'fa-magnifying-glass', 'text' => 'Utilice la barra de búsqueda de la tabla DataTables para filtrar por cédula, nombre o cargo.'],
                        ['icon' => 'fa-pen-to-square', 'text' => 'Presione el botón <b class="text-warning">Editar</b> para actualizar datos personales o cambiar el rol.'],
                        ['icon' => 'fa-trash-can', 'text' => 'Presione <b class="text-danger">Eliminar</b> si desea desactivar al empleado del sistema.'],
                    ],
                    'tip' => 'Puede exportar la lista de empleados a archivos Excel o PDF mediante los botones superiores de la tabla.',
                ],
                'beneficiarios_crear' => [
                    'id_modulo' => 2,
                    'category' => 'Gestión de Beneficiarios',
                    'categoria_key' => 'beneficiarios',
                    'title' => '¿Cómo registrar un nuevo beneficiario (estudiante)?',
                    'route' => 'beneficiarios/crear',
                    'steps' => [
                        ['icon' => 'fa-address-book', 'text' => 'Vaya a <b>Gestionar Beneficiarios >> Crear</b>.'],
                        ['icon' => 'fa-graduation-cap', 'text' => 'Complete el tipo de documento, cédula, nombres, apellidos, PNF (Programa Nacional de Formación), trayecto y datos de contacto.'],
                        ['icon' => 'fa-check-double', 'text' => 'Haga clic en <b>"Registrar Beneficiario"</b>.'],
                    ],
                    'tip' => 'Verifique que la cédula sea correcta, ya que esta identificará al beneficiario en todos los diagnósticos y citas.',
                ],
                'citas_crear' => [
                    'id_modulo' => 3,
                    'category' => 'Gestión de Citas',
                    'categoria_key' => 'citas',
                    'title' => '¿Cómo agendar una cita médica o de psicología?',
                    'route' => 'citas/crear',
                    'steps' => [
                        ['icon' => 'fa-calendar-plus', 'text' => 'Acceda a <b>Gestionar Citas >> Crear</b>.'],
                        ['icon' => 'fa-user-check', 'text' => 'Seleccione el beneficiario y escoja al especialista responsable.'],
                        ['icon' => 'fa-clock', 'text' => 'Seleccione la fecha y hora deseada. El sistema validará automáticamente la disponibilidad en la agenda del especialista.'],
                        ['icon' => 'fa-paper-plane', 'text' => 'Presione <b>"Agendar Cita"</b>. El beneficiario recibirá la confirmación correspondiente.'],
                    ],
                    'tip' => 'Si eres Psicólogo, la pantalla priorizará automáticamente tus bloques de horario registrados.',
                ],
                'horarios_gestion' => [
                    'id_modulo' => 18,
                    'solo_admin' => true,
                    'category' => 'Horarios de Psicología',
                    'categoria_key' => 'horarios',
                    'title' => '¿Cómo configurar los horarios de atención?',
                    'route' => 'horarios/consultar',
                    'steps' => [
                        ['icon' => 'fa-clock', 'text' => 'Acceda a <b>Horarios de Psicología >> Consultar</b>.'],
                        ['icon' => 'fa-user-doctor', 'text' => 'Seleccione el psicólogo y asigne su jornada laboral por día de la semana.'],
                        ['icon' => 'fa-floppy-disk', 'text' => 'Guarde los cambios. Las citas comprobarán estos bloques antes de agendar.'],
                    ],
                    'tip' => 'Solo los Administradores y Superusuarios tienen acceso para modificar horarios.',
                ],
                'diagnosticos_psicologia' => [
                    'id_modulo' => 4,
                    'category' => 'Diagnósticos y Atenciones',
                    'categoria_key' => 'diagnosticos',
                    'title' => '¿Cómo registrar un diagnóstico de Psicología?',
                    'route' => 'psicologia/crear',
                    'steps' => [
                        ['icon' => 'fa-brain', 'text' => 'En el menú lateral, seleccione <b>Diagnósticos >> Psicología</b>.'],
                        ['icon' => 'fa-magnifying-glass-chart', 'text' => 'Busque y seleccione al beneficiario atendiéndose.'],
                        ['icon' => 'fa-file-pen', 'text' => 'Complete el motivo de consulta, evaluación psicológica, diagnóstico y plan terapéutico.'],
                        ['icon' => 'fa-floppy-disk', 'text' => 'Guarde la consulta. Podrá imprimir la <b>Constancia de Atención</b> en PDF desde el listado.'],
                    ],
                    'tip' => 'El sistema registra automáticamente la auditoría de cada atención realizada en la Bitácora.',
                ],
                'diagnosticos_medicina' => [
                    'id_modulo' => 5,
                    'category' => 'Diagnósticos y Atenciones',
                    'categoria_key' => 'diagnosticos',
                    'title' => '¿Cómo registrar una consulta médica y recetar insumos?',
                    'route' => 'medicina/crear',
                    'steps' => [
                        ['icon' => 'fa-stethoscope', 'text' => 'Seleccione <b>Diagnósticos >> Medicina</b>.'],
                        ['icon' => 'fa-notes-medical', 'text' => 'Ingrese los datos fisiológicos (peso, estatura, presión), motivo y diagnóstico médico.'],
                        ['icon' => 'fa-pills', 'text' => 'Si suministra insumos del inventario, agréguelos en el apartado de medicamentos. El stock se descontará en tiempo real.'],
                        ['icon' => 'fa-print', 'text' => 'Guarde la consulta para generar automáticamente el <b>Récipe Médico</b> y la Constancia.'],
                    ],
                    'tip' => 'Los medicamentos marcados como "Agotados" o "Vencidos" no podrán asignarse en nuevas consultas.',
                ],
                'diagnosticos_orientacion' => [
                    'id_modulo' => 6,
                    'category' => 'Diagnósticos y Atenciones',
                    'categoria_key' => 'diagnosticos',
                    'title' => '¿Cómo registrar una atención en Orientación?',
                    'route' => 'orientacion/crear',
                    'steps' => [
                        ['icon' => 'fa-compass', 'text' => 'Seleccione <b>Diagnósticos >> Orientación</b>.'],
                        ['icon' => 'fa-user-check', 'text' => 'Busque al estudiante e ingrese motivo, descripción, indicaciones y observaciones.'],
                        ['icon' => 'fa-floppy-disk', 'text' => 'Guarde el registro para integrarlo al expediente del beneficiario.'],
                    ],
                    'tip' => 'Los 4 campos de texto son obligatorios para garantizar la integridad del seguimiento.',
                ],
                'diagnosticos_trabajo_social' => [
                    'id_modulo' => 7,
                    'category' => 'Diagnósticos y Atenciones',
                    'categoria_key' => 'diagnosticos',
                    'title' => '¿Cómo registrar atenciones de Trabajo Social y Estudios SE?',
                    'route' => 'trabajo-social/crear',
                    'steps' => [
                        ['icon' => 'fa-hand-holding-heart', 'text' => 'Seleccione <b>Diagnósticos >> Trabajo Social</b>.'],
                        ['icon' => 'fa-folder-open', 'text' => 'Gestione sub-registros de Becas, Exoneraciones, FAMES o Embarazadas.'],
                        ['icon' => 'fa-file-pdf', 'text' => 'Desde exoneraciones pendientes puede procesar el <b>Estudio Socioeconómico</b> que genera el PDF oficial.'],
                    ],
                    'tip' => 'El PDF del Estudio Socioeconómico adjunta la foto opcional del estudiante.',
                ],
                'diagnosticos_discapacidad' => [
                    'id_modulo' => 8,
                    'category' => 'Diagnósticos y Atenciones',
                    'categoria_key' => 'diagnosticos',
                    'title' => '¿Cómo evaluar y registrar atenciones en Discapacidad?',
                    'route' => 'discapacidad/crear',
                    'steps' => [
                        ['icon' => 'fa-wheelchair', 'text' => 'Seleccione <b>Diagnósticos >> Discapacidad</b>.'],
                        ['icon' => 'fa-clipboard-user', 'text' => 'Ingrese el tipo de discapacidad, grado, diagnóstico, habilidades funcionales y observaciones.'],
                        ['icon' => 'fa-floppy-disk', 'text' => 'Guarde la atención para alimentar los indicadores institucionales.'],
                    ],
                    'tip' => 'El formulario exige tipo, grado, diagnóstico y habilidades funcionales.',
                ],
                'inventario_crear' => [
                    'id_modulo' => 9,
                    'category' => 'Inventario Médico',
                    'categoria_key' => 'inventario',
                    'title' => '¿Cómo registrar e ingresar insumos médicos?',
                    'route' => 'inventario/consultar',
                    'steps' => [
                        ['icon' => 'fa-prescription-bottle-medical', 'text' => 'Diríjase a <b>Inventario Médico >> Consultar</b>.'],
                        ['icon' => 'fa-plus', 'text' => 'Presione <b>"Nuevo Insumo"</b> para registrar la ficha del producto (nace con cantidad 0).'],
                        ['icon' => 'fa-box-archive', 'text' => 'En la tabla, use el botón <b class="text-success">Entrada</b> para cargar el stock de unidades recibidas con su fecha de vencimiento.'],
                    ],
                    'tip' => 'Toda entrada o salida genera un movimiento auditado e inalterable en el Kardex de Inventario.',
                ],
                'referencias_crear' => [
                    'id_modulo' => 10,
                    'category' => 'Referencias entre Áreas',
                    'categoria_key' => 'referencias',
                    'title' => '¿Cómo referir a un estudiante a otra especialidad?',
                    'route' => 'referencias/crear',
                    'steps' => [
                        ['icon' => 'fa-share-nodes', 'text' => 'Seleccione <b>Gestionar Referencias >> Crear</b>.'],
                        ['icon' => 'fa-user', 'text' => 'Indique el beneficiario a ser derivado.'],
                        ['icon' => 'fa-building', 'text' => 'Seleccione el servicio o área de destino (ej. Psicología, Medicina, Trabajo Social).'],
                        ['icon' => 'fa-message', 'text' => 'Escriba el motivo detallado de la referencia y las observaciones asociadas.'],
                        ['icon' => 'fa-paper-plane', 'text' => 'Guarde la referencia. El área de destino recibirá una notificación automática.'],
                    ],
                    'tip' => 'El especialista del área de destino podrá Aceptar o Rechazar la referencia desde su panel de consulta.',
                ],
                'jornadas_crear' => [
                    'id_modulo' => 11,
                    'category' => 'Jornadas Médicas',
                    'categoria_key' => 'jornadas',
                    'title' => '¿Cómo crear y gestionar una Jornada de Salud?',
                    'route' => 'jornadas/crear',
                    'steps' => [
                        ['icon' => 'fa-briefcase-medical', 'text' => 'Acceda a <b>Gestionar Jornadas >> Crear</b>.'],
                        ['icon' => 'fa-calendar-day', 'text' => 'Complete el título de la jornada, aforo máximo de pacientes, ubicación, fecha de inicio y fin.'],
                        ['icon' => 'fa-user-check', 'text' => 'Desde el detalle de la jornada podrá ir registrando los asistentes y asentar diagnósticos masivos.'],
                    ],
                    'tip' => 'El sistema bloquea automáticamente nuevos registros al alcanzar el aforo máximo de la jornada.',
                ],
                'mobiliario_gestion' => [
                    'id_modulo' => 12,
                    'category' => 'Mobiliario y Equipos',
                    'categoria_key' => 'mobiliario',
                    'title' => '¿Cómo administrar activos, seriales y Fichas Técnicas?',
                    'route' => 'mobiliario/consultar',
                    'steps' => [
                        ['icon' => 'fa-chair', 'text' => 'Acceda al módulo de <b>Mobiliario</b>.'],
                        ['icon' => 'fa-barcode', 'text' => 'En <b>Equipos</b> registre unidades con serial único y obligatorio.'],
                        ['icon' => 'fa-file-contract', 'text' => 'En <b>Ficha Técnica</b> asigne ítems activos a un empleado responsable.'],
                    ],
                    'tip' => 'Un equipo no puede asignarse en dos Fichas Técnicas activas simultáneamente.',
                ],
                'transporte_gestion' => [
                    'id_modulo' => 13,
                    'category' => 'Gestión de Transporte',
                    'categoria_key' => 'transporte',
                    'title' => '¿Cómo administrar vehículos, rutas y repuestos?',
                    'route' => 'transporte/consultar',
                    'steps' => [
                        ['icon' => 'fa-bus', 'text' => 'Acceda al módulo de <b>Transporte</b> para ver las pestañas principales.'],
                        ['icon' => 'fa-car-side', 'text' => 'En <b>Vehículos</b> registre unidades con placa, modelo y estado.'],
                        ['icon' => 'fa-route', 'text' => 'En <b>Asignaciones/Rutas</b> asocie choferes y unidades a las rutas del día.'],
                        ['icon' => 'fa-wrench', 'text' => 'En <b>Mantenimientos</b> registre las salidas a taller e insumos utilizados.'],
                    ],
                    'tip' => 'Un vehículo no puede tener dos asignaciones activas de ruta en el mismo día.',
                ],

                // ─── REPORTES ESPECÍFICOS POR ROL/MÓDULO ───
                'reportes_general' => [
                    'id_modulo' => 15,
                    'solo_admin' => true,
                    'category' => 'Reportes Estadísticos',
                    'categoria_key' => 'reportes',
                    'title' => '¿Cómo generar el Reporte General del Sistema?',
                    'route' => 'reportes/general',
                    'steps' => [
                        ['icon' => 'fa-chart-pie', 'text' => 'En el menú lateral, haga clic en <b>Reportes >> General</b>.'],
                        ['icon' => 'fa-filter', 'text' => 'Seleccione el rango de fechas en "Fecha de inicio" y "Fecha fin".'],
                        ['icon' => 'fa-magnifying-glass', 'text' => 'Presione "Generar Reporte" para visualizar las métricas y gráficos consolidados.'],
                        ['icon' => 'fa-file-excel', 'text' => 'Exporte los resultados a Excel o PDF según sus necesidades.'],
                    ],
                    'tip' => 'El reporte general consolida las estadísticas de todas las áreas de la institución.',
                ],
                'reportes_psicologia' => [
                    'id_modulo' => 4,
                    'category' => 'Reportes Estadísticos',
                    'categoria_key' => 'reportes',
                    'title' => '¿Cómo consultar los Reportes de Psicología?',
                    'route' => 'reportes/psicologia',
                    'steps' => [
                        ['icon' => 'fa-brain', 'text' => 'En el menú lateral, seleccione <b>Reportes >> Psicología</b>.'],
                        ['icon' => 'fa-filter', 'text' => 'Filtre por rango de fechas, género, PNF o tipo de consulta.'],
                        ['icon' => 'fa-chart-bar', 'text' => 'Analice las gráficas de morbilidad y citas atendidas.'],
                    ],
                    'tip' => 'Puede hacer clic en "Ir al Módulo" para abrir directamente la pantalla de reportes de Psicología.',
                ],
                'reportes_medicina' => [
                    'id_modulo' => 5,
                    'category' => 'Reportes Estadísticos',
                    'categoria_key' => 'reportes',
                    'title' => '¿Cómo consultar los Reportes de Medicina?',
                    'route' => 'reportes/medicina',
                    'steps' => [
                        ['icon' => 'fa-stethoscope', 'text' => 'En el menú lateral, seleccione <b>Reportes >> Medicina</b>.'],
                        ['icon' => 'fa-filter', 'text' => 'Filtre por fecha y consulte las estadísticas de diagnósticos e insumos recetados.'],
                    ],
                    'tip' => 'Permite exportar en PDF el consolidado médico para la dirección.',
                ],
                'reportes_orientacion' => [
                    'id_modulo' => 6,
                    'category' => 'Reportes Estadísticos',
                    'categoria_key' => 'reportes',
                    'title' => '¿Cómo consultar los Reportes de Orientación?',
                    'route' => 'reportes/orientacion',
                    'steps' => [
                        ['icon' => 'fa-compass', 'text' => 'En el menú lateral, seleccione <b>Reportes >> Orientación</b>.'],
                        ['icon' => 'fa-filter', 'text' => 'Aplique filtros por fecha y analice las orientaciones académicas y personales.'],
                    ],
                    'tip' => 'Genera visualizaciones claras del rendimiento en orientación.',
                ],
                'reportes_trabajo_social' => [
                    'id_modulo' => 7,
                    'category' => 'Reportes Estadísticos',
                    'categoria_key' => 'reportes',
                    'title' => '¿Cómo consultar los Reportes de Trabajo Social?',
                    'route' => 'reportes/trabajo-social',
                    'steps' => [
                        ['icon' => 'fa-hand-holding-heart', 'text' => 'En el menú lateral, seleccione <b>Reportes >> Trabajo Social</b>.'],
                        ['icon' => 'fa-chart-column', 'text' => 'Analice los datos de Becas, Exoneraciones, FAMES y Embarazadas.'],
                    ],
                    'tip' => 'Muestra los estudios socioeconómicos procesados.',
                ],
                'reportes_discapacidad' => [
                    'id_modulo' => 8,
                    'category' => 'Reportes Estadísticos',
                    'categoria_key' => 'reportes',
                    'title' => '¿Cómo consultar los Reportes de Discapacidad?',
                    'route' => 'reportes/discapacidad',
                    'steps' => [
                        ['icon' => 'fa-wheelchair', 'text' => 'En el menú lateral, seleccione <b>Reportes >> Discapacidad</b>.'],
                        ['icon' => 'fa-filter', 'text' => 'Consulte los gráficos por tipo de discapacidad y grado.'],
                    ],
                    'tip' => 'Facilita la emisión de resúmenes institucionales.',
                ],
                'reportes_referencias' => [
                    'id_modulo' => 10,
                    'category' => 'Reportes Estadísticos',
                    'categoria_key' => 'reportes',
                    'title' => '¿Cómo consultar los Reportes de Referencias?',
                    'route' => 'reportes/referencias',
                    'steps' => [
                        ['icon' => 'fa-share-nodes', 'text' => 'En el menú lateral, seleccione <b>Reportes >> Referencias</b>.'],
                        ['icon' => 'fa-filter', 'text' => 'Consulte las estadísticas de referencias emitidas, aceptadas y rechazadas.'],
                    ],
                    'tip' => 'Muestra la efectividad del flujo interdepartamental.',
                ],
                'reportes_jornadas' => [
                    'id_modulo' => 11,
                    'category' => 'Reportes Estadísticos',
                    'categoria_key' => 'reportes',
                    'title' => '¿Cómo consultar los Reportes de Jornadas Médicas?',
                    'route' => 'reportes/jornadas',
                    'steps' => [
                        ['icon' => 'fa-briefcase-medical', 'text' => 'Acceda a <b>Reportes >> Jornadas</b>.'],
                        ['icon' => 'fa-chart-line', 'text' => 'Consulte el total de asistentes e insumos distribuidos por evento.'],
                    ],
                    'tip' => 'Proporciona una vista estadística consolidada de operativos masivos.',
                ],
                'reportes_mobiliario' => [
                    'id_modulo' => 12,
                    'category' => 'Reportes Estadísticos',
                    'categoria_key' => 'reportes',
                    'title' => '¿Cómo consultar los Reportes de Mobiliario?',
                    'route' => 'reportes/mobiliario',
                    'steps' => [
                        ['icon' => 'fa-chair', 'text' => 'Acceda a <b>Reportes >> Mobiliario</b>.'],
                        ['icon' => 'fa-boxes-packing', 'text' => 'Visualice los indicadores de bienes asignados, disponibles e inventariados.'],
                    ],
                    'tip' => 'Ideal para auditorías patrimoniales de la institución.',
                ],
                'reportes_transporte' => [
                    'id_modulo' => 13,
                    'category' => 'Reportes Estadísticos',
                    'categoria_key' => 'reportes',
                    'title' => '¿Cómo consultar los Reportes de Transporte?',
                    'route' => 'reportes/transporte',
                    'steps' => [
                        ['icon' => 'fa-truck', 'text' => 'Acceda a <b>Reportes >> Transporte</b>.'],
                        ['icon' => 'fa-chart-area', 'text' => 'Consulte el estado operativo de la flota, salidas a mantenimiento y consumo de repuestos.'],
                    ],
                    'tip' => 'Permite un control logístico óptimo de la flota de unidades.',
                ],

                // ─── SEGURIDAD ───
                'seguridad_bitacora' => [
                    'id_modulo' => 16,
                    'category' => 'Seguridad y Auditoría',
                    'categoria_key' => 'seguridad',
                    'title' => '¿Cómo consultar la Bitácora del Sistema?',
                    'route' => 'bitacora/consultar',
                    'steps' => [
                        ['icon' => 'fa-clipboard-list', 'text' => 'Diríjase a <b>Configuraciones >> Bitácora</b>.'],
                        ['icon' => 'fa-user-gear', 'text' => 'Visualice todas las acciones ejecutadas en el sistema (Registro, Lectura, Actualización, Eliminación, Logins).'],
                        ['icon' => 'fa-filter', 'text' => 'Filtre por fecha, usuario o tipo de acción realizada.'],
                    ],
                    'tip' => 'La Bitácora es un registro inalterable para auditorías internas de la institución.',
                ],
                'seguridad_permisos' => [
                    'id_modulo' => 17,
                    'category' => 'Seguridad y Auditoría',
                    'categoria_key' => 'seguridad',
                    'title' => '¿Cómo administrar la Matriz de Permisos?',
                    'route' => 'permisos/gestionar',
                    'steps' => [
                        ['icon' => 'fa-user-shield', 'text' => 'Acceda a <b>Configuraciones >> Permisos Empleados</b>.'],
                        ['icon' => 'fa-table-cells', 'text' => 'Visualice la matriz Rol x Módulo x Permiso (Crear, Leer, Editar, Eliminar).'],
                        ['icon' => 'fa-square-check', 'text' => 'Marque o desmarque permisos y guarde la configuración.'],
                    ],
                    'tip' => 'Los cambios en permisos se aplican inmediatamente al menú y accesos de los usuarios.',
                ],
            ],
        ];
    }
}
