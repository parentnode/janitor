u.notifier = function(node) {
	
	
	var notifications = u.qs("div.notifications", node);
	if(!notifications) {
		node.notifications = u.ae(node, "div", {"id":"notifications"});
	}

	node.notifications.hide = function() {

		this.transitioned = function() {
			u.a.transition(this, "none");
		}
		u.a.transition(this, "all 0.5s ease-in-out");
		u.a.translate(this, 0, -this.offsetHeight);
	}
	
	node.notify = function(message, _options) {


//		u.bug("message:" + message+","+ message.message[0])

		var class_name = "message";

		// additional info passed to function as JSON object
		if(typeof(_options) == "object") {
			var argument;
			for(argument in _options) {

				switch(argument) {
					case "class"	: class_name	= _options[argument]; break;
				}

			}
		}

		var output;

		u.bug("message:" + typeof(message) + "; " + message);

		// TODO: message can be JSON object
		if(typeof(message) == "object") {
			for(type in message) {
				u.bug("typeof(message[type]:" + typeof(message[type]) + "; " + type);
				if(typeof(message[type]) == "string") {
					output = u.ae(this.notifications, "div", {"class":class_name, "html":message[type]});
				}
				else if(typeof(message[type]) == "object" && message[type].length) {
					var node, i;
					for(i = 0; _message = message[type][i]; i++) {
						output = u.ae(this.notifications, "div", {"class":class_name, "html":_message});
					}
					
				}
			}
			
		}
		else if(typeof(message) == "string") {
			output = u.ae(this.notifications, "div", {"class":class_name, "html":message});
		}

		u.t.setTimer(this.notifications, this.notifications.hide, 3500);

		// if(message) {
		// 	message.hide = function() {
		// 		this.transitioned = function() {
		// 			u.a.transition(this, "none");
		// 			u.as(this, "display", "none");
		// 		}
		// 		u.a.transition(this, "all 0.5s ease-in-out");
		// 		u.a.setOpacity(this, 0);
		// 	}
		// 	u.t.setTimer(message, message.hide, 2000);
		// }

	}


}
