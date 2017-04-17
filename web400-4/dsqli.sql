-- phpMyAdmin SQL Dump
-- version 3.4.7
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Mar 12, 2017 at 08:09 PM
-- Server version: 5.5.17
-- PHP Version: 5.6.12

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `dsqli`
--

-- --------------------------------------------------------

--
-- Table structure for table `download`
--

CREATE TABLE IF NOT EXISTS `download` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL , 
  `image_name` varchar(225) NOT NULL,
  `location` varchar(225) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

CREATE TABLE IF NOT EXISTS `user`(
   `id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
   `username` varchar(20) NOT NULL ,
   `password` varchar(32) NOT NULL 
) ENGINE=InnoDB DEFAULT CHARSET=latin1  AUTO_INCREMENT=1;

--
-- Dumping data for table `download`
--

INSERT INTO `download` (`id`,`uid`, `image_name`, `location`) VALUES
(1, 0,'Wall', 'Up10aDs/Wall.jpg');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
