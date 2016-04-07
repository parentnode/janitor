Util.Objects["defaultEditActions"] = new function() {
	this.init = function(node) {
		u.bug("defaultEditActions:" + u.nodeId(node));

		node._item_id = u.cv(node, "item_id");
		node.csrf_token = node.getAttribute("data-csrf-token");

		var bn_cancel = u.qs("li.cancel a", node);

		// delete button
		var bn_delete = u.qs("li.delete", node);

		if(bn_delete && bn_cancel && bn_cancel.href) {

			// inject standard item delete form if node is empty
			if(!bn_delete.childNodes.length) {
				bn_delete.delete_item_url = bn_delete.getAttribute("data-item-delete");
				if(bn_delete.delete_item_url) {
					form = u.f.addForm(bn_delete, {"action":bn_delete.delete_item_url, "class":"delete"});
					u.ae(form, "input", {"type":"hidden","name":"csrf-token", "value":node.csrf_token});
					form.node = node;
					bn_delete = u.f.addAction(form, {"value":"Delete", "class":"button delete", "name":"delete"});
				}
			}
			// look for valid forms
			else {
				form = u.qs("form", bn_delete);
			}

			// init if form is available
			if(form) {
				u.f.init(form);

				form.cancel_url = bn_cancel.href;

				form.restore = function(event) {
					this.actions["delete"].value = "Delete";
					u.rc(this.actions["delete"], "confirm");
				}

				form.submitted = function() {

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
							page.notify(response);

							if(response.cms_status == "success") {
								// check for constraint error preventing row from actually being deleted
								if(response.cms_object && response.cms_object.constraint_error) {
									this.value = "Delete";
									u.ac(this, "disabled");
								}
								else {
									location.href = this.cancel_url;
									//this.node.parentNode.removeChild(this.node);
								}
							}
						}
						u.request(this, this.action, {"method":"post", "params":u.f.getParams(this)});
					}
				}
			}
		}
		
		// add autosave option

		// bn_autosave = u.ae(node, "li", {"class":"autosave on", "html":"Autosave ON"});
		// u.e.click(bn_autosave);
		// bn_autosave.clicked = function() {
		// 	if(u.hc(this, "on")) {
		//
		// 		u.rc(this, "on");
		// 		page.autosave_disabled = true;
		// 	}
		// 	else {
		//
		// 		u.ac(this, "on");
		// 		page.autosave_disabled = false;
		// 	}
		// 	u.bug("toggle autosave")
		// }

	}
}