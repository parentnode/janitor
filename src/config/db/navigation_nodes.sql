CREATE TABLE `SITE_DB`.`navigation_nodes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `navigation_id` int(11) NOT NULL,

  `node_name` varchar(255) NOT NULL,
  `node_link` varchar(255) DEFAULT NULL,
  `node_item_id` int(11) DEFAULT NULL,
  `node_item_controller` varchar(255) DEFAULT NULL,

  `node_classname` varchar(255) DEFAULT NULL,
  `node_target` varchar(255) DEFAULT NULL,
  `node_fallback` varchar(255) DEFAULT NULL,

  `relation` int(11) NOT NULL DEFAULT 0,
  `position` int(11) NOT NULL DEFAULT 0,

  PRIMARY KEY (`id`),
  KEY `navigation_id` (`navigation_id`),
  KEY `node_item_id` (`node_item_id`),
  CONSTRAINT `navigation_nodes_ibfk_1` FOREIGN KEY (`navigation_id`) REFERENCES `SITE_DB`.`navigation` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `navigation_nodes_ibfk_2` FOREIGN KEY (`node_item_id`) REFERENCES `SITE_DB`.`items` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;