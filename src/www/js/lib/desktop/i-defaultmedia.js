// Add images form
Util.Objects["addMedia"] = new function() {
	this.init = function(div) {

		div.form = u.qs("form.upload", div);
		div.form.div = div;
		div.media_list = u.qs("ul.media", div);

		div.item_id = u.cv(div, "item_id");


		u.f.init(div.form);


		div.csrf_token = div.form.fields["csrf-token"].val();
		div.delete_url = div.getAttribute("data-delete-media");



		div.form.file_input = u.qs("input[type=file]", div.form);
		div.form.file_input.div = div;
		div.form.file_input.changed = function() {
			this.form._submit();
		}

		div.form.submitted = function() {

			u.ac(this.file_input.field, "loading");
			u.rc(this.file_input.field, "focus");

			var form_data = new FormData(this);
			this.response = function(response) {
				page.notify(response);

				// inject/update image if everything went well
				if(response.cms_status == "success" && response.cms_object) {

					var i, media, li, image;
					for(i = 0; media = response.cms_object[i]; i++) {
						var li = u.ae(div.media_list, "li");
						u.ac(li, "media image");
						u.ac(li, "variant:"+media.variant);
						u.ac(li, "media_id:"+media.media_id);
						var image = u.ae(li, "img");
						image.src = "/images/"+media.item_id+"/"+media.variant+"/x"+li.offsetHeight+"."+media.format+"?"+u.randomString(4);
						this.div.addDeleteForm(li);
					}

					if(this.div.save_order_url) {
						u.sortable(this.div.media_list);
					}
				}

				u.rc(this.file_input.field, "loading");
				this.file_input.val("");
			}
			u.request(this, this.action, {"method":"post", "params":form_data});
		}


		// add delete form
		div.addDeleteForm = function(li) {

			var delete_form = u.f.addForm(li, {"action":this.delete_url+"/"+this.item_id+"/"+u.cv(li, "variant"), "class":"delete"});
			delete_form.li = li;
			u.ae(delete_form, "input", {"type":"hidden", "name":"csrf-token", "value":this.csrf_token});

			var bn_delete = u.f.addAction(delete_form, {"class":"button delete"});

			delete_form.deleted = function() {
				this.li.parentNode.removeChild(this.li);
			}
			u.o.deleteMedia.init(delete_form);
		}


		// image list exists?
		if(!div.media_list) {
			u.ae(div, "ul", {"class":"media"});
		}

		div.media_list.nodes = u.qsa("li.media", div.media_list);
		div.media_list.div = div;

		// inject delete forms in existing media list
		var i, node;
		for(i = 0; node = div.media_list.nodes[i]; i++) {
			div.addDeleteForm(node);
		}

		// sortable list
		if(u.hc(div, "sortable") && div.media_list) {

			div.save_order_url = div.getAttribute("data-save-order");
			if(div.save_order_url) {
				u.sortable(div.media_list);
				div.media_list.picked = function() {}
				div.media_list.dropped = function() {
					var order = new Array();
					this.nodes = u.qsa("li.media", this);
					for(i = 0; node = this.nodes[i]; i++) {
						order.push(u.cv(node, "media_id"));
					}
					this.response = function(response) {
						// Notify of event
						page.notify(response);
					}
					u.request(this, this.div.save_order_url, {"method":"post", "params":"csrf-token=" + this.div.csrf_token + "&order=" + order.join(",")});
				}
			}
			else {
				u.rc(div, "sortable");
			}
		}



	}
}

// default delete form
Util.Objects["deleteMedia"] = new function() {
	this.init = function(form) {
//		u.bug("deleteMedia init:" + u.nodeId(form));

		u.f.init(form);

		var bn_delete = u.qs("input.delete", form);
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
						page.notify(response);

						if(response.cms_status == "success") {
							// check for constraint error preventing row from actually being deleted
							if(response.cms_object && response.cms_object.constraint_error) {
								this.value = this.org_value;
								u.ac(this, "disabled");
							}
							else {
								// look for callback method on form
								if(typeof(this.form.deleted) == "function") {
									this.form.deleted();
								}
								else {
									location.reload();
								}
							}
						}
						else {
							this.restore();
						}
					}
					u.request(this, this.form.action, {"method":"post", "params" : u.f.getParams(this.form)});
				}
			}
		}

	}
}



// Add images form
Util.Objects["addMediaSingle"] = new function() {
	this.init = function(div) {

		div.form = u.qs("form.upload", div);
		div.form.div = div;
		div.image = u.qs("img", div);

		div.item_id = u.cv(div, "item_id");
		div.media_variant = u.cv(div, "variant");


		u.f.init(div.form);


		div.csrf_token = div.form.fields["csrf-token"].val();
		div.delete_url = div.getAttribute("data-delete-media");


		div.form.file_input = u.qs("input[type=file]", div.form);
		div.form.file_input.div = div;
		div.form.file_input.changed = function() {
			this.form._submit();
		}

		div.form.submitted = function() {

			u.ac(this.file_input.field, "loading");
			u.rc(this.file_input.field, "focus");

			if(this.div.image) {
				u.as(this.div.image, "display", "none");
			}

			var form_data = new FormData(this);
			this.response = function(response) {
				page.notify(response);

				// hide existing image while waiting for response
				if(this.div.image) {
					u.as(this.div.image, "display", "block");
				}

				// inject/update image if everything went well
				if(response.cms_status == "success" && response.cms_object) {
					if(!this.div.image) {
						this.div.image = u.ae(this.div, "img");
						this.div.addDeleteForm();
					}

					this.div.image.src = "/images/"+response.cms_object.item_id+"/"+response.cms_object.variant+"/x"+this.div.image.offsetHeight+"."+response.cms_object.format+"?"+u.randomString(4);
				}

				u.rc(this.file_input.field, "loading");
				this.file_input.val("");
			}
			u.request(this, this.action, {"method":"post", "params":form_data});
		}

		// add delete form
		div.addDeleteForm = function() {

			this.delete_form = u.f.addForm(this, {"action":this.delete_url+"/"+this.item_id+"/"+this.media_variant, "class":"delete"});
			this.delete_form.div = this;
			u.ae(this.delete_form, "input", {"type":"hidden", "name":"csrf-token", "value":this.csrf_token});
			this.bn_delete = u.f.addAction(this.delete_form, {"class":"button delete"});

			this.delete_form.deleted = function() {
				this.div.image.parentNode.removeChild(this.div.image);
				this.div.image = false;
				this.parentNode.removeChild(this);
			}
			u.o.deleteMedia.init(this.delete_form);
		}

		// add initial delete form if image exists
		if(div.image) {
			div.addDeleteForm();
		}

	}
}