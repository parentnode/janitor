CREATE TABLE `SITE_DB`.`shop_orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,

  `order_no` varchar(10) NOT NULL,

  `country` varchar(2) DEFAULT NULL,
  `currency` varchar(3) DEFAULT NULL,

  `status` int(11) NOT NULL DEFAULT 0,
  `payment_status` int(11) NOT NULL DEFAULT 0,
  `shipping_status` int(11) NOT NULL DEFAULT 0,


  `delivery_name` varchar(255) DEFAULT NULL,
  `delivery_att` varchar(255) DEFAULT NULL,
  `delivery_address1` varchar(255) DEFAULT NULL,
  `delivery_address2` varchar(255) DEFAULT NULL,
  `delivery_city` varchar(255) DEFAULT NULL,
  `delivery_postal` varchar(255) DEFAULT NULL,
  `delivery_state` varchar(255) DEFAULT NULL,
  `delivery_country` varchar(255) DEFAULT NULL,

  `billing_name` varchar(255) DEFAULT NULL,
  `billing_att` varchar(255) DEFAULT NULL,
  `billing_address1` varchar(255) DEFAULT NULL,
  `billing_address2` varchar(255) DEFAULT NULL,
  `billing_city` varchar(255) DEFAULT NULL,
  `billing_postal` varchar(255) DEFAULT NULL,
  `billing_state` varchar(255) DEFAULT NULL,
  `billing_country` varchar(255) DEFAULT NULL,

  `comment` text,

  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_at` timestamp NULL DEFAULT NULL,

  PRIMARY KEY (`id`),
  UNIQUE KEY (`order_no`),
  KEY `user_id` (`user_id`),
  KEY `country` (`country`),
  KEY `currency` (`currency`),
  CONSTRAINT `shop_orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `SITE_DB`.`users` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `shop_orders_ibfk_2` FOREIGN KEY (`country`) REFERENCES `SITE_DB`.`system_countries` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `shop_orders_ibfk_3` FOREIGN KEY (`currency`) REFERENCES `SITE_DB`.`system_currencies` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
