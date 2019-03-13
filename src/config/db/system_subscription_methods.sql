CREATE TABLE `SITE_DB`.`system_subscription_methods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,

  `duration` varchar(50) NOT NULL,
  `starts_on` varchar(50) DEFAULT NULL,

  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
