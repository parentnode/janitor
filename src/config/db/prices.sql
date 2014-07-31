CREATE TABLE `SITE_DB`.`prices` (
  `id` int(11) NOT NULL auto_increment,
  `item_id` int(11) NOT NULL,

  `price` float NOT NULL,
  `currency` varchar(3) NOT NULL,
  `vatrate_id` int(11) NOT NULL,

  PRIMARY KEY  (`id`),
  KEY `item_id` (`item_id`),
  KEY `currency` (`currency`),

  CONSTRAINT `prices_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `prices_ibfk_2` FOREIGN KEY (`currency`) REFERENCES `currencies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
