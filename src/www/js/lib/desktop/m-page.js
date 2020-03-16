u.bug_console_only = true;

Util.Modules["page"] = new function() {
	this.init = function(page) {
		// u.bug("init page:",  page);

		window.page = page;

		// show parentnode comment in console
		u.bug_force = true;
		u.bug("This site is built using the combined powers of body, mind and spirit. Well, and also Manipulator, Janitor and Detector");
		u.bug("Visit https://parentnode.dk for more information");
//		u.bug("Free lunch for new contributers ;-)");
		u.bug_force = false;


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


		// global resized handler 
		page.resized = function() {
			// u.bug("page resized");

			this.browser_h = u.browserH();
			this.browser_w = u.browserW();

			// forward resize event to current scene
			if(this.cN && this.cN.scene && fun(this.cN.scene.resized)) {
				this.cN.scene.resized();
			}

		}

		// global scroll handler 
		page.scrolled = function() {
			// u.bug("page scrolled");

			this.scroll_y = u.scrollY();

			// forward scroll event to current scene
			if(this.cN && this.cN.scene && fun(this.cN.scene.scrolled)) {
				this.cN.scene.scrolled();
			}

		}

		page.orientationchanged = function() {

			// forward scroll event to current scene
			if(this.cN && this.cN.scene && fun(this.cN.scene.orientationchanged)) {
				this.cN.scene.orientationchanged();
			}
		}

		// Page is ready - called from several places, evaluates when page is ready to be shown
		page.ready = function() {
			// u.bug("page ready");

			// page is ready to be shown - only initalize if not already shown
			if(!this.is_ready) {

				// page is ready
				this.is_ready = true;

				// set resize handler
				u.e.addEvent(window, "resize", this.resized);
				// set scroll handler
				u.e.addEvent(window, "scroll", this.scrolled);
				// set orientation change handler
				u.e.addEvent(window, "orientationchange", this.orientationchanged);

				// initialize header
				this.initHeader();

				// adds notifier and page.notify function
				u.notifier(this);

				// adds popstate navigation and page.cN.navigate callback
				u.navigation();

				// initial resize
				this.resized();
			}
		}

		// TODO: dummy navigation handler - just refreshes the page
		page.cN.navigate = function(url) {

			u.bug("page.navigated:", url);
			location.href = url;

		}

		page.initHeader = function() {

			var janitor = u.ie(this.hN, "ul", {"class":"janitor"});
			u.ae(janitor, u.qs(".servicenavigation .front", this.hN));

			// prepare janitor text for animation
			var janitor_text = u.qs("li a", janitor);
			if(janitor_text) {
				janitor_text.innerHTML = "<span>"+janitor_text.innerHTML.split("").join("</span><span>")+"</span>"; 
				page.hN.janitor_spans = u.qsa("span", janitor_text);

				var i, span, j, section, node;
				// set up navigation initial state
				for(i = 0; span = page.hN.janitor_spans[i]; i++) {

					if(i == 0) {
						u.ass(span, {
							"transform":"translate(-8px, 0)"
						});
					}
					else {
						u.ass(span, {
							"opacity":0,
							"transform":"translate(-8px, -30px)"
						});
					}
				}
				u.ass(janitor_text, {"opacity": 1});
			}


			u.ae(this, u.qs(".servicenavigation", this.hN));


			var sections = u.qsa("ul.navigation > li", this.nN);
			if(sections.length) {
				for(i = 0; section = sections[i]; i++) {

					// nested navigation structure
					section.header = u.qs("h3", section);
					if(section.header) {

						section.nodes = u.qsa("li", section);

						// section can be empty, if user doesn't have sufficient permissions
						if(section.nodes.length) {

							// make individual navigation nodes clickable and collapse navigation on click to make transition look nicer



							if(section.header) {
								section.header.section = section;


								u.e.click(section.header);
								section.header.clicked = function(event) {
									u.e.kill(event);

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

							// look for selected node
							for(j = 0; node = section.nodes[j]; j++) {
								u.ce(node, {"type":"link"});

								// set selected state (now being done backend)
								// open selected navigation
								if(u.hc(node, "selected|path")) {
								// if(u.hc(node, document.body.className)) {
									// u.ac(node, "selected");

									// make sure current section is open
									if(!section.is_open) {
										section.header.clicked();
									}
								}
							}

						}
						// empty section
						else {
							u.ac(section, "empty");
						}

					}
					// plain navigation item
					else {

						u.ce(section, {"type":"link"});

						// set selected state (Now done in backend)
						// if(u.hc(section, document.body.className)) {
						// 	u.ac(section, "selected");
						// }

					}

				}

			}

			// avoid accidental clicking
			if(this.nN) {
				u.ass(this.nN, {
					"display":"none"
				});
			}

			if(sections.length && janitor_text) {
				// enable collapsed navigation
				if(u.e.event_support == "mouse") {

					u.e.hover(this.hN, {"delay_over":300});
				
				}
				// touch enabled devices should not use hover method
				else {

					u.e.click(page.hN);
					page.hN.clicked = function(event) {

						// open navigation if it is not already open
						if(!this.is_open) {
							u.e.kill(event);
							this.over();
						}

					}

					// close open navigation when clicking on window
					page.hN.close = function(event) {

						if(this.is_open) {
							u.e.kill(event);
							this.out();
						}
					}
					u.e.addWindowEndEvent(page.hN, "close");

				}

				page.hN.over = function() {

					this.is_open = true;
					u.a.transition(page.nN, "none");
					page.nN.transitioned = null;

					u.t.resetTimer(this.t_navigation);

					u.a.transition(this, "all 0.3s ease-in-out");
					u.ass(this, {
						"width":"230px"
					});

					u.ass(page.nN, {
						"display":"block"
					});
					u.a.transition(page.nN, "all 0.3s ease-in");
					u.ass(page.nN, {
						"opacity":1
					});

					for(i = 0; span = page.hN.janitor_spans[i]; i++) {

						if(i == 0) {
							u.a.transition(span, "all 0.2s ease-in " + (i*50) + "ms");
							u.ass(span, {
								"transform":"translate(0, 0)"
							});
						}
						else {
							u.a.transition(span, "all 0.2s ease-in " + (i*50) + "ms");
							u.ass(span, {
								"opacity":1,
								"transform":"translate(0, 0)"
							});
						}
					}

				}

				page.hN.out = function() {

					this.is_open = false;
					u.a.transition(page.nN, "none");
					page.nN.transitioned = null;


					var span, i;
					for(i = 0; span = page.hN.janitor_spans[i]; i++) {

						if(i == 0) {
							u.a.transition(span, "all 0.2s ease-in " + ((page.hN.janitor_spans.length-i)*50) + "ms");
							u.ass(span, {
								"transform":"translate(-8px, 0)"
							});
						}
						else {
							u.a.transition(span, "all 0.2s ease-in " + ((page.hN.janitor_spans.length-i)*50) + "ms");
							u.ass(span, {
								"opacity":0,
								"transform":"translate(-8px, -30px)"
							});
						}
					}

					// avoid accidental clicking
					page.nN.transitioned = function() {
	//					u.bug("hide me")
						u.ass(this, {
							"display":"none"
						});
					}

					u.a.transition(page.nN, "all 0.2s ease-in");
					u.ass(page.nN, {
						"opacity":0
					});

					u.a.transition(this, "all 0.2s ease-in-out 300ms");
					u.ass(this, {
						"width":"30px"
					});

				}
			}

			
//			page.hN.t_navigation = u.t.setTimer(page.hN, "out", 500);

		}


		page.ready();
	}
}

u.e.addDOMReadyEvent(u.init)
