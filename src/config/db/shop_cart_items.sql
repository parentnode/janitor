CREATE TABLE `SITE_DB`.`shop_cart_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cart_id` int(11) NOT NULL,

  `item_id` int(11) NOT NULL,

  `quantity` int(11) NOT NULL,

  `custom_name` varchar(100) DEFAULT NULL,
  `custom_price` float DEFAULT NULL,


  PRIMARY KEY (`id`),
  KEY `cart_id` (`cart_id`),
  KEY `item_id` (`item_id`),
  CONSTRAINT `shop_cart_items_ibfk_1` FOREIGN KEY (`cart_id`) REFERENCES `SITE_DB`.`shop_carts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `shop_cart_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `SITE_DB`.`items` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


