-- phpMyAdmin SQL Dump
-- version 4.2.12deb2+deb8u1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 11. Aug 2017 um 17:16
-- Server Version: 5.5.49-0+deb8u1-log
-- PHP-Version: 5.6.20-0+deb8u1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Datenbank: `azebo`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `arbeitsmonat`
--

CREATE TABLE IF NOT EXISTS `arbeitsmonat` (
`id` int(11) NOT NULL,
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
  `uebertragen` enum('ja','nein') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'nein'
) ENGINE=MyISAM AUTO_INCREMENT=6767 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `arbeitsregel`
--

CREATE TABLE IF NOT EXISTS `arbeitsregel` (
`id` int(11) NOT NULL,
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
  `bis` date DEFAULT NULL
) ENGINE=MyISAM AUTO_INCREMENT=661 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `arbeitstag`
--

CREATE TABLE IF NOT EXISTS `arbeitstag` (
`id` int(11) NOT NULL,
  `mitarbeiter_id` int(11) NOT NULL,
  `tag` date NOT NULL,
  `beginn` time DEFAULT NULL,
  `ende` time DEFAULT NULL,
  `befreiung` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `bemerkung` text COLLATE utf8_unicode_ci,
  `pause` enum('-','x') COLLATE utf8_unicode_ci NOT NULL DEFAULT '-',
  `nachmittag` enum('ja','nein') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'nein',
  `nachmittagbeginn` time DEFAULT NULL,
  `nachmittagende` time DEFAULT NULL
) ENGINE=MyISAM AUTO_INCREMENT=118527 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `mitarbeiter`
--

CREATE TABLE IF NOT EXISTS `mitarbeiter` (
`id` int(11) unsigned NOT NULL,
  `benutzername` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `beamter` enum('ja','nein') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'nein',
  `tarifvertrag` enum('ja','nein') COLLATE utf8_unicode_ci DEFAULT NULL,
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
  `uebertragenbis` date DEFAULT NULL,
  `farbekopf` varchar(6) COLLATE utf8_unicode_ci DEFAULT NULL,
  `farbehoover` varchar(6) COLLATE utf8_unicode_ci DEFAULT NULL,
  `farbelink` varchar(6) COLLATE utf8_unicode_ci DEFAULT NULL,
  `farbezeile` varchar(6) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=MyISAM AUTO_INCREMENT=288 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vorjahr`
--

CREATE TABLE IF NOT EXISTS `vorjahr` (
`id` int(10) unsigned NOT NULL,
  `mitarbeiter_id` int(11) NOT NULL,
  `saldouebertragstunden` int(11) NOT NULL,
  `saldouebertragminuten` int(11) NOT NULL,
  `saldouebertragpositiv` enum('ja','nein') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'ja',
  `urlaubvorjahr` int(11) NOT NULL,
  `urlaub` int(11) NOT NULL,
  `saldo2007stunden` int(11) NOT NULL,
  `saldo2007minuten` int(11) NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=212 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `arbeitsmonat`
--
ALTER TABLE `arbeitsmonat`
 ADD PRIMARY KEY (`id`), ADD KEY `mitarbeiter_id` (`mitarbeiter_id`);

--
-- Indizes für die Tabelle `arbeitsregel`
--
ALTER TABLE `arbeitsregel`
 ADD PRIMARY KEY (`id`), ADD KEY `mitarbeiter_id` (`mitarbeiter_id`);

--
-- Indizes für die Tabelle `arbeitstag`
--
ALTER TABLE `arbeitstag`
 ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `mitarbeiter-tag` (`mitarbeiter_id`,`tag`);

--
-- Indizes für die Tabelle `mitarbeiter`
--
ALTER TABLE `mitarbeiter`
 ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `benutzername` (`benutzername`);

--
-- Indizes für die Tabelle `vorjahr`
--
ALTER TABLE `vorjahr`
 ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `mitarbeiter_id` (`mitarbeiter_id`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `arbeitsmonat`
--
ALTER TABLE `arbeitsmonat`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=6767;
--
-- AUTO_INCREMENT für Tabelle `arbeitsregel`
--
ALTER TABLE `arbeitsregel`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=661;
--
-- AUTO_INCREMENT für Tabelle `arbeitstag`
--
ALTER TABLE `arbeitstag`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=118527;
--
-- AUTO_INCREMENT für Tabelle `mitarbeiter`
--
ALTER TABLE `mitarbeiter`
MODIFY `id` int(11) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=288;
--
-- AUTO_INCREMENT für Tabelle `vorjahr`
--
ALTER TABLE `vorjahr`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=212;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
