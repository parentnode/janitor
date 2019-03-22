CREATE TABLE `SITE_DB`.`item_department` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  
  PRIMARY KEY  (`id`),
  KEY `item_id` (`item_id`),
  CONSTRAINT `item_department_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `SITE_DB`.`items` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  KEY `department_id` (`department_id`),
  CONSTRAINT `item_department_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `SITE_DB`.`system_departments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

