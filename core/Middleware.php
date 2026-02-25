<?php

namespace Core;

/**
 * Clase base para middleware.
 * Los middleware concretos deben implementar handle().
 *
 * @package ERP\Core
 */
abstract class Middleware
{
    /**
     * Ejecuta la lógica del middleware.
     * Debe redirigir o lanzar excepción si falla la validación.
     */
    abstract public function handle(): void;
}
