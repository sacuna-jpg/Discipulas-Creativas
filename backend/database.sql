-- ==========================================================
-- Base de Datos: Discipulas Creativas
-- Compatible con MySQL 5.7+ / MySQL 8.0+ / MariaDB
-- ==========================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------------------------------------
-- 1. Tabla: distritos
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `distritos` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nombre` VARCHAR(150) NOT NULL UNIQUE,
  `creado_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 2. Tabla: iglesias
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `iglesias` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `distrito_id` INT NOT NULL,
  `nombre` VARCHAR(150) NOT NULL,
  `creado_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`distrito_id`) REFERENCES `distritos`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 3. Tabla: usuarios
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nombre` VARCHAR(150) NOT NULL,
  `correo` VARCHAR(150) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `distrito_id` INT NULL,
  `iglesia_id` INT NULL,
  `creado_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`distrito_id`) REFERENCES `distritos`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`iglesia_id`) REFERENCES `iglesias`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 4. Tabla: modulos
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `modulos` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `numero` INT NOT NULL UNIQUE,
  `titulo` VARCHAR(200) NOT NULL,
  `descripcion` TEXT NOT NULL,
  `youtube_id` VARCHAR(50) NOT NULL,
  `duracion` VARCHAR(50) DEFAULT 'Seminario en Video',
  `activo` TINYINT(1) DEFAULT 1,
  `creado_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 5. Tabla: preguntas (2 reflexiones por módulo)
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `preguntas` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `modulo_id` INT NOT NULL,
  `orden` INT NOT NULL,
  `enunciado` TEXT NOT NULL,
  `creado_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`modulo_id`) REFERENCES `modulos`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 6. Tabla: respuestas (Reflexiones de las usuarias)
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `respuestas` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `usuario_id` INT NOT NULL,
  `pregunta_id` INT NOT NULL,
  `respuesta` TEXT NOT NULL,
  `enviado_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uk_usuario_pregunta` (`usuario_id`, `pregunta_id`),
  FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`pregunta_id`) REFERENCES `preguntas`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 7. Tabla: progreso (Estado de certificación)
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `progreso` (
  `usuario_id` INT NOT NULL,
  `modulo_id` INT NOT NULL,
  `completado_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`usuario_id`, `modulo_id`),
  FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`modulo_id`) REFERENCES `modulos`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 8. Tabla: sesiones (Autenticación y tokens para API REST)
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sesiones` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `usuario_id` INT NOT NULL,
  `token` VARCHAR(128) NOT NULL UNIQUE,
  `ip_address` VARCHAR(45) NULL,
  `user_agent` TEXT NULL,
  `expira_at` DATETIME NOT NULL,
  `creado_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `ultimo_acceso` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ==========================================================
-- CARGA DE DATOS INICIALES (SEED DATA)
-- ==========================================================

-- Distritos
INSERT INTO `distritos` (`id`, `nombre`) VALUES
(1, 'Algeciras'),
(2, 'Campo Alegre'),
(3, 'Florencia Central'),
(4, 'Florencia Nuevo Amanecer'),
(5, 'Florencia Redención'),
(6, 'Garzón'),
(7, 'La Plata'),
(8, 'Neiva Central'),
(9, 'Neiva Jardín'),
(10, 'Neiva Sur'),
(11, 'Pitalito Norte'),
(12, 'Pitalito Sur'),
(13, 'Puerto Rico')
ON DUPLICATE KEY UPDATE `nombre`=VALUES(`nombre`);

-- Iglesias (Muestreo inicial asociado a distritos)
INSERT INTO `iglesias` (`distrito_id`, `nombre`) VALUES
(1, 'Iglesia Shalom Algeciras'),
(1, 'Iglesia Central Algeciras'),
(1, 'Iglesia Eben Ezer Algeciras'),
(1, 'Iglesia Santana Ramos'),
(1, 'Iglesia Paraíso Algeciras'),
(1, 'Grupo Nueva Jerusalén Algeciras'),
(2, 'Iglesia Central Campo Alegre'),
(2, 'Iglesia Emmanuel Campo Alegre'),
(2, 'Grupo Sinaí Campo Alegre'),
(2, 'Iglesia Libertad Hobo'),
(2, 'Iglesia Jehová Nissi'),
(2, 'Iglesia Sión Rivera'),
(2, 'Iglesia Renacer Hobo'),
(8, 'Iglesia Neiva Central'),
(8, 'Grupo Roca Eterna'),
(8, 'Iglesia AMAS CABI'),
(8, 'Iglesia Getsemaní Neiva'),
(8, 'Iglesia Bethel Aipe'),
(8, 'Iglesia Salem (San Andrés)'),
(8, 'Grupo Tello'),
(8, 'Grupo Palermo'),
(8, 'Grupo Orión Neiva'),
(11, 'Iglesia Pitalito Central'),
(11, 'Iglesia Acevedo'),
(11, 'Grupo Maito'),
(11, 'Grupo Timaná San Antonio'),
(11, 'Iglesia Esperanza Marimba');

-- Catálogo de los 11 Módulos Oficiales
INSERT INTO `modulos` (`id`, `numero`, `titulo`, `descripcion`, `youtube_id`) VALUES
(1, 1, 'Módulo 1: Introducción', 'Bienvenida al seminario de Discípulas Creativas. Conoce el propósito del curso y los pilares del discipulado creativo.', 'GTAE-9enobE'),
(2, 2, 'Módulo 2: Tu Identidad Creativa', 'Descubre tus talentos y dones creativos dados por Dios para bendecir a tu congregación y comunidad.', 'BVfEVHRgN80'),
(3, 3, 'Módulo 3: El Poder del Testimonio', 'Cómo compartir tu historia y las maravillas de Dios a través de herramientas y formatos creativos.', 'PiXd2b528zU'),
(4, 4, 'Módulo 4: Comunidades de Gracia', 'El papel del discipulado en grupo y cómo fomentar un ambiente de amor, creatividad y fe en tu iglesia.', 'dM_hH9MyGUM'),
(5, 5, 'Módulo 5: Redes que Conectan', 'Aprende a usar los medios y la comunicación digital con un propósito eterno y un alcance de discipulado.', 'ZTivyeO5IBU'),
(6, 6, 'Módulo 6: Espacios que Inspiran', 'Ideas prácticas para embellecer y organizar los lugares de reunión y eventos del Ministerio de la Mujer.', 'XrkzVxZZID8'),
(7, 7, 'Módulo 7: Oración Creativa', 'Explora diarios de oración, diarios bíblicos (Bible Journaling) y métodos dinámicos de devoción personal.', 'k_rTSxdcMcs'),
(8, 8, 'Módulo 8: El Arte de Servir', 'Planificación de proyectos de servicio comunitario que marquen la diferencia e impacten vidas positivamente.', 'h8kU7DcH3Wg'),
(9, 9, 'Módulo 9: Palabras de Aliento', 'La escritura, las tarjetas de ánimo y la literatura como puente para alcanzar corazones para Cristo.', 'prVqmEdsEis'),
(10, 10, 'Módulo 10: Eventos con Propósito', 'Cómo configurar programas, retiros y seminarios creativos que unan a las hermanas en comunión.', '9uL8y1vanWk'),
(11, 11, 'Módulo 11: Graduación y Compromiso', 'Conclusión de tu certificación, compromiso de discipulado creativo y próximos pasos para brillar en tu iglesia.', 'Ib2aRTIPCIM')
ON DUPLICATE KEY UPDATE `titulo`=VALUES(`titulo`), `descripcion`=VALUES(`descripcion`), `youtube_id`=VALUES(`youtube_id`);

-- Preguntas de Reflexión (2 por módulo)
INSERT INTO `preguntas` (`modulo_id`, `orden`, `enunciado`) VALUES
(1, 1, '¿A quién animé esta semana?'),
(1, 2, '¿Con quién me conecté a través de algo que compartí?'),
(2, 1, '¿Qué don o talento creativo identifico que Dios me ha entregado?'),
(2, 2, '¿Cómo puedo poner mi creatividad al servicio de una hermana necesitada?'),
(3, 1, '¿De qué manera puedo testificar del amor de Dios usando medios visuales o creativos?'),
(3, 2, '¿A quién le contaré mi testimonio durante los próximos días?'),
(4, 1, '¿Cómo puedo contribuir a un ambiente de mayor gracia y amor en mi grupo pequeño?'),
(4, 2, '¿Qué hermana nueva o desanimada puedo invitar a compartir un espacio de comunión?'),
(5, 1, '¿Qué mensaje positivo o bíblico puedo compartir hoy en mis redes o estados?'),
(5, 2, '¿Con quién me conecté espiritualmente a través de un mensaje digital esta semana?'),
(6, 1, '¿Qué idea sencilla puedo aportar para embellecer el espacio de nuestro Ministerio de la Mujer?'),
(6, 2, '¿Cómo podemos hacer que una visita se sienta cálidamente recibida en nuestra congregación?'),
(7, 1, '¿Qué experiencia especial viví en mi tiempo devocional u oración creativa?'),
(7, 2, '¿Por qué hermana o petición específica estuve orando intensamente?'),
(8, 1, '¿Qué acto de servicio solidario puedo planear para bendecir a mi comunidad?'),
(8, 2, '¿A quién pude servir con alegría esta semana sin esperar nada a cambio?'),
(9, 1, '¿A quién le envié o entregaré una nota de ánimo, tarjeta o versículo inspirador?'),
(9, 2, '¿Cómo impactó una palabra de esperanza en la vida de una persona a mi alrededor?'),
(10, 1, '¿Qué temática o dinámica creativa me gustaría proponer para el próximo retiro o programa?'),
(10, 2, '¿De qué forma fomentaremos la integración entre generaciones en nuestros eventos?'),
(11, 1, '¿Cuál ha sido la mayor bendición o lección aprendida a lo largo de este seminario?'),
(11, 2, '¿Cuál es mi compromiso personal de discipulado creativo de aquí en adelante?');
