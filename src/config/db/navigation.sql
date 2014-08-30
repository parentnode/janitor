CREATE TABLE `SITE_DB`.`navigation` (
  `id` int(11) NOT NULL auto_increment,

  `name` varchar(100) NOT NULL,
  `handle` varchar(100) NOT NULL,

  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
