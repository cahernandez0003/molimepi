ALTER TABLE horarios_trabajo ADD COLUMN tipo ENUM('normal', 'descanso', 'baja', 'otros') NOT NULL DEFAULT 'normal', ADD COLUMN horas_dia INT NOT NULL DEFAULT 0;
