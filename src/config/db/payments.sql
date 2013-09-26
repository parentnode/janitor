CREATE TABLE `payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `checkout_id` int(11) NOT NULL,
  `currency` varchar(255) DEFAULT NULL,
  `amount_in_cents` int(11) DEFAULT NULL,
  `gateway_transaction_number` varchar(255) DEFAULT NULL,
  `gateway_status` varchar(255) DEFAULT NULL,
  `captured_amount_in_cents` int(11) DEFAULT NULL,
  `captured_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `gateway_name` varchar(255) DEFAULT NULL,
  `gateway_order_number` varchar(255) DEFAULT NULL,
  `card_type` varchar(255) DEFAULT NULL,
  `card_number` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
