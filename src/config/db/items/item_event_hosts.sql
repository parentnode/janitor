CREATE TABLE `SITE_DB`.`item_event_hosts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,

  `host` varchar(255) DEFAULT NULL,
  `host_address1` varchar(255) DEFAULT NULL,
  `host_address2` varchar(255) DEFAULT NULL,
  `host_city` varchar(255) DEFAULT NULL,
  `host_postal` varchar(255) DEFAULT NULL,
  `host_country` varchar(2) DEFAULT NULL,
  `host_googlemaps` varchar(255) DEFAULT NULL,
  `host_comment` text DEFAULT NULL,

  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `host_country` (`host_country`),
  CONSTRAINT `item_event_hosts_ibfk_1` FOREIGN KEY (`host_country`) REFERENCES `SITE_DB`.`system_countries` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;