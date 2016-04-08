u.bug_force = true;
u.bug_console_only = true;

Util.Objects["page"] = new function() {
	this.init = function(page) {

		window.page = page;

		// show parentnode comment in console
		u.bug_force = true;
		u.bug("think.dk is built using Manipulator, Janitor and Detector");
		u.bug("Visit http://parentnode.dk for more information");
		u.bug("Free lunch for new contributers ;-)");
		u.bug_force = false;


		// header reference
		page.hN = u.qs("#header");
		page.hN.service = u.qs(".servicenavigation", page.hN);
		page.hN = u.ae(document.body, page.hN);
		u.e.drag(page.hN, page.hN);


		// add title input field to navigation
		page._title = u.ae(page.hN, "div", {"class":"title"});


		// content reference
		page.cN = u.qs("#content", page);


		// navigation reference
		page.nN = u.qs("#navigation", page);
		page.nN = u.ae(document.body, page.nN);


		// footer reference
		page.fN = u.qs("#footer");
		page.fN.service = u.qs(".servicenavigation", page.fN);


		// global resize handler 
		page.resized = function() {
//			u.bug("page resized")

			page.browser_h = u.browserH();
			page.browser_w = u.browserW();

			// adjust content height
			this.calc_height = u.browserH();
			this.calc_width = u.browserW();
			this.available_height = this.calc_height - page.hN.offsetHeight - page.fN.offsetHeight;

			u.as(page.cN, "height", "auto", false);
			if(this.available_height >= page.cN.offsetHeight) {
				u.as(page.cN, "height", this.available_height+"px", false);
			}

			page.bn_nav.close();
			u.as(page.nN, "width", (page.offsetWidth - page.bn_nav.offsetWidth) + "px");
			u.as(page.nN, "height", (window.innerHeight) + "px");
			u.a.translate(page.nN, -(page.offsetWidth - page.bn_nav.offsetWidth), 0);


			// forward resize event to current scene
			if(page.cN && page.cN.scene) {

				if(typeof(page.cN.scene.resized) == "function") {
					page.cN.scene.resized();
				}

			}

		}

		// global scroll handler 
		page.scrolled = function() {

			// forward scroll event to current scene
			if(page.cN && page.cN.scene && typeof(page.cN.scene.scrolled) == "function") {
				page.cN.scene.scrolled();
			}

		}

		page.orientationchanged = function() {
			// resize navigation if it is open
			if(u.hc(page.bn_nav, "open")) {
				u.as(page.hN, "height", window.innerHeight + "px");
			}

			// forward scroll event to current scene
			if(page.cN && page.cN.scene && typeof(page.cN.scene.orientationchanged) == "function") {
				page.cN.scene.orientationchanged();
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
				// set orientation change handler
				u.e.addEvent(window, "orientationchange", page.orientationchanged);

				// initialize navigation/header
				page.initNavigation();

				// adds notifier and page.notify function
				u.notifier(page);

				// adds notifier and page.notify function
				u.navigation();

				// initial resize
				page.resized();
			}
		}

		// TODO: dummy navigation handler - just refreshes the page
		page.cN.navigate = function(url) {
			
			u.bug("page.navigated");
			location.href = url;

		}

		// initialize navigation elements
		page.initNavigation = function() {

			page.nN.list = u.qs("ul.navigation", page.nN);

			page.bn_nav = u.qs(".servicenavigation li.front", page.hN);
			u.ae(page.bn_nav, "div");
			u.ae(page.bn_nav, "div");
			u.ae(page.bn_nav, "div");

			u.ce(page.bn_nav);
			page.bn_nav.clicked = function(event) {
				if(u.hc(this, "open")) {

					this.close();
				}
				else {
					u.ac(this, "open");

					u.a.transition(page, "all 0.2s ease-in-out");
					u.a.transition(page.nN, "all 0.2s ease-in-out");
					u.a.transition(page.hN, "all 0.2s ease-in-out");

					u.a.translate(page, page.offsetWidth - this.offsetWidth, 0);
					u.a.translate(page.hN, page.offsetWidth - this.offsetWidth, 0);
					u.a.translate(page.nN, 0, 0);

					page.nN.start_drag_y = (window.innerHeight - 100) - page.nN.list.offsetHeight;
					page.nN.end_drag_y = page.nN.list.offsetHeight;
				}
			}
			page.bn_nav.close = function(event) {
				u.rc(this, "open");

				u.a.transition(page, "all 0.2s ease-in-out");
				u.a.transition(page.nN, "all 0.2s ease-in-out");
				u.a.transition(page.hN, "all 0.2s ease-in-out");

				u.a.translate(page, 0, 0);
				u.a.translate(page.hN, 0, 0);
				u.a.translate(page.nN, - (page.offsetWidth - this.offsetWidth), 0);

			}


			// append footer servicenavigation to header servicenavigation
			if(page.fN.service) {
				nodes = u.qsa("li:not(.copyright)", page.fN.service);
				for(i = 0; node = nodes[i]; i++) {
					u.ae(page.nN.list, node);
				}
				page.fN.removeChild(page.fN.service);
			}

			// append header servicenavigation to header servicenavigation
			if(page.hN.service) {
				nodes = u.qsa("li:not(.front)", page.hN.service);
				for(i = 0; node = nodes[i]; i++) {
					u.ae(page.nN.list, node);
				}
			}



			u.ass(page.nN, {
				"width": (page.offsetWidth - page.bn_nav.offsetWidth) + "px",
				"height": (window.innerHeight) + "px"
			});
			u.a.translate(page.nN, -(page.offsetWidth - page.bn_nav.offsetWidth), 0);
			u.ass(page.nN, {
				"display": "block",
				"opacity": 1
			});
//			u.as(page.hN, "height", "60px");


			u.e.drag(page.nN.list, [0, (window.innerHeight) - page.nN.list.offsetHeight, page.nN.offsetWidth, page.nN.list.offsetHeight], {"strict":false, "elastica":200, "vertical_lock":true});


			var sections = u.qsa("ul.navigation > li", page.nN);
			if(sections) {
				for(i = 0; section = sections[i]; i++) {

					// nested navigation structure
					section.nodes = u.qsa("li", section);
					if(section.nodes.length) {

						// make individual navigation nodes clickable and collapse navigation on click to make transition look nicer
						for(j = 0; node = section.nodes[j]; j++) {
							u.ce(node, {"type":"link"});

							// set selected state
							if(u.hc(node, document.body.className)) {
								u.ac(node, "selected");
							}
						}


						section.header = u.qs("h3", section);
						if(section.header) {
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
							if(!state) {
								section.is_open = true;
							}
							section.header.clicked();

						}

					}
					else {

						u.ce(section, {"type":"link"});

						// set selected state
						if(u.hc(section, document.body.className)) {
							u.ac(section, "selected");
						}

					}

				}

			}

		}


		// ready to start page builing process
		page.ready();

	}
}

u.e.addDOMReadyEvent(u.init);

