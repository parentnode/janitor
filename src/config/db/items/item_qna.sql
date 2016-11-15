CREATE TABLE `SITE_DB`.`item_qna` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) NOT NULL,

  `name` varchar(50) NOT NULL,
  `about_item_id` int(11) DEFAULT NULL,
  `question` text NOT NULL,
  `answer` text DEFAULT NULL,

  PRIMARY KEY (`id`),
  KEY `item_id` (`item_id`),
  KEY `about_item_id` (`about_item_id`),
  CONSTRAINT `items_qna_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `SITE_DB`.`items` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
  CONSTRAINT `items_qna_ibfk_2` FOREIGN KEY (`about_item_id`) REFERENCES `SITE_DB`.`items` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;