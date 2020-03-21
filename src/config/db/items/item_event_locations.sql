CREATE TABLE `SITE_DB`.`item_event_locations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,

  `location` varchar(255) DEFAULT NULL,

  `location_type` int(11) DEFAULT 1,

  `location_url` varchar(255) DEFAULT NULL,

  `location_address1` varchar(255) DEFAULT NULL,
  `location_address2` varchar(255) DEFAULT NULL,
  `location_city` varchar(255) DEFAULT NULL,
  `location_postal` varchar(255) DEFAULT NULL,
  `location_country` varchar(2) DEFAULT NULL,
  `location_googlemaps` varchar(255) DEFAULT NULL,

  `location_comment` text DEFAULT NULL,

  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `location_country` (`location_country`),

  CONSTRAINT `item_event_locations_ibfk_1` FOREIGN KEY (`location_country`) REFERENCES `SITE_DB`.`system_countries` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;