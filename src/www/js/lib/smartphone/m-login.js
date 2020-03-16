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


			var h1 = u.qs("h1", this);
			var h2 = u.qs("h1 + h2", this);

			if(h1) {
				u.ae(page._title, h1);
			}

			if(h2) {
				u.ac(page._title, "double");
				u.ae(page._title, h2);
			}


			this._form = u.qs("form", this);
			u.f.init(this._form);

			this._form.inputs["username"].focus();

			page.resized();
		}


		// scene is ready
		scene.ready();

	}

}