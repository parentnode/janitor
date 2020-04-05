// default owner form
Util.Modules["defaultOwner"] = new function() {
	this.init = function(div) {
		// u.bug("defaultOwner:", form);

		div.form = u.qs("form", div);

		div.current_owner = u.qs(".current_owner", div);

		if(div.form) {

			div.form.div = div;

			u.f.init(div.form);

			div.form.submitted = function(iN) {
			
				this.response = function(response) {
					page.notify(response);

					// Update ownername
					if(this.div.current_owner && response.cms_object && response.cms_object["nickname"]) {
						this.div.current_owner.innerHTML = response.cms_object["nickname"];
					}

				}

				u.request(this, this.action, {"method":"post", "data" : this.getData({"format":"formdata"})});

			}
		}

	}
}