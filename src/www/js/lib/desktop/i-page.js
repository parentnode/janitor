u.bug_console_only = true;

Util.Objects["page"] = new function() {
	this.init = function(page) {

		u.bug("init page:" + page)
		var i, node;


		// make sure page is globally available
//		window.page = page;


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

				// initialize header
				page.initHeader();

				// adds notifier and page.notify function
				u.notifier(page);

				// adds notifier and page.notify function
				u.navigation();
			}
		}


		page.initHeader = function() {

			var janitor = u.ie(this.hN, "ul", {"class":"janitor"});
			u.ae(janitor, u.qs(".servicenavigation .front", page.hN));

			u.ae(page, u.qs(".servicenavigation", page.hN));

			page.nN.sections = u.qsa("ul.sections > li", page.nN);
			if(page.nN.sections) {
				var i, section;
				for(i = 0; section = page.nN.sections[i]; i++) {
					section.header = u.qs("h3", section);
					section.header.section = section;

					u.e.click(section.header);
					section.header.clicked = function() {

						if(this.section.is_open) {
							this.section.is_open = false;

							u.as(this.section, "height", this.offsetHeight+"px");
							u.saveNodeCookie(this.section, "open", 0, {"ignore_classvars":true});
							u.addExpandArrow(this);
						}
						else {
							this.section.is_open = true;

							u.as(this.section, "height", "auto");
							u.saveNodeCookie(this.section, "open", 1, {"ignore_classvars":true});
							u.addCollapseArrow(this);

						}
						
					}

					var state = u.getNodeCookie(section, "open", {"ignore_classvars":true});
					u.bug("state " + u.nodeId(section) + ", " + state)
					if(!state) {
						section.is_open = true;
					}
					section.header.clicked();

				}
			}
			u.bug(page.nN.sections);
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
