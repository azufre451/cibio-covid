-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Host: colab1.cibio.unitn.it:33006
-- Creato il: Mag 11, 2021 alle 08:47
-- Versione del server: 8.0.19
-- Versione PHP: 7.4.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `covid`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `curves`
--

CREATE TABLE `curves` (
  `plate` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `well` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fluorophore` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `curve` text COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPRESSED;

-- --------------------------------------------------------

--
-- Struttura della tabella `estrazioni`
--

CREATE TABLE `estrazioni` (
  `barcode` varchar(100) NOT NULL,
  `data_estrazione` date NOT NULL,
  `batch` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `pcr_plates`
--

CREATE TABLE `pcr_plates` (
  `plate` varchar(100) NOT NULL,
  `data_pcr` date NOT NULL,
  `barcode` varchar(100) NOT NULL,
  `pooled_barcode` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `well` varchar(10) NOT NULL,
  `Cy5` float DEFAULT NULL,
  `FAM` float DEFAULT NULL,
  `HEX` float DEFAULT NULL,
  `TRed` float DEFAULT NULL,
  `esito_automatico` enum('POSITIVO','NEGATIVO','DUBBIO TECNICO','CONTROLLO','RIPETERE TAMPONE') CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `esito_pcr` enum('POSITIVO','NEGATIVO','RIPETERE PCR','RIPETERE ESTRAZIONE','ERRORE COMPILAZIONE','RIPETERE TAMPONE','CONTROLLO') CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `isControl` tinyint(1) NOT NULL DEFAULT '0',
  `batch_kf` varchar(3) NOT NULL,
  `kit` enum('bosphore','realstar','liferiver') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `samples`
--

CREATE TABLE `samples` (
  `barcode` varchar(100) NOT NULL,
  `data_checkin` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `curves`
--
ALTER TABLE `curves`
  ADD PRIMARY KEY (`plate`,`well`,`fluorophore`),
  ADD KEY `plate` (`plate`);

--
-- Indici per le tabelle `estrazioni`
--
ALTER TABLE `estrazioni`
  ADD PRIMARY KEY (`barcode`,`data_estrazione`,`batch`);

--
-- Indici per le tabelle `pcr_plates`
--
ALTER TABLE `pcr_plates`
  ADD PRIMARY KEY (`plate`,`barcode`,`well`);

--
-- Indici per le tabelle `samples`
--
ALTER TABLE `samples`
  ADD PRIMARY KEY (`barcode`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
