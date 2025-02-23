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

### Gestión de Horarios
- Registro de horarios de trabajo
- Diferentes tipos de registro (normal, descanso, baja, otros)
- Copia de horarios entre meses
- Prevención de superposición de horarios
- Visualización en calendario

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
- Imagen por defecto: `public/imgs/nofoto.png`
- Validaciones del lado del cliente y servidor
- Mensajes de error y éxito consistentes
- Modales de confirmación para acciones críticas
