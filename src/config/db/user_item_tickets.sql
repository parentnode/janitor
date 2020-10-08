CREATE TABLE `SITE_DB`.`user_item_tickets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,

  `order_item_id` int(11) DEFAULT NULL,

  `ticket_no` varchar(20) NOT NULL,

  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `item_id` (`item_id`),
  KEY `order_item_id` (`order_item_id`),
  CONSTRAINT `user_item_tickets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `SITE_DB`.`users` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `user_item_tickets_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `SITE_DB`.`items` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `user_item_tickets_ibfk_3` FOREIGN KEY (`order_item_id`) REFERENCES `SITE_DB`.`shop_order_items` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
