<?php
namespace App\Core;

/**
 * app/Core/ErrorCodes.php
 * ---------------------------------------------------------------
 * REGISTRO CENTRAL de códigos de error del sistema.
 *
 * REGLA ABSOLUTA: todo código de error nuevo se define AQUÍ como
 * constante. Nunca se escriben strings de error sueltos en los
 * modelos ni en los controladores.
 *
 * Los códigos son ESTABLES: el frontend (JS de las vistas, app móvil)
 * programa contra ellos (ej: `if (error.codigo === 'ALREADY_EXISTS')`),
 * nunca contra los mensajes (que sí pueden cambiar de redacción).
 */
final class ErrorCodes
{
    // ---- Genéricos ----
    public const VALIDACION          = 'VALIDATION_ERROR';       // 400 — datos inválidos o incompletos
    public const JSON_INVALIDO       = 'INVALID_JSON';           // 400 — el cuerpo no es JSON válido
    public const NO_ENCONTRADO       = 'NOT_FOUND';              // 404 — recurso inexistente
    public const RUTA_NO_ENCONTRADA  = 'ROUTE_NOT_FOUND';        // 404 — ruta inexistente
    public const METODO_NO_PERMITIDO = 'METHOD_NOT_ALLOWED';     // 405 — verbo HTTP incorrecto
    public const YA_EXISTE           = 'ALREADY_EXISTS';         // 409 — registro duplicado
    public const EN_USO              = 'IN_USE';                 // 409 — no se puede eliminar (dependencias)
    public const ERROR_INTERNO       = 'INTERNAL_SERVER_ERROR';  // 500 — fallo técnico inesperado

    // ---- Autenticación y autorización ----
    public const NO_AUTENTICADO  = 'UNAUTHENTICATED';            // 401 — sin sesión
    public const ACCESO_DENEGADO = 'ACCESS_DENIED';              // 403 — sin permiso (RBAC)
    public const CUENTA_BLOQUEADA = 'ACCOUNT_LOCKED';           // 403 — cuenta deshabilitada (ej: 3 intentos fallidos)
    public const TOKEN_FALTANTE  = 'TOKEN_MISSING';              // 401
    public const TOKEN_INVALIDO  = 'TOKEN_INVALID';              // 401
    public const TOKEN_EXPIRADO  = 'TOKEN_EXPIRED';              // 401

    // ---- Infraestructura ----
    public const RATE_LIMIT_EXCEDIDO = 'RATE_LIMIT_EXCEEDED';    // 429
    public const BD_ERROR            = 'DATABASE_ERROR';         // 500 — fallo de base de datos

    /** No instanciable: solo constantes. */
    private function __construct()
    {
    }
}
