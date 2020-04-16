-- phpMyAdmin SQL Dump
-- version 4.6.6deb5
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Apr 11, 2020 at 11:15 AM
-- Server version: 5.7.29-0ubuntu0.18.04.1
-- PHP Version: 7.2.24-0ubuntu0.18.04.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
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
-- Table structure for table `estrazioni`
--

CREATE TABLE `estrazioni` (
  `barcode` varchar(100) NOT NULL,
  `data_estrazione` date NOT NULL,
  `batch` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `pcr_plates`
--

CREATE TABLE `pcr_plates` (
  `plate` varchar(100) NOT NULL,
  `data_pcr` date NOT NULL,
  `barcode` varchar(100) NOT NULL,
  `well` varchar(10) NOT NULL,
  `Cy5` float DEFAULT NULL,
  `FAM` float DEFAULT NULL,
  `HEX` float DEFAULT NULL,
  `esito_automatico` enum('POSITIVO','NEGATIVO','DUBBIO TECNICO') NOT NULL,
  `esito_pcr` enum('POSITIVO','NEGATIVO','RIPETERE PCR','RIPETERE ESTRAZIONE','ERRORE COMPILAZIONE') NOT NULL,
  `isControl` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `samples`
--

CREATE TABLE `samples` (
  `barcode` varchar(100) NOT NULL,
  `data_checkin` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `estrazioni`
--
ALTER TABLE `estrazioni`
  ADD PRIMARY KEY (`barcode`,`data_estrazione`,`batch`);

--
-- Indexes for table `pcr_plates`
--
ALTER TABLE `pcr_plates`
  ADD PRIMARY KEY (`plate`,`barcode`);

--
-- Indexes for table `samples`
--
ALTER TABLE `samples`
  ADD PRIMARY KEY (`barcode`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
