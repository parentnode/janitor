// default owner form
Util.Modules["defaultDeveloper"] = new function() {
	this.init = function(div) {
		// u.bug("defaultOwner:", form);

		div.form = u.qs("form", div);
		div.form.div = div;

		if(div.form) {

			u.f.init(div.form);

			div.form.submitted = function(iN) {
			
				this.response = function(response) {
					page.notify(response);
				}

				u.request(this, this.action, {"method":"post", "params" : u.f.getParams(this, {"send_as":"formdata"})});

			}

		}

	}
}