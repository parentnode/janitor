CREATE TABLE `SITE_DB`.`access` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_group_id` int(11) NOT NULL,
  `action` varchar(255) NOT NULL,
  `controller` varchar(255) DEFAULT NULL,

  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_at` timestamp NULL DEFAULT NULL,

  PRIMARY KEY (`id`),
  KEY `user_group_id` (`user_group_id`),
  CONSTRAINT `access_ibfk_1` FOREIGN KEY (`user_group_id`) REFERENCES `user_groups` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
