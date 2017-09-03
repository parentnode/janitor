Util.Objects["check"] = new function() {
	this.init = function(scene) {

		var bn_continue = u.qs(".actions li.start", scene);
		if(bn_continue) {
			u.ce(bn_continue);
			bn_continue.clicked = function() {
				var steps = u.qsa("li.setup li:not(.done)", page.nN); 

				var i, node;
				for(i = 0; node = steps[i]; i++) {
					if(node.url != location.href) {
						location.href = node.url;
						break;
					}
				}
			}
		}

		this.keepAlive = function() {
			u.request(this, "/janitor/admin/setup/keepAlive");
		}

		u.t.setInterval(this, "keepAlive", 60000);
	}
}


Util.Objects["config"] = new function() {
	this.init = function(scene) {

		var form = u.qs("form.config", scene);
		if(form) {

			u.f.init(form);
			form.submitted = function() {
				this.response = function(response) {
			
					if(response && response.cms_status == "success") {
						var steps = u.qsa("li.setup li:not(.done)", page.nN); 

						var i, node;
						for(i = 0; node = steps[i]; i++) {
							if(node.url != location.href) {
								location.href = node.url;
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


		this.keepAlive = function() {
			u.request(this, "/janitor/admin/setup/keepAlive");
		}

		u.t.setInterval(this, "keepAlive", 60000);
	}
}


Util.Objects["database"] = new function() {
	this.init = function(scene) {

		var form = u.qs("form.database", scene);
		if(form) {

			u.f.init(form);
			form.submitted = function() {
				this.response = function(response) {
			
					if(response && response.cms_status == "success") {

						// reload database page to show confirm dialogue
						if(response.cms_object && response.cms_object.status == "reload") {
							location.reload();
						}
						// continue to next step
						else {
							var steps = u.qsa("li.setup li:not(.done)", page.nN);

							var i, node;
							for(i = 0; node = steps[i]; i++) {
								if(node.url != location.href) {
									location.href = node.url;
									break;
								}
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

		var form_force = u.qs("form.force", scene);
		if(form_force) {

			u.f.init(form_force);
			form_force.submitted = function() {
				this.response = function(response) {
			
					if(response && response.cms_status == "success") {

						// reload database page to show confirm dialogue
						if(response.cms_object && response.cms_object.status == "reload") {
							location.reload();
						}
						// continue to next step
						else {
							var steps = u.qsa("li.setup li:not(.done)", page.nN);

							var i, node;
							for(i = 0; node = steps[i]; i++) {
								if(node.url != location.href) {
									location.href = node.url;
									break;
								}
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


		this.keepAlive = function() {
			u.request(this, "/janitor/admin/setup/keepAlive");
		}

		u.t.setInterval(this, "keepAlive", 60000);
	}
}


Util.Objects["mail"] = new function() {
	this.init = function(scene) {

		var form = u.qs("form.mail", scene);
		if(form) {

			u.f.init(form);
			form.submitted = function() {
				this.response = function(response) {
			
					if(response && response.cms_status == "success") {
						var steps = u.qsa("li.setup li:not(.done)", page.nN); 

						var i, node;
						for(i = 0; node = steps[i]; i++) {
							if(node.url != location.href) {
								location.href = node.url;
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


		this.keepAlive = function() {
			u.request(this, "/janitor/admin/setup/keepAlive");
		}

		u.t.setInterval(this, "keepAlive", 60000);
	}
}


Util.Objects["finish"] = new function() {
	this.init = function(scene) {


		var bn_check = u.qs(".actions li.check", scene);
		if(bn_check) {

			u.ce(bn_check);
			bn_check.clicked = function() {

				var steps = u.qsa("li.setup li:not(.done)", page.nN); 

				var i, node;
				for(i = 0; node = steps[i]; i++) {
					if(node.url != location.href) {
						location.href = node.url;
						break;
					}
				}

			}

		}


		var bn_install = u.qs(".actions li.install", scene);
		if(bn_install) {

			bn_install.form = u.qs("form", bn_install);
			if(bn_install.form) {
				
				u.f.init(bn_install.form);


	//			u.ce(bn_install);
				bn_install.form.submitted = function() {

					this.h2_subheader = u.qs("h2.subheader", scene);
					this.div_ready = u.qs("div.ready", scene);
					this.ul_tasks = u.qs(".tasks", scene);
					this.div_installing = u.qs("div.installing", scene);
					this.h2_installing = u.qs("h2", this.div_installing);
					this.ul_reset = u.qs("ul.actions.reset", scene);


					// update subheader
					if(this.h2_subheader && this.h2_installing) {
						this.h2_subheader.innerHTML = u.text(this.h2_installing);
					}

					// hide ready text
					if(this.div_ready) {
						u.as(this.div_ready, "display", "none");
					}

					// hide reset button
					if(this.ul_reset) {
						u.as(this.ul_reset, "display", "none");
					}

					// start install process
					if(this.div_installing) {
						u.as(this.div_installing, "display", "block");

						// build JS and reload frontpage
						this.response = function(response) {

							if(response.cms_status == "success" && response.cms_object && (response.cms_object.completed || response.cms_object.failed)) {

								var i, task_completed, task_failed;
								if(response.cms_object.completed.length) {
									for(i = 0; task = response.cms_object.completed[i]; i++) {
										u.ae(this.ul_tasks, "li", {"html":task});
									}
								}

								if(response.cms_object.failed.length) {
									for(i = 0; task = response.cms_object.failed[i]; i++) {
										u.ae(this.ul_tasks, "li", {"html":task, "class":"error"});
									}
								}


								// Constants needs to be reloaded
								if(response.cms_object.reload_constants) {

									u.request(this, this.action, {"method":"post", "params":u.f.getParams(this)+"&setup_type=reload"});

								}
								// An error occured in the install process
								else if(response.cms_object.failed.length) {

									// Show reset button
									if(this.ul_reset) {
										u.as(this.ul_reset, "display", "block");
									}

								}
								// everything went fine
								else {

									this.div_final_touches = u.qs(".final_touches", scene);
									u.as(this.div_final_touches, "display", "block");

									if(this.h2_subheader) {
										this.h2_subheader.innerHTML = "DONE";
									}

								}

							}
							else {

								// error
								u.ae(this.ul_tasks, "li", {"html":"A problem occured: (" + response + ")"});

							}

						}
						u.request(this, this.action, {"method":"post", "params":u.f.getParams(this)});

					}

				}

			}

		}


		// var bn_finalize = u.qs(".actions li.finalize", scene);
		// if(bn_finalize) {
		//
		// 	u.ce(bn_finalize);
		// 	bn_finalize.clicked = function() {
		//
		// 		// existing site does not need JS/CSS building
		// 		this.build_first = !u.hc(this, "simple");
		// 		if(this.build_first) {
		// 			this.ul_build = u.qs(".building", scene);
		//
		// 			// build JS and reload frontpage
		// 			this.response = function(response) {
		//
		// 				var title = response.isHTML ? u.qs("title", response) : false;
		// 				if(!title || !u.text(title).match(/404/)) {
		//
		// 					u.ae(this.ul_build, "li", {"html":"Frontend CSS built"});
		//
		// 					this.response = function(response) {
		//
		// 						u.ae(this.ul_build, "li", {"html":"Frontend JS built"});
		//
		// 						this.response = function(response) {
		//
		// 							u.ae(this.ul_build, "li", {"html":"Janitor CSS built"});
		//
		// 							this.response = function(response) {
		//
		// 								u.ae(this.ul_build, "li", {"html":"Janitor JS built"});
		// 								u.t.setTimer(this, function() {location.href = "/";}, 1000);
		// 							}
		// 							u.request(this, "/janitor/js/lib/build");
		// 						}
		// 						u.request(this, "/janitor/css/lib/build");
		// 					}
		// 					u.request(this, "/js/lib/build");
		//
		// 				}
		// 				else {
		// 					alert("Apache is not responding as expected - did you forget to restart?");
		// 				}
		// 			}
		// 			u.request(this, "/css/lib/build");
		//
		// 		}
		// 		else {
		// 			location.href = this.url;
		// 		}
		//
		// 	}
		//
		// }


		this.keepAlive = function() {
			u.request(this, "/janitor/admin/setup/keepAlive");
		}

		u.t.setInterval(this, "keepAlive", 60000);
	}
}