CREATE TABLE `SITE_DB`.`items_mediae` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) NOT NULL,

  `name` varchar(255) NOT NULL,
  `description` text NOT NULL DEFAULT '',
  `format` varchar(5) DEFAULT NULL,
  `variant` varchar(40) NOT NULL,
  `width` int(11) DEFAULT NULL,
  `height` int(11) DEFAULT NULL,
  `filesize` int(11) DEFAULT NULL,

  `poster` varchar(5) DEFAULT NULL,

  `position` int(11) DEFAULT 1,

  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_at` timestamp NULL DEFAULT NULL,

  PRIMARY KEY (`id`),
  KEY `item_id` (`item_id`),
  CONSTRAINT `items_mediae_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `SITE_DB`.`items` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;