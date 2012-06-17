 n SQL Dump
-- version 2.11.9.6
-- http://www.phpmyadmin.net
--
-- Serveur: localhost:3306
-- Généré le : Dim 17 Juin 2012 à 20:22
-- Version du serveur: 5.1.59
-- Version de PHP: 5.2.6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Base de données: `waitingqueue`
--

-- --------------------------------------------------------

--
-- Structure de la table `queue`
--

DROP TABLE IF EXISTS `queue`;
CREATE TABLE IF NOT EXISTS `queue` (
  `creator` varchar(128) NOT NULL DEFAULT '',
  `name` varchar(128) NOT NULL DEFAULT '',
  `comment` text,
  `password` varchar(128) NOT NULL DEFAULT '',
  `creationDate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `endDate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `queueItem`
--

DROP TABLE IF EXISTS `queueItem`;
CREATE TABLE IF NOT EXISTS `queueItem` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `queueName` varchar(128) NOT NULL DEFAULT '',
  `comment` text,
  `submitDate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `studentName` varchar(128) NOT NULL DEFAULT '',
  `studentSessionId` varchar(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=58 ;

-- --------------------------------------------------------

--
-- Structure de la table `role`
--

DROP TABLE IF EXISTS `role`;
CREATE TABLE IF NOT EXISTS `role` (
  `name` varchar(128) NOT NULL DEFAULT '',
  `description` text,
  PRIMARY KEY (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `sessionvariables`
--

DROP TABLE IF EXISTS `sessionvariables`;
CREATE TABLE IF NOT EXISTS `sessionvariables` (
  `user_username` varchar(64) NOT NULL DEFAULT '',
  `variableName` varchar(128) NOT NULL DEFAULT '',
  `variableValue` text,
  PRIMARY KEY (`user_username`,`variableName`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `username` varchar(64) NOT NULL DEFAULT '',
  `password` varchar(128) NOT NULL DEFAULT '',
  `name` varchar(128) NOT NULL DEFAULT '',
  `surname` varchar(128) NOT NULL DEFAULT '',
  `email` text,
  `role_name` varchar(128) NOT NULL DEFAULT '',
  PRIMARY KEY (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1; 
