CREATE TABLE `SITE_DB`.`shop_carts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,

  `cart_reference` varchar(12) NOT NULL,

  `country` varchar(2) NOT NULL,
  `currency` varchar(3) NOT NULL,

  `delivery_address_id` int(11) DEFAULT NULL,
  `billing_address_id` int(11) DEFAULT NULL,
  

  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_at` timestamp NULL DEFAULT NULL,

  PRIMARY KEY  (`id`),
  UNIQUE KEY (`cart_reference`),
  KEY `country` (`country`),
  KEY `currency` (`currency`),
  KEY `delivery_address_id` (`delivery_address_id`),
  KEY `billing_address_id` (`billing_address_id`),

  CONSTRAINT `shop_carts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `SITE_DB`.`users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `shop_carts_ibfk_2` FOREIGN KEY (`country`) REFERENCES `SITE_DB`.`system_countries` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `shop_carts_ibfk_3` FOREIGN KEY (`currency`) REFERENCES `SITE_DB`.`system_currencies` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `shop_carts_ibfk_4` FOREIGN KEY (`delivery_address_id`) REFERENCES `SITE_DB`.`user_addresses` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `shop_carts_ibfk_5` FOREIGN KEY (`billing_address_id`) REFERENCES `SITE_DB`.`user_addresses` (`id`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
