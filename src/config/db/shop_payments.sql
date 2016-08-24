CREATE TABLE `SITE_DB`.`shop_payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,

  `currency` varchar(3) DEFAULT NULL,
  `payment_amount` float NOT NULL,
 
  `transaction_id` int(11) DEFAULT NULL,
  `payment_method` int(11) DEFAULT NULL,

  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_at` timestamp NULL DEFAULT NULL,

  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `currency` (`currency`),
  KEY `payment_method` (`payment_method`),
  CONSTRAINT `shop_payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `SITE_DB`.`shop_orders` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `shop_payments_ibfk_2` FOREIGN KEY (`currency`) REFERENCES `SITE_DB`.`system_currencies` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `shop_payments_ibfk_3` FOREIGN KEY (`payment_method`) REFERENCES `SITE_DB`.`system_payment_methods` (`id`) ON UPDATE CASCADE  
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
