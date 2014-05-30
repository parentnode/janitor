
// Add prices form
Util.Objects["addPrices"] = new function() {
	this.init = function(div) {

		var form = u.qs("form", div);
		u.f.init(form);

		var i, field, actions;

		// field = form.fields["prices"].field;
		// actions = u.qs(".actions", form);
		// actions = field.insertBefore(actions, u.ns(field._input));
		form.submitted = function(event) {
			this.response = function(response) {
				if(response.cms_status == "success") {
					location.reload();
				}
				else {
					alert(response.cms_message[0]);
				}
			}
			u.request(this, this.action, {"method":"post", "params":u.f.getParams(this)});
		}

	}
}



// Add prices form
Util.Objects["formAddPrices"] = new function() {
	this.init = function(form) {

		u.f.init(form);

		var i, field, actions;

		field = form.fields["prices"].field;
		actions = u.qs(".actions", form);
		actions = field.insertBefore(actions, u.ns(field._input));
		form.submitted = function(event) {
			this.response = function(response) {
				if(response.cms_status == "success") {
					location.reload();
				}
				else {
					alert(response.cms_message[0]);
				}
			}
			u.request(this, this.action, {"method":"post", "params":u.f.getParams(this)});
		}

	}
}

// Add tags form
Util.Objects["formAddTags"] = new function() {
	this.init = function(form) {

		var i, field, actions;

		u.f.init(form);

		// prepare add field
		field = form.fields["tags"].field;
		actions = u.qs(".actions", form);
		actions = field.insertBefore(actions, u.ns(field._input));
		form.submitted = function(event) {
			this.response = function(response) {
				if(response.cms_status == "success") {
					location.reload();
				}
				else {
					alert(response.cms_message[0]);
				}
			}
			u.request(this, this.action, {"method":"post", "params":u.f.getParams(this)});
		}
	}
}


// Add images form
Util.Objects["addMedia"] = new function() {
	this.init = function(div) {

		var form = u.qs("form.upload", div);
		u.f.init(form);

		var file_input = u.qs("input[type=file]", form);
		file_input.changed = function() {

			this.response = function(response) {
				response = JSON.parse(this.responseText);

				if(response.cms_status == "success" && response.cms_object) {
//					alert(response);
					location.reload();
				}
				else if(response.cms_message) {
					page.notify(response.cms_message);
				}

			}
			this.responseError = function(response) {
				response = JSON.parse(this.responseText);

				if(response.cms_status == "success") {
					location.reload();
				}
				else {
					alert(response.cms_message[0]);
				}
			}

			var fd = new FormData();
			var i, file;
			for(i = 0; file = this.files[i]; i++) {
				fd.append(this.name+"["+i+"]", file);
			}

			this.HTTPRequest = u.createRequestObject();
			this.HTTPRequest.node = this;

			u.e.addEvent(this.HTTPRequest, "load", this.response);
			u.e.addEvent(this.HTTPRequest, "error", this.responseError);


			this.HTTPRequest.open("POST", this.form.action);
			this.HTTPRequest.send(fd);
		}

		// image list
		div.media_list = u.qs("ul.media", div.media_list);
		div.mediae = u.qsa("ul.media li", div);


		if(div.mediae) {

			u.bug("media delete")

			// init delete buttons
			var node, i, delete_form, bn_delete;
			for(i = 0; node = div.mediae[i]; i++) {

				delete_form = u.qs("form.delete", node);

				if(delete_form) {

					u.bug("delete_form:" + delete_form)
					u.f.init(delete_form);

					bn_delete = u.qs("input.delete", delete_form);
					if(bn_delete) {

						bn_delete.org_value = bn_delete.value;

						u.e.click(bn_delete);
						bn_delete.restore = function(event) {
							this.value = this.org_value;
							u.rc(this, "confirm");
						}

						bn_delete.inputStarted = function(event) {
							u.e.kill(event);
						}

						bn_delete.clicked = function(event) {
							u.e.kill(event);

							// first click
							if(!u.hc(this, "confirm")) {
								u.ac(this, "confirm");
								this.value = "Confirm";
								this.t_confirm = u.t.setTimer(this, this.restore, 3000);
							}
							// confirm click
							else {
								u.t.resetTimer(this.t_confirm);

								this.response = function(response) {
									if(response.cms_status == "success") {
										// check for constraint error preventing row from actually being deleted
										if(response.cms_object && response.cms_object.constraint_error) {
											page.notify(response.cms_message);
											this.value = this.org_value;
											u.ac(this, "disabled");
										}
										else {
											location.reload();
	//										location.href = this.form.actions["cancel"].url;
										}
									}
									else {
										page.notify(response.cms_message);
									}
								}
								u.request(this, this.form.action, {"method":"post", "params" : u.f.getParams(this.form)});
							}
						}
					}
				}
			}
		}


		// sortable list
		if(u.hc(div, "sortable") && div.media_list) {

			u.s.sortable(div.media_list);
			div.media_list.picked = function() {}
			div.media_list.dropped = function() {
				var url = this.getAttribute("data-save-order");
				this.nodes = u.qsa("li.media", this);
				for(i = 0; node = this.nodes[i]; i++) {
					url += "/"+u.cv(node, "media_id");
				}
				this.response = function(response) {
					// Notify of event
					page.notify(response.cms_message);
				}
				u.request(this, url);
			}

		}



	}
}




