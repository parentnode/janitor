Util.Objects["defaultEditStatus"] = new function() {
	this.init = function(node) {

		node._item_id = u.cv(node, "item_id");

		// enable/disable button
		var action = u.qs("li.status");
		if(action) {

			// inject standard item status form if node is empty
			if(!action.childNodes.length) {
				form_disable = u.f.addForm(action, {"action":"/admin/cms/disable/"+node._item_id, "class":"disable"});
				u.f.addAction(form_disable, {"value":"Disable", "class":"button status"});
				form_enable = u.f.addForm(action, {"action":"/admin/cms/enable/"+node._item_id, "class":"enable"});
				u.f.addAction(form_enable, {"value":"Enable", "class":"button status"});
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
					u.request(this, this.action);
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
					u.request(this, this.action);
				}
			}

		}

	}
}