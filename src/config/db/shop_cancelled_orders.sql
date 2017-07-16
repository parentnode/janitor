CREATE TABLE `SITE_DB`.`shop_cancelled_orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,

  `order_id` int(11) DEFAULT NULL,
  `creditnote_no` varchar(10) NOT NULL,

  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`),
  KEY (`order_id`),
  CONSTRAINT `shop_cancelled_orders_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `SITE_DB`.`shop_orders` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
