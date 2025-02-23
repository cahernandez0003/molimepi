# BITÁCORA DE DESARROLLO - MOLIMEPI

## 17/03/2024 - Implementación de Tipos de Horario

### Petición del Usuario
Se solicitó implementar diferentes tipos de registro para los horarios:
1. Capacidad para repetir horarios en un mes
2. Opción para que el administrador pueda bloquear usuarios en fechas específicas
3. Implementación de tipos: "descanso", "baja", "otros"
4. Cálculo automático de horas trabajadas

### Cambios Realizados
1. Modificación de Base de Datos
   ```sql
   ALTER TABLE horarios_trabajo
       MODIFY COLUMN hora_entrada time NULL,
       MODIFY COLUMN hora_salida time NULL,
       ADD COLUMN tipo ENUM('normal', 'descanso', 'baja', 'otros') NOT NULL DEFAULT 'normal',
       ADD COLUMN horas_dia INT NOT NULL DEFAULT 0;
   ```

2. Ajustes en el Formulario
   - Agregado selector de tipo de registro
   - Implementada lógica para mostrar/ocultar campos de hora
   - Validaciones específicas por tipo

3. Visualización en Calendario
   - Implementación de colores por tipo
   - Agregados iconos distintivos
   - Mejora en la presentación de información

4. Correcciones
   - Solucionado problema con campos NULL en hora_entrada y hora_salida
   - Ajustada la validación de superposición de horarios
   - Corregido el cálculo de horas trabajadas

### Problemas Encontrados y Soluciones
1. **Problema**: Error al guardar tipos diferentes a "normal"
   - Causa: Campos de hora no permitían NULL
   - Solución: Modificación de la estructura de la tabla

2. **Problema**: Visualización incorrecta en calendario
   - Causa: Faltaba manejo de tipos en la consulta
   - Solución: Actualización de obtener_horarios.php

### Notas Importantes
- Se mantiene un registro por día por empleado
- Los tipos diferentes a "normal" no requieren horas
- Se implementó copia de horarios entre meses

### Próximos Pasos
1. Implementar reportes por tipo de registro
2. Agregar filtros en el calendario
3. Mejorar la visualización de estadísticas

---

[Continuar agregando entradas con fecha y detalles de cada cambio/petición]

# Bitácora de Cambios - MOLIMEPI

## [18/03/2024] - Validaciones y Sistema de Notificaciones

### Validaciones de Contraseña
- Implementación de validaciones para contraseñas nuevas:
  - Mínimo 6 caracteres
  - Al menos una letra mayúscula
  - Al menos una letra minúscula
  - Al menos un número
  - No permitir símbolos especiales
- Validaciones tanto del lado del cliente (JavaScript) como del servidor (PHP)
- Archivo: `public/js/validaciones.js`

### Sistema de Recuperación de Contraseña
- Nueva tabla `solicitudes_password` para gestionar solicitudes
- Implementación de flujo completo:
  1. Usuario solicita recuperación
  2. Administradores reciben notificación
  3. Administrador aprueba/rechaza
  4. Usuario recibe notificación y nueva contraseña
- Archivos:
  - `public/olvide_password.php`
  - `public/solicitudes_password.php`
  - `public/aprobar_reset.php`

### Sistema de Notificaciones
- Nueva estructura en tabla `notificaciones`
- Implementación de notificaciones en tiempo real
- Campana de notificaciones en navbar
- Contador de notificaciones no leídas
- Archivos:
  - `public/js/notificaciones.js`
  - `public/obtener_notificaciones.php`
  - `public/marcar_notificacion_leida.php`

### Validación de Horarios
- Verificación de duplicidad en copiar horarios
- Prevención de superposición de horarios
- Archivo: `public/copiar_horarios.php`

## [17/03/2024] - Gestión de Tipos de Horarios
- Implementación de diferentes tipos de registro
- Modificación de la tabla para permitir valores NULL
- Cálculo automático de horas trabajadas
- Visualización diferenciada por colores

## [16/03/2024] - Mejoras en el Formulario de Horarios
- Validación dinámica según tipo de registro
- Campos de hora opcionales para tipos diferentes a 'normal'
- Prevención de superposición de horarios
- Mejora en la interfaz de usuario

## [15/03/2024] - Funcionalidad de Copia de Horarios
- Implementación de copia de horarios entre meses
- Validación de fechas válidas
- Mantenimiento de tipos y horas en la copia
- Interfaz intuitiva para selección de mes

## [19/03/2024] - Creación de Tabla Solicitudes Password
### Problema Detectado
- Error en `olvide_password.php` por falta de tabla `solicitudes_password`
- Error: "Base table or view not found: 1146 Table 'molimepi.solicitudes_password' doesn't exist"

### Solución Implementada
- Creación de nueva tabla `solicitudes_password` con la siguiente estructura:
  ```sql
  CREATE TABLE solicitudes_password (
      id INT AUTO_INCREMENT PRIMARY KEY,
      usuario_id INT NOT NULL,
      token VARCHAR(255) NOT NULL,
      estado ENUM('Pendiente', 'Aprobada', 'Rechazada') NOT NULL DEFAULT 'Pendiente',
      fecha_solicitud TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      fecha_actualizacion TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
      FOREIGN KEY (usuario_id) REFERENCES usuarios(ID)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
  ```

### Detalles de la Implementación
- Tabla diseñada para gestionar solicitudes de cambio de contraseña
- Campos para seguimiento temporal (fecha_solicitud, fecha_actualizacion)
- Estados definidos: Pendiente, Aprobada, Rechazada
- Relación con tabla usuarios mediante FOREIGN KEY

### Impacto
- Habilitación del sistema de recuperación de contraseña
- Mejora en la seguridad del sistema
- Trazabilidad de solicitudes de cambio de contraseña

## [20/03/2024] - Correcciones en Sistema de Notificaciones

### Problemas Detectados
1. La campana de notificaciones no mostraba las alertas correctamente
2. Las notificaciones no redirigían a las páginas correspondientes
3. El proceso de validación de solicitudes de contraseña no funcionaba adecuadamente

### Soluciones Implementadas
1. Correcciones en `public/js/notificaciones.js`:
   - Mejorado el manejo de errores
   - Corregida la visualización del contador
   - Implementada redirección correcta según tipo de notificación

2. Optimización de `public/obtener_notificaciones.php`:
   - Mejorada la consulta SQL para ordenar por fecha y estado de lectura
   - Corregido el formato de respuesta JSON
   - Simplificada la generación de URLs

3. Ajustes en `public/solicitudes_password.php`:
   - Corregido el proceso de actualización de estado
   - Mejorada la creación de notificaciones
   - Eliminados campos innecesarios

### Impacto
- Mejor experiencia de usuario en la gestión de notificaciones
- Sistema de recuperación de contraseña funcionando correctamente
- Notificaciones más claras y funcionales

## [20/03/2024] - Mejoras en Interfaz y Exportación de Horarios

### Problemas Detectados
1. Las notificaciones no funcionaban correctamente en todas las páginas
2. El navbar se movía al hacer scroll
3. No existía opción para exportar horarios en diferentes formatos

### Soluciones Implementadas
1. Correcciones en Sistema de Notificaciones:
   - Actualización de rutas en `notificaciones.js` para usar rutas absolutas
   - Mejorado el manejo de URLs en las notificaciones

2. Mejoras en la Interfaz:
   - Navbar fijo en la parte superior con `fixed-top`
   - Agregado espacio de compensación para evitar superposición
   - Mejorada la visualización en todas las páginas

3. Sistema de Exportación:
   - Implementada exportación a Excel usando PhpSpreadsheet
   - Implementada exportación a PDF usando Dompdf
   - Agregados botones de exportación en la página de horarios
   - Personalización de estilos en los reportes

### Archivos Modificados
- `public/js/notificaciones.js`
- `public/navbar.php`
- `public/horarios.php`
- Nuevo: `public/exportar_horarios.php`
- Nuevo: `composer.json`

### Dependencias Agregadas
```json
{
    "require": {
        "phpoffice/phpspreadsheet": "^1.29",
        "dompdf/dompdf": "^2.0"
    }
}
```

### Impacto
- Mejor experiencia de usuario con navegación fija
- Sistema de notificaciones más robusto
- Nuevas funcionalidades de exportación de datos
- Reportes profesionales en PDF y Excel 

## [21/03/2024] - Correcciones en Dropdowns y Sistema de Notificaciones

### Problemas Detectados
1. Los dropdowns del navbar no funcionaban correctamente
2. Las notificaciones no se mostraban en todas las páginas
3. Conflictos con múltiples instancias de jQuery y Bootstrap

### Soluciones Implementadas
1. Reorganización de Scripts:
   - Carga ordenada de dependencias (jQuery, Popper.js, Bootstrap)
   - Eliminación de cargas duplicadas de scripts
   - Implementación de variable global para rutas base

2. Mejoras en Dropdowns:
   - Inicialización explícita de dropdowns de Bootstrap
   - Corrección de eventos click en notificaciones
   - Mejor manejo de estados de dropdown

3. Optimización de Notificaciones:
   - Uso de rutas absolutas consistentes
   - Mejora en el manejo de eventos
   - Prevención de conflictos de JavaScript

### Archivos Modificados
- `public/navbar.php`: Reorganización de scripts y mejora en estructura
- `public/js/notificaciones.js`: Actualización de lógica de notificaciones
- Varios archivos: Eliminación de cargas duplicadas de scripts

### Impacto
- Mejor funcionamiento de elementos de navegación
- Sistema de notificaciones más robusto
- Reducción de conflictos entre scripts
- Mejor experiencia de usuario en toda la aplicación 

## [20/03/2024] - Implementación de Sistema de Comentarios en Notificaciones

### Problema Detectado
- Los administradores necesitaban una forma de proporcionar retroalimentación al aprobar o rechazar solicitudes de restablecimiento de contraseña
- Los usuarios no recibían información detallada sobre por qué su solicitud fue aprobada o rechazada

### Cambios Realizados
1. Modificación de la tabla `notificaciones` para incluir campo de comentarios
2. Actualización de `solicitudes_password.php`:
   - Agregado campo de comentario en el formulario de aprobación/rechazo
   - Implementación de modal SweetAlert2 para captura de comentarios
   - Mejora en la experiencia de usuario al procesar solicitudes

### Impacto
- Mayor transparencia en el proceso de gestión de contraseñas
- Mejor comunicación entre administradores y usuarios
- Registro histórico de decisiones administrativas

### Detalles Técnicos
- Uso de SweetAlert2 para interfaces de usuario mejoradas
- Validación de comentarios obligatorios
- Integración con el sistema existente de notificaciones 

## [21/03/2024] - Correcciones en Manejo de Imágenes y Eliminación de Usuarios

### Problemas Detectados
1. Inconsistencia en las rutas de imágenes en la base de datos
2. Modal de eliminación no funcionaba correctamente con Bootstrap 5
3. Falta de limpieza de archivos de imagen al eliminar usuarios

### Soluciones Implementadas
1. Estandarización de Rutas de Imágenes:
   - Todas las rutas ahora usan el formato `public/imgs/nombre_imagen.jpg`
   - Corrección en la lógica de guardado de imágenes en `empleados.php` y `editar_usuario.php`
   - Manejo consistente de la imagen por defecto (`public/imgs/nofoto.png`)

2. Actualización de Modales a Bootstrap 5:
   - Actualización de atributos (`data-bs-toggle`, `data-bs-target`, `data-bs-dismiss`)
   - Reemplazo de clases obsoletas (`close` → `btn-close`)
   - Mejora en el manejo de eventos con JavaScript vanilla

3. Mejora en el Sistema de Eliminación:
   - Implementación de confirmación doble (modal + SweetAlert2)
   - Eliminación automática de archivos de imagen asociados
   - Manejo de errores y mensajes de retroalimentación

### Impacto
- Mejor consistencia en el manejo de archivos de imagen
- Interfaz de usuario más robusta y moderna
- Prevención de archivos huérfanos en el servidor

### Archivos Modificados
- `public/empleados.php`
- `public/editar_usuario.php`
- `public/eliminar_usuario.php` (nuevo)

### Próximos Pasos
1. Implementar validación de tipos de archivo para imágenes
2. Agregar compresión de imágenes
3. Mejorar la gestión de permisos de archivos 