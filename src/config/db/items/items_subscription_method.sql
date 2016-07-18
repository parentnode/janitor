CREATE TABLE `SITE_DB`.`items_subscription_method` (
  `id` int(11) NOT NULL AUTO_INCREMENT,

  `item_id` int(11) NOT NULL,
  `subscription_method_id` int(11) NOT NULL,


  PRIMARY KEY (`id`),
  KEY `item_id` (`item_id`),
  KEY `subscription_method_id` (`subscription_method_id`),
  CONSTRAINT `items_subscription_method_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `SITE_DB`.`items` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `items_subscription_method_ibfk_2` FOREIGN KEY (`subscription_method_id`) REFERENCES `SITE_DB`.`system_subscription_methods` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;