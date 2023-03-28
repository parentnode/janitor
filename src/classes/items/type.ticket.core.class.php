<?php
/**
* @package janitor.itemtypes
* This file contains itemtype functionality
*/

class TypeTicketCore extends Itemtype {


	public $db;
	public $db_user_tickets;
	public $db_editors;


	/**
	* Init, set varnames, validation rules
	*/
	function __construct($itemtype) {

		// construct ItemType before adding to model
		parent::__construct($itemtype);


		// itemtype database
		$this->db = SITE_DB.".item_ticket";
		$this->db_user_tickets = SITE_DB.".user_item_tickets";
		$this->db_editors = SITE_DB.".item_ticket_editors";


		// Name
		$this->addToModel("name", array(
			"type" => "string",
			"label" => "Ticket name",
			"required" => true,
			"hint_message" => "Name of the ticket.", 
			"error_message" => "Name must be filled out."
		));

		// Class
		$this->addToModel("classname", array(
			"type" => "string",
			"label" => "CSS Class for list.",
			"hint_message" => "CSS class for custom styling. If you don't know what this is, just leave it empty."
		));

		// description
		$this->addToModel("description", array(
			"type" => "text",
			"label" => "Short SEO description",
			"max" => 155,
			"hint_message" => "Write a short description of the ticket for SEO and listings.",
			"error_message" => "Your ticket needs a description – max 155 characters."
		));

		// HTML
		$this->addToModel("html", array(
			"type" => "html",
			"label" => "Full description",
			"required" => true,
			"allowed_tags" => "p,h2,h3,h4,ul,ol,download,jpg,png",
			"hint_message" => "Write the full description of what the ticket is for.",
			"error_message" => "A ticket description without any words? How weird."
		));

		// Single media
		$this->addToModel("single_media", array(
			"type" => "files",
			"label" => "Add media here",
			"max" => 1,
			"allowed_formats" => "png,jpg",
			"hint_message" => "Add single image by dragging it here. PNG or JPG allowed.",
			"error_message" => "Media does not fit requirements."
		));

		// ordered_message_id
		$this->addToModel("ordered_message_id", [
			"type" => "integer",
			"label" => "Ticket message",
			"required" => true,
			"hint_message" => "Select a message to send to users along with ticket, when they order this ticket.",
			"error_message" => "You must choose a ticket email to be sent when ticket is ordered."
		]);

		// Available from
		$this->addToModel("sale_opens", array(
			"type" => "datetime",
			"label" => "Ticket sale opens",
			"required" => true,
			"hint_message" => "State when the ticket sale should start.",
			"error_message" => "Start date/time must be a valid date/time."
		));

		// Available until
		$this->addToModel("sale_closes", array(
			"type" => "datetime",
			"label" => "Ticket sale closes",
			"required" => true,
			"hint_message" => "State when the ticket sale should end.",
			"error_message" => "End date/time must be a valid date/time."
		));

		// Total tickets
		$this->addToModel("total_tickets", array(
			"type" => "integer",
			"label" => "Total number of tickets",
			"required" => true,
			"hint_message" => "How many tickets can be sold.",
			"error_message" => "Total number of tickets must be a number."
		));

		// mail_information
		$this->addToModel("mail_information", array(
			"type" => "text",
			"label" => "Event information for ticket email",
			"max" => "600",
			"hint_message" => "A text to include in ticket email – this must be plain text. If advanced layout is needed, create a custom mail for this ticket.",
			"error_message" => "There is something wrong with this text."
		));

		// ticket information
		$this->addToModel("ticket_information", array(
			"type" => "html",
			"label" => "Event information for ticket",
			"allowed_tags" => "p,h3,ul",
			"max" => "300",
			"hint_message" => "A text to print on the ticket – preferably containing event location and date – but keep it short, there is limited space.",
			"error_message" => "Your mail text is too long."
		));

		// item_editor
		$this->addToModel("item_editor", array(
			"type" => "user_id",
			"label" => "Ticket editor",
			"required" => true,
			"hint_message" => "Select a ticket editor.",
			"error_message" => "You need to select a ticket editor."
		));

		// item_event
		$this->addToModel("item_event", array(
			"type" => "item_id",
			"label" => "Ticket event",
			"required" => true,
			"hint_message" => "Select an event for this ticket.",
			"error_message" => "You need to select a valid event."
		));

	}

	// Find specific tickets in orders
	function getParticipants($item_id) {

		$participants = [];

		$query = new Query();
		$sql = "SELECT users.nickname, usernames.username, tickets.ticket_no, orders.id as order_id, order_items.unit_price, orders.currency FROM ".SITE_DB.".shop_orders as orders, ".SITE_DB.".shop_order_items as order_items, ".SITE_DB.".user_item_tickets as tickets, ".SITE_DB.".users as users, ".SITE_DB.".user_usernames as usernames WHERE tickets.item_id = $item_id AND tickets.order_item_id = order_items.id AND order_items.order_id = orders.id AND orders.payment_status = 2 AND orders.status != 3 AND users.id = tickets.user_id AND tickets.user_id = usernames.user_id AND usernames.type = 'email'";
		// debug([$sql]);
		if($query->sql($sql)) {
			$participants["paid"] = $query->results();
		}

		$sql = "SELECT users.nickname, usernames.username, tickets.ticket_no, orders.id as order_id, order_items.unit_price, orders.currency FROM ".SITE_DB.".shop_orders as orders, ".SITE_DB.".shop_order_items as order_items, ".SITE_DB.".user_item_tickets as tickets, ".SITE_DB.".users as users, ".SITE_DB.".user_usernames as usernames WHERE tickets.item_id = $item_id AND tickets.order_item_id = order_items.id AND order_items.order_id = orders.id AND orders.payment_status != 2 AND orders.status != 3 AND users.id = tickets.user_id AND tickets.user_id = usernames.user_id AND usernames.type = 'email'";
		// debug([$sql]);
		if($query->sql($sql)) {
			$participants["unpaid"] = $query->results();
		}

		return $participants;
		// $count = $query->result(0, "sum");

	}

	function prepareParticipantListForDownload($item_id) {

		$data = "Ticket No;Name;Email;Price;Paid;\n";

		$participants = $this->getParticipants($item_id);
		// debug([$participants]);

		if($participants) {

			if($participants["paid"]) {
				foreach($participants["paid"] as $participant) {
					$data .= $participant["ticket_no"].";".$participant["nickname"].";".$participant["username"].";".$participant["unit_price"].";YES;\n";
				}
			}

			$data .= "\n\n\nNOT PAID YET;\n\n";
			$data .= "Ticket No;Name;Email;Price;Paid;\n";

			if($participants["unpaid"]) {
				foreach($participants["unpaid"] as $participant) {
					$data .= $participant["ticket_no"].";".$participant["nickname"].";".$participant["username"].";".$participant["unit_price"].";NO;\n";
				}
			}

		}

		return $data;
	}



	// Find specific tickets in orders
	function getSoldTickets($item_id) {

		$query = new Query();
		$sql = "SELECT SUM(quantity) as sum FROM ".SITE_DB.".shop_orders as orders, ".SITE_DB.".shop_order_items as items WHERE items.item_id = $item_id AND items.order_id = orders.id AND orders.status != 3";

		$query->sql($sql);
		$count = $query->result(0, "sum");

		return $count ? intval($count) : 0;

	}

	// Find specific tickets in carts
	// TODO: carts with this ticket must expire (could be done in this method)
	function getReservedTickets($item_id) {

		$count = 0;

		$query = new Query();
		$sql = "SELECT items.id, items.quantity, carts.modified_at FROM ".SITE_DB.".shop_carts as carts, ".SITE_DB.".shop_cart_items as items WHERE items.item_id = $item_id AND carts.id = items.cart_id";

		$query->sql($sql);
		$cart_items = $query->results();

		foreach($cart_items as $cart_item) {
			if(strtotime($cart_item["modified_at"]) < strtotime("- 15 MIN")) {

				$sql = "DELETE FROM ".SITE_DB.".shop_cart_items WHERE id = ".$cart_item["id"];
				$query->sql($sql);

			}
			else {

				$count += intval($cart_item["quantity"]);

			}
		}

		return $count;
	}

	function ordered($order_item, $order) {

		// check for subscription error
		if($order && $order["user_id"] && $order_item && $order_item["item_id"]) {

			$item_id = $order_item["item_id"];
			$user_id = $order["user_id"];


			logger()->addLog("ticket->ordered: item_id:$item_id, user_id:$user_id, order_id:".$order["id"].", order_item_id:".$order_item["id"]);


			// Issue ticket(s)
			$ticket = $this->issueTicket($item_id, $user_id, ["order" => $order, "order_item" => $order_item]);

		}

	}

	function orderCancelled($order_item, $order) {
		
	}

	function issueTicket($item_id, $user_id, $_options = false) {

		$order = false;
		$order_item = false;

		$quantity = 1;

		// overwrite defaults
		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "order"           : $order            = $_value; break;
					case "order_item"      : $order_item       = $_value; break;

					case "quantity"        : $quantity         = $_value; break;
				}
			}
		}
		

		$query = new Query();
		$query->checkDbExistence($this->db_user_tickets);


		if($user_id && $item_id) {

			// Tickets based on order
			if($order && $order_item) {

				// Use quantity from order
				$quantity = $order_item["quantity"];

				// variables for email
				$total_price = formatPrice(["price" => $order_item["total_price"], "vat" => $order_item["total_vat"], "country" => $order["country"], "currency" => $order["currency"]]);

			}
			// Free ticket
			else {

				$total_price = "0,-";

			}


			// Get item information
			$IC = new Items();
			$item = $IC->getItem(["id" => $item_id, "extend" => true]);
			$message_id = $item["ordered_message_id"];

			$ticket_files = [];
			$ticket_nos = [];


			// Generate ticket
			for($i = 0; $i < $quantity; $i++) {

				// Decide on next ticket number
				$sql = "SELECT ticket_no FROM ".$this->db_user_tickets." WHERE item_id = $item_id ORDER BY id desc LIMIT 1";
				if($query->sql($sql)) {
					preg_match("/\-([0-9]+)$/", $query->result(0, "ticket_no"), $match);
					$issued_ticket_counter = intval($match[1])+1;
				}
				else {
					$issued_ticket_counter = 1;
				}

				// Create ticket number
				$ticket_no = $item_id."-".time()."-".($issued_ticket_counter);
				$ticket_nos[] = $ticket_no;

				// Insert ticket
				$sql = "INSERT INTO ".$this->db_user_tickets." SET user_id = $user_id, item_id = $item_id, ticket_no = '$ticket_no'";
				if($order_item) {
					$sql .= ", order_item_id = ".$order_item["id"];
				}
				$query->sql($sql);

				// Add batch information
				$batch = ($quantity > 1 ? "(".($i+1)."/$quantity)" : "");

				// Collect ticket files
				$ticket_files[] = $this->generateTicket($item_id, $ticket_no, $batch);


				logger()->addLog("ticket->issueTicket: item_id:$item_id, user_id:$user_id, order_item_id:".$order_item["id"].", quantity:".$quantity.", ticket_no:".$ticket_no.($batch ? ", batch:".$batch : ""));

			}


			// Send ticket email
			$model = $IC->typeObject("message");
			$model->sendMessage([
				"item_id" => $message_id, 
				"user_id" => $user_id, 
				"values" => [
					"QUANTITY" => $quantity,
					"PRICE" => $total_price,
					"EVENT_NAME" => $item["name"],
					"TICKET_NO" => implode("<br>", $ticket_nos),
					"MAIL_INFORMATION" => nl2br($item["mail_information"])
				],
				"attachments" => $ticket_files
			]);

			message()->resetMessages();

		}

	}

	// Requested from ticket-layout (printed with wkhtmlto)
	function getTicketInfo($ticket_no) {

		$query = new Query();
		$sql = "SELECT user_id, item_id, order_item_id FROM ".$this->db_user_tickets." WHERE ticket_no = '$ticket_no'";
		// debug([$sql]);

		if($query->sql($sql)) {

			$user_id = $query->result(0, "user_id");
			$item_id = $query->result(0, "item_id");
			$order_item_id = $query->result(0, "order_item_id");

			$ticket_info = [];
			$IC = new Items();
			$ticket_info["item"] = $IC->getItem(["id" => $item_id, "extend" => true]);

			$UC = new User();
			$ticket_info["user"] = $UC->getUserInfo(["user_id" => $user_id]);

			if($order_item_id) {
				// $SC = new Shop();

				// TODO: Not pretty – but problems with re-issuing tickets
				include_once("classes/shop/supershop.class.php");
				$SC = new SuperShop();

				$sql = "SELECT * FROM ".$SC->db_order_items." WHERE id = '$order_item_id'";
				if($query->sql($sql)) {
					$order_item = $query->result(0);

					$order = $SC->getOrders(["order_id" => $order_item["order_id"]]);
					if($order) {
						$ticket_info["price"] = formatPrice(["price" => $order_item["unit_price"], "vat" => $order_item["unit_vat"], "country" => $order["country"], "currency" => $order["currency"]]);
						return $ticket_info;
					}
				}

			}
			else if($ticket_info["item"] && $ticket_info["name"]) {
				return $ticket_info;
			}

		}

		return false;
	}

	function generateTicket($item_id, $ticket_no, $batch = false) {

		if(is_string($ticket_no)) {

			// prepare print request url
			$url = SITE_URL."/tickets/print/$ticket_no".($batch ? "?batch=".urlencode($batch) : "");
			// debug([$url]);

			// prepare save path
			$ticket_file = PRIVATE_FILE_PATH."/$item_id/ticket/$ticket_no/pdf";
			$public_ticket_file = PUBLIC_FILE_PATH."/$item_id/$ticket_no/$ticket_no.pdf";

			$fs = new FileSystem();
			$fs->makeDirRecursively(dirname($ticket_file));

			include_once("classes/helpers/pdf.class.php");

			$pdf = new PDF();
			$pdf->create($url, $ticket_file, ["format" => "A5", "delay" => 1000, "cookie" => ["name" => "PHPSESSID", "value" => $_COOKIE["PHPSESSID"]]]);

			$fs = new FileSystem();
			$fs->copy($ticket_file, $public_ticket_file);

			return $public_ticket_file;
		}

	}

	function reIssueTicket($action) {

		// debug([$action]);
		if(count($action) == 2) {

			$ticket_no = $action[1];

			$query = new Query();

			// Look for ticket
			$sql = "SELECT user_id, item_id, order_item_id FROM ".$this->db_user_tickets." WHERE ticket_no = '$ticket_no' LIMIT 1";
			// debug([$sql]);
			if($query->sql($sql)) {

				$ticket = $query->result(0);


				$sql = "SELECT * FROM ".SITE_DB.".shop_order_items WHERE id = ".$ticket["order_item_id"]." LIMIT 1";
				if($query->sql($sql)) {

					$order_item = $query->result(0);

					include_once("classes/shop/supershop.class.php");
					$SC = new SuperShop();

					$order = $SC->getOrders(["order_id" => $order_item["order_id"]]);

					// variables for email
					$total_price = formatPrice(["price" => $order_item["total_price"], "vat" => $order_item["total_vat"], "country" => $order["country"], "currency" => $order["currency"]]);


					// Get item information
					$IC = new Items();
					$item = $IC->getItem(["id" => $ticket["item_id"], "extend" => true]);
					$message_id = $item["ordered_message_id"];

					$ticket_files = [];
					$ticket_nos = [];

					$sql = "SELECT id FROM ".SITE_DB.".shop_order_items WHERE order_id = ".$order_item["order_id"];
					if($query->sql($sql)) {
						$total_order_items = $query->results();

						// Batch information
						if(count($total_order_items) > 1) {

							foreach($total_order_items as $key => $total_order_item) {
								if($total_order_item["id"] === $ticket["order_item_id"]) {
									$batch = "(".($key+1)."/".count($total_order_items).")";
									break;
								}
							}

						}
						else {

							$batch = "";

						}

					}

					// Delete old tickets
					$fs = new FileSystem();
					$fs->removeDirRecursively(PRIVATE_FILE_PATH."/".$ticket["item_id"]."/ticket/$ticket_no");
					$fs->removeDirRecursively(PUBLIC_FILE_PATH."/".$ticket["item_id"]."/$ticket_no");
					

					// Collect ticket files
					$ticket_files[] = $this->generateTicket($ticket["item_id"], $ticket_no, $batch);
					$ticket_nos[] = $ticket_no;


					logger()->addLog("ticket->reIssueTicket: item_id:".$ticket["item_id"].", user_id:".$ticket["user_id"].", order_item_id:".$order_item["id"].", quantity:".count($total_order_items).", ticket_no:".$ticket_no.($batch ? ", batch:".$batch : ""));


					// Send ticket email
					$model = $IC->typeObject("message");
					$model->sendMessage([
						"item_id" => $message_id, 
						"user_id" => $ticket["user_id"],
						// "user_id" => 2,
						"values" => [
							"QUANTITY" => count($total_order_items),
							"PRICE" => $total_price,
							"EVENT_NAME" => $item["name"],
							"TICKET_NO" => implode("<br>", $ticket_nos),
							"MAIL_INFORMATION" => nl2br($item["mail_information"])
						],
						"attachments" => $ticket_files
					]);


					message()->addMessage("Ticket has been re-sent to Customer");

					return true;

				}

			}

		}

		message()->addMessage("Ticket could not be re-issued", ["type" => "error"]);

		return false;

	}


	function refundTicket() {
		
	}


	function getEditors($_options) {
		

		$item_id = false;

		// overwrite defaults
		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "item_id"           : $item_id            = $_value; break;
				}
			}
		}

		$query = new Query();
		$query->checkDbExistence($this->db_editors);

		$UC = new User();

		// get location by id
		if($item_id) {

			$sql = "SELECT editors.id, users.id as user_id, users.nickname FROM ".$this->db_editors." as editors, ".$UC->db." as users WHERE editors.item_id = $item_id AND editors.user_id = users.id";
			// debug([$sql]);
			if($query->sql($sql)) {
				return $query->results();
			}

		}

		return false;

	}

	function addEditor($action) {
		
		// Get posted values to make them available for models
		$this->getPostedEntities();

		if(count($action) == 2 && $this->validateList(array("item_editor"))) {


			$item_id = $action[1];
			$query = new Query();
			$UC = new User();

			// make sure type tables exist
			$query->checkDbExistence($this->db_editors);

			$item_editor = $this->getProperty("item_editor", "value");
			
			if(!$query->sql("SELECT id FROM ".$this->db_editors." WHERE user_id = $item_editor AND item_id = $item_id")) {
				$sql = "INSERT INTO ".$this->db_editors." SET user_id = $item_editor, item_id = $item_id";
				// debug([$sql]);

				if($query->sql($sql)) {
					message()->addMessage("Editor added");

					$editor_id = $query->lastInsertId();
					$user = $UC->getUserInfo(["user_id" => $item_editor]);
					return [
						"id" => $editor_id,
						"user_id" => $item_editor,
						"nickname" => $user["nickname"],
					];
				}
			}
			else {
				message()->addMessage("Editor already exists");
				return true;
			}

		}

		message()->addMessage("Editor could not be added", array("type" => "error"));
		return false;

	}

	// Remove editor
	// /janitor/admin/event/removeEditor
	function removeEditor($action) {

		if(count($action) == 1) {

			$editor_id = getPost("editor_id");
			$query = new Query();

			$sql = "DELETE FROM $this->db_editors WHERE id = ".$editor_id;
			// debug([$sql]);

			if($query->sql($sql)) {
				message()->addMessage("Editor removed");
				return true;
			}

		}

		return false;
	}



	function getTicketEvent($_options) {
		

		$item_id = false;

		// overwrite defaults
		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "item_id"           : $item_id            = $_value; break;
				}
			}
		}

		$query = new Query();
		$IC = new Items();

		$event_model = $IC->typeObject("event");
		$query->checkDbExistence($event_model->db_tickets);

		// get location by id
		if($item_id) {

			$sql = "SELECT event_id FROM ".$event_model->db_tickets." WHERE ticket_id = $item_id LIMIT 1";
			if($query->sql($sql)) {
				$event_id = $query->result(0, "event_id");

				return $IC->getItem(["id" => $event_id, "extend" => true]);
			}

		}

		return false;

	}

	function addTicketEvent($action) {
		
		// Get posted values to make them available for models
		$this->getPostedEntities();

		if(count($action) == 2 && $this->validateList(array("item_event"))) {


			$item_id = $action[1];
			$query = new Query();
			$IC = new Items();

			// make sure type tables exist
			$event_model = $IC->typeObject("event");
			$query->checkDbExistence($event_model->db_tickets);

			$item_event_id = $this->getProperty("item_event", "value");
			$event = $IC->getItem(["id" => $item_event_id, "extend" => true]);
			
			if(!$query->sql("SELECT id FROM ".$event_model->db_tickets." WHERE ticket_id = $item_id")) {
				$sql = "INSERT INTO ".$event_model->db_tickets." SET event_id = $item_event_id, ticket_id = $item_id";
			}
			else {
				$sql = "UPDATE ".$event_model->db_tickets." SET event_id = $item_event_id WHERE ticket_id = $item_id";
			}

			// debug([$sql]);
			if($query->sql($sql)) {
				message()->addMessage("Event added");

				return [
					"event_id" => $event["item_id"],
					"event_name" => $event["name"].' ('.date("Y-m-d", strtotime($event["starting_at"])).')',
				];
			}

		}

		message()->addMessage("Event could not be added", array("type" => "error"));
		return false;

	}

	// Remove editor
	// /janitor/admin/event/removeEditor
	function removeTicketEvent($action) {

		if(count($action) == 2) {

			$ticket_id = $action[1];
			$event_id = getPost("event_id");

			$query = new Query();
			$IC = new Items();

			$event_model = $IC->typeObject("event");
			$query->checkDbExistence($event_model->db_tickets);

			$sql = "DELETE FROM $event_model->db_tickets WHERE event_id = $event_id AND ticket_id = $ticket_id";
			// debug([$sql]);

			if($query->sql($sql)) {
				message()->addMessage("Event removed");
				return true;
			}

		}

		return false;
	}


}

?>