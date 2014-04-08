CREATE TABLE `SITE_DB`.`shop_carts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cart_reference` varchar(12) NOT NULL,

  `country` varchar(2) NOT NULL,
  `currency` varchar(3) NOT NULL,

  `user_id` int(11) DEFAULT NULL,
  `status` int(11) NOT NULL DEFAULT 0,

  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_at` timestamp NULL DEFAULT NULL,

  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
