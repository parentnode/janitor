Util.Modules["modules"] = new function() {
	this.init = function(scene) {
		// u.bug("init modules");

		scene.modules = u.qsa("li.module", scene);

		var i, module;
		for(i = 0; i < scene.modules.length; i++) {
			module = scene.modules[i];


			module.bn_install = u.qs("li.install", module);
			u.bug(module.bn_install);
			if(module.bn_install) {

				module.bn_install.installed = function(response) {
					u.bug("installed", response);

					if(response && response.cms_status === "success") {
						u.t.setTimer(this, function() {location.reload(true)}, 1000);
					}

				}

			}

			module.bn_upgrade = u.qs("li.upgrade", module);
			u.bug(module.bn_upgrade);
			if(module.bn_upgrade) {

				module.bn_upgrade.upgraded = function(response) {

					if(response && response.cms_status === "success") {
						u.t.setTimer(this, function() {location.reload(true)}, 1000);
					}

				}

			}

			module.bn_uninstall = u.qs("li.uninstall", module);
			u.bug(module.bn_uninstall);
			if(module.bn_uninstall) {

				module.bn_uninstall.uninstalled = function(response) {
					u.bug("uninstalled", response);

					if(response && response.cms_status === "success") {
						u.t.setTimer(this, function() {location.reload(true)}, 1000);
					}

				}

			}

		}

	}
}