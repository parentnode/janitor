CREATE TABLE `SITE_DB`.`user_maillists` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `maillist_id` int(11) NOT NULL,

  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `maillist_id` (`maillist_id`),
  CONSTRAINT `user_maillists_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `SITE_DB`.`users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `user_maillists_ibfk_2` FOREIGN KEY (`maillist_id`) REFERENCES `SITE_DB`.`system_maillists` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
