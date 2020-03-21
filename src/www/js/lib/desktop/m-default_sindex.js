// default sindex form
Util.Modules["defaultSindex"] = new function() {
	this.init = function(div) {
		// u.bug("defaultSindex:", form);

		div.current_sindex = u.qs(".current_sindex", div);

		div.li_update = u.qs("li.update", div);
		div.li_update.div = div;
		div.li_update.confirmed = function(response) {

			// Update sindex
			if(this.div.current_sindex && response.cms_object) {
				this.div.current_sindex.innerHTML = response.cms_object;
			}
			if(this.div.current_sindex_input && response.cms_object) {
				this.div.current_sindex_input.val(response.cms_object);
			}

		}

		div.form_manual = u.qs("form.manual_sindex", div);

		if(div.form_manual) {

			div.form_manual.div = div;
			div.data_check_sindex = div.getAttribute("data-check-sindex");

			u.f.init(div.form_manual);

			// Reference sindex input to enable update of value
			div.current_sindex_input = div.form_manual.inputs["item_sindex"];

			div.form_manual.submitted = function(iN) {
			
				this.response = function(response) {
					page.notify(response);

					// Update sindex
					if(this.div.current_sindex && response.cms_object) {
						this.div.current_sindex.innerHTML = response.cms_object;
					}
					if(this.div.current_sindex_input && response.cms_object) {
						this.div.current_sindex_input.val(response.cms_object);
					}

				}

				u.request(this, this.action, {"method":"post", "params" : u.f.getParams(this, {"send_as":"formdata"})});

			}

			div.form_manual.updated = function() {
				u.t.resetTimer(this.t_check_sindex);
				this.t_check_sindex = u.t.setTimer(this, "checkSindex", 300);
			}


			div.form_manual.checkSindex = function() {

				if(this.div.data_check_sindex) {

					this.response = function(response) {
						if(response.cms_object) {
							u.f.inputIsCorrect(this.div.current_sindex_input);
						}
						else {
							u.f.inputHasError(this.div.current_sindex_input);
						}
					}

					u.request(this, div.data_check_sindex, {
						"data": this.getData(),
						"method": "post"
					});

				}

			}

		}

	}
}