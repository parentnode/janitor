Util.Modules["login"] = new function() {
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

			this._form = u.qs("form", this);
			u.f.init(this._form);

			this._form.inputs["username"].focus();

			page.resized();
		}


		// scene is ready
		scene.ready();

	}

}