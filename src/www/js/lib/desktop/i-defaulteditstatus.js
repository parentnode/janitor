Util.Objects["defaultEditStatus"] = new function() {
	this.init = function(div) {

		div._item_id = u.cv(div, "item_id");

		// enable/disable button
		var li_status = u.qs("li.status");
		if(li_status) {
			form = u.f.addForm(li_status, {"action":"/admin/cms/disable/"+div._item_id, "class":"disable"});
			u.f.addAction(form, {"value":"Disable", "class":"button status"});

			u.f.init(form);
			form.submitted = function() {
				this.response = function(response) {
					page.notify(response.cms_message);
					if(response.cms_status == "success") {
						u.ac(this.parentNode, "disabled");
						u.rc(this.parentNode, "enabled");
					}
				}
				u.request(this, this.action);
			}

			form = u.f.addForm(li_status, {"action":"/admin/cms/enable/"+div._item_id, "class":"enable"});
			u.f.addAction(form, {"value":"Enable", "class":"button status"});

			u.f.init(form);
			form.submitted = function() {
				this.response = function(response) {
					page.notify(response.cms_message);
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