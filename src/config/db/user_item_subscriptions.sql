CREATE TABLE `SITE_DB`.`user_item_subscriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,

  `payment_method` int(11) DEFAULT NULL,

  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_at` timestamp NULL DEFAULT NULL,

  `renewed_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,

  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `item_id` (`item_id`),
  KEY `order_id` (`order_id`),
  KEY `payment_method` (`payment_method`),
  CONSTRAINT `user_item_subscriptions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `SITE_DB`.`users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `user_item_subscriptions_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `SITE_DB`.`items` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `user_item_subscriptions_ibfk_3` FOREIGN KEY (`order_id`) REFERENCES `SITE_DB`.`shop_orders` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `user_item_subscriptions_ibfk_4` FOREIGN KEY (`payment_method`) REFERENCES `SITE_DB`.`system_payment_methods` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
