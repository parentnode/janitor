// default new form
Util.Objects["usernames"] = new function() {
	this.init = function(div) {

		u.bug("div usernames")
		var form = u.qs("form", div);
		u.f.init(form);

		form.submitted = function(iN) {

			this.response = function(response) {
				page.notify(response.cms_message);
			}
			u.request(this, this.action, {"method":"post", "params" : u.f.getParams(this)});

		}

	}
}

// default new form
Util.Objects["password"] = new function() {
	this.init = function(div) {

		var form = u.qs("form", div);
		u.f.init(form);

		form.submitted = function(iN) {

			this.response = function(response) {
				page.notify(response.cms_message);
			}
			u.request(this, this.action, {"method":"post", "params" : u.f.getParams(this)});

		}

	}
}


// default new form
Util.Objects["formAddressNew"] = new function() {
	this.init = function(form) {

		u.f.init(form);

		form.actions["cancel"].clicked = function(event) {
			location.href = this.url;
		}

		form.submitted = function(iN) {

			this.response = function(response) {
				if(response.cms_status == "success" && response.cms_object) {


//					alert(response);
					location.href = this.actions["cancel"].url.replace("\/list", "/edit/"+response.cms_object.item_id);
				}
				else if(response.cms_message) {
					page.notify(response.cms_message);
				}
			}
			u.request(this, this.action, {"method":"post", "params" : u.f.getParams(this)});

		}

	}
}