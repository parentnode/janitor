<?php
	
$jam_sources = [
	"instantmessages" => [
		"name" => "Instant message gateways",
		"modules" => [
			"telegram" => [
				"name" => "Telegram messenger",
				"info_link" => "https://telegram.org/",
				"repos" =>	"https://github.com/parentnode/jam-instantmessages-telegram",
			],
		]
	],
	"mailer" => [
		"name" => "Mail gateways",
		"modules" => [
			"mailgun" => [
				"name" => "Mailgun",
				"info_link" => "https://mailgun.com",
				"repos" => "https://github.com/parentnode/jam-mailer-mailgun",
			],
			"phpmailer" => [
				"name" => "PHPMailer",
				"info_link" => "https://github.com/PHPMailer/PHPMailer",
				"repos" => "https://github.com/parentnode/jam-mailer-phpmailer",
			],
		]
		
	],
	"sms" => [
		"name" => "SMS message gateways",
		"modules" => [
			"twilio" => [
				"name" => "Twilio",
				"info_link" => "https://twilio.com",
				"repos" => "https://github.com/parentnode/jam-sms-twilio",
			],
		] 
	],
	
];

