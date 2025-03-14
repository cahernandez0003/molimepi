# MOLIMEPI - Sistema de Gestión de Empleados

## Descripción
MOLIMEPI es un sistema integral de gestión de empleados que permite administrar horarios, asistencias, solicitudes y más. Diseñado para facilitar la gestión del personal en empresas de cualquier tamaño.

## Características Principales

### Gestión de Usuarios
- Registro de empleados y administradores
- Perfiles con información detallada
- Gestión de roles y permisos
- Sistema de recuperación de contraseña
- Validaciones de seguridad en contraseñas
- Manejo seguro de imágenes de perfil
- Eliminación segura de usuarios y archivos asociados
- Visualización detallada de información de empleados en dashboard

### Control de Acceso
- Restricción de acceso basada en roles de usuario
- Dashboard exclusivo para administradores
- Página informativa de acceso denegado
- Redirección automática para usuarios sin permisos
- Mensajes claros sobre los motivos de restricción
- Seguridad mejorada para información sensible

### Gestión de Horarios
- Registro de horarios de trabajo
- Diferentes tipos de registro (normal, descanso, baja, otros)
- Copia de horarios entre meses
- Prevención de superposición de horarios
- Visualización en calendario
- Cálculo automático de horas trabajadas
- Exportación de datos a Excel sin librerías externas

### Sistema de Notificaciones
- Notificaciones en tiempo real
- Diferentes tipos de notificaciones (contraseña, horarios, general)
- Contador de notificaciones no leídas
- Marcado automático de notificaciones leídas

### Solicitudes y Aprobaciones
- Solicitudes de cambio de contraseña
- Solicitudes de horas extra
- Sistema de aprobación por administradores
- Notificaciones automáticas de estado

### Manejo de Archivos
- Almacenamiento estructurado de imágenes
- Rutas estandarizadas para archivos
- Limpieza automática de archivos no utilizados
- Imagen por defecto para nuevos usuarios
- Validación de tipos de archivo
- Gestión de permisos de acceso

#### Sistema de Horas Extra
- Registro y seguimiento de horas extra trabajadas
- Aprobación total o parcial de horas extra solicitadas
- Sistema de notificaciones para estados de solicitudes
- Interfaz intuitiva para gestión de solicitudes
- Validaciones automáticas de horas y fechas
- Histórico de solicitudes con estados y comentarios
- Visualización detallada en dashboard de usuario

#### Control de Asistencia
- Registro de entrada y salida de personal
- Cálculo automático de horas trabajadas
- Integración con sistema de horas extra
- Control de edición basado en estados de solicitudes
- Visualización clara del estado de registros
- Interfaz responsiva y amigable

### Gestión de Vacaciones
- Control y seguimiento de solicitudes de vacaciones
- Límite automático de 31 días de vacaciones por año por empleado
- Registro de aprobaciones con trazabilidad del aprobador
- Validaciones automáticas para prevenir solapamientos y excesos
- Interfaz intuitiva para la gestión de solicitudes y aprobaciones
- Visualización de períodos de vacaciones en dashboard

### Exportación de Datos
- Exportación a Excel sin necesidad de librerías externas
- Reportes detallados por usuario
- Información de horarios, vacaciones y horas extra
- Cálculo automático de totales
- Diseño profesional con estilos y formato adecuado
- Compatibilidad con todas las versiones de Excel

## Requisitos Técnicos

### Servidor
- PHP 8.2 o superior
- MySQL/MariaDB 10.4 o superior
- Servidor web Apache/Nginx

### Dependencias
- PHPMailer para envío de correos
- Bootstrap 4.6 para la interfaz
- jQuery 3.6 para funcionalidades dinámicas
- SweetAlert2 para alertas personalizadas
- Font Awesome para iconos

## Instalación

1. Clonar el repositorio:
```bash
git clone [URL_DEL_REPOSITORIO]
```

2. Importar la base de datos:
```bash
mysql -u [usuario] -p [nombre_base_datos] < molimepi.sql
```

3. Configurar el archivo de conexión:
```bash
cp config/database.example.php config/database.php
```
Editar `config/database.php` con los datos de conexión.

4. Configurar el servidor de correo:
- Editar las credenciales SMTP en los archivos que usan PHPMailer
- Asegurarse de que el servidor permita envío de correos

5. Configurar permisos:
```bash
chmod 755 public/imgs/
chmod 644 public/imgs/*
```

## Estructura del Proyecto

```
molimepi/
├── config/
│   ├── database.php
│   └── phpmailer/
├── public/
│   ├── imgs/          # Almacenamiento de imágenes
│   │   └── nofoto.png # Imagen por defecto
│   ├── js/
│   └── *.php
└── docs/
    ├── BITACORA.md
    └── README.md
```

## Estructura de la Base de Datos

### Tablas Principales
- `usuarios`: Almacena información de usuarios y empleados
- `horarios_trabajo`: Registra horarios y tipos de jornada
- `registro_asistencia`: Control de asistencia diaria
- `solicitudes_password`: Gestión de solicitudes de cambio de contraseña
- `notificaciones`: Sistema de notificaciones internas
- `vacaciones`: Gestión de solicitudes y períodos de vacaciones
- `hrex_empleado`: Registro de horas extra trabajadas

### Estructura de Tablas Clave

#### solicitudes_password
```sql
CREATE TABLE solicitudes_password (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    token VARCHAR(255) NOT NULL,
    estado ENUM('Pendiente', 'Aprobada', 'Rechazada') NOT NULL DEFAULT 'Pendiente',
    fecha_solicitud TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(ID)
);
```

Esta tabla es fundamental para:
- Gestión segura de solicitudes de cambio de contraseña
- Seguimiento de estados de solicitudes
- Registro temporal de cambios
- Vinculación con sistema de notificaciones

## Seguridad

- Contraseñas hasheadas con BCRYPT
- Validación de sesiones
- Protección contra SQL Injection
- Validación de permisos por rol
- Tokens únicos para recuperación de contraseña
- Confirmación doble para acciones críticas
- Manejo seguro de archivos subidos

## Mantenimiento

### Base de Datos
- Realizar backups periódicos
- Limpiar notificaciones antiguas
- Monitorear el crecimiento de las tablas
- Verificar integridad de datos

### Archivos
- Limpiar imágenes no utilizadas
- Revisar logs de errores
- Actualizar dependencias
- Mantener permisos de archivos correctos

## Soporte

Para reportar problemas o solicitar ayuda:
1. Revisar la documentación
2. Consultar la bitácora de cambios
3. Contactar al administrador del sistema

## Licencia
Este proyecto está bajo la licencia [TIPO_DE_LICENCIA].

### Convenciones de Código
- Rutas de imágenes: `public/imgs/nombre_imagen.jpg`