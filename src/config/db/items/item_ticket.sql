CREATE TABLE `SITE_DB`.`item_ticket` (
  `id` int(11) NOT NULL auto_increment,
  `item_id` int(11) NOT NULL,

  `name` varchar(100) NOT NULL,
  `classname` varchar(50) NOT NULL DEFAULT '',

  `description` text NOT NULL DEFAULT '',
  `html` text NOT NULL DEFAULT '',

  `ordered_message_id` int(11) NULL DEFAULT NULL,

  `mail_information` text NOT NULL DEFAULT '',
  `ticket_information` text NOT NULL DEFAULT '',

  `sale_opens` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `sale_closes` timestamp NULL DEFAULT NULL,

  `total_tickets` int(11) NULL DEFAULT NULL,

  PRIMARY KEY  (`id`),
  KEY `item_id` (`item_id`),
  CONSTRAINT `item_ticket_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `SITE_DB`.`items` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `item_ticket_ibfk_2` FOREIGN KEY (`ordered_message_id`) REFERENCES `SITE_DB`.`items` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
