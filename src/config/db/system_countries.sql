CREATE TABLE `SITE_DB`.`system_countries` (
  `id` varchar(2) NOT NULL,
  `name` varchar(255) NOT NULL,

  `phone_countrycode` varchar(4) NOT NULL,
  `phone_format` varchar(15) default NULL,

  `language` varchar(2) NOT NULL,
  `currency` varchar(3) NOT NULL,

  PRIMARY KEY  (`id`),
  KEY `language` (`language`),
  KEY `currency` (`currency`),
  CONSTRAINT `system_countries_ibfk_1` FOREIGN KEY (`language`) REFERENCES `SITE_DB`.`system_languages` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `system_countries_ibfk_2` FOREIGN KEY (`currency`) REFERENCES `SITE_DB`.`system_currencies` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
