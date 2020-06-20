CREATE TABLE `SITE_DB`.`system_payment_methods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `classname` varchar(50) DEFAULT NULL,
  `description` text DEFAULT '',
  `gateway` varchar(50) NULL DEFAULT NULL,
  `state` varchar(10) NULL DEFAULT NULL,
  `position` int(11) NOT NULL DEFAULT '0',

  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
