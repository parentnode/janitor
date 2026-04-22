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
					if(response && response.cms_status === "success") {

						// Show updating indicator
						u.ae(this.module, "div", {"class": "updating"});

						this.response = function(response) {
							// u.bug("installed", response);

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
	}
}