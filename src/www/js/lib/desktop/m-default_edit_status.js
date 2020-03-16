Util.Modules["defaultEditStatus"] = new function() {
	this.init = function(node) {
//		u.bug("init defaultEditStatus")

		node._item_id = u.cv(node, "item_id");
		node.csrf_token = node.getAttribute("data-csrf-token");

		// enable/disable button
		var action = u.qs("li.status");
		if(action) {

			// inject standard item status form if node is empty
			if(!action.childNodes.length) {
				action.update_status_url = action.getAttribute("data-item-status");
				if(action.update_status_url) {
					form_disable = u.f.addForm(action, {"action":action.update_status_url+"/"+node._item_id+"/0", "class":"disable"});
					u.ae(form_disable, "input", {"type":"hidden","name":"csrf-token", "value":node.csrf_token});
					u.f.addAction(form_disable, {"value":"Disable", "class":"button status"});
					form_enable = u.f.addForm(action, {"action":action.update_status_url+"/"+node._item_id+"/1", "class":"enable"});
					u.ae(form_enable, "input", {"type":"hidden","name":"csrf-token", "value":node.csrf_token});
					u.f.addAction(form_enable, {"value":"Enable", "class":"button status"});
				}
			}
			// look for valid forms
			else {
				form_disable = u.qs("form.disable", action);
				form_enable = u.qs("form.enable", action);
			}

			// init if forms are available
			if(form_disable && form_enable) {
				u.f.init(form_disable);
				form_disable.submitted = function() {
					this.response = function(response) {
						page.notify(response);
						if(response.cms_status == "success") {
							u.ac(this.parentNode, "disabled");
							u.rc(this.parentNode, "enabled");
						}
					}
					u.request(this, this.action, {"method":this.method, "params":u.f.getParams(this)});
				}

				u.f.init(form_enable);
				form_enable.submitted = function() {
					this.response = function(response) {
						page.notify(response);
						if(response.cms_status == "success") {
							u.rc(this.parentNode, "disabled");
							u.ac(this.parentNode, "enabled");
						}
					}
					u.request(this, this.action, {"method":this.method, "params":u.f.getParams(this)});
				}
			}

		}

	}
}