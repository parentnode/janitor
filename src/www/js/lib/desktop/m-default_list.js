
Util.Modules["defaultList"] = new function() {
	this.init = function(div) {
		// u.bug("init defaultList:", div);

		var i, node;

		div.list = u.qs("ul.items", div);
		if(!div.list) {
			div.list = u.ae(div, "ul", {"class":"items"});
		}

		// make div available from list
		div.list.div = div;

		// CMS interaction urls
		div.csrf_token = div.getAttribute("data-csrf-token");

		// get all items from list
		div.nodes = u.qsa("li.item", div.list);

		// initial item preparation
		var i, node;
		for(i = 0; node = div.nodes[i]; i++) {

			node._item_id = u.cv(node, "item_id");
			node.div = div;

			// wrap content in div to make alignment easier
			node._text = u.wc(node, "div", {"class":"text"});

		}


		// scroll handler (loads additional content on demand)
		div.scrolled = function() {
//			u.bug("defaultList scrolled")

			var browser_h = u.browserH();
			var scroll_y = u.scrollY();

			// build items in visible area
			var i, node, abs_y, initialized = 0;
			for(i = 0; node = this.nodes[i]; i++) {

				// don't check items already built
				if(!node._ready) {
					abs_y = u.absY(node);

					// check screen position
					if(abs_y - 200 < (scroll_y + browser_h) && (abs_y + 200) > scroll_y) {
						this.buildNode(node);
					}
				}
				else {
					initialized++;
				}
			}

			// cancel scroll handler when all nodes are built
			if(initialized == this.nodes.length && this.scroll_event_id) {
				u.e.removeWindowEvent(this, "scroll", this.scroll_event_id);
				this.scroll_event_id = false;
			}

		}
		// set window scroll handler
		div.scroll_event_id = u.e.addWindowEvent(div, "scroll", div.scrolled);

		// build node, when user scrolls it into view
		div.buildNode = function(node) {
			// u.bug("build node:", node);


			// action injection for predefined action types (to minimize page load and initialization time)
			node._actions = u.qsa(".actions li", node);
			var i, action, form, bn_detele, form_disable, form_enable;
			for(i = 0; action = node._actions[i]; i++) {

				// predefindes actions (status/delete)

				// status
				if(u.hc(action, "status")) {

					// inject standard item status form if node is empty
					if(!action.childNodes.length) {

						action.update_status_url = action.getAttribute("data-item-status");
						if(action.update_status_url) {
							// add disable form
							form_disable = u.f.addForm(action, {"action":action.update_status_url+"/"+node._item_id+"/0", "class":"disable"});
							u.f.addField(form_disable, {"type":"hidden", "name":"csrf-token", "value":this.csrf_token});
							u.f.addAction(form_disable, {"value":"Disable", "class":"button status"});

							// add enable form
							form_enable = u.f.addForm(action, {"action":action.update_status_url+"/"+node._item_id+"/1", "class":"enable"});
							u.f.addField(form_enable, {"type":"hidden", "name":"csrf-token", "value":this.csrf_token});
							u.f.addAction(form_enable, {"value":"Enable", "class":"button status"});
						}
					}
					// look for valid forms
					else {
						form_disable = u.qs("form.disable", action);
						form_enable = u.qs("form.enable", action);
					}

					// init if forms are available
					if(form_disable && form_enable) {

						// initialize disable form
						u.f.init(form_disable);
						form_disable.submitted = function() {
							this.response = function(response) {
								page.notify(response);
								if(response.cms_status == "success") {
									u.ac(this.parentNode, "disabled");
									u.rc(this.parentNode, "enabled");
								}
							}
							u.request(this, this.action, {"method":"post", "params":u.f.getParams(this)});
						}

						// initialize enable form
						u.f.init(form_enable);
						form_enable.submitted = function() {
							this.response = function(response) {
								page.notify(response);
								if(response.cms_status == "success") {
									u.rc(this.parentNode, "disabled");
									u.ac(this.parentNode, "enabled");
								}
							}
							u.request(this, this.action, {"method":"post", "params":u.f.getParams(this)});
						}
					}
				}
				else if(u.hc(action, "delete")) {

					action.node = node;

					u.m.oneButtonForm.init(action);
					// default sucessful delete action
					action.confirmed = function(response) {

						if(response.cms_status == "success") {
							// check for constraint error preventing row from actually being deleted
							if(response.cms_object && response.cms_object.constraint_error) {
								u.ac(this.form.confirm_submit_button, "disabled");
							}
							else {
								this.node.parentNode.removeChild(this.node);
								this.node.div.scrolled();

								// will only apply if items has draggable classes
								if(fun(this.node.div.list.updateDraggables)) {
									this.node.div.list.updateDraggables();
								}
								// u.sortable(this.node.div.list, {"targets":".items", "draggables":".draggable"});
							}
						}
					}
				}
			}



			// list indicates image list
			if(div._images) {

				// apply correct padding
				u.ass(node._text, {
					"padding-left": div._node_padding_left + "px"
				});

				// get image data
				node._format = u.cv(node, "format");
				node._variant = u.cv(node, "variant");

				// create image path
				if(node._format && node.div._media_width) {
					node._image_src = "/images/"+node._item_id+"/"+(node._variant ? node._variant+"/" : "")+node.div._media_width+"x."+node._format;
				}
				else {
					node._image_src = "/images/0/missing/"+node.div._media_width+"x.png";
				}

				// inject image
				if(node._image_src) {
					u.as(node, "backgroundImage", "url("+node._image_src+")");
				}

			}

			// list indicates audio list
			else if(div._audios) {

				// apply correct padding
				u.ass(node._text, {
					"padding-left": div._node_padding_left + "px"
				});

				// get image data
				node._format = u.cv(node, "format");
				node._variant = u.cv(node, "variant");
				if(node._format) {

					// inject audio player div
					node._audio = u.ie(node, "div", {"class":"audioplayer"});

					// make sure global audio player is available
					if(!page.audioplayer) {
						page.audioplayer = u.audioPlayer();
					}
					node._audio.scene = this;
					node._audio.url = "/audios/"+node._item_id+"/" + (node._variant ? node._variant+"/" : "") + "128."+node._format;

					// click toggles play
					u.e.click(node._audio);
					node._audio.clicked = function(event) {

						if(!u.hc(this.parentNode, "playing")) {
							// add global audio player for sound effects

							var node, i;
							for(i = 0; node = this.scene.nodes[i]; i++) {
								u.rc(node, "playing");
							}

							page.audioplayer.loadAndPlay(this.url);
							u.ac(this.parentNode, "playing");
						}
						else {
							page.audioplayer.stop();
							u.rc(this.parentNode, "playing");
						}
					}
				}
				// not enough information available
				else {
					u.ac(audio, "disabled");
				}

			}

			// TODO: inject video preview
			// list indicates video list
			else if(div._videos) {

			}

			// node is ready
			node._ready = true;

			// ensure all available nodes are built
			node.div.scrolled();
		}



		// images, videos or audios in list
		// actual media injection will happen on buildNode
		if(u.hc(div, "images") || u.hc(div, "videos") || u.hc(div, "audios")) {

			// declare markers
			div._images = u.hc(div, "images");
			div._videos = u.hc(div, "videos");
			div._audios = u.hc(div, "audios");

			// store image width (default 100px)
			div._media_width = u.cv(div, "width") ? u.cv(div, "width") : (div._audios ? 40 : 100);

			// calculate left padding for node._text
			div._node_padding_left = ((Number(div._media_width) - 15) + ((u.hc(div, "sortable") && div.list) ? 25 : 0));
		}



		// taggable list
		if(u.hc(div, "taggable")) {
//			u.bug("init taggable")


			div.add_tag_url = div.getAttribute("data-tag-add");
			div.delete_tag_url = div.getAttribute("data-tag-delete");
			div.get_tags_url = div.getAttribute("data-tag-get");


			// do we have required info 
			if(div.csrf_token && div.get_tags_url && div.delete_tag_url && div.add_tag_url) {
			
				// tags received
				div.tagsResponse = function(response) {

					// valid tags response
					if(response.cms_status == "success" && response.cms_object) {
						this.all_tags = response.cms_object;

					}
					// error getting tags (could be no tags exists in system)
					else {
						page.notify(response);
						this.all_tags = [];
					}

					// minimum work in first run
					// only inject add-button in first run
					var i, node;
					for(i = 0; node = this.nodes[i]; i++) {

						// map a data-div reference to share tag-functionality between edit and list pages
						node.data_div = this;

						// ensure tag-list existence
						node._tags = u.qs("ul.tags", node);
						if(!node._tags) {
							node._tags = u.ae(node, "ul", {"class":"tags"});
						}
						node._tags.div = this;

						// inject add button
						node._bn_add = u.ae(node._tags, "li", {"class":"add","html":"+"});
						node._bn_add.node = node;

						// enable tagging
						u.enableTagging(node);

					}
				}
				// get all tags from server
				u.request(div, div.get_tags_url, {"callback":"tagsResponse", "method":"post", "params":"csrf-token=" + div.csrf_token});
			}
		}



		// add filters to list
		if(u.hc(div, "filters")) {

			u.defaultFilters(div);

			// callback from list filter
			div.filtered = function() {
				this.scrolled();

				// If list is selectable
				if(this.bn_all) {
					this.bn_all.updateState();
					this.bn_range._to.value = "";
					this.bn_range._from.value = "";
				}
			}


		}



		// sortable list
		if(u.hc(div, "sortable")) {

			u.defaultSortableList(div.list);

		}


		// selectable list
		if(u.hc(div, "selectable")) {

			u.defaultSelectable(div);

		}



		// invoke appropiate item loading
		div.scrolled();

	}
}
