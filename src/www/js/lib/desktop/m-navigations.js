Util.Modules["navigationNodes"] = new function() {
	this.init = function(div) {

		div.list = u.qs("ul.items", div);

		if(div.list) {

			div.list.update_order_url = div.getAttribute("data-item-order");
			div.list.csrf_token = div.getAttribute("data-csrf-token");
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
				u.bug("updateNodeStructure");

				var structure = this.getNodeRelations();

				u.bug(structure);
				this.response = function(response) {
					page.notify(response);
				}
				u.request(this, this.update_order_url, {"method":"post", "data":"csrf-token="+this.csrf_token+"&structure="+JSON.stringify(structure)});


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
	this.init = function(form) {

		u.f.init(form);

		// form.actions["cancel"].clicked = function(event) {
		// 	location.href = this.url;
		// }

		form.submitted = function(iN) {

			this.response = function(response) {
				if(response.cms_status == "success" && response.cms_object) {

					//alert("this.action:" + this.action)
//					alert(response);
					location.href = this.actions["cancel"].url;
//					location.href = this.actions["cancel"].url.replace("\/list", "/edit/"+response.cms_object.item_id);
				}
				else {
					page.notify(response);
				}
			}
//			u.bug("params:"+u.f.getParams(this))
			u.request(this, this.action, {"method":"post", "params" : u.f.getParams(this, {"send_as":"formdata"})});

		}

	}
}


Util.Modules["editNavigationNode"] = new function() {
	this.init = function(div) {

		div._item_id = u.cv(div, "item_id");

		// primary form
		var form = u.qs("form", div);
		form.div = div;




		u.f.init(form);
		// form.actions["cancel"].clicked = function(event) {
		// 	location.href = this.url;
		// }
		form.submitted = function(iN) {

			// stop autosave (this could be a manual save)
			u.t.resetTimer(page.t_autosave);

			this.response = function(response) {
				// restart autosave
//				page.t_autosave = u.t.setTimer(this, "autosave", page._autosave_interval);

				// notifier will kill autosave if necessary (if login is required)
				// could happen if user log off in other tab
				page.notify(response);

			}
			u.request(this, this.action, {"method":"post", "params" : u.f.getParams(this, {"send_as":"formdata"})});

		}

// 		form.updated = function() {
// //			u.bug("form has been update")
//
// 			this.change_state = true;
// 			u.t.resetTimer(page.t_autosave);
//
// 			if(!page.autosave_disabled) {
//
// //				u.bug("start autosave loop after update")
// 				page.t_autosave = u.t.setTimer(this, "autosave", page._autosave_interval);
//
// 			}
// 		}

// 		// enable autosaving for testing
// 		form.autosave = function() {
// //			u.bug("autosaving")
//
// 			// is autosave on?
// 			if(!page.autosave_disabled && this.change_state) {
//
// //				u.bug("autosave execute")
//
//
// 				for(name in this.inputs) {
// 					if(this.inputs[name].field) {
//
// 						// field has not been used yet
// 						if(!this.inputs[name].used) {
//
// 							// check for required and value
// 							if(u.hc(this.inputs[name].field, "required") && !this.inputs[name].val()) {
//
// 								// cannot save due to missing values - keep trying
// //								page.t_autosave = u.t.setTimer(this, "autosave", page._autosave_interval);
// 								return false;
// 							}
//
// 						}
// 						// do actual validation
// 						else {
// 							u.f.validate(this.inputs[name]);
// 						}
// 					}
// 				}
//
// 				// if error is found after validation
// 				if(!u.qs(".field.error", this)) {
// 					this.change_state = false;
// 					this.submitted();
// 				}
// 				// keep auto save going
// 				// else {
// 				// 	page.t_autosave = u.t.setTimer(this, "autosave", page._autosave_interval);
// 				// }
//
// 			}
//
// 			// autosave is turned off
// 			else {
//
//
// 			}
//
// 		}
//
// 		form.change_state = false;
//
// 		page._autosave_node = form;
// 		page._autosave_interval = 3000;
// 		page.t_autosave = u.t.setTimer(form, "autosave", page._autosave_interval);


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