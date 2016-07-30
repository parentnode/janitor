
// New order
Util.Objects["newOrder"] = new function() {
	this.init = function(form) {

		u.f.init(form);

		u.bug("init")

		form.fields["user_id"].changed = function() {
			location.href = location.href.replace(/new.+/, "new") + "/" + this.val();
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
					location.href = location.href.replace(/new.+/, "edit") + "/" + response.cms_object["id"];
				}
			}
			u.request(this, this.action, {"method":"post", "params" : u.f.getParams(this)});

		}
	}
}

// edit Order 
Util.Objects["editOrder"] = new function() {
	this.init = function(form) {

		var header = u.qs("h2", form.parentNode);

		var action = u.ae(header, "span", {"html":"edit"});
		
		action.change_form = form;
		u.ce(action);


		u.f.init(form);


		action.clicked = function(event) {

			if(this.change_form.is_open) {
				this.change_form.is_open = false;
				this.innerHTML = "Edit";
				this.change_form.reset();
				u.ass(this.change_form, {
					"display":"none"
				})
			}
			else {
				this.change_form.is_open = true;
				this.innerHTML = "Cancel";
				u.ass(this.change_form, {
					"display":"block"
				})
				u.f.init(this.change_form);
			}
		}


		form.submitted = function() {

			this.response = function(response) {
				page.notify(response);

				if(response && response.cms_status == "success") {
					location.reload(true);
				}
			}
			
			u.request(this, this.action, {"method":"post", "params":u.f.getParams(this)});
		}


	}
}

// edit Order item
Util.Objects["editOrderItem"] = new function() {
	this.init = function(div) {

		var form = u.qs("form", div);

		u.f.init(form);

		if(form.actions["cancel"]) {
			form.actions["cancel"].clicked = function(event) {
				location.href = this.url;
			}
		}

		form.submitted = function() {

			this.response = function(response) {
				page.notify(response);

				if(response && response.cms_status == "success") {
					this.actions["cancel"].clicked();
				}
			}
			
			u.request(this, this.action, {"method":"post", "params":u.f.getParams(this)});
		}


	}
}