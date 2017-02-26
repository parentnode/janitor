// default new form
Util.Objects["usernames"] = new function() {
	this.init = function(div) {

//		u.bug("div usernames")
		var form;

		form = u.qs("form.email", div);
		u.f.init(form);

		form.updated = function() {
			u.ac(this.actions["save"], "primary");
		}
		form.submitted = function(iN) {

			this.response = function(response) {
				page.notify(response);

				if(response.cms_status == "error") {
					u.f.fieldError(this.fields["email"]);
				}
				else {
					u.rc(this.actions["save"], "primary");
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

// userNewsletters subscribe+unsubscribe form
Util.Objects["newsletters"] = new function() {
	this.init = function(div) {

		var i, node;
		div.newsletters = u.qsa("ul.newsletters > li", div);
		for(i = 0; node = div.newsletters[i]; i++) {

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



// unconfirmedAccounts form
Util.Objects["unconfirmedAccounts"] = new function() {
	this.init = function(div) {


		var i, node;
		// nodes are already available from defaultList
		for(i = 0; node = div.nodes[i]; i++) {

			node.bn_remind = u.qs("ul.actions li.remind", node);
			node.bn_remind.node = node;

			node.bn_remind.reminded = function(response) {

				if(this.parentNode) {
					this.parentNode.removeChild(this);
				}
				if(response.cms_status == "success") {
					var reminded_at = u.qs("dd.reminded_at", this.node);
					var total_reminders = u.qs("dd.total_reminders", this.node);

					reminded_at.innerHTML = response.cms_object[0]["reminded_at"] + " (just now)";
					u.ac(reminded_at, "warning");
					total_reminders.innerHTML = response.cms_object[0]["total_reminders"];
					u.ac(total_reminders, "warning");
				}
				else {
					page.notify({"cms_status":"error", "cms_message":{"error":["Could not send message"]}, "isJSON":true});
				}

			}

		}

	}

}

// unconfirmedAccountsAll
Util.Objects["unconfirmedAccountsAll"] = new function() {
	this.init = function(ul) {

		var bn_remind_all = u.qs("li.remind", ul);
		bn_remind_all.reminded = function(response) {

			if(response.cms_status == "success") {

				for(i = 0; obj = response.cms_object[i]; i++) {

					node = u.ge("id:" + obj.user_id);
//					console.log(node);
					node.bn_remind.reminded({"cms_status":"success", "cms_object":[obj]});

				}

			}

		}

	}
}