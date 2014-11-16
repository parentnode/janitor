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
						location.reload();
						//page.notify(response);
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

		var bn_install = u.qs(".actions li.install", scene);
		u.ce(bn_install);
		bn_install.clicked = function() {

			u.as(this.parentNode, "display", "none");

			this.ul_tasks = u.qs(".tasks", scene);
			this.div_installing = u.qs(".installing", scene);
			u.as(this.div_installing, "display", "block");

			// build JS and reload frontpage
			this.response = function(response) {

				if(response.cms_status == "success" && response.cms_object) {

					var i, task;
					for(i = 0; task = response.cms_object[i]; i++) {
						if(task.match(/ERROR/)) {
							u.ae(this.ul_tasks, "li", {"html":task, "class":"error"});
							return;
						}
						u.ae(this.ul_tasks, "li", {"html":task});
					}

					this.div_final_touches = u.qs(".final_touches", scene);
					u.as(this.div_final_touches, "display", "block");
				}
				
			}
			u.request(this, this.url, {"method":"post"});
		}



		var bn_finalize = u.qs(".actions li.finalize", scene);
		u.ce(bn_finalize);
		bn_finalize.clicked = function() {

			// existing site does not need JS/CSS building
			this.build_first = !u.hc(this, "simple");
			if(this.build_first) {
				this.ul_build = u.qs(".building", scene);

				// build JS and reload frontpage
				this.response = function(response) {

					var title = response.isHTML ? u.qs("title", response) : false;
					if(!title || !u.text(title).match(/404/)) {

						u.ae(this.ul_build, "li", {"html":"Frontend CSS built"});

						this.response = function(response) {

							u.ae(this.ul_build, "li", {"html":"Frontend JS built"});

							this.response = function(response) {

								u.ae(this.ul_build, "li", {"html":"Janitor CSS built"});

								this.response = function(response) {

									u.ae(this.ul_build, "li", {"html":"Janitor JS built"});
									u.t.setTimer(this, function() {location.href = "/";}, 1000);
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
			else {
				location.href = this.url;
			}

		}
	}
}