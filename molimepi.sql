-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 18-02-2025 a las 23:39:40
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
  `hora_entrada` time NOT NULL,
  `hora_salida` time NOT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `horarios_trabajo`
--

INSERT INTO `horarios_trabajo` (`id`, `usuario_id`, `fecha`, `hora_entrada`, `hora_salida`, `creado_en`) VALUES
(2, 19, '2025-02-17', '08:00:00', '18:00:00', '2025-02-17 22:57:27'),
(3, 2, '2025-02-18', '09:00:00', '17:00:00', '2025-02-17 23:20:17'),
(7, 2, '2025-02-12', '16:00:00', '21:00:00', '2025-02-18 11:44:59'),
(8, 18, '2025-02-13', '07:30:00', '16:30:00', '2025-02-18 11:45:21'),
(10, 19, '2025-02-19', '07:00:00', '14:00:00', '2025-02-18 13:26:10'),
(12, 23, '2025-02-13', '07:00:00', '15:00:00', '2025-02-18 21:42:16'),
(13, 2, '2025-02-13', '10:00:00', '18:00:00', '2025-02-18 21:43:03');

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
  `mensaje` text DEFAULT NULL,
  `estado` varchar(50) DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `usuario_id` int(11) DEFAULT NULL,
  `tipo` varchar(50) DEFAULT NULL,
  `estado` varchar(50) DEFAULT NULL,
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
(18, 'CARLOS ANDRÉS HERNÁNDEZ SIERRA', '60565839P', 'JEFE OPERACIONES', '687055651', 'poli.70811@gmail.com', '60565839P', '$2y$10$aGx3JSEgmg9v3VaQqlt2U.sj6uKdZpZ4rdePjDVCp.P4kmYsRKxK6', 'Empleado', 'public/imgs/transparent-bg-designify.png', '2025-02-16 11:36:18', 1),
(19, 'DIEGO RANGEL', '5555666333', 'ENCARGADO ALMACEN', '99988879', 'DIEGOR@MIMOUNMARKET.COM', '5555666333', '$2y$10$mTvovA/GARAyqG.eTISKvuj0dEmJQswOlDr5eJ6Los5PtR.6PJnpW', 'Empleado', 'public/imgs/nofoto.png', '2025-02-16 18:16:27', 0),
(22, 'Nicolas', 'Mora', 'Presidente Teramoda', '6311884545', 'hasdjkasdshbdas@inventado.com', 'Mora', '$2y$10$13qpQ6aJ5rKWG8OjZDYYa.b8s1uV7YrXqpiiEf8MCuGhOM.wKhnFO', 'Administrador', 'imgs/nofoto.png', '2025-02-17 06:19:34', 1),
(23, 'anas', 'g45699744g', 'MOZO ESPECIALISTA', '555555555', 'anas@mimounmarket.com', 'g45699744g', '$2y$10$p6hC/LmeIXMLk/A9S2xMLOSV7lYcSyAoFCY6.QtoNMXIBjApuUdte', 'Empleado', 'imgs/nofoto.png', '2025-02-18 21:41:42', 0);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `logs`
--
ALTER TABLE `logs`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

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
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
