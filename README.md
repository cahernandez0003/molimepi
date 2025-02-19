# MOLIMEPI - Sistema de Gestión de Asistencia y Horarios

## Descripción
MOLIMEPI es un sistema web desarrollado para la gestión de asistencia y horarios de empleados. El sistema permite administrar usuarios, horarios de trabajo, registros de asistencia y generar reportes.

## Estructura del Sistema

### Base de Datos
#### Tabla: horarios_trabajo
```sql
CREATE TABLE `horarios_trabajo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `hora_entrada` time NULL,
  `hora_salida` time NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `tipo` enum('normal','descanso','baja','otros') NOT NULL DEFAULT 'normal',
  `horas_dia` int(11) NOT NULL DEFAULT 0
);
```

### Tipos de Registro
- **Normal**: Registro regular con hora de entrada y salida
- **Descanso**: Día libre programado
- **Baja**: Ausencia por enfermedad o similar
- **Otros**: Otros tipos de ausencia

### Características Técnicas
- **Frontend**: HTML5, JavaScript, jQuery, Bootstrap
- **Backend**: PHP 7.4+
- **Base de datos**: MySQL/MariaDB
- **Componentes**:
  - FullCalendar 5.11.3
  - SweetAlert2
  - Bootstrap 4.6.0
  - Moment.js 2.29.4

### Validaciones del Sistema
1. **Horarios Normales**
   - Hora de salida posterior a hora de entrada
   - No superposición de horarios
   - Cálculo automático de horas trabajadas

2. **Otros Tipos**
   - Un registro por empleado por día
   - Sin requisito de horas
   - Validación de tipo de registro

### Visualización
- **Colores por tipo**:
  - Normal: Azul (#007bff)
  - Descanso: Gris (#6c757d)
  - Baja: Blanco/Rojo (#ffffff/#dc3545)
  - Otros: Gris (#6c757d)

- **Iconos**:
  - Normal: Sin icono
  - Descanso: 🏠
  - Baja: 🏥
  - Otros: ⚠️

### Funcionalidades Principales
1. **Gestión de Horarios**
   - Agregar/Editar/Eliminar horarios
   - Diferentes tipos de registro
   - Copia de horarios entre meses

2. **Validaciones**
   - Control de superposición
   - Validación de campos según tipo
   - Verificación de permisos

3. **Interfaz**
   - Calendario interactivo
   - Modales para gestión
   - Mensajes de confirmación

## Instalación y Configuración
1. Requisitos del servidor:
   - PHP 7.4 o superior
   - MySQL/MariaDB
   - Servidor web (Apache/Nginx)

2. Configuración de base de datos:
   - Importar estructura desde `molimepi.sql`
   - Configurar credenciales en `config/database.php`

## Seguridad
- Autenticación de usuarios
- Control de roles (Administrador/Empleado)
- Validación de sesiones
- Protección contra SQL Injection
- Sanitización de datos

## Mantenimiento
- Respaldo regular de base de datos
- Monitoreo de logs de error
- Actualización de dependencias
- Revisión de permisos

## Soporte
Para reportar problemas o solicitar mejoras:
1. Documentar el problema/solicitud
2. Incluir capturas de pantalla si es necesario
3. Especificar el comportamiento esperado

## Características Principales

### Gestión de Usuarios
- Registro y administración de empleados
- Roles diferenciados (Administrador y Empleado)
- Gestión de perfiles de usuario
- Cambio de contraseñas
- Carga de imágenes de perfil

### Control de Horarios
- Calendario interactivo para programación de horarios
- Asignación de horarios por empleado
- Visualización de horarios diarios, semanales y mensuales
- Edición y eliminación de horarios programados

### Registro de Asistencia
- Marcación de entrada y salida
- Registro de asistencia diaria
- Visualización de registros históricos
- Estado de asistencia en tiempo real

### Solicitudes y Permisos
- Sistema de solicitudes para empleados
- Gestión de permisos y ausencias
- Comunicación interna mediante sistema de correos

### Reportes y Exportación
- Generación de reportes de asistencia
- Exportación de datos en formatos PDF y Excel
- Visualización de estadísticas

## Tecnologías Utilizadas
- PHP
- MySQL/PDO
- HTML5
- CSS3
- JavaScript
- Bootstrap
- FullCalendar
- FontAwesome

## Estructura del Sistema
- `/public`: Archivos públicos y páginas del sistema
- `/config`: Configuraciones y conexión a base de datos
- `/logs`: Registros del sistema
- `/imgs`: Almacenamiento de imágenes

## Roles de Usuario

### Administrador
- Gestión completa de empleados
- Administración de horarios
- Exportación de reportes
- Gestión de solicitudes
- Configuración del sistema

### Empleado
- Visualización de horarios
- Registro de asistencia
- Envío de solicitudes
- Gestión de perfil personal

## Seguridad
- Autenticación de usuarios
- Encriptación de contraseñas
- Control de sesiones
- Validación de roles
- Protección contra inyección SQL

## Desarrollado por
MOLIMEPI - Todos los derechos reservados

## Registro de Cambios y Actualizaciones

### Corrección de Bugs (17/03/2024)
- Corrección en la carga de datos del empleado en el formulario de edición
- Mejora en la sincronización de datos entre el modal y el formulario
- Optimización en la selección de usuarios en el formulario
- Simplificación del proceso de edición de horarios
- Unificación de formularios de agregar y editar en un solo modal
- Eliminación de redirecciones innecesarias
- Mejora en la experiencia de usuario al editar horarios
- Optimización del flujo de trabajo con modales
- Validaciones mejoradas en el formulario
- Corrección en el manejo de eventos de botones
- Mejora en la persistencia de datos durante la edición

### Nuevas Funcionalidades (17/03/2024)
- Implementación de copia de horarios de un mes a otro
- Adición de tipos de registro (normal, descanso, baja, otros)
- Cálculo automático de horas diarias trabajadas
- Mejora en la gestión de horarios especiales
- Validaciones específicas por tipo de registro

### Gestión de Horarios (Última actualización)
- Implementación de validación para evitar superposición de horarios
- Los usuarios pueden tener múltiples horarios en el mismo día siempre que no se superpongan
- Mejora en la edición de horarios existentes
- Validación de rangos de tiempo para prevenir conflictos
- Soporte para diferentes tipos de registros (normal, descanso, baja, otros)
- Cálculo automático de horas trabajadas por día
- Funcionalidad de copia de horarios entre meses

### Validaciones Implementadas
- Verificación de superposición de horarios para el mismo empleado en la misma fecha
- Control de rangos de tiempo para evitar conflictos entre horarios
- Validación de campos obligatorios en formularios
- Mensajes de error específicos para cada tipo de validación

### Recomendaciones de Uso
1. Al crear o editar horarios, asegurarse de que no se superpongan con horarios existentes
2. Verificar que las horas de entrada y salida sean coherentes
3. Mantener actualizada la lista de empleados activos
4. Revisar los mensajes de validación para corregir errores

### Próximas Mejoras Planificadas
- Implementación de vista de calendario para visualización de horarios
- Mejora en la interfaz de usuario para la gestión de horarios
- Sistema de notificaciones para cambios en horarios
- Optimización de consultas de base de datos
