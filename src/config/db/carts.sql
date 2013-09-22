CREATE TABLE `carts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,

  `country` varchar(2) NOT NULL,
  `currency` varchar(3) NOT NULL,

  `user_id` int(11) DEFAULT NULL,

  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_at` timestamp NULL DEFAULT NULL,

  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
