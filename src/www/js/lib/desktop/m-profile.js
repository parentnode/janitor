// PROFILE UPDATES DO NOT RETURN SERVER MESSAGES
// AS THESE ARE THOUGHT TO BE IMPLEMENTED IN FRONTEND (FOR FULL LOCALIZATION)

Util.Modules["editProfile"] = new function() {
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
Util.Modules["usernamesProfile"] = new function() {
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
					u.f.inputHasError(this.inputs["email"]);
				}
				else if(response.cms_status == "error") {
					page.notify({"isJSON":true, "cms_status":"error", "cms_message":["Email could not be updated"]});
					u.f.inputHasError(this.inputs["email"]);
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
					u.f.inputHasError(this.inputs["mobile"]);
				}
				else if(response.cms_status == "error") {
					page.notify({"isJSON":true, "cms_status":"error", "cms_message":["Mobile could not be updated"]});
					u.f.inputHasError(this.inputs["mobile"]);
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
Util.Modules["passwordProfile"] = new function() {
	this.init = function(div) {

		var password_state = u.qs("div.password_state", div);
		var new_password = u.qs("div.new_password", div);
		var a_change = u.qs(".password_set a");

		a_change._new_password = new_password;
		a_change._password_state = password_state;

		u.ce(a_change);
		a_change.clicked = function() {
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

					page.notify({"isJSON":true, "cms_status":"success", "cms_message":"Password updated"});
				}
				else {
					page.notify({"isJSON":true, "cms_status":"error", "cms_message":"Password could not be updated"});
				}

			}
			u.request(this, this.action, {"method":"post", "params" : u.f.getParams(this)});

			this.reset();
//			this.inputs["new_password"].val("");
//			this.inputs["old_password"].val("");

		}

	}
}


// default new form
Util.Modules["apitokenProfile"] = new function() {
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
Util.Modules["addressProfile"] = new function() {
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

// userMaillists unsubscribe form
Util.Modules["maillistsProfile"] = new function() {
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
						page.notify({"isJSON":true, "cms_status":"success", "cms_message":"Unsubscribed from maillist"});
						u.rc(this.node, "subscribed");
					}
					else {
						page.notify({"isJSON":true, "cms_status":"error", "cms_message":"Could not unsubscribe"});
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
						page.notify({"isJSON":true, "cms_status":"success", "cms_message":"Subscribed to maillist"});
						//this.node.parentNode.removeChild(this.node);
					}
					else {
						page.notify({"isJSON":true, "cms_status":"error", "cms_message":"Could not subscribe to maillist"});
					}

				}

			}
		}
	}
}

// Update address
Util.Modules["resetPassword"] = new function() {
	this.init = function(form) {

		u.f.init(form);

		form.submitted = function() {

			this.response = function(response) {

				if(response.cms_status == "success") {
					location.href = "/login";
				}
				else {
					page.notify({"isJSON":true, "cms_status":"error", "cms_message":"Password could not be updated"});
				}

			}
			u.request(this, this.action, {"method":"post", "params":u.f.getParams(this)});

		}

	}
}

// Cancel account
Util.Modules["cancellationProfile"] = new function() {
	this.init = function(div) {
		u.bug("init cancellationProfile")

		div.password = u.qs("div.field.password", div);
		div.form = u.qs("form.cancelaccount", div);

		if(div.form) {

			div.form.div = div;


			u.f.init(div.form);

	//		div.form.confirm_submit_button = u.qs("input[type=submit]", div.form);
	//		console.log(div.form.actions["cancelaccount"]);

			div.form.actions["cancelaccount"].org_value = div.form.actions["cancelaccount"].value;
			div.form.actions["cancelaccount"].confirm_value = "Cancelling your account cannot be undone. OK?";
			div.form.actions["cancelaccount"].submit_value = "Confirm";

			div.form.inputs["password"].updated = function() {
				u.bug("typing password")
				u.t.resetTimer(this._form.t_confirm);

			}


			div.form.restore = function(event) {
				u.t.resetTimer(this.t_confirm);

				this.actions["cancelaccount"].value = this.actions["cancelaccount"].org_value;
				u.rc(this.actions["cancelaccount"], "confirm");
				u.rc(this.actions["cancelaccount"], "signup");


				u.ass(this.div.password, {
					"display": "none"
				})
			}


			div.form.actions["cancelaccount"].clicked = function() {

				// first click
				if(!u.hc(this, "confirm")) {
					u.ac(this, "confirm");
					this.value = this.confirm_value;
					this._form.t_confirm = u.t.setTimer(this._form, this._form.restore, 3000);
				}
				// confirm click
				else if(!u.hc(this, "signup")) {
					u.ac(this, "signup");

					u.t.resetTimer(this._form.t_confirm);

					u.ass(this._form.div.password, {
						"display": "block"
					});
					this.value = this.submit_value;



					this._form.t_confirm = u.t.setTimer(this._form, this._form.restore, 5000);
				}
				// final loop - submit cancellation
				else {

					this._form.submit();

				}

			}

			div.form.submitted = function() {


				this.response = function(response) {

					if(response.cms_status == "success" && !response.cms_object.error) {
						// show receipt
						page.notify({"isJSON":true, "cms_status":"success", "cms_message":"Your account has been cancelled"});

						// redirect user to frontpage after 2 sec
						u.t.setTimer(this, function() {location.href = "/";}, 2000);
					}
					else {

						if(response.cms_object.error == "missing_values") {
							page.notify({"isJSON":true, "cms_status":"error", "cms_message":"Some information is missing."});
						}
						else if(response.cms_object.error == "wrong_password") {
							page.notify({"isJSON":true, "cms_status":"error", "cms_message":"The password is not correct."});
						}
						else if(response.cms_object.error == "unpaid_orders") {
							page.notify({"isJSON":true, "cms_status":"error", "cms_message":"You have unpaid orders.."});
						}
						else {
							page.notify({"isJSON":true, "cms_status":"error", "cms_message":"An unknown error occured."});
						}

					}

				}
				u.request(this, this.action, {"method":"post", "params":u.f.getParams(this)});

			}

		}

	}

}
