<?php
namespace App\Core;

/**
 * app/Core/ExcepcionApi.php
 * ---------------------------------------------------------------
 * La ÚNICA clase de excepción de negocio del sistema.
 *
 * Transporta sus propios metadatos de respuesta:
 *   - $codigo  → clave estable de ErrorCodes (el frontend programa contra ella)
 *   - $estado  → código HTTP real que acompaña la respuesta
 *   - $mensaje → texto entendible para el usuario final
 *
 * QUIÉN LA USA: los Modelos. Cuando una acción del modelo detecta un
 * problema, LANZA (no devuelve arrays con 'estado' => 'error'):
 *
 *   throw ExcepcionApi::validacion('El nombre es obligatorio.');
 *   throw ExcepcionApi::yaExiste('El correo ya está registrado.');
 *   throw new ExcepcionApi(ErrorCodes::EN_USO, 409, 'Tiene diagnósticos asociados.');
 *
 * QUIÉN LA RESponde: el Controlador con Respuesta::error($e), o el
 * handler global de index.php si nadie la atrapó. El controlador
 * NUNCA adivina códigos ni estados: los vuelca de la excepción.
 */
class ExcepcionApi extends \Exception
{
    public readonly string $codigo;
    public readonly int $estado;
    /** @var array<string,mixed>|null Detalles opcionales (ej: errores por campo). */
    public readonly ?array $detalles;

    public function __construct(string $codigo, int $estado, string $mensaje, ?array $detalles = null)
    {
        parent::__construct($mensaje);
        $this->codigo   = $codigo;
        $this->estado   = $estado;
        $this->detalles = $detalles;
    }

    // ---------- Fábricas para los errores más comunes ----------

    /** 400 — Datos inválidos, incompletos o con formato incorrecto. */
    public static function validacion(string $mensaje, ?array $detalles = null): self
    {
        return new self(ErrorCodes::VALIDACION, 400, $mensaje, $detalles);
    }

    /** 400 — El cuerpo de la petición no es JSON válido. */
    public static function jsonInvalido(string $mensaje = 'El cuerpo de la solicitud no es un JSON válido.'): self
    {
        return new self(ErrorCodes::JSON_INVALIDO, 400, $mensaje);
    }

    /** 404 — El recurso consultado no existe. */
    public static function noEncontrado(string $mensaje = 'El recurso solicitado no existe.'): self
    {
        return new self(ErrorCodes::NO_ENCONTRADO, 404, $mensaje);
    }

    /** 409 — El registro ya existe (duplicado). */
    public static function yaExiste(string $mensaje): self
    {
        return new self(ErrorCodes::YA_EXISTE, 409, $mensaje);
    }

    /** 409 — El registro no puede eliminarse porque tiene dependencias. */
    public static function enUso(string $mensaje): self
    {
        return new self(ErrorCodes::EN_USO, 409, $mensaje);
    }

    /** 403 — Autenticado pero sin permiso (RBAC). */
    public static function accesoDenegado(string $mensaje = 'No tienes permiso para realizar esta acción.'): self
    {
        return new self(ErrorCodes::ACCESO_DENEGADO, 403, $mensaje);
    }

    /** 500 — Fallo técnico inesperado (envolver un PDOException, etc.). */
    public static function errorInterno(string $mensaje = 'Ocurrió un error inesperado en el servidor.'): self
    {
        return new self(ErrorCodes::ERROR_INTERNO, 500, $mensaje);
    }
}
