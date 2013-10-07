CREATE TABLE `SITE_DB`.`order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,

  `order_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,

  `name` varchar(255) DEFAULT NULL,

  `quantity` int(11) DEFAULT NULL,

  `price` float NOT NULL,
  `vat` float NOT NULL,
  `total_price` float NOT NULL,
  `total_vat` float NOT NULL,

  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `item_id` (`item_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
