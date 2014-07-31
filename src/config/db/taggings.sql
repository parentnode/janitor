CREATE TABLE `SITE_DB`.`taggings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) NOT NULL,

  `tag_id` int(11) NOT NULL,

  PRIMARY KEY (`id`),
  KEY `item_id` (`item_id`),
  KEY `tag_id` (`tag_id`),
  CONSTRAINT `taggings_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `taggings_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

