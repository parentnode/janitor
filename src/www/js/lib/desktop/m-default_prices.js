Util.Modules["defaultPrices"] = new function() {
	this.init = function(div) {

		div.item_id = u.cv(div, "item_id");

		// CMS interaction data
		div.csrf_token = div.getAttribute("data-csrf-token");
		div.delete_price_url = div.getAttribute("data-price-delete");

		// add tag form
		div._prices_form = u.qs("form", div);
		if(div._prices_form) {

			div._prices_form.div = div;

			// CMS interaction data
			div.add_price_url = div._prices_form.action;


			u.f.init(div._prices_form);

			div._prices_form.inputs["item_price_type"].changed = function() {

				if(this.val() == 3) {
					u.ac(this._form.inputs["item_price_quantity"].field, "required");
					u.ass(this._form.inputs["item_price_quantity"].field, {
						"display":"inline-block"
					})
				}
				else {
					u.rc(this._form.inputs["item_price_quantity"].field, "required");
					u.ass(this._form.inputs["item_price_quantity"].field, {
						"display":"none"
					})
				}
			}

			// make sure quantity is shown if type is already bulk
			if(div._prices_form.inputs["item_price_type"].val() == 3) {
				u.ass(div._prices_form.inputs["item_price_quantity"].field, {
					"display":"inline-block"
				})
			}


			// new price submitted
			div._prices_form.submitted = function(iN) {

				this.response = function(response) {
					page.notify(response);

					if(response.cms_status == "success" && response.cms_object) {

						var price_li = u.ae(this.div._prices_list, "li", {"class":"pricedetails price_id:"+response.cms_object["id"]});
						var info = u.ae(price_li, "ul", {"class":"info"});
						u.ae(info, "li", {"class":"price", "html":response.cms_object["formatted_price"]});
						u.ae(info, "li", {"class":"vatrate", "html":response.cms_object["vatrate"]+"%"});
						if(response.cms_object["type"] == "offer") {
							u.ae(info, "li", {"class":"offer", "html":"Special offer"});
						}
						else if(response.cms_object["type"] == "bulk") {
							u.ae(info, "li", {"class":"bulk", "html":"Bulk price for "+response.cms_object["quantity"] + " items"});
						}
						else if(response.cms_object["type"] != "default") {
							u.ae(info, "li", {"class":"custom_price", "html":response.cms_object["description"]});
						}

						this.div.initPrice(price_li);

						// reset form input
						this.reset();

					}
				}
				u.request(this, this.action, {"method":"post", "params" : u.f.getParams(this)});
			}			

		}





		div._prices_list = u.qs("ul.prices", div);

		// add delete form to price
		div.initPrice = function(node) {

			node.div = this;

			if(this.delete_price_url) {

				var actions = u.ae(node, "ul", {"class":"actions"});
				var li;

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


		// initalize existing prices
		div.prices = u.qsa("li.pricedetails", div._prices_list);
		var i, node;
		for(i = 0; node = div.prices[i]; i++) {
			div.initPrice(node);
		}

	}
}
