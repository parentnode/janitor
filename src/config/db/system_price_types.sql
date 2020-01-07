CREATE TABLE `SITE_DB`.`system_price_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) DEFAULT NULL,
  `name` varchar(20) NOT NULL,
  `description` varchar(255) NOT NULL,


  PRIMARY KEY  (`id`),
  KEY `item_id` (`item_id`),
  
  CONSTRAINT `system_price_types_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `SITE_DB`.`items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
