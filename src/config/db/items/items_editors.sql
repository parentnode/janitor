CREATE TABLE `SITE_DB`.`items_editors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) NOT NULL,

  `user_id` int(11) NOT NULL,

  PRIMARY KEY (`id`),
  CONSTRAINT `items_editors_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `SITE_DB`.`users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;