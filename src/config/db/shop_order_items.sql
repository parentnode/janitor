CREATE TABLE `SITE_DB`.`shop_order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,

  `order_id` int(11) NOT NULL,
  `item_id` int(11) DEFAULT NULL,
  `shipped_by` int(11) NULL DEFAULT NULL,

  `name` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL,

  `unit_price` float NOT NULL,
  `unit_vat` float NOT NULL,
  `total_price` float NOT NULL,
  `total_vat` float NOT NULL,

  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  CONSTRAINT `shop_order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `SITE_DB`.`shop_orders` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `shop_order_items_ibfk_2` FOREIGN KEY (`shipped_by`) REFERENCES `SITE_DB`.`users` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
