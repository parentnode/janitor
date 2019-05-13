CREATE TABLE `SITE_DB`.`user_usernames` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,

  `username` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,

  `verified` int(11) NOT NULL DEFAULT 0,
  `verification_code` varchar(8) NOT NULL,

  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `user_usernames_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `SITE_DB`.`users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
