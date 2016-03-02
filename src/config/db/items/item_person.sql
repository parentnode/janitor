CREATE TABLE `SITE_DB`.`item_person` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`item_id` int(11) NOT NULL ,
	
	`name` varchar(255) NOT NULL,
	`description` text NOT NULL,
	`html` text NOT NULL,
	
	`job_title` text NOT NULL,
	`email` varchar(255) NOT NULL,
	`tel` varchar(255) NOT NULL,

    `position` int(11) DEFAULT '0',
	
	PRIMARY KEY (`id`),
	KEY `item_id` (`item_id`),
	CONSTRAINT `item_person_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `SITE_DB`.`items` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
)