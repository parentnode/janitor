
// Add prices form
Util.Objects["addPrices"] = new function() {
	this.init = function(div) {

		var form = u.qs("form", div);
		u.f.init(form);

		var i, field, actions;

		// field = form.fields["prices"].field;
		// actions = u.qs(".actions", form);
		// actions = field.insertBefore(actions, u.ns(field._input));
		form.submitted = function(event) {
			this.response = function(response) {
				page.notify(response);
			}
			u.request(this, this.action, {"method":"post", "params":u.f.getParams(this)});
		}

	}
}



