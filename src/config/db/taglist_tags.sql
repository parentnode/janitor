CREATE TABLE `SITE_DB`.`taglist_tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `taglist_id` int(11) NOT NULL,

  `tag_id` int(11) DEFAULT NULL,
  `position` int(11) NOT NULL DEFAULT 0,

  PRIMARY KEY (`id`),
  KEY `taglist_id` (`taglist_id`),
  KEY `tag_id` (`tag_id`),
  CONSTRAINT `taglist_tags_ibfk_1` FOREIGN KEY (`taglist_id`) REFERENCES `SITE_DB`.`taglists` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `taglist_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `SITE_DB`.`tags` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;