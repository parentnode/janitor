CREATE TABLE `SITE_DB`.`vatrates` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,

  `vatrate` float NOT NULL,
  `country` varchar(2) NOT NULL,

  PRIMARY KEY  (`id`),
  KEY `country` (`country`),
  CONSTRAINT `vatrates_ibfk_1` FOREIGN KEY (`country`) REFERENCES `SITE_DB`.`countries` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
