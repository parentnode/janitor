INSERT INTO `SITE_DB`.`navigation_nodes` (`id`, `navigation_id`, `node_name`, `node_link`, `node_item_id`, `node_item_controller`, `node_classname`, `node_target`, `node_fallback`, `relation`, `position`)
VALUES
	(1,1,'Frontpage','/',NULL,NULL,'front',NULL,NULL,0,0),
	(2,1,'Posts','/posts',NULL,NULL,'posts',NULL,NULL,0,0),
	(3,2,'Posts','/janitor/admin/post/list',NULL,NULL,'post',NULL,NULL,0,0);
