CREATE TABLE `tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,

  `context` varchar(50) NOT NULL,
  `value` varchar(100) NOT NULL,
  `description` text,

  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8;