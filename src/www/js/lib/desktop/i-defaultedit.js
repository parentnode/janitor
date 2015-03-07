Util.Objects["defaultEdit"] = new function() {
	this.init = function(div) {

		div._item_id = u.cv(div, "item_id");

		// primary form
		var form = u.qs("form", div);
		form.div = div;




		u.f.init(form);
		form.actions["cancel"].clicked = function(event) {
			location.href = this.url;
		}
		form.submitted = function(iN) {

			this.response = function(response) {
				page.notify(response);
			}
			u.request(this, this.action, {"method":"post", "params" : u.f.getParams(this, {"send_as":"formdata"})});

		}


		// enable autosaving for testing
		form.autosave = function() {

			for(name in this.fields) {
				if(this.fields[name].field) {
					u.f.validate(this.fields[name]);
				}
			}

			// if error is found after validation
			if(!u.qs(".field.error", this)) {
				this.submitted();
			}

		}
		u.t.setInterval(form, "autosave", 15000);


		// kill backspace to avoid leaving for unintended
		form.cancelBackspace = function(event) {
//			u.bug("ss:" + u.qsa(".field.focus", this).length);
			if(event.keyCode == 8 && !u.qsa(".field.focus").length) {
				u.e.kill(event);
			}
		}
		u.e.addEvent(document.body, "keydown", form.cancelBackspace);

	}
}