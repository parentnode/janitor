INSERT INTO `SITE_DB`.`system_payment_methods` (`id`, `name`, `classname`, `description`, `gateway`, `state`, `position`)
VALUES
	(1,'Bank transfer','banktransfer','Regular bank transfer. Preferred option.',NULL,NULL,1),
	(2,'Credit Card','stripe','Stripe credit card payment - 1.4% transaction fee. *','stripe','public',2);
