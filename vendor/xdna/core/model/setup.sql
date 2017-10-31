-- phpMyAdmin SQL Dump
-- version 3.5.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generato il: Mag 02, 2013 alle 08:24
-- Versione del server: 5.5.24-log
-- Versione PHP: 5.4.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `xdna`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `xdna_elements`
--

CREATE TABLE IF NOT EXISTS `xdna_elements` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL DEFAULT '',
  `type` enum('string','text','html','int','double','data','color','boolean','attachment','image','object') NOT NULL DEFAULT 'string',
  `index` tinyint(1) NOT NULL DEFAULT '0',
  `required` tinyint(1) NOT NULL DEFAULT '0',
  `uri` varchar(200) NOT NULL DEFAULT '',
  `note` text NOT NULL,
  `defaultValue` text NOT NULL,
  `table` varchar(200) NOT NULL DEFAULT '',
  `multilanguage` tinyint(1) NOT NULL DEFAULT '0',
  `inherit` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `xdna_lists`
--

CREATE TABLE IF NOT EXISTS `xdna_lists` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `id_parent` int(10) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `xdna_set` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `id_parent` (`id_parent`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `xdna_set`
--

CREATE TABLE IF NOT EXISTS `xdna_set` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '',
  `dna` text NOT NULL,
  `bind_list` text NOT NULL,
  `customClass` varchar(200) NOT NULL DEFAULT '',
  `toString` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

