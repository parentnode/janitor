// default sindex form
Util.Modules["defaultCannonical"] = new function() {
	this.init = function(div) {
		// u.bug("defaultCannonical:", div);

		div.updateView = function() {

			this.updateViewResponse = function(response) {
				// u.bug(response);

				if(response.isHTML) {
					var view = u.qs("div.cannonical", response);
					if(view) {
						this.replaceWith(view);
						u.init(view.parentNode);
					}
				}
				
			}
			u.request(this, location, {
				"callback": "updateViewResponse"
			});

		}

		div.form = u.qs("form", div);
		if(div.form) {

			div.form.div = div;

			u.f.init(div.form);

			div.form.submitted = function(iN) {
			
				this.response = function(response) {
					page.notify(response);

					if(response.cms_status === "success") {
						this.div.updateView();
					}
				}

				u.request(this, this.action, {"method":"post", "data" : this.getData({"format":"formdata"})});

			}

		}

	}
}