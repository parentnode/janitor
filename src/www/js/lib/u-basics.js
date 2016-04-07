// quick toggle header with simplified memory (cross item memory)
u.toggleHeader = function(div, header) {

	header = header ? header : "h2";

	// add collapsable header
	div._toggle_header = u.qs(header, div);
	div._toggle_header_id = div.className.replace(/item_id:[0-9]+/, "").trim();

	div._toggle_header.div = div;
	u.e.click(div._toggle_header);
	div._toggle_header.clicked = function() {
		if(this.div._toggle_is_closed) {
			u.as(this.div, "height", "auto");
			this.div._toggle_is_closed = false;
			u.saveCookie(this.div._toggle_header_id+"_open", 1);
		}
		else {
			u.as(this.div, "height", this.offsetHeight+"px");
			this.div._toggle_is_closed = true;
			u.saveCookie(this.div._toggle_header_id+"_open", 0);
		}
	}
	var state = u.getCookie(div._toggle_header_id+"_open");
	if(state == "0") {
		div._toggle_header.clicked();
	}
}


Util.Objects["collapseHeader"] = new function() {
	this.init = function(div) {
		u.bug("init collapseHeader");

		// add collapsable header
		u.ac(div, "togglable");
		div._toggle_header = u.qs("h2,h3", div);

		div._toggle_header.div = div;
		u.e.click(div._toggle_header);
		div._toggle_header.clicked = function() {

			if(this.div._toggle_is_closed) {
				u.as(this.div, "height", "auto");
				this.div._toggle_is_closed = false;
				u.saveNodeCookie(this.div, "open", 1, {"ignore_classvars":true});
				u.addCollapseArrow(this);
			}
			else {
				u.as(this.div, "height", this.offsetHeight+"px");
				this.div._toggle_is_closed = true;
				u.saveNodeCookie(this.div, "open", 0, {"ignore_classvars":true});
				u.addExpandArrow(this);
			}
		}

		var state = u.getNodeCookie(div, "open", {"ignore_classvars":true});
		if(!state) {
			div._toggle_header.clicked();
		}
		else {
			u.addCollapseArrow(div._toggle_header);
		}
	}


}

u.insertAfter = function(after_node, insert_node) {
	var next_node = u.ns(after_node);
	if(next_node) {
		after_node.parentNode.insertBefore(next_node, insert_node);
	}
	else {
		after_node.parentNode.appendChild(insert_node);
	}
}



// global function to add expand arrow
u.addExpandArrow = function(node) {

	if(node.collapsearrow) {
		u.bug("remove collapsearrow");
		node.collapsearrow.parentNode.removeChild(node.collapsearrow);
		node.collapsearrow = false;
	}

	node.expandarrow = u.svg({
		"name":"expandarrow",
		"node":node,
		"class":"arrow",
		"width":17,
		"height":17,
		"shapes":[
			{
				"type": "line",
				"x1": 2,
				"y1": 2,
				"x2": 7,
				"y2": 9
			},
			{
				"type": "line",
				"x1": 6,
				"y1": 9,
				"x2": 11,
				"y2": 2
			}
		]
	});
}

// global function to add collapse arrow
u.addCollapseArrow = function(node) {

	if(node.expandarrow) {
		u.bug("remove expandarrow");
		node.expandarrow.parentNode.removeChild(node.expandarrow);
		node.expandarrow = false;
	}

	node.collapsearrow = u.svg({
		"name":"collapsearrow",
		"node":node,
		"class":"arrow",
		"width":17,
		"height":17,
		"shapes":[
			{
				"type": "line",
				"x1": 2,
				"y1": 9,
				"x2": 7,
				"y2": 2
			},
			{
				"type": "line",
				"x1": 6,
				"y1": 2,
				"x2": 11,
				"y2": 9
			}
		]
	});
}



// FILTERS
u.defaultFilters = function(div) {

	div._filter = u.ie(div, "div", {"class":"filter"});
	div._filter.div = div;


	var i, node;


	// index list, to speed up filtering process
	// list should be indexed initially to avoid indexing extended content (like tag-options)
	for(i = 0; node = div.nodes[i]; i++) {
		node._c = u.text(node).toLowerCase().replace(/\n|\t|\r/g, " ").replace(/  /g, "");
//		u.bug("c:" + node._c)
	}


	// create tag filter set
	// get all tags in list
	var tags = u.qsa("li.tag", div.list);
	if(tags) {

		var tag, li, used_tags = [];
		div._filter._tags = u.ie(div._filter, "ul", {"class":"tags"});

		for(i = 0; node = tags[i]; i++) {

			tag = u.text(node);
			if(used_tags.indexOf(tag) == -1) {
				used_tags.push(tag);
			}

		}
		used_tags.sort();


		for(i = 0; tag = used_tags[i]; i++) {
			li = u.ae(div._filter._tags, "li", {"html":tag});
			li.tag = tag.toLowerCase();
			li._filter = div._filter;

			u.e.click(li);
			li.clicked = function(event) {
				if(u.hc(this, "selected")) {
					this._filter.selected_tags.splice(this._filter.selected_tags.indexOf(this.tag), 1);
					u.rc(this, "selected");
				}
				else {
					this._filter.selected_tags.push(this.tag);
					u.ac(this, "selected");
				}

				u.bug("pre filter")
				u.xInObject(this._filter.selected_tags);

				// update list filtering
				this._filter.form.updated();
			}

		}

		div._filter.selected_tags = [];

	}


	// insert tags filter
	div._filter.form = u.f.addForm(div._filter, {"name":"filter", "class":"labelstyle:inject"});
	u.f.addField(div._filter.form, {"name":"filter", "label":"Type to search"});

	u.f.init(div._filter.form);
	div._filter.form.div = div;

	div._filter._input = div._filter.form.fields["filter"];

	div._filter.form.updated = function() {

		u.t.resetTimer(this.t_filter);
		this.t_filter = u.t.setTimer(this.div._filter, "filterItems", 400);

		u.ac(this.div._filter, "filtering");
	}


	div._filter.checkTags = function(node) {

		if(this.selected_tags.length) {

			var regex = new RegExp(this.selected_tags.join("|"), "g");
			var match = node._c.match(regex);
			if(!match || match.length != this.selected_tags.length) {
				return false;
			}
		}

		return true;
	}

	div._filter.filterItems = function() {

		var i, node;
		var query = this._input.val().toLowerCase();
		if(this.current_filter != query+","+this.selected_tags.join(",")) {

			this.current_filter = query + "," + this.selected_tags.join(",");
			for(i = 0; node = this.div.nodes[i]; i++) {

				if(node._c.match(query) && this.checkTags(node)) {
					u.as(node, "display", "block", false);
				}
				else {
					u.as(node, "display", "none", false);
				}
			}

		}

		u.rc(this, "filtering");

		// invoke appropriate image loading
//		this.div.scrolled();

	}


}



// SORTABLE

u.defaultSortableList = function(list) {

	list.div.save_order_url = list.div.getAttribute("data-item-order");

	if(list.div.save_order_url && list.div.csrf_token) {

		// add additional sorting requirements
		for(i = 0; node = list.div.nodes[i]; i++) {
			u.ac(node, "draggable");
			if(!u.qs(".drag", node)) {
				u.ie(node, "div", {"class":"drag"});
			}
		}

		// apply
		u.sortable(list, {"targets":"items", "draggables":"draggable"});
		list.picked = function() {}
		list.dropped = function() {
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
		u.rc(list.div, "sortable");
	}
	
}



// TAGS

// enable tagging
// activated bn_add
u.enableTagging = function(node) {

	// apply new add-button handler
	u.e.click(node._bn_add);
	node._bn_add.clicked = function() {

		// close tag options
		if(u.hc(this.node, "edittags")) {

			this.innerHTML = "+";

			// leave active state
			u.rc(this.node, "edittags");

			// remove tag set to avoid bloated HTML
			this.node._tag_options.parentNode.removeChild(this.node._tag_options);
			delete this.node._tag_options;

		}
		// open tag options
		else {

			this.innerHTML = "-";

			// switch to active state
			u.ac(this.node, "edittags");

			// activate full tagging functionality
			u.activateTagging(this.node);

		}


	}

	
}

// inject add tag form and new tags list
// activate edittags mode
u.activateTagging = function(node) {

	// inject tag options wrapper
	if(node._text) {
		node._tag_options = u.ae(node._text, "div", {"class":"tagoptions"});
	}
	else {
		node._tag_options = u.ae(node, "div", {"class":"tagoptions"});
	}


	// create add tag form
	node._tag_form = u.f.addForm(node._tag_options, {"action": node.data_div.add_tag_url});
	u.f.addField(node._tag_form, {"type":"hidden", "name":"csrf-token", "value":node.data_div.csrf_token});

	// add fieldset
	var fieldset = u.f.addFieldset(node._tag_form);
	// add input field
	u.f.addField(fieldset, {"name":"tags", "value":"", "id":"tag_input_"+node._item_id, "label":"Tag", "hint_message":"Type to filter existing tags or add a new tag", "error_message":"Tag must conform to tag value: context:value", "pattern":"[^$]+\:[^$]+"});
	// add submit button
	u.f.addAction(node._tag_form, {"class":"button primary", "value":"Add new tag"});

	// initialize form
	u.f.init(node._tag_form);
	node._tag_form.node = node;

	// filter tags when typing
	node._tag_form.fields["tags"].updated = function() {

		// only filter if new tags list exists
		if(this._form.node._new_tags) {
			// get all new tags
			var tags = u.qsa(".tag", this.form.node._new_tags);
			var i, tag;
			// loop through all new tags and hide tag if it doesn't match field value
			for(i = 0; tag = tags[i]; i++) {
				if(u.text(tag).toLowerCase().match(this.val().toLowerCase())) {
					u.as(tag, "display", "inline-block");
				}
				else {
					u.as(tag, "display", "none");
				}
			}
		}
	}

	// New tag submitted
	node._tag_form.submitted = function(iN) {

		// add tag response
		this.response = function(response) {
			page.notify(response);

			// success
			if(response.cms_status == "success") {

				// clear tag field and update filtering
				this.fields["tags"].val("");
				this.fields["tags"].updated();
				this.fields["tags"].focus();


				// shorter reference
				var new_tag = response.cms_object;

				// check if tag already exists in tags options
				var i, tag_node;
				var new_tags = u.qsa("li", this.node._new_tags);
				for(i = 0; tag_node = new_tags[i]; i++) {

					// tag found
					if(tag_node._id == new_tag.tag_id) {

						// move tag from new tags to existing tags
						u.ae(this.node._tags, tag_node);
						return;
					}
				}

				// tag not found in new tags - it is a brand new tag
				// add it to all_tags list
				this.node.data_div.all_tags.push({
					"id":new_tag.tag_id, 
					"context":new_tag.context, 
					"value":new_tag.value
				});

				// add it to existing tags
				tag_node = u.ae(this.node._tags, "li", {"class":"tag "+new_tag.context});
				u.ae(tag_node, "span", {"class":"context", "html":new_tag.context});
				u.ae(tag_node, document.createTextNode(":"));
				u.ae(tag_node, "span", {"class":"value", "html":new_tag.value});

				// map values to tag node
				tag_node._context = new_tag.context;
				tag_node._value = new_tag.value;
				tag_node._id = new_tag.tag_id;
				tag_node.node = this.node;


				// activate tag
				u.activateTag(tag_node);

			}

		}
		u.request(this, this.action+"/"+this.node._item_id, {"method":"post", "params" : u.f.getParams(this)});
	}
	// add focus to tag field
	node._tag_form.fields["tags"].focus();


	// add list with available tag options
	node._new_tags = u.ae(node._tag_options, "ul", {"class":"tags"});


	// index existing tags to create a clear over view of ununsed tags
	var used_tags = {};
	var item_tags = u.qsa("li:not(.add)", node._tags);
	var i, tag_node, tag, context, value;
	for(i = 0; tag_node = item_tags[i]; i++) {
		tag_node._context = u.qs(".context", tag_node).innerHTML;
		tag_node._value = u.qs(".value", tag_node).innerHTML;

		// we don't have tag id yet - it will be added when all_tags are being distributed

//		u.bug("exist context:value:" + tag._context + ":" + tag._value)

		if(!used_tags[tag_node._context]) {
			used_tags[tag_node._context] = {}
		}
		if(!used_tags[tag_node._context][tag_node._value]) {
			used_tags[tag_node._context][tag_node._value] = tag_node;
		}
	}


	// loop through all tags and add unused tags to new tags list
	for(tag in node.data_div.all_tags) {

		// tag context
		context = node.data_div.all_tags[tag].context;
		// tag value - replace single & with entity or it is not recognized
		value = node.data_div.all_tags[tag].value.replace(/ & /, " &amp; ");
		
		// tag exist on item
		if(used_tags && used_tags[context] && used_tags[context][value]) {
			tag_node = used_tags[context][value];
		}
		// tag is unused
		// add tag to new tags list
		else {
			tag_node = u.ae(node._new_tags, "li", {"class":"tag"});
			u.ae(tag_node, "span", {"class":"context", "html":context});
			u.ae(tag_node, document.createTextNode(":"));
			u.ae(tag_node, "span", {"class":"value", "html":value});

			tag_node._context = context;
			tag_node._value = value;
		}

		// add tag id and node reference
		tag_node._id = node.data_div.all_tags[tag].id;
		tag_node.node = node;


		// activate tag
		u.activateTag(tag_node);

	}

}

// activate tag node - applies click event handler
// used for both new tags, existing tags and newly added tags
u.activateTag = function(tag_node) {

	// activate tag click
	u.e.click(tag_node);
	tag_node.clicked = function() {
//		u.bug("tag clicked:" + tag_node._context+":"+tag_node._value);

		// only do anything if in addTags mode
		if(u.hc(this.node, "edittags")) {

			// tag is in existing tags list
			// delete tag
			if(this.parentNode == this.node._tags) {

				// delete tag response
				this.response = function(response) {

					// Notify of event
					page.notify(response);

					if(response.cms_status == "success") {
						// add tag to new tags list
						u.ae(this.node._new_tags, this);
					}
				}
				// delete tag request
				u.request(this, this.node.data_div.delete_tag_url+"/"+this.node._item_id+"/" + this._id, {"method":"post", "params":"csrf-token=" + this.node.data_div.csrf_token});
			}
			// add tag
			else {
 
				// add tag response
				this.response = function(response) {

					// Notify of event
					page.notify(response);

					if(response.cms_status == "success") {
						// add tag to tags
						u.ie(this.node._tags, this)
					}
				}
				// add tag request
				u.request(this, this.node.data_div.add_tag_url+"/"+this.node._item_id, {"method":"post", "params":"tags="+this._id+"&csrf-token=" + this.node.data_div.csrf_token});
			}

		}

	}

}
