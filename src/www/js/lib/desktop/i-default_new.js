// default new form
Util.Objects["defaultNew"] = new function() {
	this.init = function(form) {
//		u.bug("defaultNew:" + u.nodeId(form));

		u.f.init(form);

		if(form.actions["cancel"]) {
			form.actions["cancel"].clicked = function(event) {
				location.href = this.url;
			}
		}

		form.submitted = function(iN) {

			this.response = function(response) {
				if(response.cms_status == "success" && response.cms_object) {

//					u.bug("this.action:" + this.action)
					// u.bug("location.href:" + location.href)
					if(this.action.match(/\/save$/)) {
//						u.bug("match save")
						location.href = this.action.replace(/\/save/, "/edit/")+response.cms_object.item_id;
					}
					else if(location.href.match(/\/new$/)) {
//						u.bug("match new:" + location.href.replace(/\/new/, "/edit/")+response.cms_object.id);
						location.href = location.href.replace(/\/new/, "/edit/")+response.cms_object.id;
					}
					else if(this.actions["cancel"]) {
//						u.bug("match cancel")
						this.actions["cancel"].clicked();
					}
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