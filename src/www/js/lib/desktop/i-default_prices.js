Util.Objects["defaultPrices"] = new function() {
	this.init = function(div) {

		div.item_id = u.cv(div, "item_id");

		// add tag form
		div._prices_form = u.qs("form", div);
		div._prices_form.div = div;


		u.f.init(div._prices_form);


		// CMS interaction urls
		div.csrf_token = div._prices_form.fields["csrf-token"].value;
		div.add_price_url = div._prices_form.action;
		div.delete_price_url = div.getAttribute("data-price-delete");
		div.update_price_url = div.getAttribute("data-price-update");


		div._prices_form.list = u.qs("ul.prices", div);

		// add edit+delete form to comment
		div.initPrice = function(node) {

			node.div = this;

			if(this.delete_price_url || this.update_price_url) {

				var actions = u.ae(node, "ul", {"class":"actions"});
				var li;

				// price editing disabled for now
				if(this.update_price_url && 0) {
					li = u.ae(actions, "li", {"class":"edit"});
					bn_edit = u.ae(li, "a", {"html":"Edit", "class":"button edit"});
					bn_edit.node = node;

					u.ce(bn_edit);
					bn_edit.clicked = function() {

						var actions, bn_cancel, bn_update, form;

						form = u.f.addForm(this.node, {"action":this.node.div.update_price_url+"/"+this.node.div.item_id+"/"+u.cv(this.node, "price_id"), "class":"edit"});
						u.ae(form, "input", {"type":"hidden","name":"csrf-token", "value":this.node.div.csrf_token});
						u.f.addField(form, {"type":"text", "name":"price", "value": u.qs("ul.info li.price", this.node).innerHTML});
						form.node = node;

						actions = u.ae(form, "ul", {"class":"actions"});

						bn_update = u.f.addAction(actions, {"value":"Update", "class":"button primary update", "name":"update"});
						bn_cancel = u.f.addAction(actions, {"value":"Cancel", "class":"button cancel", "type":"button", "name":"cancel"});

						u.f.init(form);

						form.submitted = function() {

							this.response = function(response) {
								page.notify(response);

								if(response.cms_status == "success") {

									u.qs("ul.info li.price", this.node).innerHTML = this.fields["price"].val();
									this.parentNode.removeChild(this);
								}
							}
							u.request(this, this.action, {"method":"post", "params":u.f.getParams(this)});
							
						}

						u.ce(bn_cancel);
						bn_cancel.clicked = function(event) {
							u.e.kill(event);
							this.form.parentNode.removeChild(this.form);
						}
					}
				}

				if(this.delete_price_url) {
					li = u.ae(actions, "li", {"class":"delete"});

					var form = u.f.addForm(li, {"action":this.delete_price_url+"/"+this.item_id+"/"+u.cv(node, "price_id"), "class":"delete"});
					u.ae(form, "input", {"type":"hidden","name":"csrf-token", "value":this.csrf_token});
					form.node = node;
					bn_delete = u.f.addAction(form, {"value":"Delete", "class":"button delete", "name":"delete"});

					u.f.init(form);

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
										this.node.parentNode.removeChild(this.node);
									}
								}
							}
							u.request(this, this.action, {"method":"post", "params":u.f.getParams(this)});
						}
					}
				}

			}

		}

		// new comment submitted
		div._prices_form.submitted = function(iN) {

			this.response = function(response) {
				page.notify(response);

				if(response.cms_status == "success" && response.cms_object) {

					var price_li = u.ae(this.list, "li", {"class":"pricedetails price_id:"+response.cms_object["id"]});
					var info = u.ae(price_li, "ul", {"class":"info"});
					u.ae(info, "li", {"class":"price", "html":response.cms_object["formatted_price"]});
					u.ae(info, "li", {"class":"currency", "html":response.cms_object["currency"]});
					u.ae(info, "li", {"class":"vatrate", "html":"("+response.cms_object["vatrate"]+"%)"})

					this.div.initPrice(price_li);

					// reset form input
					this.fields["price"].val("");

				}
			}
			u.request(this, this.action, {"method":"post", "params" : u.f.getParams(this)});
		}

		// initalize existing comments
		div.prices = u.qsa("li.pricedetails", div._prices_form.list);
		var i, node;
		for(i = 0; node = div.prices[i]; i++) {
			div.initPrice(node);
		}

	}
}
