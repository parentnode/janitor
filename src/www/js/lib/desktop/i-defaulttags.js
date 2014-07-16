Util.Objects["defaultTags"] = new function() {
	this.init = function(div) {

		div.item_id = u.cv(div, "item_id");

		// add tag form
		div._tags_form = u.qs("form", div);
		div._tags_form.div = div;

		u.f.init(div._tags_form);


		// CMS interaction urls
		div.csrf_token = div._tags_form.fields["csrf-token"].value;
		div.update_item_url = div._tags_form.action;
		div.delete_tag_url = div.getAttribute("data-delete-tag");
		div.get_tags_url = div.getAttribute("data-get-tags");


		// show all tags when tag input has focus
		div._tags_form.fields["tags"].focused = function() {
			this.form.div.enableTagging();
		}
		// hide all tags when tag input looses focus
		div._tags_form.fields["tags"].updated = function() {


			if(this.form.div._new_tags) {
				var tags = u.qsa(".tag", this.form.div._new_tags);
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

//		 	this.form.div._filterTags(this.val());
		}

		div._tags_form.submitted = function(iN) {

			this.response = function(response) {
				if(response.cms_status == "success") {
					location.reload();
				}
				else {
					alert(response.cms_message[0]);
				}
			}
			u.request(this, this.action, {"method":"post", "params" : u.f.getParams(this)});
		}


		div._tags = u.qs("ul.tags", div);
		if(!div._tags) {
			div._tags = u.ae(div._tags, "ul", {"class":"tags"});
		}
		div._tags.div = div;


		// get all tags from server
		div._tags.tagsResponse = function(response) {

			if(response.cms_status == "success" && response.cms_object) {
				this._alltags = response.cms_object;

				var bn_add;
				// minimum work in first run
				// only inject add-button in first run
				this._bn_add = u.ae(this, "li", {"class":"add","html":"+"});
				this._bn_add.div = this.div;
				u.e.click(this._bn_add);
				this._bn_add.clicked = function() {
					this.div.enableTagging();
				}

			}
			else {
				page.notify(response.cms_message);
			}
		}
		// get tags
		u.request(div._tags, div.get_tags_url, {"callback":"tagsResponse", "method":"post", "params":"csrf-token=" + div.csrf_token});


		// enable tagging
		div.enableTagging = function() {
			u.bug("enable tagging")

			if(!this._tag_options) {

				// change button
				this._tags._bn_add.innerHTML = "-";
				this._tags._bn_add.clicked = function() {

					this.innerHTML = "+";
					u.rc(this.div, "addtags");

					// remove tag set to avoid bloated HTML
					this.div._tag_options.parentNode.removeChild(this.div._tag_options);
					this.div._tag_options = false;

					this.clicked = function() {
						this.div.enableTagging();
					}
				}
				u.ac(this, "addtags");


				this._tag_options = u.ae(this, "div", {"class":"tagoptions"});

				this._new_tags = u.ae(this._tag_options, "ul", {"class":"tags"});


				// index existing tags
				var usedtags = {};
				var itemTags = u.qsa("li:not(.add)", this._tags);

				var i, tag, context, value;

				for(i = 0; tag = itemTags[i]; i++) {
					tag._context = u.qs(".context", tag).innerHTML;
					tag._value = u.qs(".value", tag).innerHTML;

	//				u.bug("exist context:value:" + tag._context + ":" + tag._value)

					if(!usedtags[tag._context]) {
						usedtags[tag._context] = {}
					}
					if(!usedtags[tag._context][tag._value]) {
						usedtags[tag._context][tag._value] = tag;
					}
				}

				// loop through all tags
				for(tag in this._tags._alltags) {

					// tag context
					context = this._tags._alltags[tag].context;
					// tag value - replace single & with entity or it is not recognized
					value = this._tags._alltags[tag].value.replace(/ & /, " &amp; ");
	//				u.bug("context:value:" + context + ":" + value)

					if(usedtags && usedtags[context] && usedtags[context][value]) {
	// 					// 	u.ac(node, "selected");
						tag_node = usedtags[context][value];
					}
					else {
						tag_node = u.ae(this._new_tags, "li", {"class":"tag"});
						tag_node._context = context;
						tag_node._value = value;
						u.ae(tag_node, "span", {"class":"context", "html":tag_node._context});
						u.ae(tag_node, "span", {"class":"value", "html":tag_node._value});
					}

//					tag_node._taglist = this._tags._taglist;
					tag_node._id = this._tags._alltags[tag].id;
					tag_node.div = this;


	// 
	 				u.e.click(tag_node);
	 				tag_node.clicked = function() {
	// 					u.bug("tag clicked:" + tag_node._context+":"+tag_node._value);

						// only do anything if in addTags mode
						if(u.hc(this.div, "addtags")) {

							// tag is in existing tags list
							// remove tag
							if(this.parentNode == this.div._tags) {

								this.response = function(response) {
									if(response.cms_status == "success") {
										// add tag to newtags
										u.ae(this.div._new_tags, this);
									}
									// Notify of event
									page.notify(response.cms_message);
								}
//								u.request(this, "/admin/cms/tags/delete/"+this.div.item_id+"/" + this._id);
								u.request(this, this.div.delete_tag_url+"/"+this.div.item_id+"/" + this._id, {"method":"post", "params":"csrf-token=" + this.div.csrf_token});
							}
							// else add tag
							else {
	// 
								this.response = function(response) {
									if(response.cms_status == "success") {
										
										u.ie(this.div._tags, this)
									}
									// Notify of event
									page.notify(response.cms_message);
								}
//								u.request(this, "/admin/cms/update/"+this.div.item_id, {"method":"post", "params":"tags="+this._id});
								u.request(this, this.div.update_item_url, {"method":"post", "params":"tags="+this._id+"&csrf-token=" + this.div.csrf_token});
							}
						}
					}
				}
			}
		}
	}
}
