u.bug_console_only = true;

Util.Objects["page"] = new function() {
	this.init = function(page) {

		var i, node;

		// main elements
		// header element
		page.hN = u.qs("#header", page);
		// content element
		page.cN = u.qs("#content", page);

		// navigation element
		page.nN = u.qs("#navigation", page);
		if(page.nN) {
			page.nN = page.hN.appendChild(page.nN);
		}
		// footer element
		page.fN = u.qs("#footer", page);


		// global scroll handler 
		page.resized = function() {

			// forward resize event to current scene
			if(page.cN && page.cN.scene && typeof(page.cN.scene.resized) == "function") {
				page.cN.scene.resized();
			}

		}

		// global scroll handler 
		page.scrolled = function() {

			// forward scroll event to current scene
			if(page.cN && page.cN.scene && typeof(page.cN.scene.scrolled) == "function") {
				page.cN.scene.scrolled();
			}

		}

		// Page is ready - called from several places, evaluates when page is ready to be shown
		page.ready = function() {
//				u.bug("page ready")

			// page is ready to be shown - only initalize if not already shown
			if(!this.is_ready) {

				// page is ready
				this.is_ready = true;

				// set resize handler
				u.e.addEvent(window, "resize", page.resized);
				// set scroll handler
				u.e.addEvent(window, "scroll", page.scrolled);


				// adds notifier and page.notify function
				u.notifier(page);

				// adds notifier and page.notify function
				u.navigation(page);
			}
		}

		// create icon svg
		page.svgIcon = function(icon) {

			// save icon to be cloned to avoid recreating icons again and again for lists
			// test if it becomes to heavy

			var path;
			if(icon == "youtube") {
				path = "";
			}


		}

		page.ready();
	}
}

u.e.addDOMReadyEvent(u.init)
