CREATE TABLE `SITE_DB`.`item_event_tickets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,

  `event_id` int(11) NOT NULL,
  `ticket_id` int(11) NOT NULL,


  PRIMARY KEY (`id`),
  KEY `event_id` (`event_id`),
  KEY `ticket_id` (`ticket_id`),
  CONSTRAINT `item_event_tickets_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `SITE_DB`.`items` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `item_event_tickets_ibfk_2` FOREIGN KEY (`ticket_id`) REFERENCES `SITE_DB`.`items` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;