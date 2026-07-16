Util.Modules["modules"] = new function() {
	this.init = function(scene) {
		// u.bug("init modules");


		scene.ul_modules_installed = u.qs("div.modules_installed ul.modules", scene);
		scene.ul_modules_available = u.qs("div.modules_available ul.modules", scene);

		scene.modules = u.qsa("li.module", scene);

		scene.initModule = function(module) {

			module.scene = scene;

			module.bn_install = u.qs("li.install", module);
			if(module.bn_install) {
				module.bn_install.module = module;

				// Callback when install process done
				module.bn_install.installed = function(response) {
					// u.bug("installed", response);

					// On success, move module to installed list
					if(response && response.cms_object && response.cms_object.length) {

						var i, message;
						for(i = 0; i < response.cms_object.length; i++) {
							message = response.cms_object[i];
							u.bug(message);
							if(message.type === "error") {
								page.notify(message);
							}
						}

					}
					else if(response && response.cms_status === "success") {

						// Show updating indicator
						u.ae(this.module, "div", {"class": "updating"});

						this.response = function(response) {
							// u.bug("installed, get module", response);

							if(response && response.isHTML) {

								// Find module section in response, insert and init
								var module_section = u.qs("."+this.module.className.replace(/ /g, "."), response);
								this.module.scene.ul_modules_installed.appendChild(module_section);
								this.module.scene.initModule(module_section);

								// Remove old module entry
								this.module.parentNode.removeChild(this.module);

								// Scroll to newly inserted module
								u.scrollTo(window, {"node": module_section, "offset_y": 100});
							}
						}
						u.request(this, location.href);

						// u.t.setTimer(this, function() {location.reload(true)}, 1000);


					}

				}

			}

		}


		var i, module;
		for(i = 0; i < scene.modules.length; i++) {
			module = scene.modules[i];

			scene.initModule(module);
		}

	}
}


Util.Modules["module"] = new function() {
	this.init = function(scene) {
		// u.bug("init module");


		// Upgrade module
		scene.form_upgrade = u.qs("form.upgrade", scene);
		// u.bug(module.bn_upgrade);
		if(scene.form_upgrade) {

			scene.form_upgrade.scene = scene;

			u.f.init(scene.form_upgrade);

			scene.form_upgrade.submitted = function(event) {
				// u.bug("uninstall");

				// Enter submit state
				u.ac(this.actions.upgrade, "disabled");
				u.ac(this, "submitting");
				this.actions.upgrade.wait_default_value = this.actions.upgrade.value;
				this.actions.upgrade.value = "Wait"

				this.response = function(response) {
					// u.bug("upgradeed", response);

					if(response && response.cms_status === "success") {
						location.reload(true);
					}
					else {
						page.notify(response);

						u.rc(this.actions.upgrade, "disabled");
						u.rc(this, "submitting");
						this.actions.upgrade.value = this.actions.upgrade.wait_default_value;

					}

				}
				u.request(this, this.action, {
					"method": this.method,
					"data": this.getData()
				});

			}
		}


		// Uninstall module
		scene.form_uninstall = u.qs("form.uninstall", scene);
		// u.bug(scene.form_uninstall);
		if(scene.form_uninstall) {

			scene.form_uninstall.scene = scene;

			u.f.init(scene.form_uninstall);

			scene.form_uninstall.submitted = function(event) {
				// u.bug("uninstall");

				// Enter submit state
				u.ac(this.actions.uninstall, "disabled");
				u.ac(this, "submitting");
				this.actions.uninstall.wait_default_value = this.actions.uninstall.value;
				this.actions.uninstall.value = "Wait"

				this.response = function(response) {
					// u.bug("uninstalled", response);

					if(response && response.cms_status === "success") {
						location.href = "/janitor/admin/setup/modules";
					}
					else {
						page.notify(response);

						u.rc(this.actions.uninstall, "disabled");
						u.rc(this, "submitting");
						this.actions.uninstall.value = this.actions.uninstall.wait_default_value;

					}

				}
				u.request(this, this.action, {
					"method": this.method,
					"data": this.getData()
				});

			}

		}


		// Add new / delete controller
		scene.form_new_controller = u.qs("form.new_controller", scene);
		if(scene.form_new_controller) {
			scene.form_new_controller.scene = scene;

			u.f.init(scene.form_new_controller);

			scene.form_new_controller.submitted = function() {

				u.ac(this, "submitting");

				this.response = function(response) {
					// u.bug("response", response);

					page.notify(response);
					u.rc(this, "submitting");

					if(response.cms_status === "success" && response.cms_object) {
						this.scene.initController(u.ae(this.scene.ul_controllers, "li", {"html": "<h4>"+response.cms_object+"</h4>"}));

						this.scene.updateControllerDeleteState();

						this.reset();
					}

				}

				u.request(this, this.action, {
					"method": this.method,
					"data": this.getData()
				});
				
			}


			// Existing controllers
			scene.ul_controllers = u.qs("ul.controllers", scene);

			scene.csrf_token = scene.ul_controllers.getAttribute("data-csrf-token");
			scene.delete_url = scene.ul_controllers.getAttribute("data-delete-action");
			scene.confirm_value = scene.ul_controllers.getAttribute("data-confirm-value");
			scene.button_value = scene.ul_controllers.getAttribute("data-button-value");
			scene.button_name = scene.ul_controllers.getAttribute("data-button-name");

			if(scene.delete_url && scene.csrf_token) {

				// Inject delete button
				scene.initController = function(li) {

					if(!li.is_ready) {
						li.is_ready = true;
						li.scene = this;

						var ul_actions = u.ae(li, "ul", {"class": "actions"});
						var li_delete = u.ae(ul_actions, "li", {
							"class": "delete",
							"data-csrf-token": this.csrf_token,
							"data-form-action": this.delete_url,
							"data-inputs": encodeURI(JSON.stringify({"controller_path": u.text(li).trim()})),
							"data-confirm-value": this.confirm_value,
							"data-button-value": this.button_value,
							"data-button-name": this.button_name,
						});
						li_delete.li = li;

						u.m.oneButtonForm.init(li_delete);

						li_delete.confirmed = function(response) {
							// u.bug("res", response);

							if(response.cms_status === "success") {
								this.li.parentNode.removeChild(this.li);

								this.li.scene.updateControllerDeleteState();
							}

						}

					}

				}

				// Disable delete when only one controller is left
				scene.updateControllerDeleteState = function() {
					if(this.ul_controllers.children.length > 1) {
						u.rc(this.ul_controllers, "no_delete");
					}
					else {
						u.ac(this.ul_controllers, "no_delete");
					}
				}


				// Find existing controllers and initialize
				var existing_controllers = u.qsa("li", scene.ul_controllers);
				var i, li;
				for(i = 0; i < existing_controllers.length; i++) {
					li = existing_controllers[i];
					scene.initController(li);
				}

				// Update delete state
				scene.updateControllerDeleteState();

			}

		}


		// Rename controller
		scene.form_rename_controller = u.qs("form.rename_controller", scene);
		if(scene.form_rename_controller) {
			scene.form_rename_controller.scene = scene;

			u.f.init(scene.form_rename_controller);

			scene.form_rename_controller.submitted = function() {

				u.ac(this, "submitting");

				this.response = function(response) {
					// u.bug("response", response);

					page.notify(response);
					u.rc(this, "submitting");

					if(response.cms_status === "success" && response.cms_object) {

						// Update controller values
						var i, span_controller;
						for(i = 0; i < this.scene.span_controllers.length; i++) {
							span_controller = this.scene.span_controllers[i];
							span_controller.innerHTML = response.cms_object;
						}

						this.reset();
					}

				}

				u.request(this, this.action, {
					"method": this.method,
					"data": this.getData()
				});
				
			}

			scene.span_controllers = u.qsa("span.controller", scene);

		}


		// Settings
		scene.form_settings = u.qs("form.settings", scene);
		if(scene.form_settings) {
			scene.form_settings.scene = scene;

			u.f.init(scene.form_settings);

			scene.form_settings.submitted = function() {

				u.ac(this, "submitting");

				this.response = function(response) {
					// u.bug("response", response);

					page.notify(response);
					u.rc(this, "submitting");

					if(response.cms_status === "success" && response.cms_object) {

						// // Update controller values
						// var i, span_controller;
						// for(i = 0; i < this.scene.span_controllers.length; i++) {
						// 	span_controller = this.scene.span_controllers[i];
						// 	span_controller.innerHTML = response.cms_object;
						// }
						//
						// this.reset();
					}

				}

				u.request(this, this.action, {
					"method": this.method,
					"data": this.getData()
				});
				
			}

		}


	}
}