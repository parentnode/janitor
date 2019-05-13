<?php
/**
* @package janitor.itemtypes
* This file contains itemtype functionality
*/

class TypeMessage extends Itemtype {

	/**
	* Init, set varnames, validation rules
	*/
	function __construct() {

		// construct ItemType before adding to model
		parent::__construct(get_class());


		// itemtype database
		$this->db = SITE_DB.".item_message";

		$this->layouts_framework_path = FRAMEWORK_PATH."/templates/mails/layouts";
		$this->layouts_local_path = LOCAL_PATH."/templates/mails/layouts";


		// Published
		$this->addToModel("published_at", array(
			"hint_message" => "Publishing date of the message. Leave empty for current time",
		));

		// Name
		$this->addToModel("name", array(
			"type" => "string",
			"label" => "Subject",
			"required" => true,
			"hint_message" => "Subject of your message", 
			"error_message" => "Name must be filled out."
		));

		// description
		$this->addToModel("description", array(
			"type" => "text",
			"label" => "Short \"preview text\"",
			"hint_message" => "Write a short teaser text for the message preview pane.",
			"error_message" => "A short description without any words? How weird."
		));

		// HTML
		$this->addToModel("html", array(
			"hint_message" => "Write the message",
			"allowed_tags" => "p,h2,h3,h4,ul,ol,jpg,png", //,code,mp4,vimeo,youtube",
		));


		// Recipient(s)
		$this->addToModel("recipients", array(
			"type" => "string",
			"label" => "Recipient(s)",
			"pattern" => "([,;]?[\w\.\-_\+]+@[\w\-\.]+\.\w{2,10})+",
			"hint_message" => "Comma separate multiple recipients", 
			"error_message" => "Recipients or Maillist must be filled out."
		));
		// Maillist
		$this->addToModel("maillist_id", array(
			"type" => "integer",
			"label" => "Maillist",
			"hint_message" => "Choose a list of recipients", 
			"error_message" => "Recipients or Maillist must be filled out."
		));


		// template
		$this->addToModel("template", array(
			"type" => "string",
			"label" => "Standard mail",
			"hint_message" => "Select a standard mail", 
			"error_message" => "Invalid template"
		));


		// layout
		$this->addToModel("layout", array(
			"type" => "string",
			"label" => "Layout",
			"required" => true,
			"hint_message" => "Choose a layout for your mail", 
			"error_message" => "Layout must be selected."
		));


	}

	// User initiated send custom message mail
	function userSendMessage($action) {

		// Get posted values to make them available for models
		$this->getPostedEntities();

		if($this->validateList(array("item_id"))) {

			// $query = new Query();
			// $query->checkDbExistence(SITE_DB.".user_log_messages");


			$recipients = $this->getProperty("recipients", "value");
			$maillist_id = $this->getProperty("maillist_id", "value");
			$item_id = $this->getProperty("item_id", "value");


			$user_id = $this->getProperty("user_id", "value");



			return $this->sendMessage([
				"maillist_id" => $maillist_id,
				"recipients" => $recipients,
				"item_id" => $item_id,
				"from_current_user" => true,

				"values" => ["martin@think.dk" => ["NICKNAME" => "howski snowski"]],
				"user_id" => $user_id,
			]);

//
//
// 			// for value placeholders found in mail HTML
// 			$needed_values = [];
//
// 			// item_id of mailcontent and layout available
// 			if($item_id && $layout) {
//
// 				// create final HTML
// 				$html = $this->mergeItemIntoLayout($item_id, $layout);
//
// //				print "##\n".$html."\n##";
//
// 				// find variables in content
// 				preg_match_all("/\{([a-zA-Z0-9\-_]+)\}/", $html, $matches);
// 				foreach($matches[1] as $match) {
// 					if(array_search($match, $needed_values) === false) {
// 						$needed_values[] = $match;
// 					}
//
// 				}
//
// 			}
//
//
// 			$recipients = [];
// 			$recipient_values = [];
//
// 			// recipients sent as comma-separated list
// 			if($recipients_list) {
//
// 				// filter out invalid recipients
// 				$recipients_list = explode(";", preg_replace("/,/", ";", $recipients_list));
// 				foreach($recipients_list as $key => $recipient) {
// 					// if recipient seems valid, add it (and values) to the arrays that will be passed on
// 					if($recipient && preg_match("/^[\w\.\-_]+@[\w\-\.]+\.\w{2,10}$/", $recipient)) {
// 						$recipients[] = $recipient;
// 					}
//
// 					// there is no user-mapping, when using comma-separated list
// 					// so don't try to add user values
//
// 				}
//
// 			}
// 			// get recipients from maillist id
// 			else if($maillist_id) {
//
// 				include_once("classes/users/superuser.class.php");
// 				$UC = new SuperUser();
//
// 				// get all subscribers for selected maillist
// 				$subscribers = $UC->getMaillists(["maillist_id" => $maillist_id]);
// 				foreach($subscribers as $subscriber) {
//
// 					// User values placeholder
// 					$user_values = [];
//
// 					// Add message key to user values (to create massage viewer on website)
// 					$user_values["MESSAGE_TOKEN"] = randomKey(30);
// 					$user_values["MAILLIST_ID"] = $maillist_id;
//
//
// 					// Some userdata is readily available from subscriber
// 					if(array_search("NICKNAME", $needed_values) !== false) {
// 						$user_values["NICKNAME"] = $subscriber["nickname"];
// 					}
// 					if(array_search("EMAIL", $needed_values) !== false) {
// 						$user_values["EMAIL"] = $subscriber["email"];
// 					}
// 					if(array_search("USERNAME", $needed_values) !== false) {
// 						$user_values["USERNAME"] = $subscriber["email"];
// 					}
//
//
// 					// Check if we need to look up additional user info
//
// 					// USER DATA
// 					if(preg_match("/FIRSTNAME|LASTNAME|LANGUAGE/", implode(",", $needed_values))) {
// 						$user = $UC->getUsers(["user_id" => $subscriber["user_id"]]);
//
// 						if(array_search("FIRSTNAME", $needed_values) !== false) {
// 							$user_values["FIRSTNAME"] = $user && $user["firstname"] ? $user["firstname"] : "";
// 						}
//
// 						if(array_search("LASTNAME", $needed_values) !== false) {
// 							$user_values["LASTNAME"] = $user && $user["lastname"] ? $user["lastname"] : "";
// 						}
//
// 						if(array_search("LANGUAGE", $needed_values) !== false) {
// 							$user_values["LANGUAGE"] = $user && $user["language"] ? $user["language"] : "EN";
// 						}
//
// 					}
//
// 					// USER VERIFICATION CODE (FOR UNSUBSCRIBE LINK)
// 					if(preg_match("/VERIFICATION_CODE/", implode(",", $needed_values))) {
// 						$user_values["VERIFICATION_CODE"] = $UC->getVerificationCode("email", $subscriber["email"]);
// 					}
//
// 					// MEMBERSHIP DATA
// 					if(defined("SITE_MEMBERS") && SITE_MEMBERS && preg_match("/MEMBER_ID|MEMBERSHIP/", implode(",", $needed_values))) {
// 						$member = $UC->getMembers(["user_id" => $subscriber["user_id"]]);
//
// 						if(array_search("MEMBER_ID", $needed_values) !== false) {
// 							$user_values["MEMBER_ID"] = $member && $member["id"] ? $member["id"] : "N/A";
// 						}
//
// 						if(array_search("MEMBERSHIP", $needed_values) !== false) {
// 							$user_values["MEMBERSHIP"] = $member && $member["item"] && $member["item"]["name"] ? $member["item"]["name"] : "N/A";
// 						}
//
// 					}
//
// 					$recipients[] = $subscriber["email"];
// 					$recipient_values[$subscriber["email"]] = $user_values;
//
//
// 					// Insert data into messages log to enable "view in browser"
// 					$data = $user_values;
// 					unset($data["MESSAGE_TOKEN"]);
// 					// Save data object with message token in user_log_messages
// 					$sql = "INSERT INTO ".SITE_DB.".user_log_messages SET user_id = ".$subscriber["user_id"].", item_id = $item_id, token = '".$user_values["MESSAGE_TOKEN"]."', data = '".prepareForDB(json_encode($data))."'";
// //					print $sql;
// 					$query->sql($sql);
//
// 				}
//
// 			}
//
//
// 			// valid recipients and html
// 			if($html && $recipients) {
//
// 				if(mailer()->sendBulk(["recipients" => $recipients, "values" => $recipient_values, "html" => $html, "tracking" => false])) {
//
// 					global $page;
// 					$page->addLog("TypeMessage->sendMessage: user_id:".session()->value("user_id").", item_id:".$item_id.", " . ($maillist_id ? "maillist_id:".$maillist_id : "recipients:".implode(";", $recipients)));
//
// 					message()->addMessage("Mail(s) sent to ".implode(",", $recipients));
//
// 					return $recipients;
//
// 				}
// 				// message failed
// 				else {
//
// 					// remove user messages log entries, since mail wasn't sent anyway
// 					foreach($recipient_values as $values) {
//
// 						$sql = "DELETE FROM ".SITE_DB.".user_log_messages WHERE token = '".$values["MESSAGE_TOKEN"]."'";
// 				//		print $sql;
// 						$query->sql($sql);
//
// 					}
//
// 				}
//
// 			}

		}

		message()->addMessage("Mail(s) could not be sent", ["type" => "error"]);
		return false;
	}


	// User initiated send system template mail
	function userSendSystemMessage($action) {

		// Get posted values to make them available for models
		$this->getPostedEntities();

		$temp_recipients = $this->getProperty("recipients", "value");
		$template = $this->getProperty("template", "value");
		
		$values = getPost("values");

		// remap recipients and values (and filter out invalid )
		$temp_recipients = explode(";", preg_replace("/,/", ";", $temp_recipients));

		$recipients = [];
		$recipient_values = [];

		foreach($temp_recipients as $key => $recipient) {
			// if recipient seems valid, add it (and values) to the arrays that will be passed on
			if($recipient && preg_match("/^[\w\.\-_\+]+@[\w\-\.]+\.\w{2,10}$/", $recipient)) {
				$recipients[] = $recipient;
				$recipient_values[$recipient] = isset($values[$key]) ? $values[$key] : [];
			}
		}

		if($recipients) {
			if(mailer()->sendBulk(["recipients" => $recipients, "values" => $recipient_values, "template" => $template])) {

				message()->addMessage("Mail(s) sent to ".implode(",", $recipients));
				return $recipients;
			}
		}

		message()->addMessage("Mail(s) could not be sent", ["type" => "error"]);
		return false;
	}


	// Merge item html into layout html
	function mergeMessageIntoLayout($message) {

//		$IC = new Items();
		$html = "";


		// $item = $IC->getItem(["id" => $item_id, "extend" => true]);
		// if($item) {
		if($message && $message["layout"]) {
			$layout_content = $this->getLayoutContent($message["layout"]);

			$DC = DOM();
			$dom_layout = $DC->createDOM($layout_content);

			// Update title with name from message
			$DC->innerHTML($DC->getElement($dom_layout, "title"), $message["name"]);

			// get placerholder for preview (expecting exactly one element to exist)
			$preview_placeholder = $DC->getElement($dom_layout, "div.preview");
			if($preview_placeholder) {
				// insert preview text
				$DC->innerHTML($preview_placeholder, $message["description"]);
			}


			// get placerholder for mail content (expecting exactly one element to exist)
			$content_placeholder = $DC->getElement($dom_layout, "td.mailcontent");
			if($content_placeholder) {


				// get template attribute reference nodes for content tags (h1-h4, p, ul, ol, a, div.image)
				// with ID or optional classnames
				while($content_placeholder->childNodes->length) {
					$node = $content_placeholder->firstChild;

					// ignore all text nodes
					if($node->nodeName != "#text") {

						// create full tag identifier
						$tag_name = strtolower($node->nodeName);
						$ref_nodes[$tag_name . ($node->getAttribute("id") ? "#".$node->getAttribute("id") : "") . ($node->getAttribute("class") ? ".".implode(".", explode(" ", $node->getAttribute("class"))) : "")] = $node;

						// get relevant sub nodes for media and lists
						if($node->nodeName == "div" && $DC->hasClassname($node, "media")) {
							$node->img_node = $DC->getElement($node, "img");
							$node->caption_node = $DC->getElement($node, "p");
						}
						if($node->nodeName == "ul" || $node->nodeName == "ol") {
							$node->li_node = $DC->getElement($node, "li");
						}

					}

					// remove ref node from mail layout
					$content_placeholder->removeChild($node);

				}


				// Create dom from message content
				$dom_content = $DC->createDOM($message["html"]);
				$dom_content_body = $DC->getElement($dom_content, "body");
				if($dom_content_body) {

					// update and inject message content into mail layout
					foreach($dom_content_body->childNodes as $node) {

						// ignore all text nodes
						if($node->nodeName != "#text") {
//							print "LOOK FOR:" .$node->nodeName."<br>\n";

							$tag = strtolower($node->nodeName);
							$tag_id = ($node->getAttribute("id") ? $node->getAttribute("id") : "");
							$tag_classnames = ($node->getAttribute("class") ? explode(" ", $node->getAttribute("class")) : "");
							$tag_identifier = $tag.($tag_id ? "#".$tag_id : "").($tag_classnames ? ".".implode(".", $tag_classnames) : "");


							// if ref node doesn't exist, try to find best match
							if(!isset($ref_nodes[$tag_identifier])) {

								// count matching classnames
								$best_class_match = 0;

								// loop through ref nodes to look for best match
								foreach($ref_nodes as $identifier => $ref_node) {

									// matches tag and id 
									// (would need 10 classmatches to override, so we assume this is a safe match)
									if(strtolower($ref_node->nodeName) == $tag && $tag_id && $tag_id == $ref_node->getAttribute("id")) {

										$tag_identifier = $identifier;

									}
									// matches tag
									else if(strtolower($ref_node->nodeName) == $tag) {

										// compare classname matches
										$matching_classnames = 0;
										foreach($tag_classnames as $classname) {

											// ref node has tag
											if(preg_match("/\.".$classname."(\.|$)/", $identifier)) {
												$matching_classnames++;
											}

										}

										// did we find a better match?
										if($matching_classnames > $best_class_match) {
											$best_class_match = $matching_classnames;
											$tag_identifier = $identifier;
										}

									}

								}

								// if still not found, use the plain tag if it exists
								if(!isset($ref_nodes[$tag_identifier]) && isset($ref_nodes[$tag])) {
									$tag_identifier = $tag;
								}

							}


							// merge attributes if ref_node was found
							if(isset($ref_nodes[$tag_identifier]) && $ref_nodes[$tag_identifier]) {
//								print "FOUND:" . $tag_identifier . "::".$tag.($tag_id ? "#".$tag_id : "").($tag_classnames ? ".".implode(".", $tag_classnames) : "")."<br>\n";


								// special handling of images
								if($tag == "div" && $DC->hasClassname($node, "media")) {
									// image details
									$item_id = $DC->classVar($node, "item_id");
									$format = $DC->classVar($node, "format");
									$variant = $DC->classVar($node, "variant");

									// caption options
									$name = $DC->classVar($node, "name");
									$node->caption_node = $DC->getElement($node, "p");
									$caption = trim($node->caption_node->textContent);

									// import img-tag from template HTML
									$node->img_node = $dom_content->importNode($ref_nodes[$tag_identifier]->img_node, true);
									$node->insertBefore($node->img_node, $node->caption_node);

									// set image attributes
									$node->img_node->setAttribute("src", SITE_URL."/images/$item_id/$variant/600x.$format");
//									$node->img_node->setAttribute("src", "https://think.dk/images/472/single_media/600x.jpg");
 									$node->img_node->setAttribute("alt", $caption ? $caption : $name);

									// set caption
									$node->caption_node = $DC->getElement($node, "p");
									if($caption) {
										$DC->innerHTML($node->caption_node, $caption);

										// transfer caption attributes
										foreach($ref_nodes[$tag_identifier]->caption_node->attributes as $attribute) {
											$node->caption_node->setAttribute($attribute->name, $attribute->value);
										}
									}
									// or remove empty caption
									else {
										$node->removeChild($node->caption_node);
									}

									// transfer div.media attributes
									foreach($ref_nodes[$tag_identifier]->attributes as $attribute) {
										$node->setAttribute($attribute->name, $attribute->value);
									}

								}
								// special handling of lists
								else if($tag == "ul" || $tag == "ol") {

									// set each available attribute on node
									foreach($ref_nodes[$tag_identifier]->attributes as $attribute) {
										$node->setAttribute($attribute->name, $attribute->value);
									}

									// set attributes for li-elements
									$li_tags = $DC->getElements($node, "li");
									foreach($li_tags as $li_tag) {
										foreach($ref_nodes[$tag_identifier]->li_node->attributes as $attribute) {
											$li_tag->setAttribute($attribute->name, $attribute->value);
										}
									}

								}
								// plain content tag
								else {

									// set each available attribute on node
									foreach($ref_nodes[$tag_identifier]->attributes as $attribute) {
										$node->setAttribute($attribute->name, $attribute->value);
									}

								}

							}
							// if ref node could not be found, then just copy node as it is
							// else {
							// 	print "NOT FOUND:" . $tag_identifier . "::".$tag.($tag_id ? "#".$tag_id : "").($tag_classnames ? ".".implode(".", $tag_classnames) : "")."<br>\n";
							// }


							// update all links in node
							if($ref_nodes["a"]) {
								$a_tags = $DC->getElements($node, "a");
								foreach($a_tags as $a_tag) {
									foreach($ref_nodes["a"]->attributes as $attribute) {
										if($attribute->name != "href") {
											$a_tag->setAttribute($attribute->name, $attribute->value);
										}
									}
									// append domain to href if it's missing
									$href = $a_tag->getAttribute("href");
									if(preg_match("/^\//", $href)) {
										$a_tag->setAttribute("href", SITE_URL.$href);
									}
								}
							}


							// Import node to layout dom (cannot append without importing first)
							$node = $dom_layout->importNode($node, true);

							// append updated tag to mail layout
							$content_placeholder->appendChild($node);

						}

					}

				}

			}

			// get HTML from updated DOM
			$html = $DC->saveHTML($dom_layout);	
		}


		return $html;

	}

	// Find full local/framework layout path, based on stated name (local prioritized over framework)
	function getLayoutContent($layout) {

		$layout_path = false;

		if(file_exists($this->layouts_local_path."/".$layout)) {
			$layout_path = $this->layouts_local_path."/".$layout;
		}
		else if(file_exists($this->layouts_framework_path."/".$layout)) {
			$layout_path = $this->layouts_framework_path."/".$layout;
		}

		if($layout_path) {
			return file_get_contents($layout_path);
		}

		return false;
	}

	// get all available layouts with template names (based on subject/title tag)
	function getLayouts() {

		$fs = new FileSystem();

		$layouts_local = $fs->files($this->layouts_local_path, ["allow_extensions" => "html"]);
		$layouts_framework = $fs->files($this->layouts_framework_path, ["allow_extensions" => "html"]);

		$layout_full_paths = [];
		$layout_names = [];
		$layouts = [];


		// make combined list of local and framework layouts
		foreach($layouts_local as $layout) {
			$layout_full_paths[] = $layout;
			// keep track of layout names to avoid including framework layout if local exists
			$layout_names[] = basename($layout);
		}
		foreach($layouts_framework as $layout) {

			// only include framework layouts if they don't exists locally
			if(array_search(basename($layout), $layout_names) === false) {
				$layout_full_paths[] = $layout;
			}
		}


		foreach($layout_full_paths as $layout) {
			$layout_content = file_get_contents($layout);

			// look for subject in html template
			if(preg_match("/<title>([^$]+)<\/title>/", $layout_content, $subject_match)) {
				$subject = $subject_match[1];
			}
			// look for subject in text template
			else if(preg_match("/^SUBJECT\:([^\n]+)\n/", $layout_content, $subject_match)) {
				$subject = $subject_match[1];
			}

			$layout_details = [];

			// framework path
			if(strpos($layout, $this->layouts_framework_path) !== false) {
				$layout_details = [
					"type" => "framework",
					"subject" => $subject . " (Framework default)",
					"name" => str_replace($this->layouts_framework_path."/", "", $layout)
				];
			}
			// local path
			else {
				$layout_details = [
					"type" => "local",
					"subject" => $subject,
					"name" => str_replace($this->layouts_local_path."/", "", $layout)
				];
			}

			$layouts[] = $layout_details;
		}

		return $layouts;

	}


	// Send message to user, maillist or recipient list
	function sendMessage($_options) {

		// print "sendMessage";
		// print_r($_options);

		// Recipients
		$maillist_id = false;
		$user_id = false;

		// we'll do some extra recipient checking before making the final recipients list
		$temp_recipients = false;
		$recipients = [];


		// Message item id
		$item_id = false;

		$values = [];
		$from_current_user = false;


		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "recipients"             : $temp_recipients        = $_value; break;
					case "maillist_id"            : $maillist_id            = $_value; break;
					case "user_id"                : $user_id                = $_value; break;

					case "item_id"                : $item_id                = $_value; break;

					case "from_current_user"      : $from_current_user      = $_value; break;
					case "values"                 : $values                 = $_value; break;

				}
			}
		}


		if($item_id && ($maillist_id || $user_id || $temp_recipients)) {
			
			$query = new Query();
			$query->checkDbExistence(SITE_DB.".user_log_messages");

			$IC = new Items();
			$message = $IC->getItem(["id" => $item_id, "extend" => true]);

			if($message) {

				// print_r($message);

				// for value placeholders found in mail HTML
				$needed_values = [];
				$recipient_values = [];

				// item_id of mailcontent and layout available
//				if($item_id && $layout) {

				// create final HTML
				$html = $this->mergeMessageIntoLayout($message);

	//				print "##\n".$html."\n##";

				// find variables in content
				preg_match_all("/\{([a-zA-Z0-9\-_]+)\}/", $html, $matches);
				foreach($matches[1] as $match) {
					if(array_search($match, $needed_values) === false) {
						$needed_values[] = $match;
					}

				}

//				}


//				$recipients = [];
				// map any passed values to recipient_values array
//				$recipient_values = $values;

				// recipients sent as comma-separated list
				if($temp_recipients) {

					// filter out invalid recipients
					$recipients_list = explode(";", preg_replace("/,/", ";", $temp_recipients));
					foreach($recipients_list as $key => $recipient) {
						// if recipient seems valid, add it (and values) to the arrays that will be passed on
						if($recipient && preg_match("/^[\w\.\-_\+]+@[\w\-\.]+\.\w{2,10}$/", $recipient)) {
							$recipients[] = $recipient;

							if($values) {
								if(isset($values[$recipient])) {
									$recipient_values[$recipient] = $values[$recipient];
								}
								else if(!is_array($values[reset($values)])) {
									$recipient_values[$recipient] = $values;
								}

							}
						}

						// there is no user-mapping, when using comma-separated list
						// so don't try to add user values

					}
					// Values for recipients could potentially be passed to method

				}
				// get recipients from maillist_id or user_id
				else if($maillist_id || $user_id) {

					include_once("classes/users/superuser.class.php");
					$UC = new SuperUser();

					// get recipients from maillist_id
					if($maillist_id) {

						// get all subscribers for selected maillist
						$subscribers = $UC->getMaillists(["maillist_id" => $maillist_id]);

					}
					// get recipient from user_id
					else {

						// Create subscriber array for user_id
						$temp_user = $UC->getUsers(["user_id" => $user_id]);
						$user["user_id"] = $user_id;
						$user["nickname"] = $temp_user["nickname"];

						$username_email = $UC->getUsernames(["user_id" => $member["user"]["id"], "type" => "email"]);
						if ($username_email) {
							$user["email"] = $username_email["username"]; 
						}
						else {
							$user["email"] = "Not available";
						}
						
						$subscribers[] = $user;
					}

					// print_r($subscribers);

					// get all subscribers for selected maillist
//					$subscribers = $UC->getMaillists(["maillist_id" => $maillist_id]);
					foreach($subscribers as $subscriber) {

						// Prepare for recipient values
						$recipient_values[$subscriber["email"]] = [];

						// Temp user values placeholder
						$user_values = [];

						// Add message key to user values (to create massage viewer on website)
						$user_values["MESSAGE_TOKEN"] = randomKey(30);
						// Only set maillist id if message was sent to maillist
						$user_values["MAILLIST_ID"] = $maillist_id ? $maillist_id : 0;


						// Some userdata is readily available from subscriber
						if(array_search("NICKNAME", $needed_values) !== false) {
							$user_values["NICKNAME"] = $subscriber["nickname"];
						}
						if(array_search("EMAIL", $needed_values) !== false) {
							$user_values["EMAIL"] = $subscriber["email"];
						}
						if(array_search("USERNAME", $needed_values) !== false) {
							$user_values["USERNAME"] = $subscriber["email"];
						}


						// Check if we need to look up additional user info

						// USER DATA
						if(preg_match("/FIRSTNAME|LASTNAME|LANGUAGE/", implode(",", $needed_values))) {
							$user = $UC->getUsers(["user_id" => $subscriber["user_id"]]);

							if(array_search("FIRSTNAME", $needed_values) !== false) {
								$user_values["FIRSTNAME"] = $user && $user["firstname"] ? $user["firstname"] : "";
							}

							if(array_search("LASTNAME", $needed_values) !== false) {
								$user_values["LASTNAME"] = $user && $user["lastname"] ? $user["lastname"] : "";
							}

							if(array_search("LANGUAGE", $needed_values) !== false) {
								$user_values["LANGUAGE"] = $user && $user["language"] ? $user["language"] : "EN";
							}

						}

						// USER VERIFICATION CODE (FOR UNSUBSCRIBE LINK)
						if(preg_match("/VERIFICATION_CODE/", implode(",", $needed_values))) {
							$user_values["VERIFICATION_CODE"] = $UC->getVerificationCode("email", $subscriber["email"]);
						}

						// MEMBERSHIP DATA
						if(defined("SITE_MEMBERS") && SITE_MEMBERS && preg_match("/MEMBER_ID|MEMBERSHIP|MEMBERSHIP_PRICE|ORDER_NO/", implode(",", $needed_values))) {
							$member = $UC->getMembers(["user_id" => $subscriber["user_id"]]);

							if(array_search("MEMBER_ID", $needed_values) !== false) {
								$user_values["MEMBER_ID"] = $member && $member["id"] ? $member["id"] : "N/A";
							}


							if(array_search("ORDER_NO", $needed_values) !== false) {
								if($member && $member["order"] && $member["order"]["order_no"]) {
									$user_values["ORDER_NO"] = $member["order"]["order_no"];
								}
								else {
									$user_values["ORDER_NO"] = "";
								}

							}




							if(array_search("MEMBERSHIP_PRICE", $needed_values) !== false) {

								$SC = new Shop();

								if($member && $member["item"] && $member["item"]["item_id"]) {
									$price = $SC->getPrice($member["item"]["item_id"]);
									$user_values["MEMBERSHIP_PRICE"] = formatPrice($price);
								}
								else {
									$user_values["MEMBERSHIP_PRICE"] = formatPrice(["price" => 0]);
								}

							}

							if(array_search("MEMBERSHIP", $needed_values) !== false) {
								$user_values["MEMBERSHIP"] = $member && $member["item"] && $member["item"]["name"] ? $member["item"]["name"] : "N/A";
							}

						}

						$recipients[] = $subscriber["email"];

						// Values were also passed directly for this user
						// Merge specific user values and let passed specific values take priority
						if(isset($values[$subscriber["email"]])) {
							$recipient_values[$subscriber["email"]] = array_merge($user_values, $values[$subscriber["email"]]);
						}
						// Merge specific user values and let passed general values take priority
						// Make sure $values only contain one set of data (look at content of first element)
						else if($values && reset($values) && !is_array($values[key($values)])) {
							$recipient_values[$subscriber["email"]] = array_merge($user_values, $values);
						}
						// no passed values matching
						else {
							$recipient_values[$subscriber["email"]] = $user_values;
						}

						// Insert data into messages log to enable "view in browser"
						$data = $recipient_values[$subscriber["email"]];
						unset($data["MESSAGE_TOKEN"]);
						// Save data object with message token in user_log_messages
						$sql = "INSERT INTO ".SITE_DB.".user_log_messages SET user_id = ".$subscriber["user_id"].", item_id = $item_id, token = '".$user_values["MESSAGE_TOKEN"]."', data = '".prepareForDB(json_encode($data))."'";
		//					print $sql;
						$query->sql($sql);

					}

				}


				// valid recipients and html
				if($html && $recipients) {

					// print_r($recipients);
					// print_r($recipient_values);

					if(mailer()->sendBulk(["recipients" => $recipients, "values" => $recipient_values, "subject" => $message["name"], "html" => $html, "tracking" => true])) {

						global $page;
						$page->addLog("TypeMessage->sendMessage: user_id:".session()->value("user_id").", item_id:".$item_id.", " . ($maillist_id ? "maillist_id:".$maillist_id : "recipients:".implode(";", $recipients)));

						message()->addMessage("Mail(s) sent to ".implode(",", $recipients));
						return $recipients;

					}
					// message failed
					else {

						// remove user messages log entries, since mail wasn't sent anyway
						foreach($recipient_values as $values) {

							$sql = "DELETE FROM ".SITE_DB.".user_log_messages WHERE token = '".$values["MESSAGE_TOKEN"]."'";
					//		print $sql;
							$query->sql($sql);

						}

					}

				}

			}

		}

	}

}

?>