// default new form
Util.Objects["defaultNew"] = new function() {
	this.init = function(form) {

		u.f.init(form);

		form.actions["cancel"].clicked = function(event) {
			location.href = this.url;
		}

		form.submitted = function(iN) {

			this.response = function(response) {
				if(response.cms_status == "success" && response.cms_object) {

					//alert("this.action:" + this.action)
//					alert(response);
					if(this.action.match(/\/save/)) {
						location.href = this.action.replace("\/save", "/edit/"+response.cms_object.item_id);
	
					}
					else if(this.actions["cancel"]) {
						this.actions["cancel"].clicked();
					}
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