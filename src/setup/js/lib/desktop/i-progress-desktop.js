Util.Objects["start"] = new function() {
	this.init = function(scene) {

		var bn_start = u.qs(".actions li.start", scene);
		u.ce(bn_start);
		bn_start.clicked = function() {
			var steps = u.qsa("li:not(.done):not(.front)", page.nN); 

			var i, node;
			for(i = 0; node = steps[i]; i++) {
				var url = u.qs("a", steps[i]).href;
				if(url != location.href) {
					location.href = url;
					break;
				}
			}
		}
	}
}


Util.Objects["paths"] = new function() {
	this.init = function(scene) {

		var form = u.qs("form", scene);
		if(form) {

			u.f.init(form);
			form.submitted = function() {
				this.response = function(response) {
			
					if(response && response.cms_status == "success") {
						var steps = u.qsa("li:not(.done):not(.front)", page.nN); 

						var i, node;
						for(i = 0; node = steps[i]; i++) {
							var url = u.qs("a", steps[i]).href;
							if(url != location.href) {
								location.href = url;
								break;
							}
						}
					}
					else {
						page.notify(response);
					}
				}
				u.request(this, this.action, {"method":this.method, "params":u.f.getParams(this)});
			}
		}

	}
}


Util.Objects["database"] = new function() {
	this.init = function(scene) {

		var form = u.qs("form", scene);
		if(form) {

			u.f.init(form);
			form.submitted = function() {
				this.response = function(response) {
			
					if(response && response.cms_status == "success") {
						var steps = u.qsa("li:not(.done):not(.front)", page.nN); 

						var i, node;
						for(i = 0; node = steps[i]; i++) {
							var url = u.qs("a", steps[i]).href;
							if(url != location.href) {
								location.href = url;
								break;
							}
						}
					}
					else {
						page.notify(response);
					}
				}
				u.request(this, this.action, {"method":this.method, "params":u.f.getParams(this)});
			}
		}

	}
}

Util.Objects["config"] = new function() {
	this.init = function(scene) {

		var form = u.qs("form", scene);
		if(form) {

			u.f.init(form);
			form.submitted = function() {
				this.response = function(response) {
			
					if(response && response.cms_status == "success") {
						var steps = u.qsa("li:not(.done):not(.front)", page.nN); 

						var i, node;
						for(i = 0; node = steps[i]; i++) {
							var url = u.qs("a", steps[i]).href;
							if(url != location.href) {
								location.href = url;
								break;
							}
						}
					}
					else {
						page.notify(response);
					}
				}
				u.request(this, this.action, {"method":this.method, "params":u.f.getParams(this)});
			}

		}
	}
}

Util.Objects["mail"] = new function() {
	this.init = function(scene) {

		var form = u.qs("form", scene);
		if(form) {

			u.f.init(form);
			form.submitted = function() {
				this.response = function(response) {
			
					if(response && response.cms_status == "success") {
						var steps = u.qsa("li:not(.done):not(.front)", page.nN); 

						var i, node;
						for(i = 0; node = steps[i]; i++) {
							var url = u.qs("a", steps[i]).href;
							if(url != location.href) {
								location.href = url;
								break;
							}
						}
					}
					else {
						page.notify(response);
					}
				}
				u.request(this, this.action, {"method":this.method, "params":u.f.getParams(this)});
			}

		}
	}
}

Util.Objects["finish"] = new function() {
	this.init = function(scene) {

		var bn_finalize = u.qs(".actions li.finalize", scene);
		u.ce(bn_finalize);
		bn_finalize.clicked = function() {

			// build JS and reload frontpage
			this.response = function(response) {
				var title = response.isHTML ? u.qs("title", response) : false;
				if(!title || !u.text(title).match(/404/)) {

					this.response = function(response) {
						this.response = function(response) {
							this.response = function(response) {
								location.href = "/";
							}
							u.request(this, "/janitor/js/lib/build");
						}
						u.request(this, "/janitor/css/lib/build");
					}
					u.request(this, "/js/lib/build");

				}
				else {
					alert("Apache is not responding as expected - did you forget to restart?");
				}
			}
			u.request(this, "/css/lib/build");
		}
	}
}