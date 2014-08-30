Util.Objects["defaultTags"] = new function() {
	this.init = function(div) {

		div.item_id = u.cv(div, "item_id");

		// add tag form
		div._tags_form = u.qs("form", div);
		div._tags_form.div = div;


		u.f.init(div._tags_form);


		// CMS interaction urls
		div.csrf_token = div._tags_form.fields["csrf-token"].value;
		div.add_tag_url = div._tags_form.action;
		div.delete_tag_url = div.getAttribute("data-delete-tag");
		div.get_tags_url = div.getAttribute("data-get-tags");


		// show all tags when tag input has focus
		div._tags_form.fields["tags"].focused = function() {
			this.form.div.enableTagging();
		}
		// filter tags when typing
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
		}

		// 
		div._tags_form.submitted = function(iN) {

			this.response = function(response) {
				page.notify(response);

				if(response.cms_status == "success") {

					// check if tag already exists in tags options
					var i, tag_node;
					var new_tags = u.qsa("li", this.div._new_tags);
					for(i = 0; tag_node = new_tags[i]; i++) {
						// tag found?
						if(tag_node._id == response.cms_object.tag_id) {

							this.fields["tags"].val("");
							this.fields["tags"].updated();
							u.ae(this.div._tags, tag_node);
							return;
						}
					}

					// tag not found in tag options - it is a brand new tag
					// add it to all_tags list
					this.div._tags._alltags.push({"id":response.cms_object.tag_id, "context":response.cms_object.context, "value":response.cms_object.value})

					// add it to tags
					tag_node = u.ae(this.div._tags, "li", {"class":"tag"});
					tag_node._context = response.cms_object.context;
					tag_node._value = response.cms_object.value;
					tag_node._id = response.cms_object.id;
					u.ae(tag_node, "span", {"class":"context", "html":tag_node._context});
					u.ae(tag_node, "span", {"class":"value", "html":tag_node._value});
					tag_node.div = this.div;

					div.activateTag(tag_node);
					this.fields["tags"].val("");
					this.fields["tags"].updated();
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
				page.notify(response);
			}
		}
		// get tags
		u.request(div._tags, div.get_tags_url, {"callback":"tagsResponse", "method":"post", "params":"csrf-token=" + div.csrf_token});


		// enable tagging (when + is clicked or add tag field is updated)
		div.enableTagging = function() {
			u.bug("enable tagging")

			if(!this._tag_options) {

				// change button value and action
				this._tags._bn_add.innerHTML = "-";
				this._tags._bn_add.clicked = function() {

					this.innerHTML = "+";
					u.rc(this.div, "addtags");

					// remove tag set to avoid bloated HTML
					this.div._tag_options.parentNode.removeChild(this.div._tag_options);
					this.div._tag_options = false;

					// re-enable tag +
					this.clicked = function() {
						this.div.enableTagging();
					}
				}

				// go to add tags mode
				u.ac(this, "addtags");

				// add list with available tag option
				this._tag_options = u.ae(this, "div", {"class":"tagoptions"});
				this._new_tags = u.ae(this._tag_options, "ul", {"class":"tags"});


				// index existing tags
				var usedtags = {};
				var itemTags = u.qsa("li:not(.add)", this._tags);

				var i, tag_node, tag, context, value;
				for(i = 0; tag_node = itemTags[i]; i++) {
					tag_node._context = u.qs(".context", tag_node).innerHTML;
					tag_node._value = u.qs(".value", tag_node).innerHTML;

	//				u.bug("exist context:value:" + tag._context + ":" + tag._value)

					if(!usedtags[tag_node._context]) {
						usedtags[tag_node._context] = {}
					}
					if(!usedtags[tag_node._context][tag_node._value]) {
						usedtags[tag_node._context][tag_node._value] = tag_node;
					}
				}


				// loop through all tags
				for(tag in this._tags._alltags) {

					// tag context
					context = this._tags._alltags[tag].context;
					// tag value - replace single & with entity or it is not recognized
					value = this._tags._alltags[tag].value.replace(/ & /, " &amp; ");
//					u.bug("context:value:" + context + ":" + value + ", " + tag)

					
					// tag exist on item
					if(usedtags && usedtags[context] && usedtags[context][value]) {
						tag_node = usedtags[context][value];
					}
					// tag is unused
					// add tag to tag options
					else {
						tag_node = u.ae(this._new_tags, "li", {"class":"tag"});
						tag_node._context = context;
						tag_node._value = value;
						u.ae(tag_node, "span", {"class":"context", "html":tag_node._context});
						u.ae(tag_node, "span", {"class":"value", "html":tag_node._value});
					}

					tag_node._id = this._tags._alltags[tag].id;
					tag_node.div = this;

					div.activateTag(tag_node);
				}
			}
		}
		
		div.activateTag = function(tag_node) {

			u.e.click(tag_node);
			tag_node.clicked = function() {
//				u.bug("tag clicked:" + tag_node._context+":"+tag_node._value);

				// only do anything if in addTags mode
				if(u.hc(this.div, "addtags")) {

					// tag is in existing tags list
					// remove tag
					if(this.parentNode == this.div._tags) {

						this.response = function(response) {

							// Notify of event
							page.notify(response);

							if(response.cms_status == "success") {
								// add tag to newtags
								u.ae(this.div._new_tags, this);
							}
						}
						u.request(this, this.div.delete_tag_url+"/"+this.div.item_id+"/" + this._id, {"method":"post", "params":"csrf-token=" + this.div.csrf_token});
					}
					// else add tag
					else {
// 
						this.response = function(response) {

							// Notify of event
							page.notify(response);

							if(response.cms_status == "success") {
								// add tags to tag options
								u.ie(this.div._tags, this)
							}
						}
						u.request(this, this.div.add_tag_url, {"method":"post", "params":"tags="+this._id+"&csrf-token=" + this.div.csrf_token});
					}
				}
			}
			
		}
	}
}
