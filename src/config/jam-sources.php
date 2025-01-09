<?php
	
$jam_sources = [
	"instantmessage" => [
		"name" => "Instant message gateways",
		"modules" => [
			"telegram" => [
				"name" => "Telegram messenger",
				"info_link" => "https://telegram.org/",
				"repos" =>	"https://github.com/parentnode/jam-instantmessage-telegram",
			],
		]
	],
	"email" => [
		"name" => "Email gateways",
		"modules" => [
			"mailgun" => [
				"name" => "Mailgun",
				"info_link" => "https://mailgun.com",
				"repos" => "https://github.com/parentnode/jam-email-mailgun",
			],
			"smtp" => [
				"name" => "SMTP with PHPMailer",
				"info_link" => "https://github.com/PHPMailer/PHPMailer",
				"repos" => "https://github.com/parentnode/jam-email-smtp",
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

