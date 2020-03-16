Util.Modules["scene"] = new function() {
	this.init = function(scene) {
		// u.bug("scene init:", scene);
		

		scene.resized = function() {
			// u.bug("scene.resized:", this);

		}

		scene.scrolled = function() {
			// u.bug("scrolled:", this);
		}

		scene.ready = function() {
			// u.bug("scene.ready:", this);

			page.cN.scene = this;


			page.resized();
		}

		// scene is ready
		scene.ready();
	}
}