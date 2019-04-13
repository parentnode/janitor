// default new form
Util.Objects["usernames"] = new function() {
	this.init = function(div) {

		// u.bug("div usernames")
		
		var form;
		
		form = u.qs("form.email", div);
		u.f.init(form);
		
		var send_verification_link = u.qs("li.send_verification_link", div);
		send_verification_link.form = u.qs("form", send_verification_link);
		send_verification_link.input = send_verification_link.form.lastChild;

		form.fields.email.saved_email = form.fields.email.val();
		form.fields.verification_status.saved_status = form.fields.verification_status.val();
		form.fields.verification_status.current_status = form.fields.verification_status.val();

		var latest_verification_link = u.qs("div.email .send_verification_link p.reminded_at", div);
		latest_verification_link.date_time = u.qs("span.date_time", latest_verification_link);
		u.bug('Latest verification link on page load', latest_verification_link.date_time.textContent);
		if(u.hc(latest_verification_link.date_time, "never")) {
			u.ass(latest_verification_link, {"display":"none"});
		}

		// check verification status and disable/enable verification checkbox and 'verification link' button accordingly
		if (!form.fields.email.saved_email) {
			form.fields.verification_status.disabled = true;
			u.ac(send_verification_link.input, "disabled");
		}
		else if( form.fields["verification_status"].val()) {
			u.ac(send_verification_link.input, "disabled");
		}
		else {
			form.fields.verification_status.disabled = false;
			u.rc(send_verification_link.input, "disabled");
		}

		form.fields.email.updated = function() {
			if(this.val() != this.saved_email) {
				this._form.fields.verification_status.val(0);
			
				u.ac(this._form.actions["save"], "primary");
				u.rc(this._form.actions["save"], "disabled");
			
				if(this.val()) {
					this._form.fields.verification_status.disabled = false;
				}
				else {
					this._form.fields.verification_status.disabled = true;
				}

			}
			else {
				this._form.fields.verification_status.val(this._form.fields.verification_status.current_status);
				u.ac(this._form.actions["save"], "disabled");

			}
		}

		form.fields.verification_status.updated = function() {
			if(this.val() != this.saved_status) {		

				this.current_status = this.val();
				// u.bug("current verification status ", this.current_status);
				u.ac(this._form.actions["save"], "primary");
				u.rc(this._form.actions["save"], "disabled");
			}
			else if(this._form.fields.email.val() != this._form.fields.email.saved_email) {
				this.current_status = this.val();
				// u.bug("current verification status ", this.current_status);
				u.ac(this._form.actions["save"], "primary");
				u.rc(this._form.actions["save"], "disabled");
			}
			else {
				u.ac(this._form.actions["save"], "disabled");

			}
		}

		
		send_verification_link.confirmed = function(response) {
			// u.bug('Verification link', send_verification_link);
			if(!latest_verification_link) {
				latest_verification_link = u.qs("div.email .send_verification_link p.reminded_at", div);
				latest_verification_link.date_time = u.qs("span.date_time", latest_verification_link);
			}
			latest_verification_link.date_time.textContent = response.cms_object.reminded_at;
			u.bug('Latest verification link', latest_verification_link.date_time.textContent);

			u.ass(latest_verification_link, {"display":"block"});
			
			// Ensure that reminder is shown when the button has been pressed once
			u.rc(send_verification_link, "invite");
			u.rc(send_verification_link.input, "invite");
			u.ac(send_verification_link, "reminder");
			u.ac(send_verification_link.input, "reminder");
			send_verification_link.input.value = "Send reminder";
			send_verification_link.form[1].value = "signup_reminder";
			
		}

		
		form.submitted = function(iN) {
			if(!latest_verification_link) {
				latest_verification_link = u.qs("div.email .send_verification_link p span.date_time", div);
			}
			this.response = function(response) {
				if(response.cms_status == "error") {
					u.f.fieldError(this.fields["email"]);
				}
				else {
					
					
					this.fields.email.saved_email = this.fields.email.val();
					this.fields.verification_status.saved_status = this.fields.verification_status.val();
					u.ac(this.actions["save"], "disabled");
					
					u.bug("saved email ", this.fields.email.saved_email);
					u.bug("response ", response);
					
					
					if(response.cms_object.email_status == "UPDATED") {
						this.fields.username_id.val(response.cms_object.username_id);
						// u.bug("saved username_id", this.fields.username_id.val());
						
						if(send_verification_link.form.action == "http://janitor.local/janitor/admin/user/sendVerificationLink/") {
							send_verification_link.form.action += this.fields.username_id.val();
						}
						// u.bug("updated action", send_verification_link.form.action);

						if(response.cms_object.verification_status == "VERIFIED") {
							u.ac(send_verification_link.input, "disabled");
							u.rc(this.actions["save"], "primary");
						}
						else if(response.cms_object.verification_status == "NOT_VERIFIED") {
							u.rc(send_verification_link.input, "disabled");
							u.rc(this.actions["save"], "primary");
						}

					}
					else if(response.cms_object.email_status == "UNCHANGED") {
						if(response.cms_object.verification_status == "VERIFIED") {
							u.ac(send_verification_link.input, "disabled");
							u.rc(this.actions["save"], "primary");
						}
						else if(response.cms_object.verification_status == "NOT_VERIFIED") {
							u.rc(send_verification_link.input, "disabled");
							u.rc(this.actions["save"], "primary");
						}

						// delete 'email unchanged' message
						response.cms_message.message.shift();
					}
					// update username to blank
					else {
						// u.bug("Username has been updated to blank", );
						u.ac(send_verification_link.input, "disabled");
						u.rc(this.actions["save"], "primary");
						send_verification_link.form.action = "http://janitor.local/janitor/admin/user/sendVerificationLink/"
						u.ass(latest_verification_link, {"display":"none"});

						u.rc(send_verification_link, "reminder");
						u.ac(send_verification_link, "invite");
						send_verification_link.input.value = "Send invite";
						send_verification_link.form[1].value = "verify_new_email";

					}

					page.notify(response);


				}

			}
			u.request(this, this.action, {"method":"post", "params" : u.f.getParams(this)});
		}

		form = u.qs("form.mobile", div);
		u.f.init(form);

		form.updated = function() {
			u.ac(this.actions["save"], "primary");
		}
		form.submitted = function(iN) {

			this.response = function(response) {
				page.notify(response);

				if(response.cms_status == "error") {
					u.f.fieldError(this.fields["mobile"]);
				}
				else {
					u.rc(this.actions["save"], "primary");
				}
			}
			u.request(this, this.action, {"method":"post", "params" : u.f.getParams(this)});
		}

	}
}

// password form
Util.Objects["password"] = new function() {
	this.init = function(div) {

		var password_state = u.qs("div.password_state", div);
		var new_password = u.qs("div.new_password", div);

		var a_create = u.qs(".password_missing a");
		var a_change = u.qs(".password_set a");

		a_create._new_password = new_password;
		a_change._new_password = new_password;
		a_create._password_state = password_state;
		a_change._password_state = password_state;

		u.ce(a_create);
		u.ce(a_change);
		a_create.clicked = a_change.clicked = function() {
			u.as(this._new_password, "display", "block");
			u.as(this._password_state, "display", "none");
		}

		var form = u.qs("form", div);
		form._password_state = password_state;
		form._new_password = new_password;

		u.f.init(form);

		form.actions["cancel"].clicked = function() {
			u.as(this.form._password_state, "display", "block");
			u.as(this.form._new_password, "display", "none");
		}

		form.submitted = function(iN) {

			this.response = function(response) {
				if(response.cms_status == "success") {
					u.ac(this._password_state, "set");
					u.as(this._password_state, "display", "block");
					u.as(this._new_password, "display", "none");
				}
				page.notify(response);
			}
			u.request(this, this.action, {"method":"post", "params" : u.f.getParams(this)});

			this.reset();

		}

	}
}

// default new form
Util.Objects["apitoken"] = new function() {
	this.init = function(div) {

		var token = u.qs("p.token", div);

		var renew_form = u.qs("form.renew", div);
		var disable_form = u.qs("form.disable", div);

		if(renew_form) {
			renew_form._token = token;

			if(disable_form) {
				renew_form.disable_form = disable_form;
			}

			u.f.init(renew_form);

			renew_form.submitted = function(iN) {

				this.response = function(response) {
					if(response.cms_status == "success") {
						this._token.innerHTML = response.cms_object;
						if(this.disable_form) {
							u.rc(this.disable_form.actions["disable"], "disabled");
						}

						page.notify({"isJSON":true, "cms_status":"success", "cms_message":"API token updated"});
					}
					else {
						page.notify({"isJSON":true, "cms_status":"error", "cms_message":"API token could not be updated"});
					}

				}
				u.request(this, this.action, {"method":"post", "params" : u.f.getParams(this)});

			}
		}

		if(disable_form) {
			disable_form._token = token;

			u.f.init(disable_form);

			disable_form.submitted = function(iN) {

				this.response = function(response) {
					if(response.cms_status == "success") {
						this._token.innerHTML = "N/A";
						u.ac(this.actions["disable"], "disabled");
						page.notify({"isJSON":true, "cms_status":"success", "cms_message":"API token disabled"});
					}
					else {
						page.notify({"isJSON":true, "cms_status":"error", "cms_message":"API token could not be disabled"});
					}

				}
				u.request(this, this.action, {"method":"post", "params" : u.f.getParams(this)});

			}
		}
	}
}

// Update address
Util.Objects["editAddress"] = new function() {
	this.init = function(form) {

		u.f.init(form);

		form.actions["cancel"].clicked = function(event) {
			location.href = this.url;
		}

		form.submitted = function(iN) {

			this.response = function(response) {
				page.notify(response);
				if(response.cms_status == "success") {
					location.href = this.actions["cancel"].url;
				}
			}
			u.request(this, this.action, {"method":"post", "params" : u.f.getParams(this)});

		}

	}

}

// userMaillists subscribe+unsubscribe form
Util.Objects["maillists"] = new function() {
	this.init = function(div) {

		var i, node;
		div.maillists = u.qsa("ul.maillists > li", div);
		for(i = 0; node = div.maillists[i]; i++) {

			node.li_unsubscribe = u.qs("li.unsubscribe", node);
			node.li_subscribe = u.qs("li.subscribe", node);

			// init if form is available
			if(node.li_unsubscribe) {

				node.li_unsubscribe.node = node;
				// callback from oneButtonForm
				node.li_unsubscribe.confirmed = function(response) {

					if(response.cms_status == "success") {
						u.rc(this.node, "subscribed");
					}
				}
			}


			// init if form is available
			if(node.li_subscribe) {


				node.li_subscribe.node = node;
				// callback from oneButtonForm
				node.li_subscribe.confirmed = function(response) {

					if(response.cms_status == "success") {
						u.ac(this.node, "subscribed");
					}

				}

			}

		}

	}

}

// // Update address
// Util.Objects["addNewsletter"] = new function() {
// 	this.init = function(form) {
//
// 		u.f.init(form);
//
// 		form.actions["cancel"].clicked = function(event) {
// 			location.href = this.url;
// 		}
//
// 		form.submitted = function(iN) {
//
// 			this.response = function(response) {
// 				page.notify(response);
// 				if(response.cms_status == "success") {
// 					location.href = this.actions["cancel"].url;
// 				}
// 				else {
// 					page.notify({"isJSON":true, "cms_status":"error", "cms_message":"Address could not be updated"});
// 				}
// 			}
// 			u.request(this, this.action, {"method":"post", "params" : u.f.getParams(this)});
//
// 		}
// 	}
// }

Util.Objects["accessEdit"] = new function() {
	this.init = function(div) {

		div._item_id = u.cv(div, "item_id");

		// primary form
		var form = u.qs("form", div);
		u.f.init(form);
		form.actions["cancel"].clicked = function(event) {
			location.href = this.url;
		}
		form.submitted = function(iN) {

			this.response = function(response) {
				page.notify(response);
			}
			u.request(this, this.action, {"method":"post", "params" : u.f.getParams(this)});

		}

		// enable select all on controller heading
		var i, group;
		var groups = u.qsa("li.action", form);
		for(i = 0; group = groups[i]; i++) {

			var h3 = u.qs("h3", group);
			h3.group = group;
			u.ce(h3)
			h3.clicked = function() {

				var i, input;
				var inputs = u.qsa("input[type=checkbox]", this.group);
				for(i = 0; input = inputs[i]; i++) {
					input.val(1);
				}

			}

		}

	}

}

Util.Objects["flushUserSession"] = new function() {
	this.init = function(div) {

		u.bug("div flushUserSession")

		// CMS interaction urls
		div.csrf_token = div.getAttribute("data-csrf-token");
		div.flush_url = div.getAttribute("data-flush-url");


		var users = u.qsa("li.item:not(.current_user)", div);
		var i, user;
		for(i = 0; user = users[i]; i++) {


			var action = u.f.addAction(u.qs("ul.actions", user), {"type":"button", "class":"button", "value":"Flush"});
			action.div = div;
			action.user_id = u.cv(user, "user_id");
			u.ce(action);
			action.clicked = function() {

				this.response = function(response) {
					page.notify(response);
				}
				u.request(this, this.div.flush_url+"/"+this.user_id, {"method":"post", "params" : "csrf-token="+this.div.csrf_token});
				
			}

		}

	}

}


// Update subscription
Util.Objects["newSubscription"] = new function() {
	this.init = function(form) {

		u.f.init(form);

		u.bug("init")

		form.fields["item_id"].changed = function() {

			location.href = location.href.replace(/new\/([\d]+).+/, "new/$1") + "/" + this.val();
			
		}

		if(form.actions["cancel"]) {
			form.actions["cancel"].clicked = function(event) {
				location.href = this.url;
			}
		}

		form.submitted = function(iN) {

			this.response = function(response) {
				page.notify(response);

				if(response.cms_status == "success") {
					location.href = this.actions["cancel"].url;
				}
			}
			u.request(this, this.action, {"method":"post", "params" : u.f.getParams(this)});

		}

	}

}



// unverifiedUsernames form
Util.Objects["unverifiedUsernames"] = new function() {
	this.init = function(div) {

		var i, node;

		div.bn_remind_selected = u.qs("li.remind_selected");
		
		div.selectionUpdated = function(response) {
			if(response.length > 0) {
				u.rc(this.bn_remind_selected.form[2], "disabled");				
			}
			else {
				u.ac(this.bn_remind_selected.form[2], "disabled");
			}

			this.selected_username_ids = [];
			
			
			for(i = 0; i < response.length; i++) {
				node = response[i].node;
				node.username_id = u.cv(node, "username_id");
				this.selected_username_ids.push(node.username_id);
			}
			
			this.selected_username_ids = this.selected_username_ids.join();
			this.bn_remind_selected.form.fields.selected_username_ids.val(this.selected_username_ids);
		}

		
		// nodes are already available from defaultList
		for(i = 0; node = div.nodes[i]; i++) {
			
			node.bn_remind = u.qs("ul.actions li.remind", node);
			node.bn_remind.node = node;

			node.bn_remind.reminded = function(response) {
				// u.bug('Testing response', response);

				if(this.parentNode) {
					this.parentNode.removeChild(this);
				}
				if(this.node._checkbox.parentNode) {
					this.node._checkbox.parentNode.removeChild(this.node._checkbox);
				}
				if(response.cms_status == "success") {
					var reminded_at = u.qs("dd.reminded_at", this.node);
					var total_reminders = u.qs("dd.total_reminders", this.node);
					
					reminded_at.innerHTML = response.cms_object["reminded_at"] + " (just now)";
					u.ac(reminded_at, "system_warning");
					total_reminders.innerHTML = response.cms_object["total_reminders"];
					u.ac(total_reminders, "system_warning");
				}
				else {
					page.notify({"cms_status":"error", "cms_message":{"error":["Could not send message"]}, "isJSON":true});
				}
				
			}
	
		}

	}
	
}

// unverifiedUsernamesSelected
Util.Objects["unverifiedUsernamesSelected"] = new function() {
	this.init = function(ul) {

		var bn_remind_selected = u.qs("li.remind_selected", ul);
				
		bn_remind_selected.reminded = function(response) {
			
			var obj;
			if(response.cms_status == "success") {
				for(i = 0; i < response.cms_object.length; i++) {
					obj = response.cms_object[i];
					// u.bug('Testing obj', obj); 
					node = u.ge("username_id:" + obj.username_id);
					if(node) {
						node.bn_remind.reminded({"cms_status":"success", "cms_object":obj});
					}
					
				}

			}

		}

	}

}