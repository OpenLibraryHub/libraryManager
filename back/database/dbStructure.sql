-- phpMyAdmin SQL Dump
-- version 5.2.1deb1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Mar 21, 2025 at 08:00 PM
-- Server version: 10.11.11-MariaDB-0+deb12u1
-- PHP Version: 8.2.28

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
-- Table structure for table `clasificacion`
--

CREATE TABLE `clasificacion` (
  `ClasificacionID` varchar(100) NOT NULL,
  `Descripcion` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `clasificacion`
--

INSERT INTO `clasificacion` (`ClasificacionID`, `Descripcion`) VALUES
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
-- Table structure for table `etiqueta`
--

CREATE TABLE `etiqueta` (
  `EtiquetaID` int(11) NOT NULL,
  `Color` varchar(100) DEFAULT NULL,
  `Descripcion` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `etiqueta`
--

INSERT INTO `etiqueta` (`EtiquetaID`, `Color`, `Descripcion`) VALUES
(0, 'Amarillo', 'Niños'),
(1, 'Azul', 'Jóvenes'),
(2, 'Rojo', 'Adultos');

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
  `middle_name` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `libros`
--

CREATE TABLE `libros` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `LibrosID` bigint(20) DEFAULT NULL,
  `Titulo` varchar(300) DEFAULT NULL,
  `Autor` varchar(100) DEFAULT NULL,
  `ClasificacionID` varchar(100) DEFAULT NULL,
  `CodigoClasificacion` varchar(100) DEFAULT NULL,
  `N_Ejemplares` int(11) DEFAULT NULL,
  `OrigenID` int(11) DEFAULT NULL,
  `N_Disponible` int(11) DEFAULT 0,
  `EtiquetaID` int(11) DEFAULT NULL,
  `BibliotecaID` bigint(20) DEFAULT NULL,
  `SalaID` int(11) DEFAULT NULL,
  `Observacion` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `origen`
--

CREATE TABLE `origen` (
  `OrigenID` int(11) NOT NULL,
  `Donado_por` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `origen`
--

INSERT INTO `origen` (`OrigenID`, `Donado_por`) VALUES
(0, 'No se sabe'),
(1, 'Estado'),
(2, 'Particular');

-- --------------------------------------------------------

--
-- Table structure for table `prestamos`
--

CREATE TABLE `prestamos` (
  `PrestamosID` int(11) NOT NULL,
  `LibrosID` bigint(20) UNSIGNED NOT NULL,
  `UsuariosID` bigint(12) UNSIGNED ZEROFILL DEFAULT NULL,
  `Obervacion` varchar(100) DEFAULT NULL,
  `fecha_prestamo` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_limite` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `devuelto` tinyint(1) NOT NULL DEFAULT 0,
  `fecha_entregado` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sala`
--

CREATE TABLE `sala` (
  `SalaID` int(11) NOT NULL,
  `Descripcion` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sala`
--

INSERT INTO `sala` (`SalaID`, `Descripcion`) VALUES
(1, 'Sala General'),
(2, 'Sala Ludoteca');

-- --------------------------------------------------------

--
-- Table structure for table `usuarios`
--

CREATE TABLE `usuarios` (
  `UsuariosID` bigint(12) UNSIGNED ZEROFILL NOT NULL,
  `Nombre` varchar(100) DEFAULT NULL,
  `Apellido` varchar(100) DEFAULT NULL,
  `Correo` varchar(255) DEFAULT NULL,
  `Cedula` bigint(10) UNSIGNED ZEROFILL DEFAULT NULL,
  `Fecha` datetime DEFAULT NULL,
  `numero` bigint(20) DEFAULT NULL,
  `direccion` varchar(100) DEFAULT '',
  `sancionado` tinyint(1) NOT NULL DEFAULT 0,
  `fecha_sancionado` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `clasificacion`
--
ALTER TABLE `clasificacion`
  ADD PRIMARY KEY (`ClasificacionID`);

--
-- Indexes for table `etiqueta`
--
ALTER TABLE `etiqueta`
  ADD PRIMARY KEY (`EtiquetaID`);

--
-- Indexes for table `librarians`
--
ALTER TABLE `librarians`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `libros`
--
ALTER TABLE `libros`
  ADD PRIMARY KEY (`id`),
  ADD KEY `EtiquetaID` (`EtiquetaID`),
  ADD KEY `ClasificacionID` (`ClasificacionID`),
  ADD KEY `BibliotecaID` (`BibliotecaID`),
  ADD KEY `OrigenID` (`OrigenID`),
  ADD KEY `SalaID` (`SalaID`);

--
-- Indexes for table `origen`
--
ALTER TABLE `origen`
  ADD PRIMARY KEY (`OrigenID`);

--
-- Indexes for table `prestamos`
--
ALTER TABLE `prestamos`
  ADD PRIMARY KEY (`PrestamosID`),
  ADD KEY `UsuariosID` (`UsuariosID`),
  ADD KEY `fk_libros_id` (`LibrosID`);

--
-- Indexes for table `sala`
--
ALTER TABLE `sala`
  ADD PRIMARY KEY (`SalaID`);

--
-- Indexes for table `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`UsuariosID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `librarians`
--
ALTER TABLE `librarians`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `libros`
--
ALTER TABLE `libros`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `prestamos`
--
ALTER TABLE `prestamos`
  MODIFY `PrestamosID` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `prestamos`
--
ALTER TABLE `prestamos`
  ADD CONSTRAINT `fk_libros_id` FOREIGN KEY (`LibrosID`) REFERENCES `libros` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
