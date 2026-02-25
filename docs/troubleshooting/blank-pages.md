# Documentación Técnica: Resolución de Errores de Pantalla en Blanco

## Caso de Estudio: Pantalla en Blanco (White Screen of Death)
**Fecha:** 25 de Febrero, 2026
**Síntoma:** El sistema responde con código 200 OK, pero el navegador muestra una página totalmente en blanco. Error detectado tanto en Dashboard como en módulos (CRM).

---

### 1. Causa Raíz: Colisión de Buffers de Salida
El sistema utiliza el almacenamiento en búfer de salida de PHP (`ob_start`) para capturar las vistas y meterlas en un layout.

**El error lógico:**
- El motor (`View::render`) abría un buffer para capturar el archivo de vista.
- La vista (`index.php`) abría su propio buffer interno mediante `View::startSection('content')`.
- Al finalizar, el contenido estaba guardado en la sección, pero el buffer externo capturaba un string vacío. 
- El código final ejecutaba: `self::$sections['content'] = $content;` (donde `$content` era el vacío), borrando lo que la vista sí había generado.

**Solución aplicada en `core/View.php`:**
Se protegió la asignación para que solo use el buffer externo si la vista no utilizó el sistema de secciones interno:
```php
if (empty(self::$sections['content'])) {
    self::$sections['content'] = $content;
}
```

---

### 2. Configuración de Apache y Subdirectorios
Al desplegar en subcarpetas (ej: `localhost/erprd/`), la directiva `RewriteBase /` enviaba las peticiones al lugar equivocado.

**El error:**
Las URLs limpias como `/customers` se enviaban a `root/index.php` en lugar de `subfolder/index.php`.

**Solución:**
Eliminar `RewriteBase /` del `.htaccess`. Apache es capaz de auto-detectar el directorio base si no se le fuerza uno.

---

### 3. Cargador de Módulos (ModuleLoader)
El sistema depende de la tabla `module_license` para registrar las rutas de los módulos. 

**Problemas detectados:**
- **Duplicidad:** Registros múltiples para un mismo módulo causaban inconsistencia en el `JOIN`.
- **Estado:** Licencias que por error de instalación quedaban en `is_enabled = 0`.

**Solución:**
- Limpieza de base de datos (`fix_db.php`) consolidando licencias mediante `MIN(id)` y agrupando por `module_id`.
- Script de diagnóstico (`diagnose.php`) para validar visualmente si el CRM tiene rutas registradas.

---

### 💡 Lecciones para el Futuro:

1.  **Buffer vs Section:** Nunca sobrescribas una sección global con el resultado de un buffer de captura sin antes verificar si la sección ya tiene contenido.
2.  **Idempotencia del Instalador:** El `seed.sql` debe usar `INSERT IGNORE` para evitar que fallos a mitad de proceso dejen la base de datos en un estado inconsistente.
3.  **Case Sensitivity:** Hostinger (Linux) diferencia entre `Modules/` y `modules/`. Asegurar consistencia absoluta en Namespaces y nombres de carpetas.
4.  **Debugging Visible:** En desarrollo local y primera fase de prod, forzar `ini_set('display_errors', 1)` en `index.php` antes que cualquier otra carga para ver errores de sintaxis u operaciones de archivos que los buffers suelen ocultar.

---
*Documento generado por Antigravity AI - 2026*
