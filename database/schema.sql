-- phpMyAdmin SQL Dump
-- version 5.2.1deb1+deb12u1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Sep 09, 2025 at 04:25 AM
-- Server version: 10.11.14-MariaDB-0+deb12u2
-- PHP Version: 8.2.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `Library`
--

-- --------------------------------------------------------

--
-- Table structure for table `books`
--

CREATE TABLE `books` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `isbn` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'ISBN (optional)',
  `title` varchar(300) NOT NULL COMMENT 'Book title',
  `author` varchar(512) DEFAULT NULL COMMENT 'Book author (may be unknown)',
  `classification_id` varchar(100) DEFAULT '040' COMMENT 'FK to classifications.classification_id',
  `classification_code` varchar(100) DEFAULT NULL COMMENT 'Dewey/code (text)',
  `copies_total` int(11) UNSIGNED DEFAULT 1 COMMENT 'Total copies',
  `origin_id` int(11) DEFAULT 0 COMMENT 'FK to origins.origin_id',
  `copies_available` int(11) UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Available copies',
  `label_id` int(11) DEFAULT NULL COMMENT 'FK to labels.label_id',
  `library_id` bigint(20) DEFAULT NULL COMMENT 'Library identifier',
  `room_id` int(11) NOT NULL DEFAULT 1 COMMENT 'FK to rooms.room_id',
  `notes` varchar(255) DEFAULT NULL COMMENT 'Notes'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Books catalogue';

-- --------------------------------------------------------

--
-- Table structure for table `classifications`
--

CREATE TABLE `classifications` (
  `classification_id` varchar(100) NOT NULL,
  `description` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Classification catalog (Dewey etc.)';

--
-- Dumping data for table `classifications`
--

INSERT INTO `classifications` (`classification_id`, `description`) VALUES
('000', 'Generalidades'),
('010', 'Bibliografía'),
('020', 'Bibliotecología y ciencias de la información'),
('030', 'Enciclopedias generales'),
('040', 'No definido'),
('050', 'Publicaciones en serie'),
('060', 'Organizaciones y museografía'),
('070', 'Periodismo, editoriales, diarios'),
('080', 'Colecciones generales'),
('090', 'Manuscritos y libros raros'),
('100', 'Filosofía y psicología'),
('110', 'Metafísica'),
('120', 'Conocimiento, causa, fin, hombre'),
('130', 'Parapsicología, ocultismo, fenómenos paranormales'),
('140', 'Escuelas filosóficas específicas'),
('150', 'Psicología'),
('160', 'Lógica'),
('170', 'Ética (filosofía moral)'),
('180', 'Filosofía antigua, medieval, oriental'),
('190', 'Filosofía moderna occidental'),
('200', 'Religión'),
('210', 'Filosofía y teoría de la religión'),
('220', 'Biblia'),
('230', 'Teología cristiana'),
('240', 'Moral y prácticas cristianas'),
('250', 'Iglesia local y órdenes religiosas'),
('260', 'Teología social y eclesiástica'),
('270', 'Historia y geografía de la iglesia cristiana'),
('280', 'Credos y sectas de la iglesia cristiana'),
('290', 'Otras religiones'),
('300', 'Ciencias sociales'),
('310', 'Estadística'),
('320', 'Ciencia política'),
('330', 'Economía'),
('340', 'Derecho'),
('350', 'Administración pública y ciencia militar'),
('360', 'Problemas y servicios sociales'),
('370', 'Educación'),
('380', 'Comercio, comunicaciones y transporte'),
('390', 'Costumbres y folklore'),
('400', 'Lenguas'),
('410', 'Lingüística'),
('420', 'Inglés e inglés antiguo'),
('430', 'Lenguas germánicas; alemán'),
('440', 'Lenguas romances; francés'),
('450', 'Italiano, rumano, rético'),
('460', 'Español y portugués'),
('470', 'Lenguas itálicas; latín'),
('480', 'Lenguas helénicas; griego clásico'),
('490', 'Otras lenguas'),
('500', 'Matemáticas y ciencias naturales'),
('510', 'Matemáticas'),
('520', 'Astronomía y ciencias afines'),
('530', 'Física'),
('540', 'Química y ciencias afines'),
('550', 'Geociencias'),
('560', 'Paleontología. paleozoología'),
('570', 'Ciencias biológicas'),
('580', 'Ciencias botánicas'),
('590', 'Ciencias zoológicas'),
('600', 'Tecnología y ciencias aplicadas'),
('610', 'Ciencias médicas'),
('620', 'Ingeniería y operaciones afines'),
('630', 'Agricultura y tecnologías afines'),
('640', 'Economía doméstica'),
('650', 'Servicios administrativos empresariales'),
('660', 'Química industrial'),
('670', 'Manufacturas'),
('680', 'Manufacturas varias'),
('690', 'Construcciones'),
('700', 'Artes'),
('710', 'Urbanismo y arquitectura del paisaje'),
('720', 'Arquitectura'),
('730', 'Artes plásticas; escultura'),
('740', 'Dibujo, artes decorativas'),
('750', 'Pintura y pinturas'),
('760', 'Artes gráficas; grabados'),
('770', 'Fotografía y fotografías'),
('780', 'Música'),
('790', 'Entretenimiento'),
('800', 'Literatura'),
('810', 'Literatura americana en inglés'),
('820', 'Literatura inglesa e inglesa antigua'),
('830', 'Literaturas germánicas'),
('840', 'Literaturas de las lenguas romances'),
('850', 'Literaturas italiana, rumana'),
('860', 'Literaturas española y portuguesa'),
('870', 'Literaturas de las lenguas itálicas'),
('880', 'Literaturas de las lenguas helénicas'),
('890', 'Literaturas de otras lenguas'),
('900', 'Historia y geografía'),
('910', 'Geografía; viajes'),
('920', 'Biografía y genealogía'),
('930', 'Historia del mundo antiguo'),
('940', 'Historia de Europa'),
('950', 'Historia de Asia'),
('960', 'Historia de África'),
('970', 'Historia de América del Norte'),
('980', 'Historia de América del Sur'),
('990', 'Historia de otras regiones'),
('A', 'Libro álbum'),
('C', 'Cuento'),
('CD', 'Colección audiovisual'),
('CL', 'Colección Literarias'),
('CN', 'Colecciones para Niños'),
('CP', 'Colecciones Patrimonio'),
('CR', 'Colección regional'),
('CV', 'Colección Varias'),
('DE', 'Diccionarios y enciclopedias'),
('DVD', 'DVD'),
('H', 'Historieta'),
('LM', 'Leyenda y mito'),
('N', 'Novela'),
('NG', 'Novela Grafica'),
('P', 'Poesia'),
('R', 'Referencias'),
('SB', 'Sistema Braille'),
('T', 'Teatro');

-- --------------------------------------------------------

--
-- Table structure for table `holds`
--

CREATE TABLE `holds` (
  `id` int(11) NOT NULL,
  `book_id` bigint(11) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `status` enum('queued','fulfilled','canceled') NOT NULL DEFAULT 'queued',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `fulfilled_at` datetime DEFAULT NULL,
  `canceled_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `labels`
--

CREATE TABLE `labels` (
  `label_id` int(11) NOT NULL,
  `color` varchar(100) DEFAULT NULL,
  `description` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Age/color labels';

-- --------------------------------------------------------

--
-- Table structure for table `librarians`
--

CREATE TABLE `librarians` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(120) NOT NULL,
  `paternal_last_name` varchar(50) NOT NULL,
  `maternal_last_name` varchar(50) DEFAULT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `reset_token` varchar(64) DEFAULT NULL,
  `reset_token_expires` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `loans`
--

CREATE TABLE `loans` (
  `loan_id` int(11) NOT NULL,
  `book_id` bigint(20) UNSIGNED NOT NULL COMMENT 'FK to books.id',
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `note` varchar(255) DEFAULT NULL COMMENT 'Observation (typo from Obervacion)',
  `loaned_at` datetime NOT NULL COMMENT 'Loan date',
  `due_at` datetime NOT NULL COMMENT 'Due date',
  `returned` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1 if returned',
  `returned_at` datetime DEFAULT NULL COMMENT 'Return date'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Book loans';

-- --------------------------------------------------------

--
-- Table structure for table `origins`
--

CREATE TABLE `origins` (
  `origin_id` int(11) NOT NULL,
  `donated_by` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Book origins (donated by)';

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `room_id` int(11) NOT NULL,
  `description` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Rooms/areas in library';

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_number` bigint(20) UNSIGNED NOT NULL COMMENT 'Identifcation Number',
  `first_name` varchar(100) NOT NULL COMMENT 'First name',
  `last_name` varchar(100) NOT NULL COMMENT 'Last name',
  `email` varchar(255) DEFAULT NULL,
  `user_key` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT NULL COMMENT 'Created at',
  `phone` bigint(20) UNSIGNED DEFAULT NULL,
  `address` varchar(100) DEFAULT NULL COMMENT 'Address',
  `sanctioned` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1 if sanctioned',
  `sanctioned_at` datetime DEFAULT NULL COMMENT 'When sanctioned'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Library users';

--
-- Indexes for dumped tables
--

--
-- Indexes for table `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ux_libros_isbn` (`isbn`),
  ADD UNIQUE KEY `ux_books_isbn` (`isbn`),
  ADD KEY `EtiquetaID` (`label_id`),
  ADD KEY `ClasificacionID` (`classification_id`),
  ADD KEY `BibliotecaID` (`library_id`),
  ADD KEY `OrigenID` (`origin_id`),
  ADD KEY `SalaID` (`room_id`),
  ADD KEY `ix_libros_titulo` (`title`),
  ADD KEY `ix_libros_autor` (`author`),
  ADD KEY `ix_libros_clasificacion` (`classification_id`),
  ADD KEY `ix_books_title` (`title`),
  ADD KEY `ix_books_author` (`author`),
  ADD KEY `ix_books_classification` (`classification_id`);

--
-- Indexes for table `classifications`
--
ALTER TABLE `classifications`
  ADD PRIMARY KEY (`classification_id`);

--
-- Indexes for table `holds`
--
ALTER TABLE `holds`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_holds_book` (`book_id`,`status`,`created_at`),
  ADD KEY `idx_holds_user` (`user_id`,`status`),
  ADD KEY `idx_holds_status_created_at` (`status`,`created_at`);

--
-- Indexes for table `labels`
--
ALTER TABLE `labels`
  ADD PRIMARY KEY (`label_id`);

--
-- Indexes for table `librarians`
--
ALTER TABLE `librarians`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_reset_token` (`reset_token`),
  ADD KEY `idx_reset_token_expires` (`reset_token_expires`);

--
-- Indexes for table `loans`
--
ALTER TABLE `loans`
  ADD PRIMARY KEY (`loan_id`),
  ADD KEY `UsuariosID` (`user_id`),
  ADD KEY `fk_libros_id` (`book_id`),
  ADD KEY `ix_prestamos_devuelto` (`returned`),
  ADD KEY `ix_prestamos_fecha_limite` (`due_at`),
  ADD KEY `ix_prestamos_usuario` (`user_id`),
  ADD KEY `ix_prestamos_libro` (`book_id`),
  ADD KEY `ix_loans_user` (`user_id`),
  ADD KEY `ix_loans_book` (`book_id`),
  ADD KEY `ix_loans_due` (`due_at`),
  ADD KEY `ix_loans_returned` (`returned`);

--
-- Indexes for table `origins`
--
ALTER TABLE `origins`
  ADD PRIMARY KEY (`origin_id`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`room_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_number`),
  ADD UNIQUE KEY `ux_usuarios_llave` (`user_key`),
  ADD UNIQUE KEY `ux_users_user_key` (`user_key`),
  ADD UNIQUE KEY `users_user_key_uq` (`user_key`),
  ADD UNIQUE KEY `ux_usuarios_correo` (`email`),
  ADD UNIQUE KEY `ux_users_email` (`email`),
  ADD UNIQUE KEY `users_email_uq` (`email`),
  ADD KEY `ix_usuarios_cedula` (`id_number`),
  ADD KEY `ix_usuarios_nombre` (`first_name`),
  ADD KEY `ix_usuarios_apellido` (`last_name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `books`
--
ALTER TABLE `books`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `holds`
--
ALTER TABLE `holds`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `librarians`
--
ALTER TABLE `librarians`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `loans`
--
ALTER TABLE `loans`
  MODIFY `loan_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `books`
--
ALTER TABLE `books`
  ADD CONSTRAINT `fk_books_classification` FOREIGN KEY (`classification_id`) REFERENCES `classifications` (`classification_id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_books_label` FOREIGN KEY (`label_id`) REFERENCES `labels` (`label_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_books_origin` FOREIGN KEY (`origin_id`) REFERENCES `origins` (`origin_id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_books_room` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`room_id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Constraints for table `holds`
--
ALTER TABLE `holds`
  ADD CONSTRAINT `fk_holds_book` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_holds_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id_number`) ON UPDATE CASCADE;

--
-- Constraints for table `loans`
--
ALTER TABLE `loans`
  ADD CONSTRAINT `fk_loans_book` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_loans_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id_number`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
