
Util.Objects["defaultList"] = new function() {
	this.init = function(div) {
//		u.bug("init defaultList:" + u.nodeId(div))

		var i, node;

		div.list = u.qs("ul.items", div);
		if(!div.list) {
			div.list = u.ae(div, "ul", {"class":"items"});
		}

		// make div available from list
		div.list.div = div;

		// CMS interaction urls
		div.csrf_token = div.getAttribute("data-csrf-token");


		div.nodes = u.qsa("li.item", div);
		//scene.list.scene = scene;


		div.scrolled = function() {
			var scroll_y = u.scrollY()
			var browser_h = u.browserH();

			var i, node, abs_y;
			for(i = 0; node = this.nodes[i]; i++) {

				abs_y = u.absY(node);

//				u.bug("build Node:" + (abs_y - 200) + "<" + (scroll_y+browser_h) + " && " + (abs_y + 200) + ">" +  scroll_y);

				if(!node._ready && abs_y - 200 < scroll_y+browser_h && abs_y + 200 > scroll_y) {
					this.buildNode(node);
				}
			}
		}

		// executed on window
		div._scrollHandler = function() {
			u.t.resetTimer(this.t_scroll);
//			div.t_scroll = u.t.setTimer(div, div.scrolled, 100);
			this.scrolled();
		}
		// set window scroll handler
		var event_id = u.e.addWindowScrollEvent(div, div._scrollHandler);


		div.buildNode = function(node) {
//			u.bug("build node")

			node._item_id = u.cv(node, "item_id");
			node._variant = u.cv(node, "variant");
			node.div = this;


			// action injection for predefined action types (to minimize page load and initialization time)
			node._actions = u.qsa(".actions li", node);
			var i, action, form, bn_detele, form_disable, form_enable;
			for(i = 0; action = node._actions[i]; i++) {
				// do not inject if li already has content

				// predefindes actions

				// status
				if(u.hc(action, "status")) {

					// inject standard item status form if node is empty
					if(!action.childNodes.length) {

						action.update_status_url = action.getAttribute("data-update-status");
						if(action.update_status_url) {
							form_disable = u.f.addForm(action, {"action":action.update_status_url+"/"+node._item_id+"/0", "class":"disable"});
							u.ae(form_disable, "input", {"type":"hidden","name":"csrf-token", "value":this.csrf_token});
							u.f.addAction(form_disable, {"value":"Disable", "class":"button status"});
							form_enable = u.f.addForm(action, {"action":action.update_status_url+"/"+node._item_id+"/1", "class":"enable"});
							u.ae(form_enable, "input", {"type":"hidden","name":"csrf-token", "value":this.csrf_token});
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

					// inject standard item delete form if node is empty
					if(!action.childNodes.length) {

						action.delete_item_url = action.getAttribute("data-delete-item");
						if(action.delete_item_url) {
							form = u.f.addForm(action, {"action":action.delete_item_url, "class":"delete"});
							u.ae(form, "input", {"type":"hidden","name":"csrf-token", "value":this.csrf_token});
							form.node = node;
							bn_delete = u.f.addAction(form, {"value":"Delete", "class":"button delete", "name":"delete"});
						}
					}
					// look for valid forms
					else {
						form = u.qs("form", action);
						form.node = node;
					}

					// init if form is available
					if(form) {
						u.f.init(form);

						form.restore = function(event) {
							this.actions["delete"].value = "Delete";
							u.rc(this.actions["delete"], "confirm");
						}
	
						form.submitted = function() {

							// first click
							if(!u.hc(this.actions["delete"], "confirm")) {
								u.ac(this.actions["delete"], "confirm");
								this.actions["delete"].value = "Confirm";
								this.t_confirm = u.t.setTimer(this, this.restore, 3000);
							}
							// confirm click
							else {
								u.t.resetTimer(this.t_confirm);


								this.response = function(response) {
									page.notify(response);

									if(response.cms_status == "success") {
										// check for constraint error preventing row from actually being deleted
										if(response.cms_object && response.cms_object.constraint_error) {
											this.value = "Delete";
											u.ac(this, "disabled");
										}
										else {
											this.node.parentNode.removeChild(this.node);
											this.node.div.scrolled();
										}
									}
								}
								u.request(this, this.action, {"method":this.method, "params":u.f.getParams(this)});
							}
						}
					}
				}
			}


			// show node image
			node._image = u.cv(node, "image");
			node._width = u.cv(node, "width");
			node._height = u.cv(node, "height");
			if(node._image && node._width && node._height) {
				u.ac(node, "image");
				node._image_src = "/images/"+node._item_id+"/"+(node._variant ? node._variant+"/" : "")+node._width+"x"+node._height+"."+node._image;
			}
			else if(node._image && node._width) {
				u.ac(node, "image");
				node._image_src = "/images/"+node._item_id+"/"+(node._variant ? node._variant+"/" : "")+node._width+"x."+node._image;
			}
			else if(node._image && node._height) {
				u.ac(node, "image");
				node._image_src = "/images/"+node._item_id+"/"+(node._variant ? node._variant+"/" : "")+"x"+node._height+"."+node._image;
			}

			if(node._image_src) {
				u.as(node, "backgroundImage", "url("+node._image_src+")");
			}


			// show audio player
			node._audio = u.cv(node, "audio");
			if(node._audio) {
				u.ac(node, "audio");

				if(!page.audioplayer) {
					page.audioplayer = u.audioPlayer();
				}
				var audio = u.ie(node, "div", {"class":"audio"});
				audio.scene = this;
				audio.url = "/audios/"+node._item_id+"/128."+node._audio;

				u.e.click(audio);
				audio.clicked = function(event) {

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

			// TODO: inject video preview
			node._video = u.cv(node, "video");
			if(node._video) {

			}


			node._ready = true;
		}


		// taggable list
		if(u.hc(div, "taggable")) {
			u.bug("init taggable")

			div.add_tag_url = div.getAttribute("data-add-tag");
			div.delete_tag_url = div.getAttribute("data-delete-tag");
			div.get_tags_url = div.getAttribute("data-get-tags");


			if(div.get_tags_url && div.delete_tag_url && div.add_tag_url) {
			
				// tags received
				div.tagsResponse = function(response) {
//					u.bug("response:" + response);

					if(response.cms_status == "success" && response.cms_object) {
						this.all_tags = response.cms_object;

						var i, node, tag, j, bn_add, context, value;
//						u.bug("nodes:" + this.scene.nodes);
						// minimum work in first run
						// only inject add-button in first run
						for(i = 0; node = this.nodes[i]; i++) {

							node._tags = u.qs("ul.tags", node);
							if(!node._tags) {
								node._tags = u.ae(node, "ul", {"class":"tags"});
							}
							node._bn_add = u.ae(node._tags, "li", {"class":"add","html":"+"});
							node._bn_add.div = this;
							node._bn_add.node = node;
							u.e.click(node._bn_add);
							node._bn_add.clicked = function() {
								this.div.taggableNode(this.node);
							}
						}
					}
					else {
						page.notify(response);
					}
				}
				u.request(div, div.get_tags_url, {"callback":"tagsResponse", "method":"post", "params":"csrf-token=" + div.csrf_token});


				// inject tag system into node
				// done when user clicks add-button
				div.taggableNode = function(node) {

					u.ac(node, "addtags");

					// update add button
					node._bn_add.innerHTML = "-";
					node._bn_add.clicked = function() {
						this.innerHTML = "+";
						u.rc(this.node, "addtags");

						// remove tag set to avoid bloated HTML
						this.node._tag_options.parentNode.removeChild(this.node._tag_options);

						this.clicked = function() {
							this.div.taggableNode(this.node);
						}
					}

					// insert add tag options div
					node._tag_options = u.ae(node, "div", {"class":"tagoptions"});

					// insert tags filter
					node._tag_options._field = u.ae(node._tag_options, "div", {"class":"field"});

					node._tag_options._tagfilter = u.ae(node._tag_options._field, "input", {"class":"filter ignoreinput"});
					node._tag_options._tagfilter.node = node;

					node._tag_options._tagfilter.onkeyup = function() {

						if(this.node._new_tags) {
							var tags = u.qsa(".tag", this.node._new_tags);
							var i, tag;
							for(i = 0; tag = tags[i]; i++) {
								if(tag.textContent.toLowerCase().match(this.value.toLowerCase())) {
									u.as(tag, "display", "inline-block");
								}
								else {
									u.as(tag, "display", "none");
								}
							}
						}
				
					}

					node._new_tags = u.ae(node._tag_options, "ul", {"class":"tags"});

					// index existing tags
					var itemTags = u.qsa("li:not(.add)", node._tags);
					var usedTags = {};

					for(j = 0; tag = itemTags[j]; j++) {
						tag._context = u.qs(".context", tag).innerHTML;
						tag._value = u.qs(".value", tag).innerHTML;

						if(!usedTags[tag._context]) {
							usedTags[tag._context] = {}
						}
						if(!usedTags[tag._context][tag._value]) {
							usedTags[tag._context][tag._value] = tag;
						}
					}

					// create new tags list
					for(tag in this.all_tags) {

						context = this.all_tags[tag].context;
						// replace single & with entity or it is not recognized
						value = this.all_tags[tag].value.replace(/ & /, " &amp; ");

//						u.bug("context:value:" + context + ":" + value)

						if(usedTags && usedTags[context] && usedTags[context][value]) {
							tag_node = usedTags[context][value];
						}
						else {
							tag_node = u.ae(node._new_tags, "li", {"class":"tag"});
							tag_node._context = context;
							tag_node._value = value;
							u.ae(tag_node, "span", {"class":"context", "html":tag_node._context});
							u.ae(tag_node, "span", {"class":"value", "html":tag_node._value});
						}
						tag_node.new_tags = this;

						tag_node._id = this.all_tags[tag].id;
						tag_node.node = node;

						u.e.click(tag_node);
						tag_node.clicked = function() {
//							u.bug("tag clicked:" + tag_node._context+":"+tag_node._value);

							// delete tag
							if(u.hc(this.node, "addtags")) {

								// remove tag
								if(this.parentNode == this.node._tags) {

									this.response = function(response) {

										// Notify of event
										page.notify(response);

										if(response.cms_status == "success") {
											// add tag to newtags
											u.ae(this.node._new_tags, this);
										}
									}
									u.request(this, this.node.div.delete_tag_url+"/"+this.node._item_id+"/" + this._id, {"method":"post", "params":"csrf-token=" + this.node.div.csrf_token});
								}
								// else add tag
								else {

									this.response = function(response) {

										// Notify of event
										page.notify(response);

										if(response.cms_status == "success") {
											// add tag to tags
											u.ie(this.node._tags, this);
										}
									}
									u.request(this, this.node.div.add_tag_url+"/"+this.node._item_id, {"method":"post", "params":"tags="+this._id+"&csrf-token=" + this.node.div.csrf_token});
								}
							}
						}
					}
				}
			}
			else {
				u.rc(div, "taggable");
			}
		}


		// add filters to list
		if(u.hc(div, "filters")) {

			div._filter = u.ie(div, "div", {"class":"filter"});

			// index list, to speed up filtering process
			var i, node;
			for(i = 0; node = div.nodes[i]; i++) {
				node._c = node.textContent.toLowerCase();
			}

			// insert tags filter
			div._filter._field = u.ae(div._filter, "div", {"class":"field"});
			u.ae(div._filter._field, "label", {"html":"Filter"});

			div._filter._input = u.ae(div._filter._field, "input", {"class":"filter ignoreinput"});
			div._filter._input._div = div;

			div._filter._input.onkeydown = function() {
//				u.bug("reset timer")
				u.t.resetTimer(this._div.t_filter);
			}
			div._filter._input.onkeyup = function() {
//				u.bug("set timer")
				this._div.t_filter = u.t.setTimer(this._div, this._div.filter, 500);
				u.ac(this._div._filter, "filtering");
			}
			div.filter = function() {

				var i, node;
				if(this._current_filter != this._filter._input.value.toLowerCase()) {
//					u.bug("filter by:" + this._filter._input.value)

					this._current_filter = this._filter._input.value.toLowerCase();
					for(i = 0; node = this.nodes[i]; i++) {

						if(node._c.match(this._current_filter)) {
							u.as(node, "display", "block", false);
						}
						else {
							u.as(node, "display", "none", false);
						}
					}
				}

				u.rc(this._filter, "filtering");

				// invoke appropriate image loading
				this.scrolled();
			}
		}


		// sortable list
		if(u.hc(div, "sortable") && div.list) {

			div.save_order_url = div.getAttribute("data-save-order");

			if(div.save_order_url) {

				u.s.sortable(div.list);
				div.list.picked = function() {}
				div.list.dropped = function() {
					var order = new Array();

					this.nodes = u.qsa("li.item", this);
					for(i = 0; node = this.nodes[i]; i++) {
						order.push(u.cv(node, "id"));
					}
					this.orderResponse = function(response) {
						// Notify of event
						page.notify(response);
					}
					u.request(this, this.div.save_order_url, {"callback":"orderResponse", "method":"post", "params":"csrf-token=" + this.div.csrf_token + "&order=" + order.join(",")});
				}

			}
			else {
				u.rc(div, "sortable");
			}

		}


		// invoke appropiate image loading
		div.scrolled();

	}
}
