Util.Objects["defaultEdit"] = new function() {
	this.init = function(div) {

		div._item_id = u.cv(div, "item_id");

		// primary form
		var form = u.qs("form", div);
		u.f.init(form);
		form.actions["cancel"].clicked = function(event) {
			location.href = this.url;
		}
		form.submitted = function(iN) {

			this.response = function(response) {
				page.notify(response);
			}
			u.request(this, this.action, {"method":"post", "params" : u.f.getParams(this)});

		}

	}
}