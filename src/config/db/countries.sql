CREATE TABLE `SITE_DB`.`countries` (
  `id` varchar(2) NOT NULL,
  `name` varchar(255) NOT NULL,

  `phone_countrycode` varchar(4) NOT NULL,
  `phone_format` varchar(15) default NULL,

  `language` varchar(2) NOT NULL,
  `currency` varchar(3) NOT NULL,

  PRIMARY KEY  (`id`),
  KEY `language` (`language`),
  KEY `currency` (`currency`),
  CONSTRAINT `countries_ibfk_5` FOREIGN KEY (`language`) REFERENCES `languages` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `countries_ibfk_6` FOREIGN KEY (`currency`) REFERENCES `currencies` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
