// default status form
Util.Objects["formDefaultStatus"] = new function() {
	this.init = function(form) {

		u.f.init(form);

		var bn_status = u.qs("input.status", form);
		if(bn_status) {
			u.e.click(bn_status)
			bn_status.clicked = function(event) {
				u.e.kill(event);

				this.response = function(response) {
					if(response.cms_status == "success") {
//						u.xInObject(response.cms_message);
						if(response.cms_message.message.length && response.cms_message.message[0].match(/enabled/i)) {
							window.scrollTo(0,0);
						}
						location.reload();
					}
					else {
						alert(response.cms_message[0]);
					}
				}
				u.request(this, this.form.action);
			}
		}
	}
}

