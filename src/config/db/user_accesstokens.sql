CREATE TABLE `SITE_DB`.`user_accesstokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,

  `public_token` varchar(255) NOT NULL,
  `private_token` text NOT NULL,
  `useragent` text NOT NULL,
  `ip` varchar(60) NOT NULL,

  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `user_accesstokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `SITE_DB`.`users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
