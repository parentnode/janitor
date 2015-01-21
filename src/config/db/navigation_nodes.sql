CREATE TABLE `SITE_DB`.`navigation_nodes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `navigation_id` int(11) NOT NULL,

  `node_name` varchar(255) NOT NULL,
  `node_link` varchar(255) DEFAULT NULL,
  `node_page_id` int(11) DEFAULT NULL,
  `node_classname` varchar(255) DEFAULT NULL,

  `relation` int(11) DEFAULT 0,
  `position` int(11) DEFAULT 0,

  PRIMARY KEY (`id`),
  KEY `navigation_id` (`navigation_id`),
  CONSTRAINT `navigation_nodes_ibfk_1` FOREIGN KEY (`navigation_id`) REFERENCES `SITE_DB`.`navigation` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;