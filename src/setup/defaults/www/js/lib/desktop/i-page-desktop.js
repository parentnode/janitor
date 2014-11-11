Util.Objects["page"] = new function() {
	this.init = function(page) {

		// header reference
		page.hN = u.qs("#header");

		// content reference
		page.cN = u.qs("#content", page);

		// navigation reference
		page.nN = u.qs("#navigation", page);
		page.nN = u.ie(page.hN, page.nN);

		// footer reference
		page.fN = u.qs("#footer");


		// global resize handler 
		page.resized = function() {
//			u.bug("page.resized:" + u.nodeId(this));
		}

		// global scroll handler 
		page.scrolled = function() {
//			u.bug("page.scrolled:" + u.nodeId(this))
		}

		// Page is ready - called from several places, evaluates when page is ready to be shown
		page.ready = function() {
//			u.bug("page.ready:" + u.nodeId(this));

			// page is ready to be shown - only initalize if not already shown
			if(!this.is_ready) {

				// page is ready
				this.is_ready = true;

				// set resize handler
				u.e.addEvent(window, "resize", page.resized);
				// set scroll handler
				u.e.addEvent(window, "scroll", page.scrolled);

			}

		}

		// ready to start page builing process
		page.ready();
	}
}

u.e.addDOMReadyEvent(u.init);

