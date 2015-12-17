CREATE TABLE `SITE_DB`.`item_todolist_todos_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) NOT NULL,
  `todo_id` int(11) NOT NULL,

  `position` int(11) DEFAULT '0',

  PRIMARY KEY (`id`),
  KEY `item_id` (`item_id`),
  KEY `todo_id` (`todo_id`),
  CONSTRAINT `item_todolist_todos_order_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `SITE_DB`.`items` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `item_todolist_todos_order_ibfk_2` FOREIGN KEY (`todo_id`) REFERENCES `SITE_DB`.`items` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
