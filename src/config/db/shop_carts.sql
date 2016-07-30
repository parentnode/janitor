CREATE TABLE `SITE_DB`.`shop_carts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,

  `cart_reference` varchar(12) NOT NULL,

  `country` varchar(2) NOT NULL,
  `currency` varchar(3) NOT NULL,

  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_at` timestamp NULL DEFAULT NULL,

  PRIMARY KEY  (`id`),
  KEY `country` (`country`),
  KEY `currency` (`currency`),

  CONSTRAINT `shop_carts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `SITE_DB`.`users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `shop_carts_ibfk_2` FOREIGN KEY (`country`) REFERENCES `SITE_DB`.`system_countries` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `shop_carts_ibfk_3` FOREIGN KEY (`currency`) REFERENCES `SITE_DB`.`system_currencies` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
