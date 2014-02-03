
Util.Objects["defaultList"] = new function() {
	this.init = function(div) {
		u.bug("init defaultList")

		var i, node;

		div.list = u.qs("ul.items", div);
		div.nodes = u.qsa("li.item", div);
		//scene.list.scene = scene;


		div.scrolled = function() {
			var scroll_y = u.scrollY()
			var browser_h = u.browserH();

			var i, node, abs_y;
			for(i = 0; node = this.nodes[i]; i++) {

				abs_y = u.absY(node);

				if(!node._ready && node._image_src && abs_y - 200 < scroll_y+browser_h && abs_y + 200 > scroll_y) {
//					u.bug("load image:" + i);

					u.as(node, "backgroundImage", "url("+node._image_src+")");
					node._ready = true;

				}
			}
//			u.bug("update after scrolling")

		}

		div.scrollHandler = function() {
			var all_items = u.qs(".all_items");
			u.t.resetTimer(all_items.t_scroll);
			all_items.t_scroll = u.t.setTimer(all_items, all_items.scrolled, 500);
		}
		// set scroll handler
		u.e.addEvent(window, "scroll", div.scrollHandler);



		for(i = 0; node = div.nodes[i]; i++) {
			node._item_id = u.cv(node, "item_id");

			node._variant = u.cv(node, "variant");


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



			// show audio player
			node._audio = u.cv(node, "audio");
			if(node._audio) {
				u.ac(node, "audio");

				if(!page.audioplayer) {
					page.audioplayer = u.audioPlayer();
				}
				var audio = u.ie(node, "div", {"class":"audio"});
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

		}


		// taggable list
		if(u.hc(div, "taggable")) {
			u.bug("init taggable")

			// tags received
			div.tagsResponse = function(response) {
//				u.bug("response:" + response);

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
							this.div._taggableNode(this.node);
						}
					}
				}
				else {
					page.notify(response.cms_message);
				}
			}
			u.request(div, "/admin/cms/tags", {"callback":"tagsResponse"});


			// inject tag system into node
			// done when user clicks add-button
			div._taggableNode = function(node) {

				u.ac(node, "addtags");

				// update add button
				node._bn_add.innerHTML = "-";
				node._bn_add.clicked = function() {
					this.innerHTML = "+";
					u.rc(this.node, "addtags");

					// remove tag set to avoid bloated HTML
					this.node._tag_options.parentNode.removeChild(this.node._tag_options);

					this.clicked = function() {
						this.div._taggableNode(this.node);
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

//					u.bug("context:value:" + context + ":" + value)

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
//						u.bug("tag clicked:" + tag_node._context+":"+tag_node._value);

						// delete tag
						if(u.hc(this.node, "addtags")) {

							// remove tag
							if(this.parentNode == this.node._tags) {

								this.response = function(response) {
									if(response.cms_status == "success") {
										// add tag to newtags
										u.ae(this.node._new_tags, this);
									}
									// Notify of event
									page.notify(response.cms_message);
								}
								u.request(this, "/admin/cms/tags/delete/"+this.node._item_id+"/" + this._id);
							}
							// else add tag
							else {

								this.response = function(response) {
									if(response.cms_status == "success") {
										// add tag to tags
										u.ie(this.node._tags, this);
									}
									// Notify of event
									page.notify(response.cms_message);
								}
								u.request(this, "/admin/cms/update/"+this.node._item_id, {"method":"post", "params":"tags="+this._id});
							}
						}
					}
				}
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
		if(u.hc(div, "sortable")) {

			u.s.sortable(div.list);
			div.list.picked = function() {}
			div.list.dropped = function() {
				var url = this.getAttribute("data-save-order");
				this.nodes = u.qsa("li.item", this);
				for(i = 0; node = this.nodes[i]; i++) {
					url += "/"+u.cv(node, "id");
				}
				this.response = function(response) {
					// Notify of event
					page.notify(response.cms_message);
				}
				u.request(this, url);
			}

		}


		// invoke appropiate image loading
		div.scrolled();

	}
}
