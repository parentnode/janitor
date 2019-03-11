CREATE TABLE `SITE_DB`.`user_log_payment_reminders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,

  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `order_id` (`order_id`),
  CONSTRAINT `user_log_payment_reminders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `SITE_DB`.`users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `user_log_payment_reminders_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `SITE_DB`.`shop_orders` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
