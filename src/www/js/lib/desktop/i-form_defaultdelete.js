
// default delete form
Util.Objects["formDefaultDelete"] = new function() {
	this.init = function(form) {
//		u.bug("formDefaultDelete init:" + u.nodeId(form));

		u.f.init(form);

		var bn_delete = u.qs("input.delete", form);
		if(bn_delete) {

			bn_delete.org_value = bn_delete.value;

			u.e.click(bn_delete);
			bn_delete.restore = function(event) {
				this.value = this.org_value;
				u.rc(this, "confirm");
			}

			bn_delete.inputStarted = function(event) {
				u.e.kill(event);
			}

			bn_delete.clicked = function(event) {
				u.e.kill(event);

				// first click
				if(!u.hc(this, "confirm")) {
					u.ac(this, "confirm");
					this.value = "Confirm";
					this.t_confirm = u.t.setTimer(this, this.restore, 3000);
				}
				// confirm click
				else {
					u.t.resetTimer(this.t_confirm);

					this.response = function(response) {
						if(response.cms_status == "success") {
							// check for constraint error preventing row from actually being deleted
							if(response.cms_object && response.cms_object.constraint_error) {
								page.notify(response.cms_message);
								this.value = this.org_value;
								u.ac(this, "disabled");
							}
							else {
								location.reload();
//								location.href = this.form.actions["cancel"].url;
							}
						}
						else {
							page.notify(response.cms_message);
						}
					}
					u.request(this, this.form.action, {"method":"post", "params" : u.f.getParams(this.form)});
				}
			}
		}

	}
}