-- phpMyAdmin SQL Dump
-- version 3.3.7deb7
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jul 25, 2012 at 02:56 
-- Server version: 5.1.63
-- PHP Version: 5.3.3-7+squeeze13

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `azebo`
--

-- --------------------------------------------------------

--
-- Table structure for table `mitarbeiter`
--

CREATE TABLE IF NOT EXISTS `mitarbeiter` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `benutzername` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `vorname` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `nachname` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `passwort` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `rolle` enum('mitarbeiter','büroleitung','scit') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'mitarbeiter',
  PRIMARY KEY (`id`),
  UNIQUE KEY `benutzername` (`benutzername`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;

--
-- Dumping data for table `mitarbeiter`
--

INSERT INTO `mitarbeiter` (`id`, `benutzername`, `vorname`, `nachname`, `passwort`, `rolle`) VALUES
(1, 'erster', 'Anton', 'Einser', 'eins', 'mitarbeiter'),
(2, 'zweiter', 'Berta', 'Zweier', 'zwei', 'büroleitung');
