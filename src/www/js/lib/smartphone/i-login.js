Util.Objects["login"] = new function() {
	this.init = function(scene) {
//		u.bug("scene init:" + u.nodeId(scene))
		

		scene.resized = function() {
//			u.bug("scene.resized:" + u.nodeId(this));
		}

		scene.scrolled = function() {
//			u.bug("scrolled:" + u.nodeId(this))
		}

		scene.ready = function() {
//			u.bug("scene.ready:" + u.nodeId(this));

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

			this._form.fields["username"].focus();

			page.resized();
		}


		// scene is ready
		scene.ready();

	}

}