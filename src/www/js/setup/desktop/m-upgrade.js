Util.Modules["bulkremove"] = new function() {
	this.init = function(form) {
		console.log("bulkremove")

		u.f.init(form);

		form.p_response = u.ae(form, "p", {"class":"response"});


		form.submitted = function() {

			this.response = function(response) {


				form.p_response.innerHTML = response.cms_object.message;
				
				console.log(response)
			}
			u.request(this, this.action, {"method":this.method, "params":u.f.getParams(this)});
		console.log("bulkremove submitted")
			
		}

	}
}

	