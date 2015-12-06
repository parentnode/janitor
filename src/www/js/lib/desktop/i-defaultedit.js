Util.Objects["defaultEdit"] = new function() {
	this.init = function(div) {

		div._item_id = u.cv(div, "item_id");

		// primary form
		var form = u.qs("form", div);
		form.div = div;




		u.f.init(form);
		// form.actions["cancel"].clicked = function(event) {
		// 	location.href = this.url;
		// }
		form.submitted = function(iN) {

			// stop autosave (this could be a manual save)
			u.t.resetTimer(page.t_autosave);

			this.response = function(response) {
				// restart autosave
//				page.t_autosave = u.t.setTimer(this, "autosave", page._autosave_interval);

				// notifier will kill autosave if necessary (if login is required)
				// could happen if user log off in other tab
				page.notify(response);

			}
			u.request(this, this.action, {"method":"post", "params" : u.f.getParams(this, {"send_as":"formdata"})});

		}

		form.updated = function() {
//			u.bug("form has been update")

			this.change_state = true;
			u.t.resetTimer(page.t_autosave);

			if(!page.autosave_disabled) {

//				u.bug("start autosave loop after update")
				page.t_autosave = u.t.setTimer(this, "autosave", page._autosave_interval);

			}
		}

		// enable autosaving for testing
		form.autosave = function() {
//			u.bug("autosaving")

			// is autosave on?
			if(!page.autosave_disabled && this.change_state) {

//				u.bug("autosave execute")


				for(name in this.fields) {
					if(this.fields[name].field) {

						// field has not been used yet
						if(!this.fields[name].used) {

							// check for required and value
							if(u.hc(this.fields[name].field, "required") && !this.fields[name].val()) {

								// cannot save due to missing values - keep trying
//								page.t_autosave = u.t.setTimer(this, "autosave", page._autosave_interval);
								return false;
							}

						}
						// do actual validation
						else {
							u.f.validate(this.fields[name]);
						}
					}
				}

				// if error is found after validation
				if(!u.qs(".field.error", this)) {
					this.change_state = false;
					this.submitted();
				}
				// keep auto save going
				// else {
				// 	page.t_autosave = u.t.setTimer(this, "autosave", page._autosave_interval);
				// }

			}

			// autosave is turned off
			else {

				
			}

		}

		form.change_state = false;

		page._autosave_node = form;
		page._autosave_interval = 3000;
		page.t_autosave = u.t.setTimer(form, "autosave", page._autosave_interval);


		// kill backspace to avoid leaving page unintended (backspace is history.back)
		form.cancelBackspace = function(event) {
//			u.bug("ss:" + u.qsa(".field.focus", this).length);
			if(event.keyCode == 8 && !u.qsa(".field.focus").length) {
				u.e.kill(event);
			}
		}
		u.e.addEvent(document.body, "keydown", form.cancelBackspace);

	}
}