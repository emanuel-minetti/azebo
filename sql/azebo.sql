-- phpMyAdmin SQL Dump
-- version 3.3.7deb7
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Aug 27, 2012 at 12:15 PM
-- Server version: 5.1.63
-- PHP Version: 5.3.3-7+squeeze14

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
-- Table structure for table `arbeitstag`
--

CREATE TABLE IF NOT EXISTS `arbeitstag` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mitarbeiter_id` int(11) NOT NULL,
  `tag` date NOT NULL,
  `beginn` time DEFAULT NULL,
  `ende` time DEFAULT NULL,
  `befreiung` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `bemerkung` text COLLATE utf8_unicode_ci,
  `pause` enum('-','x') COLLATE utf8_unicode_ci NOT NULL DEFAULT '-',
  PRIMARY KEY (`id`),
  UNIQUE KEY `mitarbeiter-tag` (`mitarbeiter_id`,`tag`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=15 ;

--
-- Dumping data for table `arbeitstag`
--

INSERT INTO `arbeitstag` (`id`, `mitarbeiter_id`, `tag`, `beginn`, `ende`, `befreiung`, `bemerkung`, `pause`) VALUES
(2, 6, '2012-08-01', '10:00:00', '15:30:00', 'keine', 'Und dies ist ein UPDATE!', '-'),
(3, 6, '2012-08-02', NULL, '00:00:00', 'keine', 'Dies ist ein Test!', '-'),
(4, 6, '2012-08-03', NULL, '00:00:00', 'azv', '', 'x'),
(5, 6, '2012-08-06', '11:20:00', NULL, 'keine', '', '-'),
(6, 6, '2012-08-07', '10:10:00', '17:30:00', 'keine', 'Nochn Test', 'x'),
(7, 6, '2012-08-13', '09:20:00', '19:20:00', 'keine', '', '-'),
(8, 6, '2012-08-14', '10:00:00', '15:00:00', 'keine', '', '-'),
(9, 6, '2012-08-15', '19:40:00', '20:30:00', 'keine', 'Etwas spät geworden', '-'),
(10, 6, '2012-08-16', '20:40:00', '21:40:00', 'urlaub', '', '-'),
(11, 6, '2012-08-17', '00:00:00', '00:00:00', 'keine', 'Mal wieder ''nen Test!', '-'),
(12, 6, '2012-08-08', '10:10:00', '12:00:00', 'keine', '', '-'),
(13, 6, '2012-08-10', '12:00:00', '12:00:00', 'keine', '', '-'),
(14, 6, '2012-08-20', '10:30:00', '15:50:00', 'keine', '', '-');

-- --------------------------------------------------------

--
-- Table structure for table `mitarbeiter`
--

CREATE TABLE IF NOT EXISTS `mitarbeiter` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `benutzername` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `benutzername` (`benutzername`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=7 ;

--
-- Dumping data for table `mitarbeiter`
--

INSERT INTO `mitarbeiter` (`id`, `benutzername`) VALUES
(3, 'hfsusert'),
(6, 'hfmusert'),
(5, 'minettie');
