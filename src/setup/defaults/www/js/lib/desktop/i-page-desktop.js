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

		}

		// global scroll handler 
		page.scrolled = function() {

		}


		// Page is ready - called from several places, evaluates when page is ready to be shown
		page.ready = function() {

			// page is ready to be shown - only initalize if not already shown
			if(!u.hc(this, "ready")) {

				// page is ready
				u.addClass(this, "ready");

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

