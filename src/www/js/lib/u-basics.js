

// // quick toggle header with simplified memory (cross item memory)
// u.toggleHeader = function(div, header) {
//
// 	header = header ? header : "h2";
//
// 	// add collapsable header
// 	div._toggle_header = u.qs(header, div);
// 	div._toggle_header_id = div.className.replace(/item_id:[0-9]+/, "").trim();
//
// 	div._toggle_header.div = div;
// 	u.e.click(div._toggle_header);
// 	div._toggle_header.clicked = function() {
// 		if(this.div._toggle_is_closed) {
// 			u.as(this.div, "height", "auto");
// 			this.div._toggle_is_closed = false;
// 			u.saveCookie(this.div._toggle_header_id+"_open", 1);
// 		}
// 		else {
// 			u.as(this.div, "height", this.offsetHeight+"px");
// 			this.div._toggle_is_closed = true;
// 			u.saveCookie(this.div._toggle_header_id+"_open", 0);
// 		}
// 	}
// 	var state = u.getCookie(div._toggle_header_id+"_open");
// 	if(state == "0") {
// 		div._toggle_header.clicked();
// 	}
// }


Util.Modules["collapseHeader"] = new function() {
	this.init = function(div) {
		u.bug("init collapseHeader");

		// add collapsable header
		u.ac(div, "togglable");
		div._toggle_header = u.qs("h2,h3,h4", div);

		div._toggle_header.div = div;
		u.e.click(div._toggle_header);
		div._toggle_header.clicked = function() {

			if(this.div._toggle_is_closed) {
				// add class (for detailed open settings)
				u.ac(this.div, "open");

				u.ass(this.div, {
					height: "auto"
				});
				this.div._toggle_is_closed = false;
				u.saveNodeCookie(this.div, "open", 1, {"ignore_classvars":true, "ignore_classnames":"open"});
				u.addCollapseArrow(this);

				// callback
				if(typeof(this.div.headerExpanded) == "function") {
					this.div.headerExpanded();
				}
			}
			else {
				// remove class (for detailed closed settings)
				u.rc(this.div, "open");

				u.ass(this.div, {
					height: this.offsetHeight+"px"
				});
				this.div._toggle_is_closed = true;
				u.saveNodeCookie(this.div, "open", 0, {"ignore_classvars":true, "ignore_classnames":"open"});
				u.addExpandArrow(this);

				// callback
				if(typeof(this.div.headerCollapsed) == "function") {
					this.div.headerCollapsed();
				}
			}
		}

		var state = u.getNodeCookie(div, "open", {"ignore_classvars":true, "ignore_classnames":"open"});
//		console.log("state:" + state + ", " + typeof(state));
		// no state value (or state value = 0), means collapsed
		if(!state) {
			div._toggle_header.clicked();
		}
		else {
			u.addCollapseArrow(div._toggle_header);

			// add class (for detailed open settings)
			u.ac(div, "open");

			// callback
			if(typeof(div.headerExpanded) == "function") {
				div.headerExpanded();
			}
		}
	}


}



// global function to add expand arrow
u.addExpandArrow = function(node) {

	if(node.collapsearrow) {
		u.bug("remove collapsearrow");
		node.collapsearrow.parentNode.removeChild(node.collapsearrow);
		//node.collapsearrow = false;
		delete node.collapsearrow;
	}

	node.expandarrow = u.svgIcons("expandarrow", node);
}

// global function to add collapse arrow
u.addCollapseArrow = function(node) {

	if(node.expandarrow) {
		u.bug("remove expandarrow");
		node.expandarrow.parentNode.removeChild(node.expandarrow);
		// node.expandarrow = false;
		delete node.expandarrow;
	}

	node.collapsearrow = u.svgIcons("collapsearrow", node);
}



// FILTERS

u.defaultFilters = function(div) {

	div._filter = u.ie(div, "div", {"class":"filter"});
	div._filter.div = div;


	var i, node, j, text_node;


	// index list, to speed up filtering process
	// list should be indexed initially to avoid indexing extended content (like tag-options)
//	for(i = 0; node = div.nodes[i]; i++) {
	for(i = 0; i < div.nodes.length; i++) {
		node = div.nodes[i];
		node._c = "";

		var text_nodes = u.qsa("h2,h3,h4,h5,p,ul.info,dl,li.tag", node);
//		for(j = 0; text_node = text_nodes[j]; j++) {
		for(j = 0; j < text_nodes.length; j++) {
			text_node = text_nodes[j];
			node._c += u.text(text_node).toLowerCase() + ";"; //.replace(/\n|\t|\r/g, " ").replace(/[ ]+/g, ",");
		}
//		u.bug("c:" + node._c)
	}


	// create tag filter set
	// get all tags in list
	var tags = u.qsa("li.tag", div.list);
	if(tags) {

		var tag, li, used_tags = [];
		div._filter._tags = u.ie(div._filter, "ul", {"class":"tags"});

//		for(i = 0; node = tags[i]; i++) {
		for(i = 0; i < tags.length; i++) {
			node = tags[i];
			tag = u.text(node);
			if(used_tags.indexOf(tag) == -1) {
				used_tags.push(tag);
			}

		}
		used_tags.sort();


//		for(i = 0; tag = used_tags[i]; i++) {
		for(i = 0; i < used_tags.length; i++) {
			tag = used_tags[i];
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

				// u.bug("pre filter")
				// u.xInObject(this._filter.selected_tags);

				// update list filtering
				this._filter.form.updated();
			}

		}

		div._filter.selected_tags = [];

	}


	// insert tags filter
	div._filter.form = u.f.addForm(div._filter, {"name":"filter", "class":"labelstyle:inject"});
	u.f.addField(div._filter.form, {"name":"filter", "label":"Type to filter"});

	u.f.init(div._filter.form);
	div._filter.form.div = div;

	div._filter.input = div._filter.form.inputs["filter"];

	div._filter.form.updated = function() {

		u.t.resetTimer(this.t_filter);
		this.t_filter = u.t.setTimer(this.div._filter, "filterItems", 400);

		u.ac(this.div._filter, "filtering");
	}


	div._filter.checkTags = function(node) {

		if(this.selected_tags.length) {

			var regex = new RegExp("("+this.selected_tags.join(";|")+";)", "g");
			var match = node._c.match(regex);
//			u.bug("match:" + match + ", " + "("+this.selected_tags.join(";|")+";)")
			if(!match || match.length != this.selected_tags.length) {
				return false;
			}
		}

		return true;
	}

	div._filter.filterItems = function() {

		var i, node;
		var query = this.input.val().toLowerCase();
		if(this.current_filter != query+","+this.selected_tags.join(",")) {

			this.current_filter = query + "," + this.selected_tags.join(",");
//			for(i = 0; node = this.div.nodes[i]; i++) {
			for(i = 0; i < this.div.nodes.length; i++) {
				node = this.div.nodes[i];
//				u.bug("match:" + node._c.match(query) + ", " + node._c + ", " + query)
				if(node._c.match(query) && this.checkTags(node)) {
					node._hidden = false;
					u.rc(node, "hidden", false);
					u.as(node, "display", "block", false);
				}
				else {
					node._hidden = true;
					u.ac(node, "hidden", false);
					u.as(node, "display", "none", false);
				}
			}

		}

		u.rc(this, "filtering");

		// let list know filtering was done
		if(typeof(this.div.filtered) == "function") {
			this.div.filtered();
		}
		// invoke appropriate image loading
//		this.div.scrolled();

	}


}



// SORTABLE

u.defaultSortableList = function(list) {
	// u.bug("defaultSortableList");
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
		u.sortable(list, {"targets":".items", "draggables":".draggable"});
		list.picked = function() {}
		list.dropped = function() {

			// Get node order
			var order = this.getNodeOrder();

			this.orderResponse = function(response) {
				// Notify of event
				page.notify(response);
			}
			u.request(this, this.div.save_order_url, {
				"callback":"orderResponse", 
				"method":"post", 
				"params":"csrf-token=" + this.div.csrf_token + "&order=" + order.join(",")
			});
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

	// Tag context filter
	node._tags_context = node._tags.getAttribute("data-context");

	// create add tag form
	node._tag_form = u.f.addForm(node._tag_options, {"action": node.data_div.add_tag_url});
	u.f.addField(node._tag_form, {"type":"hidden", "name":"csrf-token", "value":node.data_div.csrf_token});

	// add fieldset
	var fieldset = u.f.addFieldset(node._tag_form);
	// add input field
	u.f.addField(fieldset, {
		"name":"tags", 
		"value":"", 
		"id":"tag_input_"+node._item_id, 
		"label":"Tag", 
		"hint_message":"Type to filter existing tags or add a new tag", 
		"error_message":"Tag must conform to tag value: context:value", 
		"pattern":(node._tags_context ? "^("+node._tags_context.split(/,|;/).join("|")+")\:[^$]+" : "[^$]+\:[^$]+")
	});
	// add submit button
	u.f.addAction(node._tag_form, {"class":"button primary", "value":"Add new tag"});

	// initialize form
	u.f.init(node._tag_form);
	node._tag_form.node = node;

	// filter tags when typing
	node._tag_form.inputs["tags"].updated = function() {

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
				this.inputs["tags"].val("");
				this.inputs["tags"].updated();
				this.inputs["tags"].focus();


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
	node._tag_form.inputs["tags"].focus();


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

		// Only include allowed contexts
		if(
			(!node._tags_context || (context.match(new RegExp("^(" + node._tags_context.split(/,|;/).join("|") + ")$"))))
		) {

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



// SELECTABLE

u.defaultSelectable = function(div) {
	// u.bug("defaultSelectable:", div);


	// add select all option
	div.bn_all = u.ie(div.list, "li", {"class":"all"});
	div.bn_all._text = u.ae(div.bn_all, "span", {"html":"Select all"});
	div.bn_all._checkbox = u.ie(div.bn_all, "input", {"type":"checkbox"});


	// disable regular onclick event
	div.bn_all.onclick = function(event) {u.e.kill(event);}

	div.bn_all.div = div;
	div.bn_all._checkbox.div = div;


	// handle clicking
	u.e.click(div.bn_all._checkbox);
	div.bn_all._checkbox.clicked = function(event) {
		var i, node;
		u.e.kill(event);
		// figure out wether to select or deselect (if one is selected, de-select all)
		var inputs = u.qsa("li.item:not(.hidden) input:checked", this.div.list);

//			for(i = 0; node = this.div.nodes[i]; i++) {
		for(i = 0; i < this.div.nodes.length; i++) {
			node = this.div.nodes[i];
			if(inputs.length) {
				node._checkbox.checked = false;
			}

			// don't select hidden nodes
			else if(!node._hidden) {
				node._checkbox.checked = true;

			}
		}

		// update range inputs
		this.div.bn_range._from.value = "";
		this.div.bn_range._to.value = "";

		// Update bn_all state
		this.div.bn_all.updateState();

	}

	// update select all state
	div.bn_all.updateState = function() {
//		u.bug("updateState");

		// figure out what the current state is and deal with it
		this.div.checked_inputs = u.qsa("li.item input:checked", this.div.list);
		this.div.visible_inputs = u.qsa("li.item:not(.hidden) input", this.div.list);

		// u.bug("checked_inputs:", this.div.checked_inputs.length, "visible_inputs:", this.div.visible_inputs.length)

		// all is selected
		if(this.div.checked_inputs.length && this.div.checked_inputs.length == this.div.visible_inputs.length) {

			this._text.innerHTML = "Deselect all";
			u.rc(this, "deselect");
			this._checkbox.checked = true;
		}
		else if(this.div.checked_inputs.length) {
			this._text.innerHTML = "Deselect all";
			u.ac(this, "deselect");
			this._checkbox.checked = true;
		}
		else {
			this._text.innerHTML = "Select all";
			u.rc(this, "deselect");
			this._checkbox.checked = false;
		}

		// update options
		if(fun(this.div.selectionUpdated)) {
			this.div.selectionUpdated(this.div.checked_inputs);
		}

	}


	// add select range option
	div.bn_range = u.ae(div.bn_all, "div", {class:"range"});
	div.bn_range._text = u.ae(div.bn_range, "span", {html:"Select range:"});
	div.bn_range._from = u.ae(div.bn_range, "input", {type:"text", name:"range_from", maxlength:4});
	div.bn_range._text = u.ae(div.bn_range, "span", {html:"to"});
	div.bn_range._to = u.ae(div.bn_range, "input", {type:"text", name:"range_to", maxlength:4});


	div.bn_range.div = div;
	div.bn_range._from.bn_range = div.bn_range;
	div.bn_range._to.bn_range = div.bn_range;

	// attached to inputs
	div.bn_range._updated = function(event) {


//			console.log(event)
		var key = event.key;
		// console.log(key);
		// console.log(event.code)

//			return;
		// increment
		if(key == "ArrowUp" && event.shiftKey) {
			u.e.kill(event);

			this.value = this.value > 0 ? Number(this.value)+10 : 10;
		}
		else if(key == "ArrowUp") {
			u.e.kill(event);

			this.value = this.value > 0 ? Number(this.value)+1 : 1;
		}

		// decrement
		else if(key == "ArrowDown" && event.shiftKey) {
			u.e.kill(event);

			this.value = this.value > 10 ? Number(this.value)-10 : 1;
		}
		else if(key == "ArrowDown") {
			u.e.kill(event);

			this.value = this.value > 1 ? Number(this.value)-1 : 1;
		}

// 			// kill non-numeric keys
		else if((parseInt(key) != key) && (key != "Backspace" && key != "Delete" && key != "Tab" && key != "ArrowLeft" && key != "ArrowRight" && !event.metaKey && !event.ctrlKey)) {
			u.e.kill(event);
		}

		var value = false;
		var to, from;

		// figure out what the value will be after keyup
		if(parseInt(key) == key) {
			value = this.value.length < 4 ? this.value + key : this.value;
		}
		else if(key == "Backspace") {
			value = this.value.substring(0, this.value.length-1);
		}
		else if(key == "Delete") {
			value = this.value.substring(1);
		}
		else if(key == "ArrowUp" || key == "ArrowDown") {
			value = this.value;
		}

		if(value !== false) {

			value = Number(value);

			// add updated values and correct "sister" values
			if(this.name == "range_from") {

				if(Number(this.bn_range._to.value) < value) {
					this.bn_range._to.value = value;
				}

				from = value;
				to = Number(this.bn_range._to.value);
			}
			else if(this.name == "range_to") {

				if(!this.bn_range._from.value) {
					this.bn_range._from.value = 1;
				}
				else if(Number(this.bn_range._from.value) > value) {
					this.bn_range._from.value = value;
				}

				to = value;
				from = Number(this.bn_range._from.value);
			}

			// input indecies to select between
			to = to-1;
			from = from-1;

			if(!isNaN(from && !isNaN(to))) {
				var inputs = u.qsa("li.item:not(.hidden) input", this.bn_range.div.list);
				var i, input;
				for(i = 0; i < inputs.length; i++) {
					input = inputs[i];
					if(i >= from && i <= to) {
						input.checked = true;
					}
					else {
						input.checked = false;
					}
				}

				// Update bn_all state
				this.bn_range.div.bn_all.updateState();

			}

		}

	}

	u.e.addEvent(div.bn_range._from, "keypress", div.bn_range._updated);
	u.e.addEvent(div.bn_range._to, "keypress", div.bn_range._updated);





	// add checkboxes and handlers to all rows
//		for(i = 0; node = div.nodes[i]; i++) {
	for(i = 0; i < div.nodes.length; i++) {
		node = div.nodes[i];
		node.ua_id = u.cv(node, "ua_id");
		node.div = div;

		// enable selection
		node._checkbox = u.ie(node, "input", {"type":"checkbox"});
		node._checkbox.node = node;

		u.e.click(node._checkbox);
		node._checkbox.onclick = function(event) {u.e.kill(event);}

		// enable multiple selection on drag
		node._checkbox.inputStarted = function(event) {
			u.e.kill(event);

			// map div for body events
			document.body.selection_div = this.node.div;


			if(this.checked) {
				this.checked = false;
				document.body._multideselection = true;
			}
			else {
				this.checked = true;
				document.body._multiselection = true;
			}

			// end multi de/selection
			document.body.onmouseup = function(event) {
//					console.log("selection end")

				this.onmouseup = null;
				this._multiselection = false;
				this._multideselection = false;


				// Update bn_all state
				this.selection_div.bn_all.updateState();

				delete document.body.selection_div;

			}

		}

		// select/deselect if state is correct on mouseover
		node._checkbox.onmouseover = function() {
			if(document.body._multiselection) {
				this.checked = true;
			}
			else if(document.body._multideselection) {
				this.checked = false;
			}
		}

	}
}


// create icon svg
u.svgIcons = function(icon, node) {

	// save icon to be cloned to avoid recreating icons again and again for lists
	// test if it becomes to heavy

	switch(icon) {
		case "expandarrow" : return u.svg({
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
		case "collapsearrow" : return u.svg({
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
		case "totoparrow" : return u.svg({
			"name":"totoparrow",
			"node":node,
			"class":"arrow",
			"width":30,
			"height":30,
			"shapes":[
				{
					"type": "line",
					"x1": 2,
					"y1": 21,
					"x2": 16,
					"y2": 2
				},
				{
					"type": "line",
					"x1": 14,
					"y1": 2,
					"x2": 28,
					"y2": 21
				}
			]
		});
	}

}
