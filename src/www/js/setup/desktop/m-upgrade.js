Util.Modules["bulkremove"] = new function() {
	this.init = function(form) {

		u.f.init(form);

		form.p_response = u.ae(form, "p", {"class":"response"});


		form.submitted = function() {

			this.response = function(response) {

				form.p_response.innerHTML = response.cms_object.message;

			}
			u.request(this, this.action, {"method":this.method, "data":this.getData()});

		}

	}
}

Util.Modules["replace_emails"] = new function() {
	this.init = function(form) {

		u.f.init(form);

		form.p_response = u.ae(form, "p", {"class":"response"});


		form.submitted = function() {

			this.response = function(response) {

				form.p_response.innerHTML = response.cms_object.message;

			}
			u.request(this, this.action, {"method":this.method, "data":this.getData()});

		}

	}
}

	