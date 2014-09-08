Util.Objects["navigationNodes"] = new function() {
	this.init = function(div) {

		div.list = u.qs("ul.nodes", div);

		if(div.list) {

			div.list.update_order_url = div.getAttribute("data-update-order");
			div.list.csrf_token = div.getAttribute("data-csrf-token");
			div.list.nodes = u.qsa("li.item", div.list);


			var i, node;
			for(i = 0; node = div.list.nodes[i]; i++) {

				node.list = div.list;

				// delete button
				var action = u.qs("li.delete", node);

				if(action) {

					form = u.qs("form", action);
					form.node = node;

					// init if form is available
					if(form) {
						u.f.init(form);

						if(u.qs("ul.nodes li.item", node)) {
							u.ac(form.actions["delete"], "disabled");
						}

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
	//									location.href = this.cancel_url;
										this.node.parentNode.removeChild(this.node);

										// update
										this.node.list.updateNodeStructure();
									}
								}
								u.request(this, this.action, {"method":"post", "params":u.f.getParams(this)});
							}
						}
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

				var structure = this.getStructure();

				this.response = function(response) {
					page.notify(response);
				}
				u.request(this, this.update_order_url, {"method":"post", "params":"csrf-token="+this.csrf_token+"&structure="+JSON.stringify(structure)});


				var i, node;
				this.nodes = u.qsa("li.item", this);
				for(i = 0; node = this.nodes[i]; i++) {

					// update delete button states
					var action = u.qs("li.delete", node);
					if(action) {
						form = u.qs("form", action);
						if(form) {
							if(u.qs("ul.nodes li.item", node)) {
								u.ac(form.actions["delete"], "disabled");
							}
							else {
								u.rc(form.actions["delete"], "disabled");
							}
						}
					}
				}
			}

			u.sortable(div.list, {"allow_nesting":true, "targets":"nodes", "draggables":"draggable"});

		}

	}
}