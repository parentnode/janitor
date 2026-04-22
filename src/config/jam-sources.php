<?php
	
$jam_sources = [
	"instantmessage" => [
		"name" => "Instant message gateways",
		"description" => "Modules to send instant messages via popular messaging platforms.",
		"modules" => [
			"telegram" => [
				"name" => "Telegram messenger",
				"description" => "Telegram instant message gateway integration",
				"info_link" => "https://telegram.org/",
				"repos" =>	"https://github.com/parentnode/jam-instantmessage-telegram",
			],
		]
	],
	"email" => [
		"name" => "Email gateways",
		"description" => "Modules to send email notifications and messages.",
		"modules" => [
			"mailgun" => [
				"name" => "Mailgun",
				"description" => "Mailgun email gateway integration",
				"info_link" => "https://mailgun.com",
				"repos" => "https://github.com/parentnode/jam-email-mailgun",
			],
			"smtp" => [
				"name" => "SMTP with PHPMailer",
				"description" => "SMTP email gateway using PHPMailer library",
				"info_link" => "https://github.com/PHPMailer/PHPMailer",
				"repos" => "https://github.com/parentnode/jam-email-smtp",
			],
		]
		
	],
	"sms" => [
		"name" => "SMS message gateways",
		"description" => "Modules to send SMS messages via popular SMS platforms.",
		"modules" => [
			"twilio" => [
				"name" => "Twilio",
				"description" => "Twilio SMS gateway integration",
				"info_link" => "https://twilio.com",
				"repos" => "https://github.com/parentnode/jam-sms-twilio",
			],
		] 
	],
	"fraudprotection" => [
		"name" => "Fraud protections systems",
		"description" => "Modules to protect forms and other user inputs from spam and abuse.",
		"modules" => [
			"recaptcha" => [
				"name" => "reCAPTCHA",
				"description" => "Google reCAPTCHA v3 protection system",
				"info_link" => "https://www.google.com/recaptcha/intro/v3",
				"repos" => "https://github.com/parentnode/jam-fraudprotection-recaptcha",
			],
		] 
	],
	"item" => [
		"name" => "Itemtypes",
		"description" => "Itemtypes define the data structure for basic content items in Janitor.",
		"modules" => [
			"service" => [
				"name" => "Service itemtype",
				"description" => "The simplest content item for describing the services offered.",
				"info_link" => "https://github.com/parentnode/jam-item-service-simplest",
				"repos" => "https://github.com/parentnode/jam-item-service-simplest",
			],
		] 
	],
];

