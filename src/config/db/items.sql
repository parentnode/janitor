CREATE TABLE `items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,

  `sindex` varchar(255) DEFAULT NULL,
  `status` int(11) NOT NULL,
  `itemtype` varchar(40) NOT NULL,

  `user_id` int(11) DEFAULT NULL,

  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_at` timestamp NULL DEFAULT NULL,
  `published_at` timestamp NULL DEFAULT NULL,

  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;