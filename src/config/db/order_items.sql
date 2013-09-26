CREATE TABLE `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shop_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_item_id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `currency` varchar(255) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `item_price_ex_vat_in_cents` int(11) DEFAULT NULL,
  `item_price_inc_vat_in_cents` int(11) DEFAULT NULL,
  `total_price_ex_vat_in_cents` int(11) DEFAULT NULL,
  `total_price_inc_vat_in_cents` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
