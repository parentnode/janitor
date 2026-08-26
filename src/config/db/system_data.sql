CREATE TABLE `SITE_DB`.`system_data` (
  `id` varchar(255) NOT NULL,

  `data` text NOT NULL DEFAULT '',

  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_at` timestamp NULL DEFAULT NULL,

  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
