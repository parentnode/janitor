CREATE TABLE `SITE_DB`.`item_membership` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) NOT NULL,

  `name` varchar(255) NOT NULL,
  `classname` varchar(100) NOT NULL DEFAULT '',
  `description` text NOT NULL DEFAULT '',
  `introduction` text NOT NULL DEFAULT '',
  `html` text NOT NULL DEFAULT '',

  `position` int(11) NOT NULL DEFAULT '0',

  PRIMARY KEY (`id`),
  KEY `item_id` (`item_id`),
  CONSTRAINT `item_membership_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `SITE_DB`.`items` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;