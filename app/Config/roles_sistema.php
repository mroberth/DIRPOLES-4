<?php
/**
 * app/Config/roles_sistema.php
 * ---------------------------------------------------------------
 * Roles (id_tipo_emp) que USAN el sistema y por lo tanto aparecen en la
 * matriz de permisos (y, en general, en la gestión de accesos).
 *
 * Los que NO están aquí (Chofer, Mecánico, Obreros, Administrativo...) se
 * usan en otros módulos pero no inician sesión, así que no se muestran.
 *
 * Ajusta esta lista si un rol cambia de uso.
 */
return [
    1,  // Psicologo
    2,  // Medico
    3,  // Trabajador Social
    4,  // Orientador
    5,  // Discapacidad
    6,  // Administrador
    7,  // Secretaria
    10, // Superusuario
    12, // Enfermero
];
