-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Isäntä: db
-- Luontiaika: 17.11.2026 klo 06:48
-- Palvelimen versio: 8.0.46
-- PHP-versio 8.3.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Tietokanta: `Luokka_Tietokanta`
--

-- --------------------------------------------------------

--
-- Rakenne taululle `aihealue`
--

CREATE TABLE `aihealue` (
  `id` int NOT NULL,
  `nimi` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Vedos taulusta `aihealue`
--

INSERT INTO `aihealue` (`id`, `nimi`) VALUES
(2, 'Historia'),
(1, 'Matematiikka'),
(3, 'Ohjelmointi');

-- --------------------------------------------------------

--
-- Rakenne taululle `kayttaja`
--

CREATE TABLE `kayttaja` (
  `id` int NOT NULL,
  `nimi` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rooli` enum('opettaja','opiskelija') COLLATE utf8mb4_unicode_ci NOT NULL,
  `salasana_hash` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Vedos taulusta `kayttaja`
--

INSERT INTO `kayttaja` (`id`, `nimi`, `rooli`, `salasana_hash`) VALUES
(1, 'Matti Opettaja', 'opettaja', NULL),
(2, 'Anna Opettaja', 'opettaja', NULL),
(3, 'Pekka Opettaja', 'opettaja', NULL),
(4, 'Mikko Virtanen', 'opiskelija', NULL),
(5, 'Liisa Korhonen', 'opiskelija', NULL),
(6, 'Ville Nieminen', 'opiskelija', NULL),
(7, 'Emma Laine', 'opiskelija', NULL),
(8, 'Oskari Heikkinen', 'opiskelija', NULL),
(9, 'Sofia Mäkelä', 'opiskelija', NULL),
(10, 'Elias Hämäläinen', 'opiskelija', NULL),
(11, 'Aino Lehtinen', 'opiskelija', NULL);

-- --------------------------------------------------------

--
-- Rakenne taululle `kysymys`
--

CREATE TABLE `kysymys` (
  `id` int NOT NULL,
  `paketti_id` int NOT NULL,
  `teksti` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `selite` text COLLATE utf8mb4_unicode_ci,
  `jarjestys` smallint NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Vedos taulusta `kysymys`
--

INSERT INTO `kysymys` (`id`, `paketti_id`, `teksti`, `selite`, `jarjestys`) VALUES
(1, 1, 'Paljonko on 5 + 7?', 'Viiden ja seitsemän summa on 12.', 1),
(2, 1, 'Paljonko on 9 x 6?', '9 kertaa 6 on 54.', 2),
(3, 1, 'Paljonko on 100 / 4?', 'Sata jaettuna neljällä on 25.', 3),
(4, 2, 'Minä vuonna Suomi itsenäistyi?', 'Suomi itsenäistyi vuonna 1917.', 1),
(5, 2, 'Kuka oli Suomen ensimmäinen presidentti?', 'Ensimmäinen presidentti oli K. J. Ståhlberg.', 2),
(6, 2, 'Mikä oli Suomen rahayksikkö ennen euroa?', 'Suomen markka.', 3),
(7, 3, 'Millä PHP-tunnisteella PHP-koodi alkaa?', 'PHP-koodi alkaa tunnisteella <?php.', 1),
(8, 3, 'Millä merkillä PHP-muuttuja alkaa?', 'PHP-muuttujan nimen edessä on dollarimerkki.', 2),
(9, 3, 'Millä funktiolla tulostetaan tekstiä PHP:ssä?', 'echo tulostaa tekstiä.', 3),
(10, 5, 'Mitä SQL tarkoittaa?', 'Structured Query Language.', 1),
(11, 5, 'Millä komennolla haetaan tietoja tietokannasta?', 'SELECT hakee tietoja.', 2),
(12, 5, 'Millä komennolla lisätään uusi tietue?', 'INSERT INTO lisää tietueita.', 3),
(13, 4, 'Paljonko on 12 potenssiin 2?', '12 x 12 = 144.', 1),
(14, 4, 'Mikä on luvun 81 neliöjuuri?', 'Neliöjuuri luvusta 81 on 9.', 2),
(15, 4, 'Paljonko on 15 % luvusta 200?', '15 prosenttia 200:sta on 30.', 3);

-- --------------------------------------------------------

--
-- Rakenne taululle `kysymyspaketti`
--

CREATE TABLE `kysymyspaketti` (
  `id` int NOT NULL,
  `opettaja_id` int NOT NULL,
  `aihealue_id` int DEFAULT NULL,
  `nimi` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kuvaus` text COLLATE utf8mb4_unicode_ci,
  `julkaistu` tinyint(1) NOT NULL DEFAULT '0',
  `luotu` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Vedos taulusta `kysymyspaketti`
--

INSERT INTO `kysymyspaketti` (`id`, `opettaja_id`, `aihealue_id`, `nimi`, `kuvaus`, `julkaistu`, `luotu`) VALUES
(1, 1, 1, 'Matematiikan perusteet', 'Peruslaskutoimitukset ja matematiikka.', 1, '2026-11-17 06:41:27'),
(2, 2, 2, 'Suomen historia', 'Suomen historian tärkeimmät tapahtumat.', 1, '2026-11-17 06:41:27'),
(3, 3, 3, 'PHP-ohjelmoinnin perusteet', 'PHP:n perusrakenteet ja käsitteet.', 1, '2026-11-17 06:41:27'),
(4, 1, 1, 'Matematiikan haastavat tehtävät', 'Vaativampia laskutehtäviä.', 0, '2026-11-17 06:41:27'),
(5, 2, 3, 'SQL-perusteet', 'Tietokantojen ja SQL:n perusteet.', 1, '2026-11-17 06:41:27');

-- --------------------------------------------------------

--
-- Rakenne taululle `pelikerta`
--

CREATE TABLE `pelikerta` (
  `id` int NOT NULL,
  `kayttaja_id` int NOT NULL,
  `paketti_id` int NOT NULL,
  `alkoi` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `pisteet` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Vedos taulusta `pelikerta`
--

INSERT INTO `pelikerta` (`id`, `kayttaja_id`, `paketti_id`, `alkoi`, `pisteet`) VALUES
(1, 4, 1, '2026-11-17 06:41:27', 3),
(2, 5, 1, '2026-11-17 06:41:27', 2),
(3, 6, 2, '2026-11-17 06:41:27', 3),
(4, 7, 3, '2026-11-17 06:41:27', 2),
(5, 8, 5, '2026-11-17 06:41:27', 3);

-- --------------------------------------------------------

--
-- Rakenne taululle `pelivastaus`
--

CREATE TABLE `pelivastaus` (
  `id` int NOT NULL,
  `pelikerta_id` int NOT NULL,
  `kysymys_id` int NOT NULL,
  `valittu_id` int DEFAULT NULL,
  `oikein` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Vedos taulusta `pelivastaus`
--

INSERT INTO `pelivastaus` (`id`, `pelikerta_id`, `kysymys_id`, `valittu_id`, `oikein`) VALUES
(1, 1, 1, 3, 1),
(2, 1, 2, 6, 1),
(3, 1, 3, 10, 1),
(4, 2, 1, 3, 1),
(5, 2, 2, 5, 0),
(6, 2, 3, 10, 1),
(7, 3, 4, 14, 1),
(8, 3, 5, 19, 1),
(9, 3, 6, 22, 1),
(10, 4, 7, 25, 1),
(11, 4, 8, 29, 1),
(12, 4, 9, 33, 0),
(13, 5, 10, 38, 1),
(14, 5, 11, 42, 1),
(15, 5, 12, 47, 1);

-- --------------------------------------------------------

--
-- Rakenne taululle `vastausvaihtoehto`
--

CREATE TABLE `vastausvaihtoehto` (
  `id` int NOT NULL,
  `kysymys_id` int NOT NULL,
  `teksti` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `on_oikea` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Vedos taulusta `vastausvaihtoehto`
--

INSERT INTO `vastausvaihtoehto` (`id`, `kysymys_id`, `teksti`, `on_oikea`) VALUES
(1, 1, '10', 0),
(2, 1, '11', 0),
(3, 1, '12', 1),
(4, 1, '13', 0),
(5, 2, '45', 0),
(6, 2, '54', 1),
(7, 2, '56', 0),
(8, 2, '64', 0),
(9, 3, '20', 0),
(10, 3, '25', 1),
(11, 3, '30', 0),
(12, 3, '40', 0),
(13, 4, '1900', 0),
(14, 4, '1917', 1),
(15, 4, '1920', 0),
(16, 4, '1939', 0),
(17, 5, 'Urho Kekkonen', 0),
(18, 5, 'Carl Gustaf Mannerheim', 0),
(19, 5, 'K. J. Ståhlberg', 1),
(20, 5, 'Mauno Koivisto', 0),
(21, 6, 'Euro', 0),
(22, 6, 'Markka', 1),
(23, 6, 'Kruunu', 0),
(24, 6, 'Rupla', 0),
(25, 7, '<?php', 1),
(26, 7, '<php>', 0),
(27, 7, '<?', 0),
(28, 7, '<script>', 0),
(29, 8, '$', 1),
(30, 8, '#', 0),
(31, 8, '@', 0),
(32, 8, '&', 0),
(33, 9, 'printText()', 0),
(34, 9, 'echo', 1),
(35, 9, 'output()', 0),
(36, 9, 'write()', 0),
(37, 10, 'Simple Question Language', 0),
(38, 10, 'Structured Query Language', 1),
(39, 10, 'System Query Logic', 0),
(40, 10, 'Standard Quick Language', 0),
(41, 11, 'GET', 0),
(42, 11, 'SELECT', 1),
(43, 11, 'FETCH INTO', 0),
(44, 11, 'READ', 0),
(45, 12, 'UPDATE', 0),
(46, 12, 'CREATE', 0),
(47, 12, 'INSERT INTO', 1),
(48, 12, 'DELETE', 0),
(49, 13, '124', 0),
(50, 13, '144', 1),
(51, 13, '154', 0),
(52, 13, '164', 0),
(53, 14, '8', 0),
(54, 14, '9', 1),
(55, 14, '10', 0),
(56, 14, '11', 0),
(57, 15, '20', 0),
(58, 15, '25', 0),
(59, 15, '30', 1),
(60, 15, '35', 0);

--
-- Indexes for dumped tables
--

--
-- Indeksit taulukolle `aihealue`
--
ALTER TABLE `aihealue`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nimi` (`nimi`);

--
-- Indeksit taulukolle `kayttaja`
--
ALTER TABLE `kayttaja`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_kayttaja_nimi` (`nimi`);

--
-- Indeksit taulukolle `kysymys`
--
ALTER TABLE `kysymys`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_kysymys_paketti` (`paketti_id`);

--
-- Indeksit taulukolle `kysymyspaketti`
--
ALTER TABLE `kysymyspaketti`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_paketti_haku` (`julkaistu`,`aihealue_id`),
  ADD KEY `fk_paketti_opettaja` (`opettaja_id`),
  ADD KEY `fk_paketti_aihe` (`aihealue_id`);

--
-- Indeksit taulukolle `pelikerta`
--
ALTER TABLE `pelikerta`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_pk_kayttaja` (`kayttaja_id`),
  ADD KEY `fk_pk_paketti` (`paketti_id`);

--
-- Indeksit taulukolle `pelivastaus`
--
ALTER TABLE `pelivastaus`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_pelikerta_kysymys` (`pelikerta_id`,`kysymys_id`),
  ADD KEY `fk_pv_kysymys` (`kysymys_id`),
  ADD KEY `fk_pv_valittu` (`valittu_id`);

--
-- Indeksit taulukolle `vastausvaihtoehto`
--
ALTER TABLE `vastausvaihtoehto`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_vv_kysymys` (`kysymys_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `aihealue`
--
ALTER TABLE `aihealue`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `kayttaja`
--
ALTER TABLE `kayttaja`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `kysymys`
--
ALTER TABLE `kysymys`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `kysymyspaketti`
--
ALTER TABLE `kysymyspaketti`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `pelikerta`
--
ALTER TABLE `pelikerta`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `pelivastaus`
--
ALTER TABLE `pelivastaus`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `vastausvaihtoehto`
--
ALTER TABLE `vastausvaihtoehto`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- Rajoitteet vedostauluille
--

--
-- Rajoitteet taululle `kysymys`
--
ALTER TABLE `kysymys`
  ADD CONSTRAINT `fk_kysymys_paketti` FOREIGN KEY (`paketti_id`) REFERENCES `kysymyspaketti` (`id`) ON DELETE CASCADE;

--
-- Rajoitteet taululle `kysymyspaketti`
--
ALTER TABLE `kysymyspaketti`
  ADD CONSTRAINT `fk_paketti_aihe` FOREIGN KEY (`aihealue_id`) REFERENCES `aihealue` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_paketti_opettaja` FOREIGN KEY (`opettaja_id`) REFERENCES `kayttaja` (`id`) ON DELETE CASCADE;

--
-- Rajoitteet taululle `pelikerta`
--
ALTER TABLE `pelikerta`
  ADD CONSTRAINT `fk_pk_kayttaja` FOREIGN KEY (`kayttaja_id`) REFERENCES `kayttaja` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pk_paketti` FOREIGN KEY (`paketti_id`) REFERENCES `kysymyspaketti` (`id`) ON DELETE CASCADE;

--
-- Rajoitteet taululle `pelivastaus`
--
ALTER TABLE `pelivastaus`
  ADD CONSTRAINT `fk_pv_kysymys` FOREIGN KEY (`kysymys_id`) REFERENCES `kysymys` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pv_pelikerta` FOREIGN KEY (`pelikerta_id`) REFERENCES `pelikerta` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pv_valittu` FOREIGN KEY (`valittu_id`) REFERENCES `vastausvaihtoehto` (`id`) ON DELETE SET NULL;

--
-- Rajoitteet taululle `vastausvaihtoehto`
--
ALTER TABLE `vastausvaihtoehto`
  ADD CONSTRAINT `fk_vv_kysymys` FOREIGN KEY (`kysymys_id`) REFERENCES `kysymys` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
