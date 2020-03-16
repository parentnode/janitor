Util.Modules["defaultEdit"] = new function() {
	this.init = function(div) {

		div._item_id = u.cv(div, "item_id");

		// primary form
		var form = u.qs("form", div);
		form.div = div;

		form.h2_name = u.qs("#content .scene > h2.name");
		form.p_sindex = u.qs("#content .scene div.sindex p.sindex");


		var autosave_setting = u.cv(div, "autosave");
		if(autosave_setting == "off") {
			page.autosave_disabled = true;
		}

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
				// could happen if user logs off in other tab
				page.notify(response);

				// Update any potential filelists
				u.f.updateFilelistStatus(this, response);


				// Update values typically shown in edit page
				if(response && response.cms_object) {

					if(response.cms_object.name && this.h2_name) {
						this.h2_name.innerHTML = response.cms_object.name;
					}

					if(response.cms_object.sindex && this.p_sindex) {
						this.p_sindex.innerHTML = response.cms_object.sindex;
					}

				}

			}
			u.request(this, this.action, {"method":"post", "data" : this.getData()});

		}

		form.updated = function() {
//			u.bug("form has been updated")

			this.change_state = true;
			u.t.resetTimer(page.t_autosave);

			if(!page.autosave_disabled) {

//				u.bug("start autosave loop after update")
				page.t_autosave = u.t.setTimer(this, "autosave", page._autosave_interval);

			}
		}

		// enable autosaving for testing
		form.autosave = function() {
//			u.bug("autosaving:" + this.change_state)

			// is autosave on?
			if(!page.autosave_disabled && this.change_state) {

//				u.bug("autosave execute")


				for(name in this.inputs) {
					if(this.inputs[name].field) {

						// field has not been used yet
						if(!this.inputs[name].used) {

							// check for required and value
							if(u.hc(this.inputs[name].field, "required") && !this.inputs[name].val()) {

								// cannot save due to missing values - keep trying
//								page.t_autosave = u.t.setTimer(this, "autosave", page._autosave_interval);
								return false;
							}

						}
						// do actual validation
						else {
							u.f.validate(this.inputs[name]);
						}
					}
				}

				// if no error is found after validation
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


Util.Modules["newSystemMessage"] = new function() {
	this.init = function(div) {

		// primary form
		var form = u.qs("form", div);
		form.div = div;

		form.ul_actions = u.qs("ul.actions", form);
		var fieldset = u.qs("fieldset.values", form);
		fieldset.h3_span = u.qs("h3 span.recipient", fieldset);
		fieldset.h3_span.innerHTML = "?";

		u.f.init(form);

		form.inputs["recipients"].keyup = function(event) {

			var recipients = this.val().replace(/,/g, ";").split(";");
			var fieldsets = u.qsa("fieldset.values", this._form);
			var i, recipient, inputs, input, labels, label, fieldset;

			// if these keys, update number of fieldsets
			if(event.key == "," || event.key == "," || event.key == "Delete" || event.key == "Backspace") {
				if(recipients.length < fieldsets.length && fieldsets.length > 1) {
					fieldsets[0].parentNode.removeChild(fieldsets[fieldsets.length-1]);
					fieldsets = u.qsa("fieldset.values", this._form);
				}
				else if(recipients.length > fieldsets.length) {

					// duplicate fieldset
					fieldset = fieldsets[0].parentNode.insertBefore(fieldsets[0].cloneNode(true), this._form.ul_actions);

					// make reference to header span
					fieldset.h3_span = u.qs("h3 span.recipient", fieldset);
					fieldsets = u.qsa("fieldset.values", this._form);

					// update input and label attributes
					inputs = u.qsa("input[type=text]", fieldset);
					for(i = 0; i < inputs.length; i++) {
						input = inputs[i];
						input.value = "";
						input.name = input.name.replace(/values\[[\d]+\]/, "values["+(fieldsets.length-1)+"]");
						input.id = input.id.replace(/values\[[\d]+\]/, "values["+(fieldsets.length-1)+"]");

					}
					labels = u.qsa("label", fieldset);
					for(i = 0; i < labels.length; i++) {
						label = labels[i];
						label.setAttribute("for", label.getAttribute("for").replace(/values\[[\d]+\]/, "values["+(fieldsets.length-1)+"]"));
					}

					// reinitialize form
					u.f.init(this._form);

				}

			}

			// update recipients text in all value fieldsets
			for(i = 0; i < recipients.length; i++) {
				recipient = recipients[i];
				fieldsets[i].h3_span.innerHTML = recipient;
			}

		}
		u.e.addEvent(form.inputs["recipients"], "keyup", form.inputs["recipients"].keyup);


		form.submitted = function(iN) {

			u.ac(this, "submitting");
			this.response = function(response) {
				u.rc(this, "submitting");

				if(response.cms_status == "success") {

					var div_receipt = u.ae(this.div, "div", {class:"receipt"});
					u.ae(div_receipt, "p", {html:"Mail(s) was successfully sent to:"});
					var ul_receipt = u.ae(div_receipt, "ul", {class:"receipt"});

					var i;
					for(i = 0; i < response.cms_object.length; i++) {
						u.ae(ul_receipt, "li", {html:response.cms_object[i]})
					}
					this.parentNode.replaceChild(div_receipt, this);
//					console.log(response.cms_object);

				}
				// error could happen if user log off in other tab
				page.notify(response);

			}
			u.request(this, this.action, {"method":"post", "data" : this.getData({"format":"formdata"})});

		}

	}
}

Util.Modules["sendMessage"] = new function() {
	this.init = function(div) {

		// primary form
		var form = u.qs("form", div);
		form.div = div;

		u.f.init(form);

		form.div_message_form = u.qs("div.item.message form");


		form.submitted = function(iN) {

			// recipients or maillist_id must be filled out
			if(this.inputs["recipients"].val() || this.inputs["maillist_id"].val() || this.inputs["user_id"].val()) {

				// save message before sending mail
				this.div_message_form.submit();

				u.ac(this, "submitting");
				this.response = function(response) {
					u.rc(this, "submitting");

					page.notify(response);

					// successful send
					if(response.cms_status == "success") {
						u.ass(this, {
							display:"none",
						});

						this.div_receipt = u.ae(this.div, "div", {class:"receipt"});
						u.ae(this.div_receipt, "p", {html:"Mail(s) was successfully sent to:"});
						var ul_receipt = u.ae(this.div_receipt, "ul", {class:"receipt"});

						var i;
						for(i = 0; i < response.cms_object.length; i++) {
							u.ae(ul_receipt, "li", {html:response.cms_object[i]})
						}

						// enable easy sending another
						var ul_actions = u.ae(this.div_receipt, "ul", {class:"actions"});
						var action = u.f.addAction(ul_actions, {name:"send_another", value:"Send another", type:"button", class:"button"});
						action._form = this
						u.ce(action);
						action.clicked = function() {
							this._form.div_receipt.parentNode.removeChild(this._form.div_receipt);
							u.ass(this._form, {
								display: "block",
							});
						}

					}

				}
				u.request(this, this.action, {"method":"post", "params" : u.f.getParams(this, {"send_as":"formdata"})});
				
			}
			else {
				
				u.f.inputHasError(this.inputs["recipients"]);
				u.f.inputHasError(this.inputs["maillist_id"]);

			}

		}

	}

}
