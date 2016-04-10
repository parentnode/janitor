// PROFILE UPDATES DO NOT RETURN SERVER MESSAGES
// AS THESE ARE THOUGHT TO BE IMPLEMENTED IN FRONTEND

Util.Objects["editProfile"] = new function() {
	this.init = function(div) {

		div._item_id = u.cv(div, "item_id");

		// primary form
		var form = u.qs("form", div);
		form.div = div;

		u.f.init(form);
		form.submitted = function(iN) {

			this.response = function(response) {

				if(response.cms_status == "success") {
					response.cms_message = ["Profile updated"];
				}
				else {
					response.cms_message = ["Profile could not be updated"];
				}

				page.notify(response);
			}
			u.request(this, this.action, {"method":"post", "params" : u.f.getParams(this, {"send_as":"formdata"})});

		}

	}
}

// default new form
Util.Objects["usernamesProfile"] = new function() {
	this.init = function(div) {
		u.bug("init usernamesProfile")

		var form;

		form = u.qs("form.email", div);
		u.f.init(form);

		form.updated = function() {
			u.ac(this.actions["save"], "primary");
		}

		form.submitted = function(iN) {

			this.response = function(response) {

				if(response.cms_object && response.cms_object.status == "USER_EXISTS") {
					page.notify({"isJSON":true, "cms_status":"error", "cms_message":["Email already exists"]});
					u.f.fieldError(this.fields["email"]);
				}
				else if(response.cms_status == "error") {
					page.notify({"isJSON":true, "cms_status":"error", "cms_message":["Email could not be updated"]});
					u.f.fieldError(this.fields["email"]);
				}
				else {
					u.rc(this.actions["save"], "primary");

					page.notify({"isJSON":true, "cms_status":"success", "cms_message":["Email updated"]});
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

				if(response.cms_object && response.cms_object.status == "USER_EXISTS") {
					page.notify({"isJSON":true, "cms_status":"error", "cms_message":["Mobile already exists"]});
					u.f.fieldError(this.fields["mobile"]);
				}
				else if(response.cms_status == "error") {
					page.notify({"isJSON":true, "cms_status":"error", "cms_message":["Mobile could not be updated"]});
					u.f.fieldError(this.fields["mobile"]);
				}
				else {
					u.rc(this.actions["save"], "primary");

					page.notify({"isJSON":true, "cms_status":"success", "cms_message":["Mobile updated"]});
				}
			}
			u.request(this, this.action, {"method":"post", "params" : u.f.getParams(this)});
		}

	}
}

// default new form
Util.Objects["passwordProfile"] = new function() {
	this.init = function(div) {

		var password_state = u.qs("div.password_state", div);
		var new_password = u.qs("div.new_password", div);
		var a_change = u.qs(".password_set a");

		a_change._new_password = new_password;
		a_change._password_state = password_state;

		u.ce(a_change);
		a_change.clicked = function() {
			u.as(this._password_state, "display", "none");
			u.as(this._new_password, "display", "block");
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

					page.notify({"isJSON":true, "cms_status":"success", "cms_message":"Password updated"});
				}
				else {
					page.notify({"isJSON":true, "cms_status":"error", "cms_message":"Password could not be updated"});
				}

			}
			u.request(this, this.action, {"method":"post", "params" : u.f.getParams(this)});
			this.fields["new_password"].val("");
			this.fields["old_password"].val("");

		}

	}
}


// default new form
Util.Objects["apitokenProfile"] = new function() {
	this.init = function(div) {

		var token = u.qs("p.token", div);

		var form = u.qs("form", div);
		if(form) {
			form._token = token;

			u.f.init(form);

			form.submitted = function(iN) {

				this.response = function(response) {
					if(response.cms_status == "success") {
						this._token.innerHTML = response.cms_object;

						page.notify({"isJSON":true, "cms_status":"success", "cms_message":"API token updated"});
					}
					else {
						page.notify({"isJSON":true, "cms_status":"error", "cms_message":"API token could not be updated"});
					}

				}
				u.request(this, this.action, {"method":"post", "params" : u.f.getParams(this)});

			}
		}

	}
}

// Update address
Util.Objects["addressProfile"] = new function() {
	this.init = function(form) {

		u.f.init(form);

		form.actions["cancel"].clicked = function(event) {
			location.href = this.url;
		}

		form.submitted = function(iN) {

			this.response = function(response) {
				if(response.cms_status == "success") {
					location.href = this.actions["cancel"].url;
				}
				else {
					page.notify({"isJSON":true, "cms_status":"error", "cms_message":"Address could not be updated"});
				}
			}
			u.request(this, this.action, {"method":"post", "params" : u.f.getParams(this)});

		}
	}
}

// userNewsletters unsubscribe form
Util.Objects["newslettersProfile"] = new function() {
	this.init = function(div) {

		var i, node;
		div.newsletters = u.qsa("ul.newsletters > li", div);
		for(i = 0; node = div.newsletters[i]; i++) {

			node.li_delete = u.qs("li.delete", node);
			node.li_subscribe = u.qs("li.subscribe", node);

			// init if form is available
			if(node.li_delete) {

				// look for form
				node.li_delete.form = u.qs("form", node.li_delete)

				u.f.init(node.li_delete.form);
				node.li_delete.form.node = node;

				node.li_delete.form.restore = function(event) {
					this.actions["delete"].value = "Unsubscribe";
					u.rc(this.actions["delete"], "confirm");
				}

				node.li_delete.form.submitted = function() {

					// first click
					if(!u.hc(this.actions["delete"], "confirm")) {
						u.ac(this.actions["delete"], "confirm");
						this.actions["delete"].value = "Confirm";
						this.t_confirm = u.t.setTimer(this, this.restore, 3000);
					}
					// confirm click
					else {
						u.t.resetTimer(this.t_confirm);

						this.response = function(response) {


							if(response.cms_status == "success") {
								page.notify({"isJSON":true, "cms_status":"success", "cms_message":"Unsubscribed from newsletter"});
								u.rc(this.node, "subscribed");
							}
							else {
								page.notify({"isJSON":true, "cms_status":"error", "cms_message":"Could not unsubscribe"});
							}
							this.restore();

						}
						u.request(this, this.action, {"method":"post", "params":u.f.getParams(this)});
					}
				}
			}


			// init if form is available
			if(node.li_subscribe) {

				// look for form
				node.li_subscribe.form = u.qs("form", node.li_subscribe)

				u.f.init(node.li_subscribe.form);
				node.li_subscribe.form.node = node;

				node.li_subscribe.form.submitted = function() {


					this.response = function(response) {

						// show message
						page.notify(response);

						if(response.cms_status == "success") {
							u.ac(this.node, "subscribed");
							page.notify({"isJSON":true, "cms_status":"success", "cms_message":"Subscribed to newsletter"});
							//this.node.parentNode.removeChild(this.node);
						}
						else {
							page.notify({"isJSON":true, "cms_status":"error", "cms_message":"Could not subscribe to newsletter"});
						}
					}
					u.request(this, this.action, {"method":"post", "params":u.f.getParams(this)});

				}
			}
		}
	}
}
