CREATE TABLE `SITE_DB`.`user_gateway_stripe` (
  `id` int(11) NOT NULL AUTO_INCREMENT,

  `user_id` int(11) NOT NULL,
  `customer_id` varchar(50) NOT NULL,

  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `user_gateway_stripe_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `SITE_DB`.`users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;