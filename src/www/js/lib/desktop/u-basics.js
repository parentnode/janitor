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
		div._toggle_header = u.qs("h2", div);

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