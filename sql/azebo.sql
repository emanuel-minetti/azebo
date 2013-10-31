-- phpMyAdmin SQL Dump
-- version 3.3.7deb7
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Oct 30, 2013 at 01:01 PM
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
-- Table structure for table `arbeitsmonat`
--

CREATE TABLE IF NOT EXISTS `arbeitsmonat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mitarbeiter_id` int(11) NOT NULL,
  `monat` date NOT NULL,
  `saldostunden` int(11) NOT NULL,
  `saldominuten` int(11) NOT NULL,
  `saldopositiv` enum('ja','nein') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'ja',
  `urlaubvorjahr` int(11) NOT NULL,
  `urlaub` int(11) NOT NULL,
  `azv` int(11) DEFAULT NULL,
  `abgelegt` enum('ja','nein') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'nein',
  `saldo2007stunden` int(11) DEFAULT NULL,
  `saldo2007minuten` int(11) DEFAULT NULL,
  `uebertragen` enum('ja','nein') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'nein',
  PRIMARY KEY (`id`),
  KEY `mitarbeiter_id` (`mitarbeiter_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=40 ;

-- --------------------------------------------------------

--
-- Table structure for table `arbeitsregel`
--

CREATE TABLE IF NOT EXISTS `arbeitsregel` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mitarbeiter_id` int(11) NOT NULL,
  `wochentag` enum('montag','dienstag','mittwoch','donnerstag','freitag','samstag','sonntag','alle') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'alle',
  `kalenderwoche` enum('gerade','ungerade','alle') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'alle',
  `rahmenanfang` time DEFAULT NULL,
  `rahmenende` time DEFAULT NULL,
  `ohnekern` enum('ja','nein') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'nein',
  `kernanfang` time DEFAULT NULL,
  `kernende` time DEFAULT NULL,
  `soll` time NOT NULL,
  `von` date NOT NULL,
  `bis` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mitarbeiter_id` (`mitarbeiter_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=42 ;

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
  `nachmittag` enum('ja','nein') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'nein',
  `nachmittagbeginn` time DEFAULT NULL,
  `nachmittagende` time DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mitarbeiter-tag` (`mitarbeiter_id`,`tag`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=339 ;

-- --------------------------------------------------------

--
-- Table structure for table `mitarbeiter`
--

CREATE TABLE IF NOT EXISTS `mitarbeiter` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `benutzername` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `beamter` enum('ja','nein') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'nein',
  `saldouebertragstunden` int(11) NOT NULL,
  `saldouebertragminuten` int(11) NOT NULL,
  `saldouebertragpositiv` enum('ja','nein') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'ja',
  `urlaubvorjahr` int(11) NOT NULL,
  `urlaub` int(11) NOT NULL,
  `saldo2007stunden` int(11) DEFAULT NULL,
  `saldo2007minuten` int(11) DEFAULT NULL,
  `vertreter` int(11) DEFAULT NULL,
  `kappungtotalstunden` int(11) DEFAULT NULL,
  `kappungtotalminuten` int(11) DEFAULT NULL,
  `kappungmonatstunden` int(11) DEFAULT NULL,
  `kappungmonatminuten` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `benutzername` (`benutzername`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=21 ;
