CREATE TABLE `SITE_DB`.`item_product` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) NOT NULL,

  `name` varchar(100) NOT NULL,
  `description` text NOT NULL DEFAULT '',
  `producttype` text NOT NULL DEFAULT '',
  `supplier` int(11) NOT NULL,
  `productAvailability` varchar(255) NULL,

  PRIMARY KEY  (`id`),
  KEY `item_id` (`item_id`),
  CONSTRAINT `item_product_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `SITE_DB`.`items` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;


