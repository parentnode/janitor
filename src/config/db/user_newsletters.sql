CREATE TABLE `SITE_DB`.`user_newsletters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `newsletter_id` int(11) NOT NULL,

  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `newsletter_id` (`newsletter_id`),
  CONSTRAINT `user_newsletters_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `SITE_DB`.`users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `user_newsletters_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `SITE_DB`.`users` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
