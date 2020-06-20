CREATE TABLE `SITE_DB`.`user_gateway_stripe_subscription_payment_method` (
  `id` int(11) NOT NULL AUTO_INCREMENT,

  `user_id` int(11) NOT NULL,
  `subscription_id` int(11) NOT NULL,
  `payment_method_id` varchar(100) NOT NULL,

  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `subscription_id` (`subscription_id`),
  CONSTRAINT `user_gateway_stripe_subscription_payment_method_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `SITE_DB`.`users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `user_gateway_stripe_subscription_payment_method_ibfk_2` FOREIGN KEY (`subscription_id`) REFERENCES `SITE_DB`.`user_item_subscriptions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;