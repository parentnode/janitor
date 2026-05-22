Util.Modules["navigationNodes"] = new function() {
	this.init = function(div) {

		div.list = u.qs("ul.items", div);

		if(div.list) {

			div.list.update_order_url = div.getAttribute("data-item-order");
			div.list.csrf_token = div.getAttribute("data-csrf-token");
			div.list.navigation_id = div.getAttribute("data-navigation-id");

			div.list.nodes = u.qsa("li.item", div.list);


			var i, node;
			for(i = 0; node = div.list.nodes[i]; i++) {

				node.list = div.list;

				// delete button
				node.bn_delete = u.qs("li.delete", node);
				if(node.bn_delete) {

					node.bn_delete.node = node;
					// callback from oneButtonForm
					node.bn_delete.confirmed = function(response) {
						this.node.parentNode.removeChild(this.node);

						// update
						this.node.list.updateNodeStructure();
					}

					// disable delete buttons for nodes with children
					var child_nodes = u.qs("ul.items li.item", node);
					var bn_delete_input =  u.qs("ul.actions li.delete input[type=submit]", node);
					if(child_nodes && bn_delete_input) {
						u.ac(bn_delete_input, "disabled");
					}

				}
			}

			// node is dropped
			div.list.dropped = function(event) {
//				u.bug("dropped")

				this.updateNodeStructure();
			}


			// save structure and update button states
			div.list.updateNodeStructure = function() {
				// u.bug("updateNodeStructure");

				var structure = this.getNodeRelations();
				// u.bug(structure);

				this.response = function(response) {
					page.notify(response);
				}
				u.request(this, this.update_order_url, {
					"method":"post", 
					"data":"csrf-token="+this.csrf_token+"&navigation_id="+this.navigation_id+"&structure="+JSON.stringify(structure)});


				var i, node;
				this.nodes = u.qsa("li.item", this);
				for(i = 0; node = this.nodes[i]; i++) {

					// disable delete buttons for nodes with children
					var child_nodes = u.qs("ul.items li.item", node);
					var bn_delete_input =  u.qs("ul.actions li.delete input[type=submit]", node);
					if(child_nodes && bn_delete_input) {
						u.ac(bn_delete_input, "disabled");
					}
					else {
						u.rc(bn_delete_input, "disabled");
					}

				}
			}

			u.sortable(div.list, {"allow_nesting":true, "targets":".items", "draggables":".draggable"});

		}

	}
}

// default new form
Util.Modules["newNavigationNode"] = new function() {
	this.init = function(div) {

		// primary form
		var form = u.qs("form", div);
		form.div = div;


		div.form = form;
		div.validate_link_action = div.getAttribute("data-validate-link");


		u.f.init(form);

		form.submitted = function(iN) {

			this.response = function(response) {
				if(response.cms_status == "success" && response.cms_object) {
					location.href = this.actions["cancel"].url;
				}
				else {
					page.notify(response);
				}
			}
			u.request(this, this.action, {"method":"post", "data" : this.getData({"format":"formdata"})});

		}

		form.changed = function(iN) {

			u.t.resetTimer(this.div.t_validate);
			this.div.t_validate = u.t.setTimer(this.div, this.div.validateLink, 1000);

		}

		div.validateLink = function() {
			// u.bug("validateLink");

			this.response = function(response) {
				// u.bug(response);

				this.form.inputs["node_link"].custom_error = false;

				if(response && response.cms_object) {
					if(response.cms_object.status === "error") {
						page.notify(response);
						this.form.inputs["node_link"].custom_error = true;
					}
					else if(response.cms_object.status === "warning") {
						page.notify(response);
					}
				}
				u.f.validate(this.form.inputs["node_link"]);

			}
			u.request(this, this.validate_link_action, {
				"method": "post",
				"data": this.form.getData(),
			});

		}

	}
}


Util.Modules["editNavigationNode"] = new function() {
	this.init = function(div) {

		// primary form
		var form = u.qs("form", div);
		form.div = div;


		div.form = form;
		div.validate_link_action = div.getAttribute("data-validate-link");



		u.f.init(form);
		form.submitted = function(iN) {

			// stop autosave (this could be a manual save)
			u.t.resetTimer(page.t_autosave);
			u.t.resetTimer(this.div.t_validate);

			this.response = function(response) {
				page.notify(response);
			}
			u.request(this, this.action, {"method":"post", "data" : this.getData({"format":"formdata"})});

		}

		form.changed = function(iN) {

			u.t.resetTimer(this.div.t_validate);
			this.div.t_validate = u.t.setTimer(this.div, this.div.validateLink, 1000);

		}

		div.validateLink = function() {
			// u.bug("validateLink");

			this.response = function(response) {
				// u.bug(response);

				this.form.inputs["node_link"].custom_error = false;

				if(response && response.cms_object) {
					if(response.cms_object.status === "error") {
						page.notify(response);
						this.form.inputs["node_link"].custom_error = true;
					}
					else if(response.cms_object.status === "warning") {
						page.notify(response);
					}
				}
				u.f.validate(this.form.inputs["node_link"]);

			}
			u.request(this, this.validate_link_action, {
				"method": "post",
				"data": this.form.getData(),
			});

		}

		// kill backspace to avoid leaving page unintended (backspace is history.back)
		form.cancelBackspace = function(event) {
//			u.bug("ss:" + u.qsa(".field.focus", this).length);
			if(event.keyCode == 8 && !u.qsa(".field.focus").length) {
				u.e.kill(event);
			}
		}
		u.e.addEvent(document.body, "keydown", form.cancelBackspace);

	}
}