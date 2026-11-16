-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Isäntä: db
-- Luontiaika: 16.11.2026 klo 11:41
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

-- --------------------------------------------------------

--
-- Rakenne taululle `kayttaja`
--

CREATE TABLE `kayttaja` (
  `id` int NOT NULL,
  `nimi` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rooli` enum('opettaja','opiskelija') COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  ADD PRIMARY KEY (`id`);

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
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kayttaja`
--
ALTER TABLE `kayttaja`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kysymys`
--
ALTER TABLE `kysymys`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kysymyspaketti`
--
ALTER TABLE `kysymyspaketti`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pelikerta`
--
ALTER TABLE `pelikerta`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pelivastaus`
--
ALTER TABLE `pelivastaus`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vastausvaihtoehto`
--
ALTER TABLE `vastausvaihtoehto`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

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
