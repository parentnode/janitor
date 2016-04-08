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

			this._form = u.qs("form", this);
			u.f.init(this._form);

			this._form.fields["username"].focus();

			page.resized();
		}


		// scene is ready
		scene.ready();

	}

}