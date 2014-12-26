u.bug_force = true;
u.bug_console_only = true;

Util.Objects["page"] = new function() {
	this.init = function(page) {

		// header reference
		page.hN = u.qs("#header");
		page.hN.service = u.qs(".servicenavigation", page.hN);
		u.e.drag(page.hN, page.hN);

		// add logo to navigation
		page.title = u.ae(page.hN, "a", {"class":"title", "html":u.qs("h1").innerHTML});

		// content reference
		page.cN = u.qs("#content", page);

		// navigation reference
		page.nN = u.qs("#navigation", page);
		page.nN.list = u.qs("ul", page.nN);
		page.nN = u.ie(page.hN, page.nN);
		u.ie(page.nN.list, u.qs(".front", page.hN.service));

		// footer reference
		page.fN = u.qs("#footer");
		// move li to #header .servicenavigation
		page.fN.service = u.qs(".servicenavigation", page.fN);

		u.ce(page.fN.service);
		page.fN.service.clicked = function(event) {
			window.open("http://parentnode.dk");
		}


		// global resize handler 
		page.resized = function() {

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
			if(u.hc(page.bn_nav, "open")) {
				u.as(page.hN, "height", window.innerHeight + "px");
			}
		}
		


		// Page is ready - called from several places, evaluates when page is ready to be shown
		page.ready = function() {
//				u.bug("page ready")

			// page is ready to be shown - only initalize if not already shown
			if(!u.hc(this, "ready")) {

				// page is ready
				u.addClass(this, "ready");

				// set resize handler
				u.e.addEvent(window, "resize", page.resized);
				// set scroll handler
				u.e.addEvent(window, "scroll", page.scrolled);
				// set orientation change handler
				u.e.addEvent(window, "orientationchange", page.orientationchanged);

				this.initNavigation();

				this.resized();
			}
		}


		// initialize navigation elements
		page.initNavigation = function() {

			this.bn_nav = u.qs(".servicenavigation li.navigation", this.hN);
			u.ae(this.bn_nav, "div");
			u.ae(this.bn_nav, "div");
			u.ae(this.bn_nav, "div");

			u.ce(this.bn_nav);
			this.bn_nav.clicked = function(event) {
				if(u.hc(this, "open")) {

					this.close();
				}
				else {
					u.ac(this, "open");

					u.a.transition(page, "all 0.5s ease-in-out");
					u.a.translate(page, page.offsetWidth - this.offsetWidth, 0);

					page.nN.start_drag_y = (window.innerHeight - 100) - page.nN.list.offsetHeight;
					page.nN.end_drag_y = page.nN.list.offsetHeight;
				}
			}
			this.bn_nav.close = function(event) {
				u.rc(this, "open");

				u.a.transition(page, "all 0.5s ease-in-out");
				u.a.translate(page, 0, 0);
			}


			u.as(this.nN, "width", (this.offsetWidth - this.bn_nav.offsetWidth) + "px");
			u.as(this.nN, "height", (window.innerHeight) + "px");
			u.a.translate(this.nN, -(this.offsetWidth - this.bn_nav.offsetWidth), 0);
			u.as(page.nN, "display", "block");
			u.as(page.hN, "height", "60px");


			u.e.drag(this.nN.list, [0, (window.innerHeight) - this.nN.list.offsetHeight, this.nN.offsetWidth, this.nN.list.offsetHeight], {"strict":false, "elastica":200, "vertical_lock":true});


			var i, node;
			// enable submenus where relevant
			this.hN.nodes = u.qsa("#navigation li", page.hN);
			for(i = 0; node = this.hN.nodes[i]; i++) {

				// build first living proof model of CEL clickableElementLink
				u.ce(node, {"type":"link"});
			}

		}


		// ready to start page builing process
		page.ready();

	}
}

u.e.addDOMReadyEvent(u.init);

