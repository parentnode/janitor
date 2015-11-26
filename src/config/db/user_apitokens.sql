CREATE TABLE `SITE_DB`.`user_apitokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,

  `token` varchar(255) NOT NULL,

  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `user_apitokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `SITE_DB`.`users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
