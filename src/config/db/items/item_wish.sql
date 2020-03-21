CREATE TABLE `SITE_DB`.`item_wish` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) NOT NULL,

  `name` varchar(100) NOT NULL,
  `description` text NOT NULL DEFAULT '',
  `link` varchar(255) NULL,
  `price` int(11) NOT NULL DEFAULT 0,
  `reserved` varchar(100) NOT NULL DEFAULT '',

  PRIMARY KEY (`id`),
  KEY `item_id` (`item_id`),
  CONSTRAINT `item_wish_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;