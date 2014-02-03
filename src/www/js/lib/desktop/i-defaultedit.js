Util.Objects["defaultEdit"] = new function() {
	this.init = function(div) {

		var form = u.qs("form", div);

		u.f.init(form);

		form.actions["cancel"].clicked = function(event) {
			location.href = this.url;
		}

		form.submitted = function(iN) {

			this.response = function(response) {
				if(response.cms_status == "success") {
					page.notify(response.cms_message);
//					location.reload();
//					location.href = this.actions["cancel"].url;
				}
				else {
					alert(response.cms_message[0]);
				}
			}
			u.request(this, this.action, {"method":"post", "params" : u.f.getParams(this)});

		}

	}
}