CREATE TABLE `SITE_DB`.`user_log_verification_links` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `username_id` int(11) NOT NULL,

  `reminded_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `user_log_user_log_verification_links_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `SITE_DB`.`users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `user_log_user_log_verification_links_ibfk_2` FOREIGN KEY (`username_id`) REFERENCES `SITE_DB`.`user_usernames` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
