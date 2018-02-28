CREATE TABLE `SITE_DB`.`item_post` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) NOT NULL,

  `name` varchar(100) NOT NULL,
  `classname` varchar(100) NOT NULL DEFAULT '',
  `description` text NOT NULL DEFAULT '',
  `html` text NOT NULL DEFAULT '',

  PRIMARY KEY  (`id`),
  KEY `item_id` (`item_id`),
  CONSTRAINT `item_post_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `SITE_DB`.`items` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
