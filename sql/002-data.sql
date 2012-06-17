-- phpMyAdmin SQL Dump
-- version 2.11.9.6
-- http://www.phpmyadmin.net
--
-- Serveur: localhost:3306
-- Généré le : Dim 17 Juin 2012 à 20:23
-- Version du serveur: 5.1.59
-- Version de PHP: 5.2.6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Base de données: `waitingqueue`
--

--
-- Contenu de la table `queue`
--

INSERT INTO `queue` (`creator`, `name`, `comment`, `password`, `creationDate`, `endDate`) VALUES
('teacher', 'abc', '', 'd41d8cd98f00b204e9800998ecf8427e', '2012-06-14 16:30:25', '2012-06-14 16:30:25');

--
-- Contenu de la table `queueItem`
--


--
-- Contenu de la table `role`
--

INSERT INTO `role` (`name`, `description`) VALUES
('teacher', 'A teacher can edit queues and queueitems.');

--
-- Contenu de la table `sessionvariables`
--


--
-- Contenu de la table `user`
--

INSERT INTO `user` (`username`, `password`, `name`, `surname`, `email`, `role_name`) VALUES
('teacher', '084af732f5dfb63b511db777aa91e503', 'Generic teacher role account.', 'NA', NULL, 'teacher');
