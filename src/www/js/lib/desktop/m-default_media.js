// Add images form
Util.Modules["addMedia"] = new function() {
	this.init = function(div) {
		// u.bug("addMedia init:", div);

		div.form = u.qs("form.upload", div);
		div.form.div = div;

		u.f.init(div.form);


		// Submit on change
		div.form.changed = function() {
			this.submit();
		}

		// upload form submitted
		div.form.submitted = function() {

			this.response = function(response) {
				page.notify(response);

				// inject/update image if everything went well
				if(response.cms_status == "success" && response.cms_object) {

					// Update file list status
					u.f.updateFormAfterResponse(this, response);

				}

			}
			u.request(this, this.action, {"method":"post", "data":this.getData()});

		}

	}
}

// Add images form
Util.Modules["addMediaSingle"] = new function() {
	this.init = function(div) {
		// u.bug("addMediaSingle init:", div);

		div.form = u.qs("form.upload", div);
		div.form.div = div;

		u.f.init(div.form);


		// Submit on change
		div.form.changed = function() {
			this.submit();
		}

		// Handle upload
		div.form.submitted = function() {

			this.response = function(response) {
				page.notify(response);

				// inject/update preview if everything went well
				if(response.cms_status == "success" && response.cms_object) {

					// Update file list status
					u.f.updateFormAfterResponse(this, response);

				}

			}
			u.request(this, this.action, {"method":"post", "data":this.getData()});

		}

	}
}
