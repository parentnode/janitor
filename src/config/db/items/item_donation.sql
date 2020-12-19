CREATE TABLE `SITE_DB`.`item_donation` (
  `id` int(11) NOT NULL auto_increment,
  `item_id` int(11) NOT NULL,

  `name` varchar(100) NOT NULL,
  `classname` varchar(50) NOT NULL DEFAULT '',

  `description` text NOT NULL DEFAULT '',
  `html` text NOT NULL DEFAULT '',

  `ordered_message_id` int(11) NULL DEFAULT NULL,

  `position` int(11) NOT NULL DEFAULT '0',

  PRIMARY KEY  (`id`),
  KEY `item_id` (`item_id`),
  CONSTRAINT `item_donation_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `SITE_DB`.`items` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `item_donation_ibfk_2` FOREIGN KEY (`ordered_message_id`) REFERENCES `SITE_DB`.`items` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
