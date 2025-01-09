CREATE TABLE `SITE_DB`.`system_autoconversions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoked_by_ip` varchar(40) DEFAULT NULL,
  `converted_at` timestamp DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
