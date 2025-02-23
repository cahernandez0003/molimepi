-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 23-02-2025 a las 12:52:24
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `molimepi`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empleados`
--

CREATE TABLE `empleados` (
  `ID` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `antiguedad` int(11) DEFAULT NULL,
  `horario_planificado` varchar(255) DEFAULT NULL,
  `vacaciones` int(11) DEFAULT NULL,
  `horas_extra` int(11) DEFAULT NULL,
  `dias_extra` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `horarios_trabajo`
--

CREATE TABLE `horarios_trabajo` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `hora_entrada` time DEFAULT NULL,
  `hora_salida` time DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `tipo` enum('normal','descanso','baja','otros') NOT NULL DEFAULT 'normal',
  `horas_dia` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `horarios_trabajo`
--

INSERT INTO `horarios_trabajo` (`id`, `usuario_id`, `fecha`, `hora_entrada`, `hora_salida`, `creado_en`, `tipo`, `horas_dia`) VALUES
(2, 19, '2025-02-17', '08:00:00', '14:00:00', '2025-02-17 22:57:27', 'normal', 6),
(3, 2, '2025-02-18', '09:00:00', '17:00:00', '2025-02-17 23:20:17', 'normal', 8),
(7, 2, '2025-02-12', '16:00:00', '19:00:00', '2025-02-18 11:44:59', 'normal', 3),
(12, 23, '2025-02-13', '07:00:00', '16:00:00', '2025-02-18 21:42:16', 'normal', 9),
(17, 18, '2025-02-19', '07:00:00', '15:00:00', '2025-02-18 23:08:26', 'normal', 8),
(18, 19, '2025-02-19', '07:00:00', '13:00:00', '2025-02-18 23:08:43', 'normal', 6),
(19, 2, '2025-02-13', '13:00:00', '15:00:00', '2025-02-18 23:25:33', 'normal', 2),
(22, 18, '2025-02-11', '07:00:00', '15:00:00', '2025-02-19 00:08:47', 'normal', 8),
(23, 18, '2025-02-10', '07:00:00', '15:00:00', '2025-02-19 00:13:48', 'normal', 8),
(24, 2, '2025-02-10', '01:00:00', '02:00:00', '2025-02-19 00:21:00', 'normal', 1),
(26, 23, '2025-02-11', NULL, NULL, '2025-02-19 00:28:59', 'descanso', 0),
(27, 2, '2025-02-11', NULL, NULL, '2025-02-19 00:29:28', 'otros', 0),
(83, 18, '2025-02-21', '07:00:00', '15:00:00', '2025-02-21 20:29:38', 'normal', 8),
(84, 19, '2025-03-17', '08:00:00', '14:00:00', '2025-02-21 20:32:10', 'normal', 6),
(85, 2, '2025-03-18', '09:00:00', '17:00:00', '2025-02-21 20:32:10', 'normal', 8),
(86, 2, '2025-03-12', '16:00:00', '19:00:00', '2025-02-21 20:32:10', 'normal', 3),
(87, 23, '2025-03-13', '07:00:00', '16:00:00', '2025-02-21 20:32:10', 'normal', 9),
(88, 18, '2025-03-19', '07:00:00', '15:00:00', '2025-02-21 20:32:10', 'normal', 8),
(89, 19, '2025-03-19', '07:00:00', '13:00:00', '2025-02-21 20:32:10', 'normal', 6),
(90, 2, '2025-03-13', NULL, NULL, '2025-02-21 20:32:10', 'descanso', 0),
(91, 18, '2025-03-12', '13:00:00', '19:00:00', '2025-02-21 20:32:10', 'normal', 6),
(92, 18, '2025-03-11', '07:00:00', '15:00:00', '2025-02-21 20:32:10', 'normal', 8),
(94, 2, '2025-03-10', '01:00:00', '02:00:00', '2025-02-21 20:32:10', 'normal', 1),
(95, 23, '2025-03-11', NULL, NULL, '2025-02-21 20:32:10', 'descanso', 0),
(96, 2, '2025-03-11', NULL, NULL, '2025-02-21 20:32:10', 'otros', 0),
(97, 18, '2025-03-21', '08:00:00', '14:00:00', '2025-02-21 20:32:10', 'normal', 6),
(98, 19, '2025-02-12', NULL, NULL, '2025-02-23 11:36:12', 'descanso', 0),
(99, 23, '2025-02-12', '01:00:00', '06:00:00', '2025-02-23 11:36:53', 'normal', 5);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `logs`
--

CREATE TABLE `logs` (
  `ID` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `acción` text DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones`
--

CREATE TABLE `notificaciones` (
  `ID` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `tipo` varchar(50) DEFAULT NULL,
  `referencia_id` int(11) DEFAULT NULL,
  `mensaje` text DEFAULT NULL,
  `comentario` text DEFAULT NULL,
  `leida` tinyint(1) DEFAULT 0,
  `estado` varchar(50) DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `notificaciones`
--

INSERT INTO `notificaciones` (`ID`, `usuario_id`, `tipo`, `referencia_id`, `mensaje`, `comentario`, `leida`, `estado`, `fecha_creacion`) VALUES
(1, 19, 'solicitud_password', NULL, 'Tu solicitud de restablecimiento de contraseña ha sido aprobada. Tu nueva contraseña temporal es: 123456', NULL, 0, NULL, '2025-02-19 23:54:56'),
(2, 1, 'solicitud_password', 2, 'El usuario DIEGO RANGEL ha solicitado restablecer su contraseña', NULL, 1, NULL, '2025-02-19 23:58:03'),
(3, 22, 'solicitud_password', 2, 'El usuario DIEGO RANGEL ha solicitado restablecer su contraseña', NULL, 0, NULL, '2025-02-19 23:58:09'),
(4, 24, 'solicitud_password', 2, 'El usuario DIEGO RANGEL ha solicitado restablecer su contraseña', NULL, 0, NULL, '2025-02-19 23:58:16'),
(5, 19, 'solicitud_password', NULL, 'Tu solicitud de restablecimiento de contraseña ha sido rechazada.', NULL, 0, NULL, '2025-02-19 23:58:42'),
(6, 1, 'solicitud_password', 3, 'El usuario DIEGO RANGEL ha solicitado restablecer su contraseña', NULL, 1, NULL, '2025-02-19 23:59:05'),
(7, 22, 'solicitud_password', 3, 'El usuario DIEGO RANGEL ha solicitado restablecer su contraseña', NULL, 0, NULL, '2025-02-19 23:59:12'),
(8, 24, 'solicitud_password', 3, 'El usuario DIEGO RANGEL ha solicitado restablecer su contraseña', NULL, 0, NULL, '2025-02-19 23:59:19'),
(9, 19, 'solicitud_password', NULL, 'Tu solicitud de restablecimiento de contraseña ha sido aprobada. Tu nueva contraseña temporal es: 123456', NULL, 0, NULL, '2025-02-20 00:01:32'),
(10, 1, 'solicitud_password', 4, 'El usuario CARLOS ANDRÉS HERNÁNDEZ SIERRA ha solicitado restablecer su contraseña', NULL, 1, NULL, '2025-02-21 20:33:52'),
(11, 22, 'solicitud_password', 4, 'El usuario CARLOS ANDRÉS HERNÁNDEZ SIERRA ha solicitado restablecer su contraseña', NULL, 0, NULL, '2025-02-21 20:34:00'),
(12, 24, 'solicitud_password', 4, 'El usuario CARLOS ANDRÉS HERNÁNDEZ SIERRA ha solicitado restablecer su contraseña', NULL, 0, NULL, '2025-02-21 20:34:07'),
(13, 18, 'solicitud_password', NULL, 'Tu solicitud de restablecimiento de contraseña ha sido aprobada. Tu nueva contraseña temporal es: 123456', NULL, 0, NULL, '2025-02-21 20:34:49');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `registro_asistencia`
--

CREATE TABLE `registro_asistencia` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `hora_entrada` time NOT NULL,
  `hora_salida` time DEFAULT NULL,
  `total_horas` decimal(5,2) DEFAULT 0.00,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `solicitudes`
--

CREATE TABLE `solicitudes` (
  `ID` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `tipo` enum('Vacaciones','Permiso especial','Asistencia sanitaria','Otros') DEFAULT NULL,
  `estado` enum('Pendiente','Aprobado','Rechazado','Aprobado Parcialmente') DEFAULT 'Pendiente',
  `comentario` mediumtext DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `solicitudes_horas_extra`
--

CREATE TABLE `solicitudes_horas_extra` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `horas_solicitadas` decimal(5,2) NOT NULL,
  `horas_aprobadas` decimal(5,2) DEFAULT 0.00,
  `estado` enum('Pendiente','Aprobado','Rechazado','Aprobado Parcialmente') DEFAULT 'Pendiente',
  `comentarios` text DEFAULT NULL,
  `aprobado_por` int(11) DEFAULT NULL,
  `aprobado_en` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `solicitudes_password`
--

CREATE TABLE `solicitudes_password` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `estado` enum('Pendiente','Aprobada','Rechazada') NOT NULL DEFAULT 'Pendiente',
  `fecha_solicitud` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `solicitudes_password`
--

INSERT INTO `solicitudes_password` (`id`, `usuario_id`, `token`, `estado`, `fecha_solicitud`, `fecha_actualizacion`) VALUES
(1, 19, 'c1e7237a598279ebbc17273c607915bb', 'Aprobada', '2025-02-19 23:28:03', '2025-02-19 23:54:56'),
(2, 19, '8d2bd2b583244959abea48f571930d39', 'Rechazada', '2025-02-19 23:58:03', '2025-02-19 23:58:42'),
(3, 19, 'bd366f63a4f5612b8d99708e35ce4d77', 'Aprobada', '2025-02-19 23:59:05', '2025-02-20 00:01:32'),
(4, 18, '5b1b3c6d36b954bd47cf206dda36ecb5', 'Aprobada', '2025-02-21 20:33:52', '2025-02-21 20:34:49');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `ID` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `identificacion` varchar(50) NOT NULL,
  `cargo` varchar(50) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `nickname` varchar(50) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `rol` enum('Administrador','Empleado') DEFAULT 'Empleado',
  `imagen` varchar(255) DEFAULT 'imgs/nofoto.png',
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `cambio_password` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`ID`, `nombre`, `identificacion`, `cargo`, `telefono`, `correo`, `nickname`, `password`, `rol`, `imagen`, `fecha_registro`, `cambio_password`) VALUES
(1, 'Admin Ejemplo', '12345678', 'Administrador', '1234567890', 'admin@molimepi.com', '12345678', '$2y$10$/5H3v7XJX/LMSazUX7nBmuPDJHVWgEs/UFs73iJ7xXNeTUgHP..Ay', 'Administrador', 'public/imgs/d7ffed4b9b4aa74281d77c1aad3e2ec5.jpg', '2025-02-13 19:09:25', 1),
(2, 'Empleado Ejemplo', '87654321', 'Empleado', '0987654321', 'empleado@ejemplo.com', '87654321', '$2y$10$xMskf2AerDH6eVLVheLz1.yPHtuJER524ht/iFFmM/yd84JIBhwJm', 'Empleado', 'public/imgs/nofoto.png', '2025-02-13 19:09:25', 0),
(18, 'CARLOS ANDRÉS HERNÁNDEZ SIERRA', '60565839P', 'JEFE OPERACIONES', '687055651', 'poli.70811@gmail.com', '60565839P', '$2y$10$y1YXjeQJI4zzJlE7x77LfuYZ1Ll36qEug4Fhw0RU7Kkoso41zgSNq', 'Empleado', 'public/imgs/transparent-bg-designify.png', '2025-02-16 11:36:18', 1),
(19, 'DIEGO RANGEL', '5555666333', 'ENCARGADO ALMACEN', '99988879', 'DIEGOR@MIMOUNMARKET.COM', '5555666333', '$2y$10$A/GXx3g53qZnpnHV5NTuMebi9F/7AbkStbiKVZ7wkOB.l0fRWofv.', 'Empleado', 'public/imgs/nofoto.png', '2025-02-16 18:16:27', 0),
(22, 'Nicolas', 'Mora', 'Presidente Teramoda', '6311884545', 'hasdjkasdshbdas@inventado.com', 'Mora', '$2y$10$13qpQ6aJ5rKWG8OjZDYYa.b8s1uV7YrXqpiiEf8MCuGhOM.wKhnFO', 'Administrador', 'imgs/ZR03TUTTI13.jpg', '2025-02-17 06:19:34', 1),
(23, 'anas', 'g45699744g', 'MOZO ESPECIALISTA', '555555555', 'anas@mimounmarket.com', 'g45699744g', '$2y$10$p6hC/LmeIXMLk/A9S2xMLOSV7lYcSyAoFCY6.QtoNMXIBjApuUdte', 'Empleado', 'imgs/nofoto.png', '2025-02-18 21:41:42', 0),
(24, 'Admin Sistema', 'ADMIN001', 'Administrador Sistema', '123456789', 'admin@molimepi.com', 'admin', '.yPHtuJER524ht/iFFmM/yd84JIBhwJm', 'Administrador', 'imgs/nofoto.png', '2025-02-19 23:40:37', 1),
(25, 'GARI NEVIL', '75103000', 'PRUEBA', '9696969696', 'ANIMAL@PLANET.COM', '75103000', '$2y$10$.CG2fgqeHzUEj7Bq0Jwmju6cbcsuFNeCK2hPhg9kC2aV98WPJUh5O', 'Empleado', 'imgs/nofoto.png', '2025-02-23 11:35:11', 0),
(27, 'prueba prueba', 'prueba', 'prueba', 'prueba', 'prueba@prueba', 'prueba', '$2y$10$cysnAobQtKewByANtk0K8O12uc3/lYlgOnPUs99JWlHUkU6HvlHb.', 'Empleado', 'public/imgs/0VACA.jpg', '2025-02-23 11:39:23', 0),
(28, 'kkkk', 'kkkk', 'kkkk', 'kkkk', 'kkk@kkkk', 'kkkk', '$2y$10$jDMuLjSKk/tXrJKH06y94u1J4z2Jl23JBJJPSzQ3yw54sOrecGvJG', 'Empleado', 'imgs/CO06SAMRA01.jpg', '2025-02-23 11:40:28', 0);

--
-- Disparadores `usuarios`
--
DELIMITER $$
CREATE TRIGGER `before_insert_usuarios` BEFORE INSERT ON `usuarios` FOR EACH ROW BEGIN
    IF NEW.nickname IS NULL OR NEW.nickname = '' THEN
        SET NEW.nickname = NEW.identificacion;
    END IF;
END
$$
DELIMITER ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `empleados`
--
ALTER TABLE `empleados`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `horarios_trabajo`
--
ALTER TABLE `horarios_trabajo`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `registro_asistencia`
--
ALTER TABLE `registro_asistencia`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `solicitudes`
--
ALTER TABLE `solicitudes`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `solicitudes_horas_extra`
--
ALTER TABLE `solicitudes_horas_extra`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `aprobado_por` (`aprobado_por`);

--
-- Indices de la tabla `solicitudes_password`
--
ALTER TABLE `solicitudes_password`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `nickname` (`nickname`),
  ADD UNIQUE KEY `nickname_2` (`nickname`),
  ADD KEY `identificacion` (`identificacion`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `empleados`
--
ALTER TABLE `empleados`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `horarios_trabajo`
--
ALTER TABLE `horarios_trabajo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=100;

--
-- AUTO_INCREMENT de la tabla `logs`
--
ALTER TABLE `logs`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `registro_asistencia`
--
ALTER TABLE `registro_asistencia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `solicitudes`
--
ALTER TABLE `solicitudes`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `solicitudes_horas_extra`
--
ALTER TABLE `solicitudes_horas_extra`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `solicitudes_password`
--
ALTER TABLE `solicitudes_password`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `empleados`
--
ALTER TABLE `empleados`
  ADD CONSTRAINT `empleados_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`ID`);

--
-- Filtros para la tabla `horarios_trabajo`
--
ALTER TABLE `horarios_trabajo`
  ADD CONSTRAINT `horarios_trabajo_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`ID`) ON DELETE CASCADE;

--
-- Filtros para la tabla `logs`
--
ALTER TABLE `logs`
  ADD CONSTRAINT `logs_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`ID`);

--
-- Filtros para la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD CONSTRAINT `notificaciones_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`ID`);

--
-- Filtros para la tabla `registro_asistencia`
--
ALTER TABLE `registro_asistencia`
  ADD CONSTRAINT `registro_asistencia_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`ID`) ON DELETE CASCADE;

--
-- Filtros para la tabla `solicitudes`
--
ALTER TABLE `solicitudes`
  ADD CONSTRAINT `solicitudes_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`ID`);

--
-- Filtros para la tabla `solicitudes_horas_extra`
--
ALTER TABLE `solicitudes_horas_extra`
  ADD CONSTRAINT `solicitudes_horas_extra_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `solicitudes_horas_extra_ibfk_2` FOREIGN KEY (`aprobado_por`) REFERENCES `usuarios` (`ID`) ON DELETE SET NULL;

--
-- Filtros para la tabla `solicitudes_password`
--
ALTER TABLE `solicitudes_password`
  ADD CONSTRAINT `solicitudes_password_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`ID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
