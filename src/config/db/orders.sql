CREATE TABLE `SITE_DB`.`orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cart_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,

  `country` varchar(2) NOT NULL,
  `currency` varchar(3) NOT NULL,

  `status` int(11) NOT NULL,

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

  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_at` timestamp NULL DEFAULT NULL,

  PRIMARY KEY (`id`),
  KEY `cart_id` (`cart_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `orders_cart_ibfk_1` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `orders_user_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
