INSERT INTO `languages` (`id`, `name`) VALUES 
('DA', 'Dansk');

INSERT INTO `currencies` (`id`, `name`, `abbreviation`, `abbreviation_position`, `decimals`, `decimal_separator`, `grouping_separator`) VALUES 
('DKK', 'Kroner (Denmark)', 'DKK', 'after', 2, ',', '.');

INSERT INTO `countries` (`id`, `name`, `phone_countrycode`, `phone_format`, `language`, `currency`) VALUES 
('DK', 'Danmark', '45', '#### ####', 'DA', 'DKK');

INSERT INTO `vat_rates` (`id`, `name`, `vat_rate`, `country`) VALUES 
(DEFAULT, 'General', '25', 'DK');


