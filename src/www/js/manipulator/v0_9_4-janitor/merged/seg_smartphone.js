/*
Manipulator v0.9.4-janitor Copyright 2023 https://manipulator.parentnode.dk
js-merged @ 2026-06-15 07:28:52
*/

/*seg_smartphone_include.js*/

/*u.js*/
if(!u || !Util) {
	var u, Util = u = new function() {};
	u.version = "0.9.3";
	u.bug = u.nodeId = u.exception = function() {};
	u.stats = new function() {this.pageView = function(){};this.event = function(){};}
	u.txt = function(index) {return index;}
}
function fun(v) {return (typeof(v) === "function")}
function obj(v) {return (typeof(v) === "object")}
function str(v) {return (typeof(v) === "string")}

/*u-debug.js*/
u.bug_console_only = true;
Util.debugURL = function(url) {
	if(u.bug_force) {
		return true;
	}
	return document.domain.match(/(\.local|\.proxy)$/);
}
Util.nodeId = function(node, include_path) {
	console.log("Util.nodeId IS DEPRECATED. Use commas in u.bug in stead.");
	console.log(arguments.callee.caller);
	try {
		if(!include_path) {
			return node.id ? node.nodeName+"#"+node.id : (node.className ? node.nodeName+"."+node.className : (node.name ? node.nodeName + "["+node.name+"]" : node.nodeName));
		}
		else {
			if(node.parentNode && node.parentNode.nodeName != "HTML") {
				return u.nodeId(node.parentNode, include_path) + "->" + u.nodeId(node);
			}
			else {
				return u.nodeId(node);
			}
		}
	}
	catch(exception) {
		u.exception("u.nodeId", arguments, exception);
	}
	return "Unindentifiable node!";
}
Util.exception = function(name, _arguments, _exception) {
	u.bug("Exception in: " + name + " (" + _exception + ")");
	console.error(_exception);
	u.bug("Invoked with arguments:");
	console.log(_arguments);
	// 
	// 
}
Util.bug = function() {
	if(u.debugURL()) {
		if(!u.bug_console_only) {
			var i, message;
			if(obj(console)) {
				for(i = 0; i < arguments.length; i++) {
					if(arguments[i] || typeof(arguments[i]) == "undefined") {
						console.log(arguments[i]);
					}
				}
			}
			var option, options = new Array([0, "auto", "auto", 0], [0, 0, "auto", "auto"], ["auto", 0, 0, "auto"], ["auto", "auto", 0, 0]);
			var corner = u.bug_corner ? u.bug_corner : 0;
			var color = u.bug_color ? u.bug_color : "black";
			option = options[corner];
			if(!document.getElementById("debug_id_"+corner)) {
				var d_target = u.ae(document.body, "div", {"class":"debug_"+corner, "id":"debug_id_"+corner});
				d_target.style.position = u.bug_position ? u.bug_position : "absolute";
				d_target.style.zIndex = 16000;
				d_target.style.top = option[0];
				d_target.style.right = option[1];
				d_target.style.bottom = option[2];
				d_target.style.left = option[3];
				d_target.style.backgroundColor = u.bug_bg ? u.bug_bg : "#ffffff";
				d_target.style.color = "#000000";
				d_target.style.fontSize = "11px";
				d_target.style.lineHeight = "11px";
				d_target.style.textAlign = "left";
				if(d_target.style.maxWidth) {
					d_target.style.maxWidth = u.bug_max_width ? u.bug_max_width+"px" : "auto";
				}
				d_target.style.padding = "2px 3px";
			}
			for(i = 0; i < arguments.length; i++) {
				if(arguments[i] === undefined) {
					message = "undefined";
				}
				else if(!str(arguments[i]) && fun(arguments[i].toString)) {
					message = arguments[i].toString();
				}
				else {
					message = arguments[i];
				}
				var debug_div = document.getElementById("debug_id_"+corner);
				message = message ? message.replace(/\>/g, "&gt;").replace(/\</g, "&lt;").replace(/&lt;br&gt;/g, "<br>") : "Util.bug with no message?";
				u.ae(debug_div, "div", {"style":"color: " + color, "html": message});
			}
		}
		else if(typeof(console) !== "undefined" && obj(console)) {
			var i;
			for(i = 0; i < arguments.length; i++) {
				console.log(arguments[i]);
			}
		}
	}
}
Util.xInObject = function(object, _options) {
	if(u.debugURL()) {
		var return_string = false;
		var explore_objects = false;
		if(obj(_options)) {
			var _argument;
			for(_argument in _options) {
				switch(_argument) {
					case "return"     : return_string               = _options[_argument]; break;
					case "objects"    : explore_objects             = _options[_argument]; break;
				}
			}
		}
		var x, s = "--- start object ---\n";
		for(x in object) {
			if(explore_objects && object[x] && obj(object[x]) && !str(object[x].nodeName)) {
				s += x + "=" + object[x]+" => \n";
				s += u.xInObject(object[x], true);
			}
			else if(object[x] && obj(object[x]) && str(object[x].nodeName)) {
				s += x + "=" + object[x]+" -> " + u.nodeId(object[x], 1) + "\n";
			}
			else if(object[x] && fun(object[x])) {
				s += x + "=function\n";
			}
			else {
				s += x + "=" + object[x]+"\n";
			}
		}
		s += "--- end object ---\n";
		if(return_string) {
			return s;
		}
		else {
			u.bug(s);
		}
	}
}


/*u-animation.js*/
Util.Animation = u.a = new function() {
	this.support3d = function() {
		if(this._support3d === undefined) {
			var node = u.ae(document.body, "div");
			try {
				u.as(node, "transform", "translate3d(10px, 10px, 10px)");
				if(u.gcs(node, "transform").match(/matrix3d\(1, 0, 0, 0, 0, 1, 0, 0, 0, 0, 1, 0, 10, 10, 10, 1\)/)) {
					this._support3d = true;
				}
	 			else {
					this._support3d = false;
				}
			}
			catch(exception) {
				this._support3d = false;
			}
			document.body.removeChild(node);
		}
		return this._support3d;
	}
	this.transition = function(node, transition, callback) {
		try {
			var duration = transition.match(/[0-9.]+[ms]+/g);
			if(duration) {
				node.duration = duration[0].match("ms") ? parseFloat(duration[0]) : (parseFloat(duration[0]) * 1000);
				if(callback) {
					var transitioned;
					transitioned = (function(event) {
						u.e.removeEvent(event.target, u.a.transitionEndEventName(), transitioned);
						if(event.target == this) {
							u.a.transition(this, "none");
							if(fun(callback)) {
								var key = u.randomString(4);
								node[key] = callback;
								node[key](event);
								delete node[key];
								callback = null;
							}
							else if(fun(this[callback])) {
								this[callback](event);
							}
						}
						else {
						}
					});
					u.e.addEvent(node, u.a.transitionEndEventName(), transitioned);
				}
				else {
					u.e.addEvent(node, u.a.transitionEndEventName(), this._transitioned);
				}
			}
			else {
				node.duration = false;
			}
			u.as(node, "transition", transition);
		}
		catch(exception) {
			u.exception("u.a.transition", arguments, exception);
		}
	}
	this.transitionEndEventName = function() {
		if(!this._transition_end_event_name) {
			this._transition_end_event_name = "transitionend";
			var transitions = {
				"transition": "transitionend",
				"MozTransition": "transitionend",
				"msTransition": "transitionend",
				"webkitTransition": "webkitTransitionEnd",
				"OTransition": "otransitionend"
			};
			var x, div = document.createElement("div");
			for(x in transitions){
				if(typeof(div.style[x]) !== "undefined") {
					this._transition_end_event_name = transitions[x];
					break;
				}
			}
		}
		return this._transition_end_event_name;
	}
	this._transitioned = function(event) {
		if(event.target == this) {
			u.e.removeEvent(event.target, u.a.transitionEndEventName(), u.a._transitioned);
			u.a.transition(event.target, "none");
			if(fun(this.transitioned)) {
				this.transitioned_before = this.transitioned;
				this.transitioned(event);
				if(this.transitioned === this.transitioned_before) {
					delete this.transitioned;
				}
			}
		}
	}
	this.translate = function(node, x, y) {
		if(this.support3d()) {
			u.as(node, "transform", "translate3d("+x+"px, "+y+"px, 0)");
		}
		else {
			u.as(node, "transform", "translate("+x+"px, "+y+"px)");
		}
		node._x = x;
		node._y = y;
		node.offsetHeight;
	}
	this.rotate = function(node, deg) {
		u.as(node, "transform", "rotate("+deg+"deg)");
		node._rotation = deg;
		node.offsetHeight;
	}
	this.scale = function(node, scale) {
		u.as(node, "transform", "scale("+scale+")");
		node._scale = scale;
		node.offsetHeight;
	}
	this.setOpacity = this.opacity = function(node, opacity) {
		u.as(node, "opacity", opacity);
		node._opacity = opacity;
		node.offsetHeight;
	}
	this.setWidth = this.width = function(node, width) {
		width = width.toString().match(/\%|auto|px/) ? width : (width + "px");
		node.style.width = width;
		node._width = width;
		node.offsetHeight;
	}
	this.setHeight = this.height = function(node, height) {
		height = height.toString().match(/\%|auto|px/) ? height : (height + "px");
		node.style.height = height;
		node._height = height;
		node.offsetHeight;
	}
	this.setBgPos = this.bgPos = function(node, x, y) {
		x = x.toString().match(/\%|auto|px|center|top|left|bottom|right/) ? x : (x + "px");
		y = y.toString().match(/\%|auto|px|center|top|left|bottom|right/) ? y : (y + "px");
		node.style.backgroundPosition = x + " " + y;
		node._bg_x = x;
		node._bg_y = y;
		node.offsetHeight;
	}
	this.setBgColor = this.bgColor = function(node, color) {
		node.style.backgroundColor = color;
		node._bg_color = color;
		node.offsetHeight;
	}
	// 
	// 	
	// 
	// 	
	// 	
	this._animationqueue = {};
	this.requestAnimationFrame = function(node, callback, duration) {
		duration = duration || false;
		if(!u.a.__animation_frame_start) {
			u.a.__animation_frame_start = Date.now();
		}
		var id = u.randomString();
		u.a._animationqueue[id] = {};
		u.a._animationqueue[id].id = id;
		u.a._animationqueue[id].node = node;
		u.a._animationqueue[id].callback = callback;
		u.a._animationqueue[id].duration = duration;
		if(duration) {
			u.t.setTimer(u.a, function() {u.a.finalAnimationFrame(id)}, duration);
		}
		if(!u.a._animationframe) {
			window._requestAnimationFrame = eval(u.vendorProperty("requestAnimationFrame"));
			window._cancelAnimationFrame = eval(u.vendorProperty("cancelAnimationFrame"));
			u.a._animationframe = function(timestamp) {
				var id, animation;
				for(id in u.a._animationqueue) {
					animation = u.a._animationqueue[id];
					if(!animation["__animation_frame_start_"+id]) {
						animation["__animation_frame_start_"+id] = timestamp;
					}
					if(fun(animation.node[animation.callback])) {
						animation.node[animation.callback]((timestamp-animation["__animation_frame_start_"+id]) / (animation.duration ? animation.duration : 1));
					}
				}
				if(Object.keys(u.a._animationqueue).length) {
					u.a._requestAnimationId = window._requestAnimationFrame(u.a._animationframe);
				}
			}
		}
		if(!u.a._requestAnimationId) {
			u.a._requestAnimationId = window._requestAnimationFrame(u.a._animationframe);
		}
		return id;
	}
	this.finalAnimationFrame = function(id) {
		var animation = u.a._animationqueue[id];
		animation["__animation_frame_start_"+id] = false;
		if(fun(animation.node[animation.callback])) {
			animation.node[animation.callback](1);
		}
		if(fun(animation.node.transitioned)) {
			animation.node.transitioned({});
		}
		delete u.a._animationqueue[id];
		if(!Object.keys(u.a._animationqueue).length) {
			this.cancelAnimationFrame(id);
		}
	}
	this.cancelAnimationFrame = function(id) {
		if(id && u.a._animationqueue[id]) {
			delete u.a._animationqueue[id];
		}
		if(u.a._requestAnimationId) {
			window._cancelAnimationFrame(u.a._requestAnimationId);
			u.a.__animation_frame_start = false;
			u.a._requestAnimationId = false;
		}
	}
}


/*u-cookie.js*/
Util.saveCookie = function(name, value, _options) {
	var expires = true;
	var path = false;
	var samesite = "lax";
	var force = false;
	if(obj(_options)) {
		var _argument;
		for(_argument in _options) {
			switch(_argument) {
				case "expires"	: expires	= _options[_argument]; break;
				case "path"		: path		= _options[_argument]; break;
				case "samesite"	: samesite	= _options[_argument]; break;
				case "force"	: force		= _options[_argument]; break;
			}
		}
	}
	if(!force && obj(window.localStorage) && obj(window.sessionStorage)) {
		if(expires === true) {
			window.sessionStorage.setItem(name, value);
		}
		else {
			window.localStorage.setItem(name, value);
		}
		return;
	}
	if(expires === false) {
		expires = ";expires="+(new Date((new Date()).getTime() + (1000*60*60*24*365))).toGMTString();
	}
	else if(str(expires)) {
		expires = ";expires="+expires;
	}
	else {
		expires = "";
	}
	if(str(path)) {
		path = ";path="+path;
	}
	else {
		path = "";
	}
	samesite = ";samesite="+samesite;
	document.cookie = encodeURIComponent(name) + "=" + encodeURIComponent(value) + path + expires + samesite;
}
Util.getCookie = function(name) {
	var matches;
	if(obj(window.sessionStorage) && window.sessionStorage.getItem(name)) {
		return window.sessionStorage.getItem(name)
	}
	else if(obj(window.localStorage) && window.localStorage.getItem(name)) {
		return window.localStorage.getItem(name)
	}
	return (matches = document.cookie.match(encodeURIComponent(name) + "=([^;]+)")) ? decodeURIComponent(matches[1]) : false;
}
Util.deleteCookie = function(name, _options) {
	var path = false;
	if(obj(_options)) {
		var _argument;
		for(_argument in _options) {
			switch(_argument) {
				case "path"	: path	= _options[_argument]; break;
			}
		}
	}
	if(obj(window.sessionStorage)) {
		window.sessionStorage.removeItem(name);
	}
	if(obj(window.localStorage)) {
		window.localStorage.removeItem(name);
	}
	if(str(path)) {
		path = ";path="+path;
	}
	else {
		path = "";
	}
	document.cookie = encodeURIComponent(name) + "=" + path + ";expires=Thu, 01-Jan-70 00:00:01 GMT";
}
Util.saveNodeCookie = function(node, name, value, _options) {
	var ref = u.cookieReference(node, _options);
	var mem = JSON.parse(u.getCookie("man_mem"));
	if(!mem) {
		mem = {};
	}
	if(!mem[ref]) {
		mem[ref] = {};
	}
	mem[ref][name] = (value !== false && value !== undefined) ? value : "";
	u.saveCookie("man_mem", JSON.stringify(mem), {"path":"/"});
}
Util.getNodeCookie = function(node, name, _options) {
	var ref = u.cookieReference(node, _options);
	var mem = JSON.parse(u.getCookie("man_mem"));
	if(mem && mem[ref]) {
		if(name) {
			return (typeof(mem[ref][name]) != "undefined") ? mem[ref][name] : false;
		}
		else {
			return mem[ref];
		}
	}
	return false;
}
Util.deleteNodeCookie = function(node, name, _options) {
	var ref = u.cookieReference(node, _options);
	var mem = JSON.parse(u.getCookie("man_mem"));
	if(mem && mem[ref]) {
		if(name) {
			delete mem[ref][name];
		}
		else {
			delete mem[ref];
		}
	}
	u.saveCookie("man_mem", JSON.stringify(mem), {"path":"/"});
}
Util.cookieReference = function(node, _options) {
	var ref;
	var ignore_classnames = false;
	var ignore_classvars = false;
	if(obj(_options)) {
		var _argument;
		for(_argument in _options) {
			switch(_argument) {
				case "ignore_classnames"	: ignore_classnames	= _options[_argument]; break;
				case "ignore_classvars" 	: ignore_classvars	= _options[_argument]; break;
			}
		}
	}
	if(node.id) {
		ref = node.nodeName + "#" + node.id;
	}
	else {
		var node_identifier = "";
		if(node.name) {
			node_identifier = node.nodeName + "["+node.name+"]";
		}
		else if(node.className) {
			var classname = node.className;
			if(ignore_classnames) {
				var regex = new RegExp("(^| )("+ignore_classnames.split(",").join("|")+")($| )", "g");
				classname = classname.replace(regex, " ").replace(/[ ]{2,4}/, " ");
			}
			if(ignore_classvars) {
				classname = classname.replace(/\b[a-zA-Z_]+\:[\?\=\w\/\\#~\:\.\,\+\&\%\@\!\-]+\b/g, "").replace(/[ ]{2,4}/g, " ");
			}
			node_identifier = node.nodeName+"."+classname.trim().replace(/ /g, ".");
		}
		else {
			node_identifier = node.nodeName
		}
		var id_node = node;
		while(!id_node.id) {
			id_node = id_node.parentNode;
		}
		if(id_node.id) {
			ref = id_node.nodeName + "#" + id_node.id + " " + node_identifier;
		}
		else {
			ref = node_identifier;
		}
	}
	return ref;
}


/*u-date.js*/
Util.date = function(format, timestamp, months) {
	var date = timestamp ? new Date(timestamp) : new Date();
	if(isNaN(date.getTime())) {
		if(new Date(timestamp.replace(/ /, "T"))) {
			date = new Date(timestamp.replace(/ /, "T"));
		}
		else {
			if(!timestamp.match(/[A-Z]{3}\+[0-9]{4}/)) {
				if(timestamp.match(/ \+[0-9]{4}/)) {
					date = new Date(timestamp.replace(/ (\+[0-9]{4})/, " GMT$1"));
				}
			}
		}
		if(isNaN(date.getTime())) {
			date = new Date();
		}
	}
	var tokens = /d|j|m|n|F|Y|G|H|i|s/g;
	var chars = new Object();
	chars.j = date.getDate();
	chars.d = (chars.j > 9 ? "" : "0") + chars.j;
	chars.n = date.getMonth()+1;
	chars.m = (chars.n > 9 ? "" : "0") + chars.n;
	chars.F = months ? months[date.getMonth()] : "";
	chars.Y = date.getFullYear();
	chars.G = date.getHours();
	chars.H = (chars.G > 9 ? "" : "0") + chars.G;
	var i = date.getMinutes();
	chars.i = (i > 9 ? "" : "0") + i;
	var s = date.getSeconds();
	chars.s = (s > 9 ? "" : "0") + s;
	return format.replace(tokens, function (_) {
		return _ in chars ? chars[_] : _.slice(1, _.length - 1);
	});
};


/*u-dom.js*/
Util.querySelector = u.qs = function(query, scope) {
	scope = scope ? scope : document;
	return scope.querySelector(query);
}
Util.querySelectorAll = u.qsa = function(query, scope) {
	try {
		scope = scope ? scope : document;
		return scope.querySelectorAll(query);
	}
	catch(exception) {
		u.exception("u.qsa", arguments, exception);
	}
	return [];
}
Util.getElement = u.ge = function(identifier, scope) {
	var node, nodes, i, regexp;
	if(document.getElementById(identifier)) {
		return document.getElementById(identifier);
	}
	scope = scope ? scope : document;
	regexp = new RegExp("(^|\\s)" + identifier + "(\\s|$|\:)");
	nodes = scope.getElementsByTagName("*");
	for(i = 0; i < nodes.length; i++) {
		node = nodes[i];
		if(regexp.test(node.className)) {
			return node;
		}
	}
	return scope.getElementsByTagName(identifier).length ? scope.getElementsByTagName(identifier)[0] : false;
}
Util.getElements = u.ges = function(identifier, scope) {
	var node, nodes, i, regexp;
	var return_nodes = new Array();
	scope = scope ? scope : document;
	regexp = new RegExp("(^|\\s)" + identifier + "(\\s|$|\:)");
	nodes = scope.getElementsByTagName("*");
	for(i = 0; i < nodes.length; i++) {
		node = nodes[i];
		if(regexp.test(node.className)) {
			return_nodes.push(node);
		}
	}
	return return_nodes.length ? return_nodes : scope.getElementsByTagName(identifier);
}
Util.parentNode = u.pn = function(node, _options) {
	var exclude = "";
	var include = "";
	if(obj(_options)) {
		var _argument;
		for(_argument in _options) {
			switch(_argument) {
				case "include"      : include       = _options[_argument]; break;
				case "exclude"      : exclude       = _options[_argument]; break;
			}
		}
	}
	var exclude_nodes = exclude ? u.qsa(exclude) : [];
	var include_nodes = include ? u.qsa(include) : [];
	node = node.parentNode;
	while(node && (node.nodeType == 3 || node.nodeType == 8 || (exclude && (u.inNodeList(node, exclude_nodes))) || (include && (!u.inNodeList(node, include_nodes))))) {
		node = node.parentNode;
	}
	return node;
}
Util.previousSibling = u.ps = function(node, _options) {
	var exclude = "";
	var include = "";
	if(obj(_options)) {
		var _argument;
		for(_argument in _options) {
			switch(_argument) {
				case "include"      : include       = _options[_argument]; break;
				case "exclude"      : exclude       = _options[_argument]; break;
			}
		}
	}
	var exclude_nodes = exclude ? u.qsa(exclude, node.parentNode) : [];
	var include_nodes = include ? u.qsa(include, node.parentNode) : [];
	node = node.previousSibling;
	while(node && (node.nodeType == 3 || node.nodeType == 8 || (exclude && (u.inNodeList(node, exclude_nodes))) || (include && (!u.inNodeList(node, include_nodes))))) {
		node = node.previousSibling;
	}
	return node;
}
Util.nextSibling = u.ns = function(node, _options) {
	var exclude = "";
	var include = "";
	if(obj(_options)) {
		var _argument;
		for(_argument in _options) {
			switch(_argument) {
				case "include"      : include       = _options[_argument]; break;
				case "exclude"      : exclude       = _options[_argument]; break;
			}
		}
	}
	var exclude_nodes = exclude ? u.qsa(exclude, node.parentNode) : [];
	var include_nodes = include ? u.qsa(include, node.parentNode) : [];
	node = node.nextSibling;
	while(node && (node.nodeType == 3 || node.nodeType == 8 || (exclude && (u.inNodeList(node, exclude_nodes))) || (include && (!u.inNodeList(node, include_nodes))))) {
		node = node.nextSibling;
	}
	return node;
}
Util.childNodes = u.cn = function(node, _options) {
	var exclude = "";
	var include = "";
	if(obj(_options)) {
		var _argument;
		for(_argument in _options) {
			switch(_argument) {
				case "include"      : include       = _options[_argument]; break;
				case "exclude"      : exclude       = _options[_argument]; break;
			}
		}
	}
	var exclude_nodes = exclude ? u.qsa(exclude, node) : [];
	var include_nodes = include ? u.qsa(include, node) : [];
	var i, child;
	var children = new Array();
	for(i = 0; i < node.childNodes.length; i++) {
		child = node.childNodes[i]
		if(child && child.nodeType != 3 && child.nodeType != 8 && (!exclude || (!u.inNodeList(child, exclude_nodes))) && (!include || (u.inNodeList(child, include_nodes)))) {
			children.push(child);
		}
	}
	return children;
}
Util.appendElement = u.ae = function(_parent, node_type, attributes) {
	try {
		var node = (obj(node_type)) ? node_type : (node_type == "svg" ? document.createElementNS("http://www.w3.org/2000/svg", node_type) : document.createElement(node_type));
		node = _parent.appendChild(node);
		if(attributes) {
			var attribute;
			for(attribute in attributes) {
				if(attribute == "html") {
					node.innerHTML = attributes[attribute];
				}
				else {
					node.setAttribute(attribute, attributes[attribute]);
				}
			}
		}
		return node;
	}
	catch(exception) {
		u.exception("u.ae", arguments, exception);
	}
	return false;
}
Util.insertElement = u.ie = function(_parent, node_type, attributes) {
	try {
		var node = (obj(node_type)) ? node_type : (node_type == "svg" ? document.createElementNS("http://www.w3.org/2000/svg", node_type) : document.createElement(node_type));
		node = _parent.insertBefore(node, _parent.firstChild);
		if(attributes) {
			var attribute;
			for(attribute in attributes) {
				if(attribute == "html") {
					node.innerHTML = attributes[attribute];
				}
				else {
					node.setAttribute(attribute, attributes[attribute]);
				}
			}
		}
		return node;
	}
	catch(exception) {
		u.exception("u.ie", arguments, exception);
	}
	return false;
}
Util.wrapElement = u.we = function(node, node_type, attributes) {
	try {
		var wrapper_node = node.parentNode.insertBefore(document.createElement(node_type), node);
		if(attributes) {
			var attribute;
			for(attribute in attributes) {
				wrapper_node.setAttribute(attribute, attributes[attribute]);
			}
		}	
		wrapper_node.appendChild(node);
		return wrapper_node;
	}
	catch(exception) {
		u.exception("u.we", arguments, exception);
	}
	return false;
}
Util.wrapContent = u.wc = function(node, node_type, attributes) {
	try {
		var wrapper_node = document.createElement(node_type);
		if(attributes) {
			var attribute;
			for(attribute in attributes) {
				wrapper_node.setAttribute(attribute, attributes[attribute]);
			}
		}	
		while(node.childNodes.length) {
			wrapper_node.appendChild(node.childNodes[0]);
		}
		node.appendChild(wrapper_node);
		return wrapper_node;
	}
	catch(exception) {
		u.exception("u.wc", arguments, exception);
	}
	return false;
}
Util.textContent = u.text = function(node) {
	try {
		return node.textContent;
	}
	catch(exception) {
		u.exception("u.text", arguments, exception);
	}
	return "";
}
Util.clickableElement = u.ce = function(node, _options) {
	node._use_link = "a";
	node._click_type = "manual";
	if(obj(_options)) {
		var _argument;
		for(_argument in _options) {
			switch(_argument) {
				case "use"			: node._use_link		= _options[_argument]; break;
				case "type"			: node._click_type		= _options[_argument]; break;
			}
		}
	}
	var a = (node.nodeName.toLowerCase() == "a" ? node : u.qs(node._use_link, node));
	if(a) {
		u.ac(node, "link");
		if(a.getAttribute("href") !== null) {
			node.url = a.href;
			a.url = a.href;
			node.onclick = function(event) {
				event.preventDefault();
			}
			node._a = a;
		}
	}
	else {
		u.ac(node, "clickable");
	}
	if(obj(u.e) && fun(u.e.click)) {
		u.e.click(node, _options);
		if(node._click_type == "link") {
			node.clicked = function(event) {
				if(fun(node.preClicked)) {
					node.preClicked();
				}
				if(event && (event.metaKey || event.ctrlKey || (this._a && this._a.target))) {
					window.open(this.url);
				}
				else {
					if(obj(u.h) && u.h.is_listening) {
						u.h.navigate(this.url, this);
					}
					else {
						location.href = this.url;
					}
				}
			}
		}
	}
	return node;
}
Util.classVar = u.cv = function(node, var_name) {
	try {
		var regexp = new RegExp("(\^| )" + var_name + ":[?=\\w/\\#~:.,?+=?&%@!\\-]*");
		var match = node.className.match(regexp);
		if(match) {
			return match[0].replace(var_name + ":", "").trim();
		}
	}
	catch(exception) {
		u.exception("u.cv", arguments, exception);
	}
	return false;
}
Util.setClass = u.sc = function(node, classname, dom_update) {
	var old_class;
	if(node instanceof SVGElement) {
		old_class = node.className.baseVal;
		node.setAttribute("class", classname);
	}
	else {
		old_class = node.className;
		node.className = classname;
	}
	dom_update = (dom_update === false) || (node.offsetTop);
	return old_class;
}
Util.hasClass = u.hc = function(node, classname) {
	if(node.classList.contains(classname)) {
		return true;
	}
	else {
		var regexp = new RegExp("(^|\\s)(" + classname + ")(\\s|$)");
		if(node instanceof SVGElement) {
			if(regexp.test(node.className.baseVal)) {
				return true;
			}
		}
		else {
			if(regexp.test(node.className)) {
				return true;
			}
		}
	}
	return false;
}
Util.addClass = u.ac = function(node, classname, dom_update) {
	var classnames = classname.split(" ");
	while(classnames.length) {
		node.classList.add(classnames.shift());
	}
	dom_update = (dom_update === false) || (node.offsetTop);
	return node.className;
}
Util.removeClass = u.rc = function(node, classname, dom_update) {
	if(node.classList.contains(classname)) {
		node.classList.remove(classname);
	}
	else {
		var regexp = new RegExp("(^|\\s)(" + classname + ")(?=[\\s]|$)", "g");
		if(node instanceof SVGElement) {
			node.setAttribute("class", node.className.baseVal.replace(regexp, " ").trim().replace(/[\s]{2}/g, " "));
		}
		else {
			node.className = node.className.replace(regexp, " ").trim().replace(/[\s]{2}/g, " ");
		}
	}
	dom_update = (dom_update === false) || (node.offsetTop);
	return node.className;
}
Util.toggleClass = u.tc = function(node, classname, _classname, dom_update) {
	if(u.hc(node, classname)) {
		u.rc(node, classname, dom_update);
		if(_classname) {
			u.ac(node, _classname, dom_update);
		}
	}
	else {
		u.ac(node, classname);
		if(_classname) {
			u.rc(node, _classname, dom_update);
		}
	}
	dom_update = (dom_update === false) || (node.offsetTop);
	return node.className;
}
Util.applyStyle = u.as = function(node, property, value, dom_update) {
	node.style[u.vendorProperty(property)] = value;
	dom_update = (dom_update === false) || (node.offsetTop);
}
Util.applyStyles = u.ass = function(node, styles, dom_update) {
	if(styles) {
		var style;
		for(style in styles) {
			if(obj(u.a) && style == "transition") {
				u.a.transition(node, styles[style]);
			}
			else {
				node.style[u.vendorProperty(style)] = styles[style];
			}
		}
	}
	dom_update = (dom_update === false) || (node.offsetTop);
}
Util.getComputedStyle = u.gcs = function(node, property) {
	var dom_update = node.offsetHeight;
	property = (u.vendorProperty(property).replace(/([A-Z]{1})/g, "-$1")).toLowerCase().replace(/^(webkit|ms)/, "-$1");
	return window.getComputedStyle(node, null).getPropertyValue(property);
}
Util.hasFixedParent = u.hfp = function(node) {
	while(node.nodeName.toLowerCase() != "body") {
		if(u.gcs(node.parentNode, "position").match("fixed")) {
			return true;
		}
		node = node.parentNode;
	}
	return false;
}
u.contains = function(scope, node) {
	if(scope != node) {
		if(scope.contains(node)) {
			return true
		}
	}
	return false;
}
u.containsOrIs = function(scope, node) {
	if(scope == node || u.contains(scope, node)) {
		return true
	}
	return false;
}
u.elementMatches = u.em = function(node, selector) {
	return node.matches(selector);
}
Util.insertAfter = u.ia = function(insert_node, after_node) {
	var next_node = u.ns(after_node);
	if(next_node) {
		after_node.parentNode.insertBefore(insert_node, next_node);
	}
	else {
		after_node.parentNode.appendChild(insert_node);
	}
}
Util.selectText = function(node) {
	var selection = window.getSelection();
	var range = document.createRange();
	range.selectNodeContents(node);
	selection.removeAllRanges();
	selection.addRange(range);
}
Util.inNodeList = function(node, list) {
	var i, list_node;
	for(i = 0; i < list.length; i++) {
		list_node = list[i]
		if(list_node === node) {
			return true;
		}
	}
	return false;
}


/*u-easings.js*/
u.easings = new function() {
	this["ease-in"] = function(progress) {
		return Math.pow((progress), 3);
	}
	this["linear"] = function(progress) {
		return progress;
	}
	this["ease-out"] = function(progress) {
		return 1 - Math.pow(1 - ((progress)), 3);
	}
	this["linear"] = function(progress) {
		return (progress);
	}
	this["ease-in-out-veryslow"] = function(progress) {
		if(progress > 0.5) {
			return 4*Math.pow((progress-1),3)+1;
		}
		return 4*Math.pow(progress,3);  
	}
	this["ease-in-out"] = function(progress) {
		if(progress > 0.5) {
			return 1 - Math.pow(1 - ((progress)), 2);
		}
		return Math.pow((progress), 2);
	}
	this["ease-out-slow"] = function(progress) {
		return 1 - Math.pow(1 - ((progress)), 2);
	}
	this["ease-in-slow"] = function(progress) {
		return Math.pow((progress), 2);
	}
	this["ease-in-veryslow"] = function(progress) {
		return Math.pow((progress), 1.5);
	}
	this["ease-in-fast"] = function(progress) {
		return Math.pow((progress), 4);
	}
	this["easeOutQuad"] = function (progress) {
		d = 1;
		b = 0;
		c = progress;
		t = progress;
		t /= d;
		return -c * t*(t-2) + b;
	};
	this["easeOutCubic"] = function (progress) {
		d = 1;
		b = 0;
		c = progress;
		t = progress;
		t /= d;
		t--;
		return c*(t*t*t + 1) + b;
	};
	this["easeOutQuint"] = function (progress) {
		d = 1;
		b = 0;
		c = progress;
		t = progress;
		t /= d;
		t--;
		return c*(t*t*t*t*t + 1) + b;
	};
	this["easeInOutSine"] = function (progress) {
		d = 1;
		b = 0;
		c = progress;
		t = progress;
		return -c/2 * (Math.cos(Math.PI*t/d) - 1) + b;
	};
	this["easeInOutElastic"] = function (progress) {
		d = 1;
		b = 0;
		c = progress;
		t = progress;
		var s=1.70158;var p=0;var a=c;
		if (t==0) return b;  if ((t/=d/2)==2) return b+c;  if (!p) p=d*(.3*1.5);
		if (a < Math.abs(c)) { a=c; var s=p/4; }
		else var s = p/(2*Math.PI) * Math.asin (c/a);
		if (t < 1) return -.5*(a*Math.pow(2,10*(t-=1)) * Math.sin( (t*d-s)*(2*Math.PI)/p )) + b;
		return a*Math.pow(2,-10*(t-=1)) * Math.sin( (t*d-s)*(2*Math.PI)/p )*.5 + c + b;
	}
	this["easeOutBounce"] = function (progress) {
		d = 1;
		b = 0;
		c = progress;
		t = progress;
			if ((t/=d) < (1/2.75)) {
				return c*(7.5625*t*t) + b;
			} else if (t < (2/2.75)) {
				return c*(7.5625*(t-=(1.5/2.75))*t + .75) + b;
			} else if (t < (2.5/2.75)) {
				return c*(7.5625*(t-=(2.25/2.75))*t + .9375) + b;
			} else {
				return c*(7.5625*(t-=(2.625/2.75))*t + .984375) + b;
			}
	}
	this["easeInBack"] = function (progress) {
		var s = 1.70158;
		d = 1;
		b = 0;
		c = progress;
		t = progress;
			return c*(t/=d)*t*((s+1)*t - s) + b;
	}
}

/*u-events.js*/
Util.Events = u.e = new function() {
	this.event_pref = typeof(document.ontouchmove) == "undefined" || (navigator.maxTouchPoints > 1 && navigator.userAgent.match(/Windows/i)) ? "mouse" : "touch";
	if (navigator.userAgent.match(/Windows/i) && ((obj(document.ontouchmove) && obj(document.onmousemove)) || (fun(document.ontouchmove) && fun(document.onmousemove)))) {
		this.event_support = "multi";
	}
	else if (obj(document.ontouchmove) || fun(document.ontouchmove)) {
		this.event_support = "touch";
	}
	else {
		this.event_support = "mouse";
	}
	this.events = {
		"mouse": {
			"start":"mousedown",
			"move":"mousemove",
			"end":"mouseup",
			"over":"mouseover",
			"out":"mouseout"
		},
		"touch": {
			"start":"touchstart",
			"move":"touchmove",
			"end":"touchend",
			"over":"touchstart",
			"out":"touchend"
		}
	}
	this.kill = function(event) {
		if(event) {
			event.preventDefault();
			event.stopPropagation();
		}
	}
	this.addEvent = function(node, type, action) {
		try {
			node.addEventListener(type, action, false);
		}
		catch(exception) {
			u.exception("u.e.addEvent", arguments, exception);
		}
	}
	this.removeEvent = function(node, type, action) {
		try {
			node.removeEventListener(type, action, false);
		}
		catch(exception) {
			u.exception("u.e.removeEvent", arguments, exception);
		}
	}
	this.addStartEvent = this.addDownEvent = function(node, action) {
		if(this.event_support == "multi") {
			u.e.addEvent(node, this.events.mouse.start, action);
			u.e.addEvent(node, this.events.touch.start, action);
		}
		else {
			u.e.addEvent(node, this.events[this.event_support].start, action);
		}
	}
	this.removeStartEvent = this.removeDownEvent = function(node, action) {
		if(this.event_support == "multi") {
			u.e.removeEvent(node, this.events.mouse.start, action);
			u.e.removeEvent(node, this.events.touch.start, action);
		}
		else {
			u.e.removeEvent(node, this.events[this.event_support].start, action);
		}
	}
	this.addMoveEvent = function(node, action) {
		if(this.event_support == "multi") {
			u.e.addEvent(node, this.events.mouse.move, action);
			u.e.addEvent(node, this.events.touch.move, action);
		}
		else {
			u.e.addEvent(node, this.events[this.event_support].move, action);
		}
	}
	this.removeMoveEvent = function(node, action) {
		if(this.event_support == "multi") {
			u.e.removeEvent(node, this.events.mouse.move, action);
			u.e.removeEvent(node, this.events.touch.move, action);
		}
		else {
			u.e.removeEvent(node, this.events[this.event_support].move, action);
		}
	}
	this.addEndEvent = this.addUpEvent = function(node, action) {
		if(this.event_support == "multi") {
			u.e.addEvent(node, this.events.mouse.end, action);
			u.e.addEvent(node, this.events.touch.end, action);
		}
		else {
			u.e.addEvent(node, this.events[this.event_support].end, action);
		}
	}
	this.removeEndEvent = this.removeUpEvent = function(node, action) {
		if(this.event_support == "multi") {
			u.e.removeEvent(node, this.events.mouse.end, action);
			u.e.removeEvent(node, this.events.touch.end, action);
		}
		else {
			u.e.removeEvent(node, this.events[this.event_support].end, action);
		}
	}
	this.addOverEvent = function(node, action) {
		if(this.event_support == "multi") {
			u.e.addEvent(node, this.events.mouse.over, action);
			u.e.addEvent(node, this.events.touch.over, action);
		}
		else {
			u.e.addEvent(node, this.events[this.event_support].over, action);
		}
	}
	this.removeOverEvent = function(node, action) {
		if(this.event_support == "multi") {
			u.e.removeEvent(node, this.events.mouse.over, action);
			u.e.removeEvent(node, this.events.touch.over, action);
		}
		else {
			u.e.removeEvent(node, this.events[this.event_support].over, action);
		}
	}
	this.addOutEvent = function(node, action) {
		if(this.event_support == "multi") {
			u.e.addEvent(node, this.events.mouse.out, action);
			u.e.addEvent(node, this.events.touch.out, action);
		}
		else {
			u.e.addEvent(node, this.events[this.event_support].out, action);
		}
	}
	this.removeOutEvent = function(node, action) {
		if(this.event_support == "multi") {
			u.e.removeEvent(node, this.events.mouse.out, action);
			u.e.removeEvent(node, this.events.touch.out, action);
		}
		else {
			u.e.removeEvent(node, this.events[this.event_support].out, action);
		}
	}
	this.resetClickEvents = function(node) {
		u.t.resetTimer(node.t_held);
		u.t.resetTimer(node.t_clicked);
		this.removeEvent(node, "mouseup", this._dblclicked);
		this.removeEvent(node, "touchend", this._dblclicked);
		this.removeEvent(node, "mouseup", this._rightclicked);
		this.removeEvent(node, "touchend", this._rightclicked);
		this.removeEvent(node, "mousemove", this._cancelClick);
		this.removeEvent(node, "touchmove", this._cancelClick);
		this.removeEvent(node, "mouseout", this._cancelClick);
		this.removeEvent(node, "mousemove", this._move);
		this.removeEvent(node, "touchmove", this._move);
	}
	this.resetEvents = function(node) {
		this.resetClickEvents(node);
		if(fun(this.resetDragEvents)) {
			this.resetDragEvents(node);
		}
	}
	this.resetNestedEvents = function(node) {
		while(node && node.nodeName != "HTML") {
			this.resetEvents(node);
			node = node.parentNode;
		}
	}
	this._inputStart = function(event) {
		this.event_var = event;
		this.input_timestamp = event.timeStamp;
		this.start_event_x = u.eventX(event);
		this.start_event_y = u.eventY(event);
		this.current_xps = 0;
		this.current_yps = 0;
		this.move_timestamp = event.timeStamp;
		this.move_last_x = 0;
		this.move_last_y = 0;
		this.swiped = false;
		if(!event.button) {
			if(this.e_click || this.e_dblclick || this.e_hold) {
				if(event.type.match(/mouse/)) {
					var node = this;
					while(node) {
						if(node.e_drag || node.e_swipe) {
							u.e.addMoveEvent(this, u.e._cancelClick);
							break;
						}
						else {
							node = node.parentNode;
						}
					}
					u.e.addEvent(this, "mouseout", u.e._cancelClick);
				}
				else {
					u.e.addMoveEvent(this, u.e._cancelClick);
				}
				u.e.addMoveEvent(this, u.e._move);
				u.e.addEndEvent(this, u.e._dblclicked);
				if(this.e_hold) {
					this.t_held = u.t.setTimer(this, u.e._held, 750);
				}
			}
			if(this.e_drag || this.e_swipe) {
				u.e.addMoveEvent(this, u.e._pick);
				this.e_cancelPick = u.e.addWindowEndEvent(this, u.e._cancelPick);
			}
			if(this.e_scroll) {
				u.e.addMoveEvent(this, u.e._scrollStart);
				u.e.addEndEvent(this, u.e._scrollEnd);
			}
		}
		else if(event.button === 2) {
			if(this.e_rightclick) {
				if(event.type.match(/mouse/)) {
					u.e.addEvent(this, "mouseout", u.e._cancelClick);
				}
				else {
					u.e.addMoveEvent(this, u.e._cancelClick);
				}
				u.e.addMoveEvent(this, u.e._move);
				u.e.addEndEvent(this, u.e._rightclicked);
			}
		}
		if(fun(this.inputStarted)) {
			this.inputStarted(event);
		}
	}
	this._cancelClick = function(event) {
		var offset_x = u.eventX(event) - this.start_event_x;
		var offset_y = u.eventY(event) - this.start_event_y;
		if(event.type.match(/mouseout/) || (event.type.match(/move/) && (Math.abs(offset_x) > 15 || Math.abs(offset_y) > 15))) {
			u.e.resetClickEvents(this);
			if(fun(this.clickCancelled)) {
				this.clickCancelled(event);
			}
		}
	}
	this._move = function(event) {
		if(fun(this.moved)) {
			this.current_x = u.eventX(event) - this.start_event_x;
			this.current_y = u.eventY(event) - this.start_event_y;
			this.current_xps = Math.round(((this.current_x - this.move_last_x) / (event.timeStamp - this.move_timestamp)) * 1000);
			this.current_yps = Math.round(((this.current_y - this.move_last_y) / (event.timeStamp - this.move_timestamp)) * 1000);
			this.move_timestamp = event.timeStamp;
			this.move_last_x = this.current_x;
			this.move_last_y = this.current_y;
			this.moved(event);
		}
	}
	this.hold = function(node, _options) {
		node.e_hold_options = _options ? _options : {};
		node.e_hold_options.eventAction = u.stringOr(node.e_hold_options.eventAction, "Held");
		node.e_hold = true;
		u.e.addStartEvent(node, this._inputStart);
	}
	this._held = function(event) {
		this.e_hold_options.event = this.e_hold_options.event || "hold";
		u.stats.event(this, this.e_hold_options);
		u.e.resetNestedEvents(this);
		if(fun(this.held)) {
			this.held(event);
		}
	}
	this.click = this.tap = function(node, _options) {
		node.e_click_options = _options ? _options : {};
		node.e_click_options.eventAction = u.stringOr(node.e_click_options.eventAction, "Clicked");
		node.e_click = true;
		u.e.addStartEvent(node, this._inputStart);
	}
	this._clicked = function(event) {
		if(this.e_click_options) {
			this.e_click_options.event = this.e_click_options.event || "click";
			u.stats.event(this, this.e_click_options);
		}
		u.e.resetNestedEvents(this);
		if(fun(this.clicked)) {
			this.clicked(event);
		}
	}
	this.rightclick = function(node, _options) {
		node.e_rightclick_options = _options ? _options : {};
		node.e_rightclick_options.eventAction = u.stringOr(node.e_rightclick_options.eventAction, "RightClicked");
		node.e_rightclick = true;
		u.e.addStartEvent(node, this._inputStart);
		u.e.addEvent(node, "contextmenu", function(event){u.e.kill(event);});
	}
	this._rightclicked = function(event) {
		u.bug("_rightclicked:", this);
		if(this.e_rightclick_options) {
			this.e_rightclick_options.event = this.e_rightclick_options.event || "rightclick";
			u.stats.event(this, this.e_rightclick_options);
		}
		u.e.resetNestedEvents(this);
		if(fun(this.rightclicked)) {
			this.rightclicked(event);
		}
	}
	this.dblclick = this.doubleclick = this.doubletap = this.dbltap = function(node, _options) {
		node.e_dblclick_options = _options ? _options : {};
		node.e_dblclick_options.eventAction = u.stringOr(node.e_dblclick_options.eventAction, "DblClicked");
		node.e_dblclick = true;
		u.e.addStartEvent(node, this._inputStart);
	}
	this._dblclicked = function(event) {
		if(u.t.valid(this.t_clicked) && event) {
			this.e_dblclick_options.event = this.e_dblclick_options.event || "doubleclick";
			u.stats.event(this, this.e_dblclick_options);
			u.e.resetNestedEvents(this);
			if(fun(this.dblclicked)) {
				this.dblclicked(event);
			}
			return;
		}
		else if(!this.e_dblclick) {
			this._clicked = u.e._clicked;
			this._clicked(event);
		}
		else if(event.type == "timeout") {
			this._clicked = u.e._clicked;
			this._clicked(this.event_var);
		}
		else {
			u.e.resetNestedEvents(this);
			this.t_clicked = u.t.setTimer(this, u.e._dblclicked, 400);
		}
	}
	this.hover = function(node, _options) {
		node._hover_out_delay = 100;
		node._hover_over_delay = 0;
		node._callback_out = "out";
		node._callback_over = "over";
		if(obj(_options)) {
			var argument;
			for(argument in _options) {
				switch(argument) {
					case "over"				: node._callback_over		= _options[argument]; break;
					case "out"				: node._callback_out		= _options[argument]; break;
					case "delay_over"		: node._hover_over_delay	= _options[argument]; break;
					case "delay"			: node._hover_out_delay		= _options[argument]; break;
				}
			}
		}
		node.e_hover = true;
		u.e.addOverEvent(node, this._over);
		u.e.addOutEvent(node, this._out);
	}
	this._over = function(event) {
		u.t.resetTimer(this.t_out);
		if(!this._hover_over_delay) {
			u.e.__over.call(this, event);
		}
		else if(!u.t.valid(this.t_over)) {
			this.t_over = u.t.setTimer(this, u.e.__over, this._hover_over_delay, event);
		}
	}
	this.__over = function(event) {
		u.t.resetTimer(this.t_out);
		if(!this.is_hovered) {
			this.is_hovered = true;
			u.e.removeOverEvent(this, u.e._over);
			u.e.addOverEvent(this, u.e.__over);
			if(fun(this[this._callback_over])) {
				this[this._callback_over](event);
			}
		}
	}
	this._out = function(event) {
		u.t.resetTimer(this.t_over);
		u.t.resetTimer(this.t_out);
		this.t_out = u.t.setTimer(this, u.e.__out, this._hover_out_delay, event);
	}
	this.__out = function(event) {
		this.is_hovered = false;
		u.e.removeOverEvent(this, u.e.__over);
		u.e.addOverEvent(this, u.e._over);
		if(fun(this[this._callback_out])) {
			this[this._callback_out](event);
		}
	}
}


/*u-events-browser.js*/
u.e.addDOMReadyEvent = function(action) {
	if(document.readyState && document.addEventListener) {
		if((document.readyState == "interactive" && !u.browser("ie")) || document.readyState == "complete" || document.readyState == "loaded") {
			action();
		}
		else {
			var id = u.randomString();
			window["_DOMReady_" + id] = {
				id: id,
				action: action,
				callback: function(event) {
					if(fun(this.action)) {
						this.action.bind(window)(event);
					}
					else if(fun(this[this.action])){
						this[this.action].bind(window)(event);
					}
 					u.e.removeEvent(document, "DOMContentLoaded", window["_DOMReady_" + this.id].eventCallback); 
					delete window["_DOMReady_" + this.id];
				}
			}
			eval('window["_DOMReady_' + id + '"].eventCallback = function() {window["_DOMReady_'+id+'"].callback(event);}');
			u.e.addEvent(document, "DOMContentLoaded", window["_DOMReady_" + id].eventCallback);
		}
	}
	else {
		u.e.addOnloadEvent(action);
	}
}
u.e.addOnloadEvent = function(action) {
	if(document.readyState && (document.readyState == "complete" || document.readyState == "loaded")) {
		action();
	}
	else {
		var id = u.randomString();
		window["_Onload_" + id] = {
			id: id,
			action: action,
			callback: function(event) {
				if(fun(this.action)) {
					this.action.bind(window)(event);
				}
				else if(fun(this[this.action])){
					this[this.action].bind(window)(event);
				}
				u.e.removeEvent(document, "load", window["_Onload_" + this.id].eventCallback); 
				delete window["_Onload_" + this.id];
			}
		}
		eval('window["_Onload_' + id + '"].eventCallback = function() {window["_Onload_'+id+'"].callback(event);}');
		u.e.addEvent(window, "load", window["_Onload_" + id].eventCallback);
	}
}
u.e.addWindowEvent = function(node, type, action) {
	var id = u.randomString();
	window["_OnWindowEvent_"+ id] = {
		id: id,
		node: node,
		type: type,
		action: action,
		callback: function(event) {
			if(fun(this.action)) {
				this.action.bind(this.node)(event);
			}
			else if(fun(this[this.action])){
				this[this.action](event);
			}
		}
	};
	eval('window["_OnWindowEvent_' + id + '"].eventCallback = function(event) {window["_OnWindowEvent_'+ id + '"].callback(event);}');
	u.e.addEvent(window, type, window["_OnWindowEvent_" + id].eventCallback);
	return id;
}
u.e.removeWindowEvent = function(id) {
	if(window["_OnWindowEvent_" + id]) {
		u.e.removeEvent(window, window["_OnWindowEvent_"+id].type, window["_OnWindowEvent_"+id].eventCallback);
		delete window["_OnWindowEvent_"+id];
	}
}
u.e.addWindowStartEvent = function(node, action) {
	var id = u.randomString();
	window["_OnWindowStartEvent_"+ id] = {
		id: id,
		node: node,
		action: action,
		callback: function(event) {
			if(fun(this.action)) {
				this.action.bind(this.node)(event);
			}
			else if(fun(this[this.action])){
				this[this.action](event);
			}
		}
	};
	eval('window["_OnWindowStartEvent_' + id + '"].eventCallback = function(event) {window["_OnWindowStartEvent_'+ id + '"].callback(event);}');
	u.e.addStartEvent(window, window["_OnWindowStartEvent_" + id].eventCallback);
	return id;
}
u.e.removeWindowStartEvent = function(id) {
	if(window["_OnWindowStartEvent_" + id]) {
		u.e.removeStartEvent(window, window["_OnWindowStartEvent_"+id].eventCallback);
		delete window["_OnWindowStartEvent_"+id];
	}
}
u.e.addWindowMoveEvent = function(node, action) {
	var id = u.randomString();
	window["_OnWindowMoveEvent_"+ id] = {
		id: id,
		node: node,
		action: action,
		callback: function(event) {
			if(fun(this.action)) {
				this.action.bind(this.node)(event);
			}
			else if(fun(this[this.action])){
				this[this.action](event);
			}
		}
	};
	eval('window["_OnWindowMoveEvent_' + id + '"].eventCallback = function(event) {window["_OnWindowMoveEvent_'+ id + '"].callback(event);}');
	u.e.addMoveEvent(window, window["_OnWindowMoveEvent_" + id].eventCallback);
	return id;
}
u.e.removeWindowMoveEvent = function(id) {
	if(window["_OnWindowMoveEvent_" + id]) {
		u.e.removeMoveEvent(window, window["_OnWindowMoveEvent_"+id].eventCallback);
		delete window["_OnWindowMoveEvent_"+id];
	}
}
u.e.addWindowEndEvent = function(node, action) {
	var id = u.randomString();
	window["_OnWindowEndEvent_"+ id] = {
		id: id,
		node: node,
		action: action,
		callback: function(event) {
			if(fun(this.action)) {
				this.action.bind(this.node)(event);
			}
			else if(fun(this[this.action])){
				this[this.action](event);
			}
		}
	};
	eval('window["_OnWindowEndEvent_' + id + '"].eventCallback = function(event) {window["_OnWindowEndEvent_'+ id + '"].callback(event);}');
	u.e.addEndEvent(window, window["_OnWindowEndEvent_" + id].eventCallback);
	return id;
}
u.e.removeWindowEndEvent = function(id) {
	if(window["_OnWindowEndEvent_" + id]) {
		u.e.removeEndEvent(window, window["_OnWindowEndEvent_" + id].eventCallback);
		delete window["_OnWindowEndEvent_"+id];
	}
}


/*u-events-movements.js*/
u.e.resetDragEvents = function(node) {
	node._moves_pick = 0;
	this.removeEvent(node, "mousemove", this._pick);
	this.removeEvent(node, "touchmove", this._pick);
	this.removeEvent(node, "mousemove", this._drag);
	this.removeEvent(node, "touchmove", this._drag);
	this.removeEvent(node, "mouseup", this._drop);
	this.removeEvent(node, "touchend", this._drop);
	this.removeWindowEndEvent(node.e_cancelPick);
	this.removeEvent(node, "mouseout", this._dropOut);
	this.removeEvent(node, "mousemove", this._scrollStart);
	this.removeEvent(node, "touchmove", this._scrollStart);
	this.removeEvent(node, "mousemove", this._scrolling);
	this.removeEvent(node, "touchmove", this._scrolling);
	this.removeEvent(node, "mouseup", this._scrollEnd);
	this.removeEvent(node, "touchend", this._scrollEnd);
}
u.e.overlap = function(node, boundaries, strict) {
	if(boundaries.constructor.toString().match("Array")) {
		var boundaries_start_x = Number(boundaries[0]);
		var boundaries_start_y = Number(boundaries[1]);
		var boundaries_end_x = Number(boundaries[2]);
		var boundaries_end_y = Number(boundaries[3]);
	}
	else if(boundaries.constructor.toString().match("HTML")) {
		var boundaries_start_x = u.absX(boundaries) - u.absX(node);
		var boundaries_start_y =  u.absY(boundaries) - u.absY(node);
		var boundaries_end_x = Number(boundaries_start_x + boundaries.offsetWidth);
		var boundaries_end_y = Number(boundaries_start_y + boundaries.offsetHeight);
	}
	var node_start_x = Number(node._x);
	var node_start_y = Number(node._y);
	var node_end_x = Number(node_start_x + node.offsetWidth);
	var node_end_y = Number(node_start_y + node.offsetHeight);
	if(strict) {
		if(node_start_x >= boundaries_start_x && node_start_y >= boundaries_start_y && node_end_x <= boundaries_end_x && node_end_y <= boundaries_end_y) {
			return true;
		}
		else {
			return false;
		}
	} 
	else if(node_end_x < boundaries_start_x || node_start_x > boundaries_end_x || node_end_y < boundaries_start_y || node_start_y > boundaries_end_y) {
		return false;
	}
	return true;
}
u.e.drag = function(node, boundaries, _options) {
	node.e_drag_options = _options ? _options : {};
	node.e_drag = true;
	if(node.childNodes.length < 2 && node.innerHTML.trim() == "") {
		node.innerHTML = "&nbsp;";
	}
	node.distance_to_pick = 2;
	node.drag_strict = true;
	node.drag_overflow = false;
	node.drag_elastica = 0;
	node.drag_dropout = true;
	node.show_bounds = false;
	node.callback_ready = "ready";
	node.callback_picked = "picked";
	node.callback_moved = "moved";
	node.callback_dropped = "dropped";
	if(obj(_options)) {
		var _argument;
		for(_argument in _options) {
			switch(_argument) {
				case "strict"			: node.drag_strict			= _options[_argument]; break;
				case "overflow"			: node.drag_overflow		= _options[_argument]; break;
				case "elastica"			: node.drag_elastica		= Number(_options[_argument]); break;
				case "dropout"			: node.drag_dropout			= _options[_argument]; break;
				case "show_bounds"		: node.show_bounds			= _options[_argument]; break; 
				case "vertical_lock"	: node.vertical_lock		= _options[_argument]; break;
				case "horizontal_lock"	: node.horizontal_lock		= _options[_argument]; break;
				case "callback_picked"	: node.callback_picked		= _options[_argument]; break;
				case "callback_moved"	: node.callback_moved		= _options[_argument]; break;
				case "callback_dropped"	: node.callback_dropped		= _options[_argument]; break;
			}
		}
	}
	u.e.setDragBoundaries(node, boundaries);
	u.e.addStartEvent(node, this._inputStart);
	if(fun(node[node.callback_ready])) {
		node[node.callback_ready]();
	}
}
u.e._pick = function(event) {
	var init_speed_x = Math.abs(this.start_event_x - u.eventX(event));
	var init_speed_y = Math.abs(this.start_event_y - u.eventY(event));
	if(
		(init_speed_x > init_speed_y && this.only_horizontal) || 
		(init_speed_x < init_speed_y && this.only_vertical) ||
		(!this.only_vertical && !this.only_horizontal)) {
		if((init_speed_x > this.distance_to_pick || init_speed_y > this.distance_to_pick)) {
			u.e.resetNestedEvents(this);
			u.e.kill(event);
			if(u.hasFixedParent(this)) {
				this.has_fixed_parent = true;
			}
			else {
				this.has_fixed_parent = false;
			}
			this.move_timestamp = event.timeStamp;
			this.move_last_x = this._x;
			this.move_last_y = this._y;
			if(u.hasFixedParent(this)) {
				this.start_input_x = u.eventX(event) - this._x - u.scrollX(); 
				this.start_input_y = u.eventY(event) - this._y - u.scrollY();
			}
			else {
				this.start_input_x = u.eventX(event) - this._x; 
				this.start_input_y = u.eventY(event) - this._y;
			}
			this.current_xps = 0;
			this.current_yps = 0;
			u.a.transition(this, "none");
			u.e.addMoveEvent(this, u.e._drag);
			u.e.addEndEvent(this, u.e._drop);
			if(fun(this[this.callback_picked])) {
				this[this.callback_picked](event);
			}
			if(this.drag_dropout && event.type.match(/mouse/)) {
				// 	
				// 	
				// 	
				// 	
				// 	
				// 
				// 
				// 	
				this._dropOutDrag = u.e._drag;
				this._dropOutDrop = u.e._drop;
				u.e.addOutEvent(this, u.e._dropOut);
			}
		}
	}
}
u.e._drag = function(event) {
	if(this.has_fixed_parent) {
		this.current_x = u.eventX(event) - this.start_input_x - u.scrollX();
		this.current_y = u.eventY(event) - this.start_input_y - u.scrollY();
	}
	else {
		this.current_x = u.eventX(event) - this.start_input_x;
		this.current_y = u.eventY(event) - this.start_input_y;
	}
	this.current_xps = Math.round(((this.current_x - this.move_last_x) / (event.timeStamp - this.move_timestamp)) * 1000);
	this.current_yps = Math.round(((this.current_y - this.move_last_y) / (event.timeStamp - this.move_timestamp)) * 1000);
	this.last_x_distance_travelled = (this.current_xps) ? this.current_x - this.move_last_x : this.last_x_distance_travelled;
	this.last_y_distance_travelled = (this.current_yps) ? this.current_y - this.move_last_y : this.last_y_distance_travelled;
	this.move_timestamp = event.timeStamp;
	this.move_last_x = this.current_x;
	this.move_last_y = this.current_y;
	if(!this.locked && this.only_vertical) {
		this._y = this.current_y;
	}
	else if(!this.locked && this.only_horizontal) {
		this._x = this.current_x;
	}
	else if(!this.locked) {
		this._x = this.current_x;
		this._y = this.current_y;
	}
	if(this.e_swipe) {
		if(this.only_horizontal) {
			if(this.current_xps < 0 || this.current_xps === 0 && this.last_x_distance_travelled < 0) {
				this.swiped = "left";
			}
			else {
				this.swiped = "right";
			}
		}
		else if(this.only_vertical) {
			if(this.current_yps < 0 || this.current_yps === 0 && this.last_y_distance_travelled < 0) {
				this.swiped = "up";
			}
			else {
				this.swiped = "down";
			}
		}
		else {
			if(Math.abs(this.current_xps) > Math.abs(this.current_yps)) {
				if(this.current_xps < 0) {
					this.swiped = "left";
				}
				else {
					this.swiped = "right";
				}
			}
			else if(Math.abs(this.current_xps) < Math.abs(this.current_yps)) {
				if(this.current_yps < 0) {
					this.swiped = "up";
				}
				else {
					this.swiped = "down";
				}
			}
		}
	}
	if(!this.locked) {
		if(u.e.overlap(this, [this.start_drag_x, this.start_drag_y, this.end_drag_x, this.end_drag_y], true)) {
			u.a.translate(this, this._x, this._y);
		}
		else if(this.drag_elastica) {
			this.swiped = false;
			this.current_xps = 0;
			this.current_yps = 0;
			var offset = false;
			if(!this.only_vertical && this._x < this.start_drag_x) {
				offset = this._x < this.start_drag_x - this.drag_elastica ? - this.drag_elastica : this._x - this.start_drag_x;
				this._x = this.start_drag_x;
				this.current_x = this._x + offset + (Math.round(Math.pow(offset, 2)/this.drag_elastica));
			}
			else if(!this.only_vertical && this._x + this.offsetWidth > this.end_drag_x) {
				offset = this._x + this.offsetWidth > this.end_drag_x + this.drag_elastica ? this.drag_elastica : this._x + this.offsetWidth - this.end_drag_x;
				this._x = this.end_drag_x - this.offsetWidth;
				this.current_x = this._x + offset - (Math.round(Math.pow(offset, 2)/this.drag_elastica));
			}
			else {
				this.current_x = this._x;
			}
			if(!this.only_horizontal && this._y < this.start_drag_y) {
				offset = this._y < this.start_drag_y - this.drag_elastica ? - this.drag_elastica : this._y - this.start_drag_y;
				this._y = this.start_drag_y;
				this.current_y = this._y + offset + (Math.round(Math.pow(offset, 2)/this.drag_elastica));
			}
			else if(!this.horizontal && this._y + this.offsetHeight > this.end_drag_y) {
				offset = (this._y + this.offsetHeight > this.end_drag_y + this.drag_elastica) ? this.drag_elastica : (this._y + this.offsetHeight - this.end_drag_y);
				this._y = this.end_drag_y - this.offsetHeight;
				this.current_y = this._y + offset - (Math.round(Math.pow(offset, 2)/this.drag_elastica));
			}
			else {
				this.current_y = this._y;
			}
			if(offset) {
				u.a.translate(this, this.current_x, this.current_y);
			}
		}
		else {
			this.swiped = false;
			this.current_xps = 0;
			this.current_yps = 0;
			if(this._x < this.start_drag_x) {
				this._x = this.start_drag_x;
			}
			else if(this._x + this.offsetWidth > this.end_drag_x) {
				this._x = this.end_drag_x - this.offsetWidth;
			}
			if(this._y < this.start_drag_y) {
				this._y = this.start_drag_y;
			}
			else if(this._y + this.offsetHeight > this.end_drag_y) { 
				this._y = this.end_drag_y - this.offsetHeight;
			}
			u.a.translate(this, this._x, this._y);
		}
	}
	if(fun(this[this.callback_moved])) {
		this[this.callback_moved](event);
	}
}
u.e._drop = function(event) {
	u.e.resetEvents(this);
	if(this.e_swipe && this.swiped) {
		this.e_swipe_options.eventAction = "Swiped "+ this.swiped;
		u.stats.event(this, this.e_swipe_options);
		if(this.swiped == "left" && fun(this.swipedLeft)) {
			this.swipedLeft(event);
		}
		else if(this.swiped == "right" && fun(this.swipedRight)) {
			this.swipedRight(event);
		}
		else if(this.swiped == "down" && fun(this.swipedDown)) {
			this.swipedDown(event);
		}
		else if(this.swiped == "up" && fun(this.swipedUp)) {
			this.swipedUp(event);
		}
	}
	else if(!this.drag_strict && !this.locked) {
		this.current_x = Math.round(this._x + (this.current_xps/2));
		this.current_y = Math.round(this._y + (this.current_yps/2));
		if(this.only_vertical || this.current_x < this.start_drag_x) {
			this.current_x = this.start_drag_x;
		}
		else if(this.current_x + this.offsetWidth > this.end_drag_x) {
			this.current_x = this.end_drag_x - this.offsetWidth;
		}
		if(this.only_horizontal || this.current_y < this.start_drag_y) {
			this.current_y = this.start_drag_y;
		}
		else if(this.current_y + this.offsetHeight > this.end_drag_y) {
			this.current_y = this.end_drag_y - this.offsetHeight;
		}
		this.transitioned = function() {
			if(fun(this.projected)) {
				this.projected(event);
			}
		}
		if(this.current_xps || this.current_yps) {
			u.a.transition(this, "all 1s cubic-bezier(0,0,0.25,1)");
		}
		else {
			u.a.transition(this, "none");
		}
		u.a.translate(this, this.current_x, this.current_y);
	}
	if(this.e_drag && !this.e_swipe) {
		this.e_drag_options.eventAction = u.stringOr(this.e_drag_options.eventAction, "Dropped");
		u.stats.event(this, this.e_drag_options);
	}
	if(fun(this[this.callback_dropped])) {
		this[this.callback_dropped](event);
	}
}
u.e._dropOut = function(event) {
	this._drop_out_id = u.randomString();
	document["_DroppedOutNode" + this._drop_out_id] = this;
	eval('document["_DroppedOutMove' + this._drop_out_id + '"] = function(event) {document["_DroppedOutNode' + this._drop_out_id + '"]._dropOutDrag(event);}');
	eval('document["_DroppedOutOver' + this._drop_out_id + '"] = function(event) {u.e.removeEvent(document, "mousemove", document["_DroppedOutMove' + this._drop_out_id + '"]);u.e.removeEvent(document, "mouseup", document["_DroppedOutEnd' + this._drop_out_id + '"]);u.e.removeEvent(document["_DroppedOutNode' + this._drop_out_id + '"], "mouseover", document["_DroppedOutOver' + this._drop_out_id + '"]);}');
	eval('document["_DroppedOutEnd' + this._drop_out_id + '"] = function(event) {u.e.removeEvent(document, "mousemove", document["_DroppedOutMove' + this._drop_out_id + '"]);u.e.removeEvent(document, "mouseup", document["_DroppedOutEnd' + this._drop_out_id + '"]);u.e.removeEvent(document["_DroppedOutNode' + this._drop_out_id + '"], "mouseover", document["_DroppedOutOver' + this._drop_out_id + '"]);document["_DroppedOutNode' + this._drop_out_id + '"]._dropOutDrop(event);}');
	u.e.addEvent(document, "mousemove", document["_DroppedOutMove" + this._drop_out_id]);
	u.e.addEvent(this, "mouseover", document["_DroppedOutOver" + this._drop_out_id]);
	u.e.addEvent(document, "mouseup", document["_DroppedOutEnd" + this._drop_out_id]);
}
u.e._cancelPick = function(event) {
	u.e.resetDragEvents(this);
	if(fun(this.pickCancelled)) {
		this.pickCancelled(event);
	}
}
u.e.setDragBoundaries = function(node, boundaries) {
	if((boundaries.constructor && boundaries.constructor.toString().match("Array")) || (boundaries.scopeName && boundaries.scopeName != "HTML")) {
		node.start_drag_x = Number(boundaries[0]);
		node.start_drag_y = Number(boundaries[1]);
		node.end_drag_x = Number(boundaries[2]);
		node.end_drag_y = Number(boundaries[3]);
	}
	else if((boundaries.constructor && boundaries.constructor.toString().match("HTML")) || (boundaries.scopeName && boundaries.scopeName == "HTML")) {
		if(node.drag_overflow == "scroll") {
			node.start_drag_x = node.offsetWidth > boundaries.offsetWidth ? boundaries.offsetWidth - node.offsetWidth : 0;
			node.start_drag_y = node.offsetHeight > boundaries.offsetHeight ? boundaries.offsetHeight - node.offsetHeight : 0;
			node.end_drag_x = node.offsetWidth > boundaries.offsetWidth ? node.offsetWidth : boundaries.offsetWidth;
			node.end_drag_y = node.offsetHeight > boundaries.offsetHeight ? node.offsetHeight : boundaries.offsetHeight;
		}
		else {
			node.start_drag_x = u.absX(boundaries) - u.absX(node);
			node.start_drag_y = u.absY(boundaries) - u.absY(node);
			node.end_drag_x = node.start_drag_x + boundaries.offsetWidth;
			node.end_drag_y = node.start_drag_y + boundaries.offsetHeight;
		}
	}
	if(node.show_bounds) {
		var debug_bounds = u.ae(document.body, "div", {"class":"debug_bounds"})
		debug_bounds.style.position = "absolute";
		debug_bounds.style.background = "red"
		debug_bounds.style.left = (u.absX(node) + node.start_drag_x - 1) + "px";
		debug_bounds.style.top = (u.absY(node) + node.start_drag_y - 1) + "px";
		debug_bounds.style.width = (node.end_drag_x - node.start_drag_x) + "px";
		debug_bounds.style.height = (node.end_drag_y - node.start_drag_y) + "px";
		debug_bounds.style.border = "1px solid white";
		debug_bounds.style.zIndex = 9999;
		debug_bounds.style.opacity = .5;
		if(document.readyState && document.readyState == "interactive") {
			debug_bounds.innerHTML = "WARNING - injected on DOMLoaded"; 
		}
		u.bug("node: ", node, " in (" + u.absX(node) + "," + u.absY(node) + "), (" + (u.absX(node)+node.offsetWidth) + "," + (u.absY(node)+node.offsetHeight) +")");
		u.bug("boundaries: (" + node.start_drag_x + "," + node.start_drag_y + "), (" + node.end_drag_x + ", " + node.end_drag_y + ")");
	}
	node._x = node._x ? node._x : 0;
	node._y = node._y ? node._y : 0;
	if(node.drag_overflow == "scroll" && (boundaries.constructor && boundaries.constructor.toString().match("HTML")) || (boundaries.scopeName && boundaries.scopeName == "HTML")) {
		node.locked = ((node.end_drag_x - node.start_drag_x <= boundaries.offsetWidth) && (node.end_drag_y - node.start_drag_y <= boundaries.offsetHeight));
		node.only_vertical = (node.vertical_lock || (!node.locked && node.end_drag_x - node.start_drag_x <= boundaries.offsetWidth));
		node.only_horizontal = (node.horizontal_lock || (!node.locked && node.end_drag_y - node.start_drag_y <= boundaries.offsetHeight));
	}
	else {
		node.locked = ((node.end_drag_x - node.start_drag_x == node.offsetWidth) && (node.end_drag_y - node.start_drag_y == node.offsetHeight));
		node.only_vertical = (node.vertical_lock || (!node.locked && node.end_drag_x - node.start_drag_x == node.offsetWidth));
		node.only_horizontal = (node.horizontal_lock || (!node.locked && node.end_drag_y - node.start_drag_y == node.offsetHeight));
	}
}
u.e.setDragPosition = function(node, x, y) {
	node.current_xps = 0;
	node.current_yps = 0;
	node._x = x;
	node._y = y;
	u.a.translate(node, node._x, node._y);
	if(fun(node[node.callback_moved])) {
		node[node.callback_moved](event);
	}
}
u.e.swipe = function(node, boundaries, _options) {
	node.e_swipe_options = _options ? _options : {};
	node.e_swipe = true;
	u.e.drag(node, boundaries, _options);
}


/*u-form.js*/
Util.Form = u.f = new function() {
	this.customInit = {};
	this.customValidate = {};
	this.customDataFormat = {};
	this.customHintPosition = {};
	this.customLabelStyle = {};
	this.init = function(_form, _options) {
		var i, j, field, action, input, hidden_input;
		_form._bulk_operation = true;
		if(_form.nodeName.toLowerCase() != "form") {
			_form.native_form = u.pn(_form, {"include":"form"});
			if(!_form.native_form) {
				u.bug("there is no form in this document??");
				return;
			}
		}
		else {
			_form.native_form = _form;
		}
		_form._focus_z_index = 50;
		_form._validation = true;
		_form._debug = false;
		_form._item_id = _form.getAttribute("data-item_id");
		if(!_form._item_id) {
			var item_id_match = _form.action.match(/\/([0-9]+)(\/|$)/);
			if(item_id_match) {
				_form._item_id = item_id_match[1];
			}
		}
		_form._label_style = u.cv(_form, "labelstyle");
		_form._callback_ready = "ready";
		_form._callback_submitted = "submitted";
		_form._callback_submit_failed = "submitFailed";
		_form._callback_pre_submitted = "preSubmitted";
		_form._callback_resat = "resat";
		_form._callback_updated = "updated";
		_form._callback_changed = "changed";
		_form._callback_blurred = "blurred";
		_form._callback_focused = "focused";
		_form._callback_validation_failed = "validationFailed";
		_form._callback_validation_passed = "validationPassed";
		if(obj(_options)) {
			var _argument;
			for(_argument in _options) {
				switch(_argument) {
					case "validation"               : _form._validation                = _options[_argument]; break;
					case "debug"                    : _form._debug                     = _options[_argument]; break;
					case "focus_z"                  : _form._focus_z_index             = _options[_argument]; break;
					case "label_style"              : _form._label_style               = _options[_argument]; break;
					case "callback_ready"           : _form._callback_ready            = _options[_argument]; break;
					case "callback_submitted"       : _form._callback_submitted        = _options[_argument]; break;
					case "callback_submit_failed"   : _form._callback_submit_failed    = _options[_argument]; break;
					case "callback_pre_submitted"   : _form._callback_pre_submitted    = _options[_argument]; break;
					case "callback_resat"           : _form._callback_resat            = _options[_argument]; break;
					case "callback_updated"         : _form._callback_updated          = _options[_argument]; break;
					case "callback_changed"         : _form._callback_changed          = _options[_argument]; break;
					case "callback_blurred"         : _form._callback_blurred          = _options[_argument]; break;
					case "callback_focused"         : _form._callback_focused          = _options[_argument]; break;
					case "callback_validation_failed"         : _form._callback_validation_failed          = _options[_argument]; break;
					case "callback_validation_passed"         : _form._callback_validation_passed          = _options[_argument]; break;
				}
			}
		}
		_form._hover_z_index = _form._focus_z_index - 1;
		_form.native_form.onsubmit = function(event) {
			if(event.target._form) {
				return false;
			}
		}
		_form.native_form.setAttribute("novalidate", "novalidate");
		_form.DOMsubmit = _form.native_form.submit;
		_form.submit = this._submit;
		_form.DOMreset = _form.native_form.reset;
		_form.reset = this._reset;
		_form.getData = function(_options) {
			return u.f.getFormData(this, _options);
		}
		_form.inputs = {};
		_form.actions = {};
		_form._error_inputs = {};
		var hidden_inputs = u.qsa("input[type=hidden]", _form);
		for(i = 0; i < hidden_inputs.length; i++) {
			hidden_input = hidden_inputs[i];
			if(!_form.inputs[hidden_input.name]) {
				_form.inputs[hidden_input.name] = hidden_input;
				hidden_input._form = _form;
				hidden_input.val = this._value;
			}
		}
		var fields = u.qsa(".field", _form);
		for(i = 0; i < fields.length; i++) {
			field = fields[i];
			u.f.initField(_form, field);
		}
		var actions = u.qsa(".actions li input[type=button],.actions li input[type=submit],.actions li input[type=reset],.actions li a.button", _form);
		for(i = 0; i < actions.length; i++) {
			action = actions[i];
			this.initButton(_form, action);
		}
		u.t.setTimer(_form, function() {
			var validate_inputs = [];
			for(input in this.inputs) {
				if(this.inputs[input].field) {
					validate_inputs.push(this.inputs[input]);
				}
			}
			u.f.bulkValidate(validate_inputs);
			if(_form._debug) {
				u.bug(_form, "inputs:", _form.inputs, "actions:", _form.actions);
			}
			if(fun(this[this._callback_ready])) {
				this[this._callback_ready]();
			}
		}, 100);
	}
	this.initField = function(_form, field) {
		field._form = _form;
		field._base_z_index = u.gcs(field, "z-index");
		field.help = u.qs(".help", field);
		field.hint = u.qs(".hint", field);
		field.error = u.qs(".error", field);
		field.label = u.qs("label", field);
		field.indicator = u.ae(field, "div", {"class":"indicator"});
		if(fun(u.f.fixFieldHTML)) {
			u.f.fixFieldHTML(field);
		}
		field._custom_initialized = false;
		var custom_init;
		for(custom_init in this.customInit) {
			if(u.hc(field, custom_init)) {
				this.customInit[custom_init](field);
				field._custom_initialized = true;
				break;
			}
		}
		if(!field._custom_initialized) {
			if(u.hc(field, "string|email|tel|number|integer|password")) {
				field.type = field.className.match(/(?:^|\b)(string|email|tel|number|integer|password)(?:\b|$)/)[0];
				field.input = u.qs("input", field);
				field.input._form = _form;
				field.input.label = u.qs("label[for='"+field.input.id+"']", field);
				field.input.field = field;
				field.input.val = this._value;
				u.e.addEvent(field.input, "keyup", this._updated);
				u.e.addEvent(field.input, "change", this._changed);
				this.inputOnEnter(field.input);
				this.activateInput(field.input);
			}
			else if(u.hc(field, "text")) {
				field.type = "text";
				field.input = u.qs("textarea", field);
				field.input._form = _form;
				field.input.label = u.qs("label[for='"+field.input.id+"']", field);
				field.input.field = field;
				field.input.val = this._value;
				if(u.hc(field, "autoexpand")) {
					u.ass(field.input, {
						"overflow": "hidden"
					});
					field.input.setHeight = function() {
						u.ass(this, {
							height: "auto"
						});
						u.ass(this, {
							height: (this.scrollHeight) + "px"
						});
					}
					u.e.addEvent(field.input, "input", field.input.setHeight);
					field.input.setHeight();
				}
				u.e.addEvent(field.input, "keyup", this._updated);
				u.e.addEvent(field.input, "change", this._changed);
				this.activateInput(field.input);
			}
			else if(u.hc(field, "json")) {
				field.type = "json";
				field.input = u.qs("textarea", field);
				field.input._form = _form;
				field.input.label = u.qs("label[for='"+field.input.id+"']", field);
				field.input.field = field;
				field.input.val = this._value;
				if(u.hc(field, "autoexpand")) {
					u.ass(field.input, {
						"overflow": "hidden"
					});
					field.input.setHeight = function() {
						u.ass(this, {
							height: "auto"
						});
						u.ass(this, {
							height: (this.scrollHeight) + "px"
						});
					}
					u.e.addEvent(field.input, "input", field.input.setHeight);
					field.input.setHeight();
				}
				u.e.addEvent(field.input, "keyup", this._updated);
				u.e.addEvent(field.input, "change", this._changed);
				this.activateInput(field.input);
			}
			else if(u.hc(field, "select")) {
				field.type = "select";
				field.input = u.qs("select", field);
				field.input._form = _form;
				field.input.label = u.qs("label[for='"+field.input.id+"']", field);
				field.input.field = field;
				field.input.val = this._value_select;
				u.e.addEvent(field.input, "change", this._updated);
				u.e.addEvent(field.input, "keyup", this._updated);
				u.e.addEvent(field.input, "change", this._changed);
				this.activateInput(field.input);
			}
			else if(u.hc(field, "checkbox|boolean")) {
				field.type = field.className.match(/(?:^|\b)(checkbox|boolean)(?:\b|$)/)[0];
				field.input = u.qs("input[type=checkbox]", field);
				field.input._form = _form;
				field.input.label = u.qs("label[for='"+field.input.id+"']", field);
				field.input.field = field;
				field.input.val = this._value_checkbox;
				u.f._update_checkbox_field.bind(field.input)();
				u.e.addEvent(field.input, "change", this._changed);
				u.e.addEvent(field.input, "change", this._updated);
				u.e.addEvent(field.input, "change", this._update_checkbox_field);
				this.inputOnEnter(field.input);
				this.activateInput(field.input);
				if(u.e.event_support != "touch") {
					u.e.addEvent(field.input.label, "mouseenter", this._mouseenter.bind(field.input));
					u.e.addEvent(field.input.label, "mouseleave", this._mouseleave.bind(field.input));
				}
			}
			else if(u.hc(field, "radiobuttons")) {
				field.type = "radiobuttons";
				field.inputs = u.qsa("input", field);
				field.input = field.inputs[0];
				for(j = 0; j < field.inputs.length; j++) {
					input = field.inputs[j];
					input._form = _form;
					input.label = u.qs("label[for='"+input.id+"']", field);
					input.field = field;
					input.val = this._value_radiobutton;
					u.e.addEvent(input, "change", this._changed);
					u.e.addEvent(input, "change", this._updated);
					this.inputOnEnter(input);
					this.activateInput(input);
					if(u.e.event_support != "touch") {
						u.e.addEvent(input.label, "mouseenter", this._mouseenter.bind(input));
						u.e.addEvent(input.label, "mouseleave", this._mouseleave.bind(input));
					}
				}
			}
			else if(u.hc(field, "date|datetime")) {
				field.type = field.className.match(/(?:^|\b)(date|datetime)(?:\b|$)/)[0];
				field.input = u.qs("input", field);
				field.input._form = _form;
				field.input.label = u.qs("label[for='"+field.input.id+"']", field);
				field.input.field = field;
				field.input.val = this._value_date;
				u.e.addEvent(field.input, "keyup", this._updated);
				u.e.addEvent(field.input, "change", this._changed);
				u.e.addEvent(field.input, "change", this._updated);
				this.inputOnEnter(field.input);
				this.activateInput(field.input);
			}
			else if(u.hc(field, "files")) {
				field.type = "files";
				field.input = u.qs("input", field);
				field.input._form = _form;
				field.input.label = u.qs("label[for='"+field.input.id+"']", field);
				field.input.field = field;
				field.file_delete_action = field.getAttribute("data-file-delete");
				field.file_order_action = field.getAttribute("data-file-order");
				field.file_update_metadata_action = field.getAttribute("data-file-update-metadata");
				field.file_media_info_action = field.getAttribute("data-file-media-info");
				field._item_id = _form._item_id;
				field.input.val = this._value_file;
				field.div_droparea = u.we(field.input, "div", {"class":"droparea"});
				field.filelist = u.qs("ul.filelist", field);
				if(!field.filelist) {
					field.filelist = u.ae(field.div_droparea, "ul", {"class":"filelist"});
				}
				else {
					field.div_droparea.appendChild(field.filelist);
				}
				field.filelist.field = field;
				field.uploaded_files = u.qsa("li.uploaded", field.filelist);
				this._update_filelist.bind(field.input)();
				u.e.addEvent(field.input, "change", this._update_filelist);
				// 
				if(u.e.event_support != "touch") {
					u.e.addEvent(field.input, "dragenter", this._focus);
					u.e.addEvent(field.input, "dragleave", this._blur);
					u.e.addEvent(field.input, "drop", this._blur);
				}
				this.activateInput(field.input);
			}
			else {
				u.bug("UNKNOWN FIELD IN FORM INITIALIZATION:", field);
			}
		}
		if(field.input) {
			_form.inputs[field.input.name] = field.input;
			if(!_form._bulk_operation) {
				this.validate(field.input);
			}
		}
		if(field.virtual_input && !field.virtual_input.tabindex) {
			field.virtual_input.setAttribute("tabindex", 0);
			field.input.setAttribute("tabindex", 0);
		}
		else if(field.input && field.input.getAttribute("readonly")) {
			field.input.setAttribute("tabindex", -1);
		}
		else if(field.input && !field.input.tabindex) {
			field.input.setAttribute("tabindex", 0);
		}
	}
	this.initButton = function(_form, action) {
		action._form = _form;
		action.setAttribute("tabindex", 0);
		action.confirm = action.getAttribute("data-confirm");
		this.buttonOnEnter(action);
		this.activateButton(action);
	}
	this._reset = function(event, iN) {
		for (name in this.inputs) {
			if (this.inputs[name] && this.inputs[name].field && this.inputs[name].type != "hidden" && !this.inputs[name].getAttribute("readonly")) {
				this.inputs[name]._used = false;
				this.inputs[name].val("");
				if(fun(u.f.updateDefaultState)) {
					u.f.updateDefaultState(this.inputs[name]);
				}
			}
		}
		if(fun(this[this._callback_resat])) {
			this[this._callback_resat](iN);
		}
	}
	this._submit = function(event, iN) {
		var validate_inputs = [];
		for(name in this.inputs) {
			if(this.inputs[name] && this.inputs[name].field && fun(this.inputs[name].val)) {
				this.inputs[name]._used = true;
				validate_inputs.push(this.inputs[name]);
			}
		}
		u.f.bulkValidate(validate_inputs);
		if(!Object.keys(this._error_inputs).length) {
			if(fun(this[this._callback_pre_submitted])) {
				this[this._callback_pre_submitted](iN);
			}
			if(fun(this[this._callback_submitted])) {
				this[this._callback_submitted](iN);
			}
			else {
				for(name in this.inputs) {
					if(this.inputs[name] && this.inputs[name].default_value && this.inputs[name].nodeName.match(/^(input|textarea)$/i)) {
						if(fun(this.inputs[name].val) && !this.inputs[name].val()) {
							this.inputs[name].value = "";
						}
					}
				}
				this.DOMsubmit();
			}
		}
		else {
			if(fun(this[this._callback_submit_failed])) {
				this[this._callback_submit_failed](iN);
			}
		}
	}
	this._value = function(value) {
		if(value !== undefined) {
			this.value = value;
			if(value !== this.default_value) {
				u.rc(this, "default");
			}
			u.f.validate(this);
		}
		return (this.value != this.default_value) ? this.value : "";
	}
	this._value_radiobutton = function(value) {
		var i, option;
		if(value !== undefined) {
			for(i = 0; i < this.field.inputs.length; i++) {
				option = this.field.inputs[i];
				if(option.value == value || (option.value == "true" && value) || (option.value == "false" && value === false)) {
					option.checked = true;
					u.f.validate(this);
				}
				else {
					option.checked = false;
				}
			}
		}
		for(i = 0; i < this.field.inputs.length; i++) {
			option = this.field.inputs[i];
			if(option.checked) {
				return option.value;
			}
		}
		return "";
	}
	this._value_checkbox = function(value) {
		if(value !== undefined) {
			if(value) {
				this.checked = true
			}
			else {
				this.checked = false;
			}
			u.f._update_checkbox_field.bind(this)();
			u.f.validate(this);
		}
		if(this.checked) {
			return this.value;
		}
		return "";
	}
	this._value_select = function(value) {
		if(value !== undefined) {
			var i, option;
			for(i = 0; i < this.options.length; i++) {
				option = this.options[i];
				if(option.value == value) {
					this.selectedIndex = i;
					u.f.validate(this);
					return this.options[this.selectedIndex].value;
				}
			}
			if (value === "") {
				this.selectedIndex = -1;
				u.f.validate(this);
				return "";
			}
		}
		return (this.selectedIndex >= 0 && this.default_value != this.options[this.selectedIndex].value) ? this.options[this.selectedIndex].value : "";
	}
	this._value_date = function(value) {
		if(value !== undefined) {
			this.value = value;
			if(value !== this.default_value) {
				u.rc(this, "default");
			}
			u.f.validate(this);
		}
		return (this.value != this.default_value) ? this.value.replace("T", " ") : "";
	}
	this._value_file = function(value) {
		if(value !== undefined) {
			if(value === "") {
				this.value = null;
			}
			else {
				u.bug('ADDING VALUES MANUALLY TO INPUT type="file" IS NOT SUPPORTED IN JAVASCRIPT');
			}
			u.f._update_filelist.bind(this)();
			u.f.validate(this);
		}
		if(this.files && this.files.length) {
			var i, file, files = [];
			for(i = 0; i < this.files.length; i++) {
				file = this.files[i];
				files.push(file);
			}
			return files;
		}
		else if(!this.files && this.value) {
			return this.value;
		}
		else if(this.field.uploaded_files && this.field.uploaded_files.length){
			return true;
		}
		return "";
	}
	this._changed = function(event) {
		u.f.positionHint(this.field);
		if(fun(this[this._form._callback_changed])) {
			this[this._form._callback_changed](this);
		}
		else if(fun(this.field.input[this._form._callback_changed])) {
			this.field.input[this._form._callback_changed](this);
		}
		if(fun(this._form[this._form._callback_changed])) {
			this._form[this._form._callback_changed](this);
		}
	}
	this._updated = function(event) {
		if(event.keyCode != 9 && event.keyCode != 13 && event.keyCode != 16 && event.keyCode != 17 && event.keyCode != 18) {
			u.f.validate(this);
			if(fun(this[this._form._callback_updated])) {
				this[this._form._callback_updated](this);
			}
			else if(fun(this.field.input[this._form._callback_updated])) {
				this.field.input[this._form._callback_updated](this);
			}
			if(fun(this._form[this._form._callback_updated])) {
				this._form[this._form._callback_updated](this);
			}
		}
	}
	this._update_checkbox_field = function(event) {
		if(this.checked) {
			u.ac(this.field, "checked");
		}
		else {
			u.rc(this.field, "checked");
		}
	}
	this._update_filelist = function(event) {
		var i;
		var files = this.val();
		this.field.filelist.innerHTML = "";
		this.e_updated = event;
		u.ae(this.field.filelist, "li", {
			"html":this.field.hint ? u.text(this.field.hint) : u.text(this.label), class:"label",
		});
		if(files && files.length) {
			u.ac(this.field, "has_new_files");
			var i, file, li_file;
			this.field.filelist.load_queue = 0;
			for(i = 0; i < files.length; i++) {
				file = files[i];
				li_file = u.ae(this.field.filelist, "li", {"html":file.name, "class":"new"})
				li_file.setAttribute("data-format", file.name.substring(file.name.lastIndexOf(".")+1).toLowerCase());
				li_file.input = this;
				if(file.type.match(/image/)) {
					li_file.image = new Image();
					li_file.image.li = li_file;
					u.ac(li_file, "loading");
					this.field.filelist.load_queue++;
					li_file.image.onload = function() {
						this.li.setAttribute("data-width", this.width);
						this.li.setAttribute("data-height", this.height);
						u.rc(this.li, "loading");
						this.li.input.field.filelist.load_queue--;
						delete this.li.image;
						u.f.filelistUpdated(this.li.input);
					}
					li_file.image.src = URL.createObjectURL(file);
				}
				else if(file.type.match(/video/)) {
					li_file.video = document.createElement("video");
					li_file.video.preload = "metadata";
					li_file.video.li = li_file;
					u.ac(li_file, "loading");
					this.field.filelist.load_queue++;
					li_file.video.onloadedmetadata = function() {
						if(!this.videoWidth || !this.videoHeight && this.li.input._form && this.li.input.field.file_media_info_action) {
							delete this.li.video;
							var data = new FormData();
							data.append("input_name", "video");
							data.append("video[]", file, file.name);
							data.append("csrf-token", this.li.input._form.inputs["csrf-token"].val());
							this.response = function(response) {
								u.bug("response", response);
								var width = 0;
								var height = 0;
								if(response && response.cms_object && response.cms_object.length && response.cms_object[0].width && response.cms_object[0].height) {
									width = response.cms_object[0].width;
									height = response.cms_object[0].height;
								}
								this.li.setAttribute("data-width", width);
								this.li.setAttribute("data-height", height);
								u.rc(this.li, "loading");
								this.li.input.field.filelist.load_queue--;
								u.f.filelistUpdated(this.li.input);
							}
							u.request(this, this.li.input.field.file_media_info_action, {
								"method": "post",
								"data": data,
							});
							return;
						}
						this.li.setAttribute("data-width", this.videoWidth);
						this.li.setAttribute("data-height", this.videoHeight);
						u.rc(this.li, "loading");
						this.li.input.field.filelist.load_queue--;
						delete this.li.video;
						u.f.filelistUpdated(this.li.input);
					}
					li_file.video.src = URL.createObjectURL(file);
				}
			}
			if(this.multiple) {
				for(i = 0; i < this.field.uploaded_files.length; i++) {
					u.ae(this.field.filelist, this.field.uploaded_files[i]);
				}
			}
			else {
				this.field.uploaded_files = [];
			}
			u.f.filelistUpdated(this);
			this.focus();
		}
		else if(this.field.uploaded_files && this.field.uploaded_files.length) {
			u.rc(this.field, "has_new_files");
			var i;
			for(i = 0; i < this.field.uploaded_files.length; i++) {
				u.ae(this.field.filelist, this.field.uploaded_files[i]);
			}
		}
		else {
			u.rc(this.field, "has_new_files");
		}
		u.f.updateFilePreview(this.field);
	}
	this.filelistUpdated = function(input) {
		if(input.field.filelist.load_queue === 0) {
			this._changed.bind(input.field.input)(input.e_updated);
			this._updated.bind(input.field.input)(input.e_updated);
			delete input.e_updated;
		}
	}
	this.updateFilePreview = function(field) {
		if(!field.input.multiple) {
			if(field.filelist.preview) {
				if(field.filelist.preview.parentNode) {
					field.filelist.removeChild(field.filelist.preview);
				}
				if(field.filelist.preview.ul_actions) {
					field.div_droparea.removeChild(field.filelist.preview.ul_actions);
				}
				if(field.filelist.preview.ul_controls) {
					field.div_droparea.removeChild(field.filelist.preview.ul_controls);
				}
				if(obj(u.k)) {
					u.k.removeKey(field.filelist.preview.keyboard_target, "e");
					u.k.removeKey(field.filelist.preview.keyboard_target, "DELETE");
				}
				delete field.filelist.preview;
				u.rc(field.filelist, "previewing");
			}
			var file = u.qs("li.uploaded", field.filelist);
			if(file) {
				u.ac(field.filelist, "previewing");
				field.filelist.preview = u.ae(field.filelist, "li", {"class":"preview"});
				field.filelist.preview.field = field;
				field.filelist.preview.file = file;
				field.filelist.preview.controls_parent = field.div_droparea;
				field.filelist.preview.keyboard_target = field;
				u.f.addPreview(field.filelist.preview);
			}
		}
		else {
			if(field.ul_previews) {
				var preview, li_preview, i, files, file, previews;
				previews = u.qsa("li.preview", field.ul_previews);
				for(i = 0; i < previews.length; i++) {
					preview = previews[i];
					if(obj(u.k)) {
						u.k.removeKey(preview.keyboard_target, "e");
						u.k.removeKey(preview.keyboard_target, "DELETE");
					}
				}
				field.removeChild(field.ul_previews);
				delete field.ul_previews;
			}
			field.ul_previews = u.ae(field, "ul", {"class": "previews"});
			field.ul_previews.field = field;
			files = u.qsa("li.uploaded", field.filelist);
			if(files) {
				for(i = 0; i < files.length; i++) {
					file = files[i];
					preview = u.ae(field.ul_previews, "li", {"class":"preview", "tabindex": 0});
					preview.field = field;
					preview.file = file;
					preview.controls_parent = preview;
					preview.keyboard_target = preview;
					u.f.addPreview(preview);
				}
			}
			if(field.file_order_action) {
				u.sortable(field.ul_previews);
				field.ul_previews.picked = function(node) {}
				field.ul_previews.dropped = function(node) {
					var order = this.getNodeOrder({node_property:"media_id"});
					var form_data = new FormData();
					form_data.append("csrf-token", this.field._form.inputs["csrf-token"].val());
					form_data.append("item_id", this.field._item_id);
					form_data.append("order", order.join(","));
					this.response = function(response) {
						page.notify(response);
					}
					u.request(this, field.file_order_action, {
						"method": "post", 
						"data": form_data
					});
				}
			}
		}
	}
	this.addPreview = function(preview) {
		preview.media_id = preview.file.getAttribute("data-media_id");
		preview.media_name = preview.file.innerHTML;
		preview.media_description = preview.file.getAttribute("data-description");
		preview.media_format = preview.file.getAttribute("data-format");
		preview.media_variant = preview.file.getAttribute("data-variant");
		preview.media_width = preview.file.getAttribute("data-width");
		preview.media_height = preview.file.getAttribute("data-height");
		preview.media_created_at = preview.file.getAttribute("data-created_at");
		preview.media_poster = preview.file.getAttribute("data-poster");
		preview.view = u.ie(preview, "div", {"class":"view"});
		u.ae(preview.view, "span", {"class": "name", "html": preview.media_name});
		if(preview.field.file_delete_action || preview.field.file_update_metadata_action) {
			preview.ul_actions = u.ae(preview.controls_parent, "ul", {"class":"actions"});
		}
		if(preview.field.file_update_metadata_action) {
			preview.bn_edit = u.ae(preview.ul_actions, "li", {"class": "edit", "html": "edit"});
			preview.bn_edit.preview = preview;
			u.ce(preview.bn_edit);
			preview.bn_edit.inputStarted = function(event) {
				u.e.kill(event);
			}
			preview.bn_edit.clicked = function(event) {
				u.e.kill(event);
				u.f.editMetadata(this.preview);
			}
			if(obj(u.k)) {
				u.k.addKey(preview.keyboard_target, "e", {
					"callback": preview.bn_edit.clicked.bind(preview.bn_edit),
					"focused": true,
				});
			}
		}
		if(preview.field.file_delete_action) {
			preview.bn_delete = u.ae(preview.ul_actions, "li", {"class": "delete", "html": "Are you sure?"});
			preview.bn_delete.preview = preview;
			u.ce(preview.bn_delete);
			preview.bn_delete.inputStarted = function(event) {
				u.e.kill(event);
			}
			preview.bn_delete.clicked = function(event) {
				u.e.kill(event);
				if(u.hc(this, "confirm")) {
					u.f.deleteFile(this.preview);
				}
				else {
					u.ac(this, "confirm");
					this.t_confirm = u.t.setTimer(this, "restore", 1500);
				}
			}
			preview.bn_delete.restore = function() {
				u.rc(this, "confirm");
			}
			if(obj(u.k)) {
				u.k.addKey(preview.keyboard_target, "DELETE", {
					"callback": preview.bn_delete.clicked.bind(preview.bn_delete),
					"focused": true,
				});
			}
		}
		if(preview.media_format.match(/^(jpg|png|gif)$/i)) {
			u.f.addImagePreview(preview);
		}
		else if(preview.media_format.match(/^(mp3|ogg|wav|aac)$/i)) {
			u.f.addAudioPreview(preview);
		}
		else if(preview.media_format.match(/^(mov|mp4|ogv|3gp)$/i)) {
			u.f.addVideoPreview(preview);
		}
		else if(preview.media_format.match(/^zip$/i)) {
			u.f.addZipPreview(preview);
		}
		else if(preview.media_format.match(/^pdf$/i)) {
			u.f.addPdfPreview(preview);
		}
	}
	this.addImagePreview = function(preview) {
		u.ac(preview, "preview_image");
		var image_src = "/images/"+preview.field._item_id+"/"+preview.media_variant+"/"+preview.offsetWidth+"x."+preview.media_format;
		u.ass(preview.view, {
			"aspect-ratio": preview.media_width / preview.media_height,
			"backgroundImage": "url("+image_src+"?"+u.randomString(4)+")"
		});
	}
	this.addPdfPreview = function(preview) {
		u.ac(preview, "preview_pdf");
		u.ass(preview.view, {
			"backgroundImage": "url(/images/0/pdf/30x.png)"
		});
	}
	this.addZipPreview = function(preview) {
		u.ac(preview, "preview_zip");
		u.ass(preview.view, {
			"backgroundImage": "url(/images/0/zip/30x.png)"
		});
	}
	this.addAudioPreview = function(preview) {
		u.ac(preview, "preview_audio");
		var audio_src = "/audios/"+preview.field._item_id+"/"+preview.media_variant+"/128."+preview.media_format;
		preview.player = u.audioPlayer({"loop":true, "preload":"metadata"});
		preview.player.preview = preview;
		u.ae(preview.view, preview.player);
		preview.player.load(audio_src+"?"+u.randomString(4));
		this.addMediaPlayerControls(preview.player);
	}
	this.addVideoPreview = function(preview) {
		u.ac(preview, "preview_video");
		var video_src = "/videos/"+preview.field._item_id+"/"+preview.media_variant+"/1000x."+preview.media_format;
		preview.player = u.videoPlayer({"muted":true, "loop":true, "preload":"metadata"});
		preview.player.media.setAttribute("tabindex", -1);
		preview.player.preview = preview;
		u.ae(preview.view, preview.player);
		u.ac(preview, "loading-preview");
		preview.player.loadedmetadata = function(event) {
			u.rc(this.preview, "loading-preview");
		}
		preview.player.load(video_src+"?"+u.randomString(4));
		u.ass(preview.view, {
			"aspect-ratio": preview.media_width / preview.media_height,
		});
		if(preview.media_poster) {
			var poster_src = "/images/"+preview.field._item_id+"/"+preview.media_variant+"/1000x."+preview.media_poster;
			preview.player.media.poster = poster_src;
		}
		this.addMediaPlayerControls(preview.player);
	}
	this.addMediaPlayerControls = function(player) {
		var ul_controls = u.ae(player.preview.controls_parent, "ul", {"class":"controls"});
		player.preview.ul_controls = ul_controls;
		var bn_play = u.ae(ul_controls, "li", {"class":"play"});
		bn_play.player = player;
		bn_play.ul_controls = ul_controls;
		u.ce(bn_play);
		bn_play.inputStarted = function(event) {
			u.e.kill(event);
		}
		bn_play.clicked = function(event) {
			u.e.kill(event);
			if(!u.hc(this.ul_controls, "playing")) {
				this.player.play();
				u.ac(this.ul_controls, "playing");
			}
			else {
				this.player.pause();
				u.rc(this.ul_controls, "playing");
			}
		}
		if(u.hc(player.preview, "preview_video")) {
			var bn_mute = u.ae(ul_controls, "li", {"class":"mute"});
			bn_mute.player = player;
			bn_mute.ul_controls = ul_controls;
			u.ce(bn_mute);
			bn_mute.inputStarted = function(event) {
				u.e.kill(event);
			}
			bn_mute.clicked = function(event) {
				u.e.kill(event);
				if(!u.hc(this.ul_controls, "muted")) {
					this.player.mute();
					u.ac(this.ul_controls, "muted");
				}
				else {
					this.player.unmute();
					u.rc(this.ul_controls, "muted");
				}
			}
			u.ac(ul_controls, "muted");
		}
	}
	this.editMetadataOverlay = function(preview, title) {
		var overlay = u.overlay({
			"title": title,
			"width": 600,
			"height": 510,
			"esc": true
		});
		overlay.preview = preview;
		overlay.closed = function(event) {
			this.preview.field.input.focus();
		}
		return overlay;
	}
	this.editMetadata = function(preview) {
		preview.overlay = this.editMetadataOverlay(preview, preview.media_name);
		var form = u.f.addForm(preview.overlay.div_content);
		form.preview = preview;
		form.setAttribute("data-item_id", preview.field._item_id);
		u.f.addField(form, {
			"name": "csrf-token",
			"type": "hidden",
			"value": preview.field._form.inputs["csrf-token"].val(),
		});
		var fieldset = u.f.addFieldset(form);
		if(u.hc(preview, "preview_video")) {
			u.f.addField(fieldset, {
				"name": "file_poster[0]",
				"type": "files",
				"max": 1,
				"label": "Poster for this file name.",
				"value": (preview.media_poster ? [{
					"name": "Poster",
					"variant": preview.media_variant,
					"format": preview.media_poster,
					"width": preview.media_width,
					"height": preview.media_height,
				}] : false),
				"hint_message": "Add a poster for the file. This will be provided to search engines for better ranking.",
				"file_delete": preview.field.getAttribute("data-file-delete"),
				"is_poster": true,
			});
		}
		u.f.addField(fieldset, {
			"name": "file_name",
			"type": "string",
			"label": "SEO friendly file name.",
			"value": preview.media_name,
			"hint_message": "Enter the name or title of the file. This will be provided to search engines for better ranking.",
		});
		u.f.addField(fieldset, {
			"name": "created_at",
			"type": "datetime",
			"label": "Created date and time",
			"value": (preview.media_created_at ? preview.media_created_at : u.date("Y-m-d H:i:s")),
			"hint_message": "When was the file created. This will be provided to search engines for better ranking.",
		});
		u.f.addField(fieldset, {
			"name": "file_description",
			"type": "text",
			"label": "Description",
			"value": preview.media_description,
			"hint_message": "Enter a meaningful description for the file. This will be provided to search engines for better ranking.",
		});
		u.f.addAction(form, {
			"name": "update",
			"value": "Update",
			"class": "button primary",
		});
		u.f.addAction(form, {
			"name": "cancel",
			"value": "Cancel",
			"class": "button",
		});
		u.f.init(form);
		form.actions.cancel.clicked = function() {
			this._form.preview.overlay.close();
		}
		u.k.addKey(form, "s", {
			"callback":"submit",
			"focused":true
		});
		if(u.hc(preview, "preview_video")) {
			form.inputs["file_poster[0]"].focus();
		}
		else {
			form.inputs["file_name"].focus();
		}
		form.submitted = function(iN) {
			var form_data = this.getData();
			form_data.append("file_variant", this.preview.media_variant);
			form_data.append("item_id", this.preview.field._item_id);
			u.ac(this, "submitting");
			this.response = function(response) {
				page.notify(response);
				u.rc(this, "submitting");
				if(response && response.cms_status == "success") {
					this.preview.file.innerHTML = response.cms_object.name;
					this.preview.file.setAttribute("data-media_id", response.cms_object.id);
					this.preview.file.setAttribute("data-description", response.cms_object.description);
					this.preview.file.setAttribute("data-variant", response.cms_object.variant);
					this.preview.file.setAttribute("data-format", response.cms_object.format);
					this.preview.file.setAttribute("data-height", response.cms_object.height ? response.cms_object.height : "");
					this.preview.file.setAttribute("data-width", response.cms_object.width ? response.cms_object.width : "");
					this.preview.file.setAttribute("data-poster", response.cms_object.poster ? response.cms_object.poster : "");
					this.preview.file.setAttribute("data-created_at", response.cms_object.created_at);
					u.f.updateFilePreview(this.preview.field);
					this.preview.overlay.close();
				}
			}
			u.request(this, this.preview.field.file_update_metadata_action, {
				"method":"post",
				"data":form_data
			});
		}
	}
	this.deleteFile = function(preview) {
		var form_data = new FormData();
		form_data.append("csrf-token", preview.field._form.inputs["csrf-token"].val());
		form_data.append("item_id", preview.field._item_id);
		form_data.append("file_variant", preview.media_variant);
		if(preview.field.getAttribute("data-is-poster")) {
			form_data.append("is_poster", preview.field.getAttribute("data-is-poster"));
		}
		preview.deleteResponse = function(response) {
			page.notify(response);
			if(response.cms_status && response.cms_status == "success") {
				this.file.parentNode.removeChild(this.file);
				this.field.uploaded_files = u.qsa("li.uploaded", this.field.filelist);
				u.f.updateFilePreview(this.field);
				if(fun(this.field.input.fileDeleted)) {
					this.field.input.fileDeleted(this.file);
				}
			}
		}
		u.request(preview, preview.field.file_delete_action, {
			"callback": "deleteResponse",
			"method": "post",
			"data": form_data
		});
	}
	this.updateFilelistStatus = function(form, response) {
		if(form && form.inputs && response && response.cms_status == "success" && response.cms_object && (response.cms_object.mediae || response.cms_object.length && response.cms_object[0].variant)) {
			var mediae;
			if(response.cms_object.mediae) {
				mediae = JSON.parse(JSON.stringify(response.cms_object.mediae));
			}
			else {
				mediae = JSON.parse(JSON.stringify(response.cms_object));
			}
			var filelists = u.qsa("div.field.files ul.filelist", form);
			var i, j, k, filelist, old_files, old_file, new_files, new_files;
			for(i = 0; i < filelists.length; i++) {
				filelist = filelists[i];
				new_files = u.qsa("li.new", filelist);
				if(new_files.length) {
					old_files = u.qsa("li.uploaded", filelist);
					if(old_files.length) {
						for(j in mediae) {
							media = mediae[j];
							if(media.variant.match("^" + filelist.field.input.name.replace(/\[\]$/, "") + "(\-|$)")) {
								for(k = 0; k < old_files.length; k++) {
									old_file = old_files[k];
									if(old_file.getAttribute("data-media_id") === media.id) {
										delete mediae[j];
									}
								}
							}
						}
					}
					if(Object.keys(mediae).length) {
						for(j in mediae) {
							media = mediae[j];
							if(media.variant.match("^"+filelist.field.input.name.replace(/\[\]$/, "")+"(\-|$)")) {
								for(k = 0; k < new_files.length; k++) {
									new_file = new_files[k];
									if(u.text(new_file) == media.name || u.text(new_file)+".zip" == media.name) {
										new_file.innerHTML = media.name;
										u.f.removeFileFromFileInput(filelist.field.input, media.name);
										u.rc(new_file, "new");
										u.ac(new_file, "uploaded");
										new_file.setAttribute("data-medie-id", media.id);
										new_file.setAttribute("data-variant", media.variant);
										new_file.setAttribute("data-format", media.format);
										new_file.setAttribute("data-width", media.width);
										new_file.setAttribute("data-height", media.height);
										new_file.setAttribute("data-description", media.description);
										new_file.setAttribute("data-created_at", media.created_at);
										delete mediae[j];
									}
								}
							}
						}
					}
					var remaining_new_files = u.qsa("li.new", filelist);
					if(!remaining_new_files.length) {
						u.rc(filelist.field, "has_new_files");
					}
				}
				filelist.field.uploaded_files = u.qsa("li.uploaded", filelist);
			}
		}
	}
	this.removeFileFromFileInput = function(input, file_name) {
		var datatransfer = new DataTransfer();
		var i, file;
		var files = Array.from(input.files);
		for(i = 0; i < files.length; i++) {
			file = files[i];
			if(file_name !== file.name) {
				datatransfer.items.add(file);
			}
		}
		input.files = datatransfer.files;
	}
	this.updateFormAfterResponse = function(form, response) {
		this.updateFilelistStatus(form, response);
		var input;
		for(input in form.inputs) {
			if(form.inputs[input].field && form.inputs[input].field.type === "files") {
				this.updateFilePreview(form.inputs[input].field);
			}
		}
	}
	this._mouseenter = function(event) {
		u.ac(this.field, "hover");
		u.ac(this, "hover");
		if(!this.is_focused) {
			u.as(this.field, "zIndex", this._form._hover_z_index);
		}
		u.f.positionHint(this.field);
	}
	this._mouseleave = function(event) {
		u.rc(this.field, "hover");
		u.rc(this, "hover");
		if(!this.is_focused) {
			u.as(this.field, "zIndex", this.field._base_z_index);
		}
		u.f.positionHint(this.field);
	}
	this._focus = function(event) {
		this.field.is_focused = true;
		this.is_focused = true;
		u.ac(this.field, "focus");
		u.ac(this, "focus");
		u.as(this.field, "zIndex", this._form._focus_z_index);
		u.f.positionHint(this.field);
		if(fun(this[this._form._callback_focused])) {
			this[this._form._callback_focused](this);
		}
		else if(fun(this.field.input[this._form._callback_focused])) {
			this.field.input[this._form._callback_focused](this);
		}
		if(fun(this._form[this._form._callback_focused])) {
			this._form[this._form._callback_focused](this);
		}
	}
	this._blur = function(event) {
		this.field.is_focused = false;
		this.is_focused = false;
		u.rc(this.field, "focus");
		u.rc(this, "focus");
		u.as(this.field, "zIndex", this.field._base_z_index);
		u.f.positionHint(this.field);
		this._used = true;
		if(fun(this[this._form._callback_blurred])) {
			this[this._form._callback_blurred](this);
		}
		else if(fun(this.field.input[this._form._callback_blurred])) {
			this.field.input[this._form._callback_blurred](this);
		}
		if(fun(this._form[this._form._callback_blurred])) {
			this._form[this._form._callback_blurred](this);
		}
	}
	this._button_focus = function(event) {
		u.ac(this, "focus");
		if(fun(this[this._form._callback_focused])) {
			this[this._form._callback_focused](this);
		}
		if(fun(this._form[this._form._callback_focused])) {
			this._form[this._form._callback_focused](this);
		}
	}
	this._button_blur = function(event) {
		u.rc(this, "focus");
		if(fun(this[this._form._callback_blurred])) {
			this[this._form._callback_blurred](this);
		}
		if(fun(this._form[this._form._callback_blurred])) {
			this._form[this._form._callback_blurred](this);
		}
	}
	this._validate = function(event) {
		u.f.validate(this);
	}
	this.inputOnEnter = function(node) {
		node.keyPressed = function(event) {
			if(this.nodeName.match(/input/i) && (event.keyCode == 40 || event.keyCode == 38)) {
				this._submit_disabled = true;
			}
			else if(this.nodeName.match(/input/i) && this._submit_disabled && (
				event.keyCode == 46 || 
				(event.keyCode == 39 && u.browser("firefox")) || 
				(event.keyCode == 37 && u.browser("firefox")) || 
				event.keyCode == 27 || 
				event.keyCode == 13 || 
				event.keyCode == 9 ||
				event.keyCode == 8
			)) {
				this._submit_disabled = false;
			}
			else if(event.keyCode == 13 && !this._submit_disabled) {
				u.e.kill(event);
				this.blur();
				this._form.submitInput = this;
				this._form.submitButton = false;
				this._form.submit(event, this);
			}
		}
		u.e.addEvent(node, "keydown", node.keyPressed);
	}
	this.buttonOnEnter = function(node) {
		node.keyPressed = function(event) {
			if(event.keyCode == 13 && !u.hc(this, "disabled") && fun(this.clicked)) {
				u.e.kill(event);
				this.clicked(event);
			}
		}
		u.e.addEvent(node, "keydown", node.keyPressed);
	}
	this.activateInput = function(iN) {
		u.e.addEvent(iN, "focus", this._focus);
		u.e.addEvent(iN, "blur", this._blur);
		if(u.e.event_support != "touch") {
			u.e.addEvent(iN, "mouseenter", this._mouseenter);
			u.e.addEvent(iN, "mouseleave", this._mouseleave);
		}
		u.e.addEvent(iN, "blur", this._validate);
		if(iN._form._label_style && fun(this.customLabelStyle[iN._form._label_style])) {
			this.customLabelStyle[iN._form._label_style](iN);
		}
		else {
			iN.default_value = "";
		}
	}
	this.activateButton = function(action) {
		if(action.type && action.type == "submit" || action.type == "reset") {
			action.onclick = function(event) {
				u.e.kill(event);
			}
		}
		u.ce(action);
		if(!action.clicked) {
			if(action.confirm) {
				action.restore = function() {
					u.rc(this, "confirm");
					this.wait_for_confirm = false;
					this.value = this.confirm_default_value;
				}
			}
			action.clicked = function(event) {
				if(!u.hc(this, "disabled")) {
					if(this.confirm && !this.wait_for_confirm) {
						this.wait_for_confirm = true;
						u.ac(this, "confirm");
						this.confirm_default_value = this.value;
						this.value = this.confirm;
						this.t_confirm = u.t.setTimer(this, this.restore, 3000);
						return;
					}
					else if(this.confirm && this.t_confirm) {
						u.t.resetTimer(this.t_confirm);
					}
					if(this.type && this.type.match(/submit/i)) {
						this._form._submit_button = this;
						this._form._submit_input = false;
						this._form.submit(event, this);
					}
					else if(this.type && this.type.match(/reset/i)) {
						this._form._submit_button = false;
						this._form._submit_input = false;
						this._form.reset(event, this);
					}
					else if(this.url) {
						if(event && (event.metaKey || event.ctrlKey)) {
							window.open(this.url);
						}
						else {
							if(obj(u.h) && u.h.is_listening) {
								u.h.navigate(this.url, this);
							}
							else {
								location.href = this.url;
							}
						}
					}
				}
			}
		}
		var action_name = action.name ? action.name : (action.parentNode.className ? u.superNormalize(action.parentNode.className) : (action.value ? u.superNormalize(action.value) : u.superNormalize(u.text(action))));
		if(action_name && !action._form.actions[action_name]) {
			action._form.actions[action_name] = action;
		}
		if(obj(u.k) && u.hc(action, "key:[a-z0-9]+")) {
			u.k.addKey(action._form, u.cv(action, "key"), {
				"callback": action.clicked.bind(action),
				"focused": true
			});
		}
		u.e.addEvent(action, "focus", this._button_focus);
		u.e.addEvent(action, "blur", this._button_blur);
	}
	this.positionHint = function(field) {
		if(field.help) {
			var custom_hint_position;
			for(custom_hint_position in this.customHintPosition) {
				if(u.hc(field, custom_hint_position)) {
					this.customHintPosition[custom_hint_position](field);
					return;
				}
			}
			var input_middle, help_top;
			if(field.virtual_input) {
				input_middle = field.virtual_input.parentNode.offsetTop + (field.virtual_input.parentNode.offsetHeight / 2);
			}
			else {
				input_middle = field.input.offsetTop + (field.input.offsetHeight / 2);
			}
			help_top = input_middle - field.help.offsetHeight / 2;
			u.ass(field.help, {
				"top": help_top + "px"
			});
		}
	}
	this.inputHasError = function(iN) {
		u.rc(iN, "correct");
		u.rc(iN.field, "correct");
		delete iN.is_correct;
		if(iN.val() !== "") {
			if(!iN.has_error && (iN._used || iN._form._bulk_operation)) {
				iN._form._error_inputs[iN.name] = true;
				u.ac(iN, "error");
				u.ac(iN.field, "error");
				iN.has_error = true;
				this.updateInputValidationState(iN);
			 }
		}
		else if(!iN.has_error && iN._used) {
			iN._form._error_inputs[iN.name] = true;
			u.ac(iN, "error");
			u.ac(iN.field, "error");
			iN.has_error = true;
			this.updateInputValidationState(iN);
		}
		else if(!iN._used) {
			delete iN._form._error_inputs[iN.name];
			u.rc(iN, "error");
			u.rc(iN.field, "error");
			delete iN.has_error;
		}
		this.positionHint(iN.field);
	}
	this.inputIsCorrect = function(iN) {
		u.rc(iN, "error");
		u.rc(iN.field, "error");
		delete iN.has_error;
		delete iN._form._error_inputs[iN.name];
		if(iN.val() !== "") {
			if(!iN.is_correct) {
				iN._used = true;
				u.ac(iN, "correct");
				u.ac(iN.field, "correct");
				iN.is_correct = true;
				this.updateInputValidationState(iN);
			}
		}
		else if(iN.is_correct || iN.has_error) {
			u.rc(iN, "correct");
			u.rc(iN.field, "correct");
			delete iN.is_correct;
			this.updateInputValidationState(iN);
		}
		this.positionHint(iN.field);
	}
	this.updateInputValidationState = function(iN) {
		if(iN.has_error && fun(iN[iN._form._callback_validation_failed])) {
			iN[iN._form._callback_validation_failed]();
		}
		else if(iN.is_correct && fun(iN[iN._form._callback_validation_passed])) {
			iN[iN._form._callback_validation_passed]();
		}
		this.updateFormValidationState(iN._form);
	}
	this.updateFormValidationState = function(_form) {
		if(!_form._bulk_operation) {
			if(Object.keys(_form._error_inputs).length) {
				_form._validation_state = "error";
				if(_form._error_inputs !== _form._reference_error_inputs) {
					if(fun(_form[_form._callback_validation_failed])) {
						_form[_form._callback_validation_failed](_form._error_inputs);
					}
				}
			}
			else if(u.qsa(".field.required", _form).length === u.qsa(".field.required.correct", _form).length) {
				if(fun(_form[_form._callback_validation_passed]) && _form._validation_state !== "correct") {
					_form[_form._callback_validation_passed]();
				}
				_form._validation_state = "correct";
			}
			else {
				_form._validation_state = "void";
			}
			_form._reference_error_inputs = JSON.parse(JSON.stringify(_form._error_inputs));
		}
	}
	this.bulkValidate = function(inputs) {
		if(inputs && inputs.length) {
			var _form = inputs[0]._form;
			_form._bulk_operation = true;
			var i;
			for(i = 0; i < inputs.length; i++) {
				u.f.validate(inputs[i]);
			}
			_form._bulk_operation = false;
			this.updateFormValidationState(_form);
		}
	}
	this.validate = function(iN) {
		if(!iN._form._validation || !iN.field) {
			return true;
		}
		var min, max, pattern;
		var validated = false;
		var compare_to = iN.getAttribute("data-compare-to");
		if(!u.hc(iN.field, "required") && iN.val() === "" && (!compare_to || iN._form.inputs[compare_to].val() === "")) {
			this.inputIsCorrect(iN);
			return true;
		}
		else if(u.hc(iN.field, "required") && iN.val() === "") {
			this.inputHasError(iN);
			return false;
		}
		var custom_validate;
		for(custom_validate in u.f.customValidate) {
			if(u.hc(iN.field, custom_validate)) {
				u.f.customValidate[custom_validate](iN);
				validated = true;
			}
		}
		if(!validated) {
			if(u.hc(iN.field, "password")) {
				min = Number(u.cv(iN.field, "min"));
				max = Number(u.cv(iN.field, "max"));
				min = min ? min : 8;
				max = max ? max : 255;
				pattern = iN.getAttribute("pattern");
				if(
					iN.val().length >= min && 
					iN.val().length <= max && 
					(!pattern || iN.val().match("^"+pattern+"$")) &&
					(!compare_to || iN.val() == iN._form.inputs[compare_to].val())
				) {
					this.inputIsCorrect(iN);
					if(compare_to) {
						this.inputIsCorrect(iN._form.inputs[compare_to]);
					}
				}
				else {
					this.inputHasError(iN);
					if(compare_to) {
						this.inputHasError(iN._form.inputs[compare_to]);
					}
				}
			}
			else if(u.hc(iN.field, "number")) {
				min = Number(u.cv(iN.field, "min"));
				max = Number(u.cv(iN.field, "max"));
				min = min ? min : 0;
				max = max ? max : 99999999999999999999999999999;
				pattern = iN.getAttribute("pattern");
				if(
					!isNaN(iN.val()) && 
					iN.val() >= min && 
					iN.val() <= max && 
					(!pattern || iN.val().match("^"+pattern+"$"))
				) {
					this.inputIsCorrect(iN);
				}
				else {
					this.inputHasError(iN);
				}
			}
			else if(u.hc(iN.field, "integer")) {
				min = Number(u.cv(iN.field, "min"));
				max = Number(u.cv(iN.field, "max"));
				min = min ? min : 0;
				max = max ? max : 99999999999999999999999999999;
				pattern = iN.getAttribute("pattern");
				if(
					!isNaN(iN.val()) && 
					Math.round(iN.val()) == iN.val() && 
					iN.val() >= min && 
					iN.val() <= max && 
					(!pattern || iN.val().match("^"+pattern+"$"))
				) {
					this.inputIsCorrect(iN);
				}
				else {
					this.inputHasError(iN);
				}
			}
			else if(u.hc(iN.field, "tel")) {
				pattern = iN.getAttribute("pattern");
				if(
					(
						(!pattern && iN.val().match(/^([\+0-9\-\.\s\(\)]){5,18}$/))
						||
						(pattern && iN.val().match("^"+pattern+"$"))
					)
					&&
					(!compare_to || iN.val() == iN._form.inputs[compare_to].val())
				) {
					this.inputIsCorrect(iN);
					if(compare_to) {
						this.inputIsCorrect(iN._form.inputs[compare_to]);
					}
				}
				else {
					this.inputHasError(iN);
					if(compare_to) {
						this.inputHasError(iN._form.inputs[compare_to]);
					}
				}
			}
			else if(u.hc(iN.field, "email")) {
				pattern = iN.getAttribute("pattern");
				if(
					(
						(!pattern && iN.val().match(/^([^<>\\\/%$])+\@([^<>\\\/%$])+\.([^<>\\\/%$]{2,20})$/))
						||
						(pattern && iN.val().match("^"+pattern+"$"))
					)
					&&
					(!compare_to || iN.val() == iN._form.inputs[compare_to].val())
				) {
					this.inputIsCorrect(iN);
					if(compare_to) {
						this.inputIsCorrect(iN._form.inputs[compare_to]);
					}
				}
				else {
					this.inputHasError(iN);
					if(compare_to) {
						this.inputHasError(iN._form.inputs[compare_to]);
					}
				}
			}
			else if(u.hc(iN.field, "text")) {
				min = Number(u.cv(iN.field, "min"));
				max = Number(u.cv(iN.field, "max"));
				min = min ? min : 1;
				max = max ? max : 10000000;
				pattern = iN.getAttribute("pattern");
				if(
					iN.val().length >= min && 
					iN.val().length <= max && 
					(!pattern || iN.val().match("^"+pattern+"$"))
				) {
					this.inputIsCorrect(iN);
				}
				else {
					this.inputHasError(iN);
				}
			}
			else if(u.hc(iN.field, "json")) {
				min = Number(u.cv(iN.field, "min"));
				max = Number(u.cv(iN.field, "max"));
				min = min ? min : 2;
				max = max ? max : 10000000;
				if(
					iN.val().length >= min && 
					iN.val().length <= max && 
					(function(value) {
						try {
							JSON.parse(value);
							return true;
						}
						catch(exception) {
							return false;
						}
					}(iN.val()))
				) {
					this.inputIsCorrect(iN);
				}
				else {
					this.inputHasError(iN);
				}
			}
			else if(u.hc(iN.field, "date")) {
				min = u.cv(iN.field, "min");
				max = u.cv(iN.field, "max");
				pattern = iN.getAttribute("pattern");
				if(
					(!min || new Date(decodeURIComponent(min)) <= new Date(iN.val())) &&
					(!max || new Date(decodeURIComponent(max)) >= new Date(iN.val())) &&
					(
						(!pattern && iN.val().match(/^([\d]{4}[\-\/\ ]{1}[\d]{2}[\-\/\ ][\d]{2})$/))
						||
						(pattern && iN.val().match("^"+pattern+"$"))
					)
				) {
					this.inputIsCorrect(iN);
				}
				else {
					this.inputHasError(iN);
				}
			}
			else if(u.hc(iN.field, "datetime")) {
				min = u.cv(iN.field, "min");
				max = u.cv(iN.field, "max");
				pattern = iN.getAttribute("pattern");
				if(
					(!min || new Date(decodeURIComponent(min)) <= new Date(iN.val())) &&
					(!max || new Date(decodeURIComponent(max)) >= new Date(iN.val())) &&
					(
						(!pattern && iN.val().match(/^([\d]{4}[\-\/\ ]{1}[\d]{2}[\-\/\ ][\d]{2} [\d]{2}[\-\/\ \:]{1}[\d]{2}[\-\/\ \:]{0,1}[\d]{0,2})$/))
						||
						(pattern && iN.val().match(pattern))
					)
				) {
					this.inputIsCorrect(iN);
				}
				else {
					this.inputHasError(iN);
				}
			}
			else if(u.hc(iN.field, "files")) {
				min = Number(u.cv(iN.field, "min"));
				max = Number(u.cv(iN.field, "max"));
				min = min ? min : 1;
				max = max ? max : 10000000;
				pattern = iN.getAttribute("accept");
				if(pattern) {
					pattern = pattern.split(",");
				}
				var i, files = Array.prototype.slice.call(u.qsa("li:not(.label,.preview)", iN.field.filelist));
				var min_width = Number(iN.getAttribute("data-min-width"));
				var min_height = Number(iN.getAttribute("data-min-height"));
				var allowed_sizes = iN.getAttribute("data-allowed-sizes");
				if(allowed_sizes) {
					allowed_sizes = allowed_sizes.split(",");
				}
				var allowed_proportions = iN.getAttribute("data-allowed-proportions");
				if(allowed_proportions) {
					allowed_proportions = allowed_proportions.split(",");
					for(i = 0; i < allowed_proportions.length; i++) {
						allowed_proportions[i] = u.round(eval(allowed_proportions[i]), 4);
					}
				}
				if(
					(files.length >= min && files.length <= max)
					&&
					(!pattern || files.every(function(node) {return pattern.indexOf("."+node.getAttribute("data-format")) !== -1}))
					&&
					(!min_width || files.every(function(node) {return node.getAttribute("data-width") >= min_width}))
					&&
					(!min_height || files.every(function(node) {return node.getAttribute("data-height") >= min_height}))
					&&
					(!allowed_sizes || files.every(function(node) {return allowed_sizes.indexOf(node.getAttribute("data-width")+"x"+node.getAttribute("data-height")) !== -1}))
					&&
					(!allowed_proportions || files.every(function(node) {return allowed_proportions.indexOf(u.round(Number(node.getAttribute("data-width"))/Number(node.getAttribute("data-height")), 4)) !== -1}))
				) {
					this.inputIsCorrect(iN);
				}
				else {
					this.inputHasError(iN);
				}
			}
			else if(u.hc(iN.field, "select")) {
				if(iN.val() !== "") {
					this.inputIsCorrect(iN);
				}
				else {
					this.inputHasError(iN);
				}
			}
			else if(u.hc(iN.field, "checkbox|boolean|radiobuttons")) {
				if(iN.val() !== "") {
					this.inputIsCorrect(iN);
				}
				else {
					this.inputHasError(iN);
				}
			}
			else if(u.hc(iN.field, "string")) {
				min = Number(u.cv(iN.field, "min"));
				max = Number(u.cv(iN.field, "max"));
				min = min ? min : 1;
				max = max ? max : 255;
				pattern = iN.getAttribute("pattern");
				if(
					iN.val().length >= min &&
					iN.val().length <= max && 
					(!pattern || iN.val().match("^"+pattern+"$"))
					&&
					(!compare_to || iN.val() == iN._form.inputs[compare_to].val())
				) {
					this.inputIsCorrect(iN);
					if(compare_to) {
						this.inputIsCorrect(iN._form.inputs[compare_to]);
					}
				}
				else {
					this.inputHasError(iN);
					if(compare_to) {
						this.inputHasError(iN._form.inputs[compare_to]);
					}
				}
			}
		}
		if(u.hc(iN.field, "error")) {
			return false;
		}
		else {
			return true;
		}
	}
	this.getFormData = this.getParams = function(_form, _options) {
		var format = "formdata";
		var ignore_inputs = "ignoreinput";
		if(obj(_options)) {
			var _argument;
			for(_argument in _options) {
				switch(_argument) {
					case "ignore_inputs"    : ignore_inputs     = _options[_argument]; break;
					case "format"           : format            = _options[_argument]; break;
				}
			}
		}
		var i, input, select, textarea, param, params;
		if(format == "formdata") {
			params = new FormData();
		}
		else {
			params = new Object();
			params.append = function(name, value, filename) {
				this[name] = filename || value;
			}
		}
		if(_form._submit_button && _form._submit_button.name) {
			params.append(_form._submit_button.name, _form._submit_button.value);
		}
		var inputs = u.qsa("input", _form);
		var selects = u.qsa("select", _form)
		var textareas = u.qsa("textarea", _form)
		for(i = 0; i < inputs.length; i++) {
			input = inputs[i];
			if(!u.hc(input, ignore_inputs)) {
				if((input.type == "checkbox" || input.type == "radio") && input.checked) {
					if(fun(input.val)) {
						params.append(input.name, input.val());
					}
					else {
						params.append(input.name, input.value);
					}
				}
				else if(input.type == "file") {
					var f, file, files;
					if(fun(input.val)) {
						files = input.val();
					}
					else if(input.files) {
						files = input.files;
					}
					if(files && files.length) {
						for(f = 0; f < files.length; f++) {
							file = files[f];
							params.append(input.name, file, file.name);
						}
					}
					else {
						params.append(input.name, (input.value || ""));
					}
				}
				else if(!input.type.match(/button|submit|reset|file|checkbox|radio/i)) {
					if(fun(input.val)) {
						params.append(input.name, input.val());
					}
					else {
						params.append(input.name, input.value);
					}
				}
			}
		}
		for(i = 0; i < selects.length; i++) {
			select = selects[i];
			if(!u.hc(select, ignore_inputs)) {
				if(fun(select.val)) {
					params.append(select.name, select.val());
				}
				else {
					params.append(select.name, select.options[select.selectedIndex] ? select.options[select.selectedIndex].value : "");
				}
			}
		}
		for(i = 0; i < textareas.length; i++) {
			textarea = textareas[i];
			if(!u.hc(textarea, ignore_inputs)) {
				if(fun(textarea.val)) {
					params.append(textarea.name, textarea.val());
				}
				else {
					params.append(textarea.name, textarea.value);
				}
			}
		}
		if(format && fun(this.customDataFormat[format])) {
			return this.customDataFormat[format](params, _form);
		}
		else if(format == "formdata") {
			return params;
		}
		else if(format == "object") {
			delete params.append;
			return params;
		}
		else {
			var string = "";
			for(param in params) {
				if(!fun(params[param])) {
					string += (string ? "&" : "") + param + "=" + encodeURIComponent(params[param]);
				}
			}
			return string;
		}
	}
}


/*u-form-builder.js*/
u.f.customBuild = {};
u.f.addForm = function(node, _options) {
	var form_name = "js_form";
	var form_action = "#";
	var form_method = "post";
	var form_class = "";
	if(obj(_options)) {
		var _argument;
		for(_argument in _options) {
			switch(_argument) {
				case "name"			: form_name				= _options[_argument]; break;
				case "action"		: form_action			= _options[_argument]; break;
				case "method"		: form_method			= _options[_argument]; break;
				case "class"		: form_class			= _options[_argument]; break;
			}
		}
	}
	var form = u.ae(node, "form", {"class":form_class, "name": form_name, "action":form_action, "method":form_method});
	return form;
}
u.f.addFieldset = function(node, _options) {
	var fieldset_class = "";
	if(obj(_options)) {
		var _argument;
		for(_argument in _options) {
			switch(_argument) {
				case "class"			: fieldset_class			= _options[_argument]; break;
			}
		}
	}
	return u.ae(node, "fieldset", {"class":fieldset_class});
}
u.f.addField = function(node, _options) {
	var field_name = "js_name";
	var field_label = "Label";
	var field_type = "string";
	var field_value = "";
	var field_options = [];
	var field_checked = false;
	var field_class = "";
	var field_id = "";
	var field_max = false;
	var field_min = false;
	var field_allowed_formats = false;
	var field_file_delete = false;
	var field_file_update = false;
	var field_is_poster = false
	var field_disabled = false;
	var field_readonly = false;
	var field_required = false;
	var field_pattern = false;
	var field_error_message = "There is an error in your input";
	var field_hint_message = "";
	if(obj(_options)) {
		var _argument;
		for(_argument in _options) {
			switch(_argument) {
				case "name"					: field_name				= _options[_argument]; break;
				case "label"				: field_label				= _options[_argument]; break;
				case "type"					: field_type				= _options[_argument]; break;
				case "value"				: field_value				= _options[_argument]; break;
				case "options"				: field_options				= _options[_argument]; break;
				case "checked"				: field_checked				= _options[_argument]; break;
				case "class"				: field_class				= _options[_argument]; break;
				case "id"					: field_id					= _options[_argument]; break;
				case "max"					: field_max					= _options[_argument]; break;
				case "min"					: field_min					= _options[_argument]; break;
				case "allowed_formats"		: field_allowed_formats		= _options[_argument]; break;
				case "file_delete"			: field_file_delete			= _options[_argument]; break;
				case "file_update"			: field_file_update			= _options[_argument]; break;
				case "is_poster"			: field_is_poster			= _options[_argument]; break;
				case "disabled"				: field_disabled			= _options[_argument]; break;
				case "readonly"				: field_readonly			= _options[_argument]; break;
				case "required"				: field_required			= _options[_argument]; break;
				case "pattern"				: field_pattern				= _options[_argument]; break;
				case "error_message"		: field_error_message		= _options[_argument]; break;
				case "hint_message"			: field_hint_message		= _options[_argument]; break;
			}
		}
	}
	var custom_build;
	if(field_type in u.f.customBuild) {
		return u.f.customBuild[field_type](node, _options);
	}
	field_id = field_id ? field_id : "input_"+field_type+"_"+field_name;
	field_disabled = !field_disabled ? (field_class.match(/(^| )disabled( |$)/) ? "disabled" : false) : "disabled";
	field_readonly = !field_readonly ? (field_class.match(/(^| )readonly( |$)/) ? "readonly" : false) : "readonly";
	field_required = !field_required ? (field_class.match(/(^| )required( |$)/) ? true : false) : true;
	field_class += field_disabled ? (!field_class.match(/(^| )disabled( |$)/) ? " disabled" : "") : "";
	field_class += field_readonly ? (!field_class.match(/(^| )readonly( |$)/) ? " readonly" : "") : "";
	field_class += field_required ? (!field_class.match(/(^| )required( |$)/) ? " required" : "") : "";
	field_class += field_min ? (!field_class.match(/(^| )min:[0-9]+( |$)/) ? " min:"+field_min : "") : "";
	field_class += field_max ? (!field_class.match(/(^| )max:[0-9]+( |$)/) ? " max:"+field_max : "") : "";
	field_class += field_type === "files" && field_max && field_max !== 1 ? " multiple" : "";
	if(field_type == "hidden") {
		return u.ae(node, "input", {"type":"hidden", "name":field_name, "value":field_value, "id":field_id});
	}
	var field_options = {};
	if(field_type === "files") {
		if(field_file_delete) {
			field_options["data-file-delete"] = field_file_delete;
		}
		if(field_file_update) {
			field_options["data-file-update"] = field_file_update;
		}
		if(field_is_poster) {
			field_options["data-is-poster"] = field_is_poster;
		}
	}
	field_options["class"] = "field "+field_type+" "+field_class;
	var field = u.ae(node, "div", field_options);
	var attributes = {};
	if(field_type == "string") {
		field_max = field_max ? field_max : 255;
		attributes = {
			"type":"text", 
			"id":field_id, 
			"value":field_value, 
			"name":field_name, 
			"maxlength":field_max, 
			"minlength":field_min,
			"pattern":field_pattern,
			"readonly":field_readonly,
			"disabled":field_disabled
		};
		u.ae(field, "label", {"for":field_id, "html":field_label});
		u.ae(field, "input", u.f.verifyAttributes(attributes));
	}
	else if(field_type == "email" || field_type == "tel" || field_type == "password") {
		field_max = field_max ? field_max : 255;
		attributes = {
			"type":field_type, 
			"id":field_id, 
			"value":field_value, 
			"name":field_name, 
			"maxlength":field_max, 
			"minlength":field_min,
			"pattern":field_pattern,
			"readonly":field_readonly,
			"disabled":field_disabled
		};
		u.ae(field, "label", {"for":field_id, "html":field_label});
		u.ae(field, "input", u.f.verifyAttributes(attributes));
	}
	else if(field_type == "number" || field_type == "integer" || field_type == "date") {
		attributes = {
			"type":field_type, 
			"id":field_id, 
			"value":field_value, 
			"name":field_name, 
			"max":field_max, 
			"min":field_min,
			"pattern":field_pattern,
			"readonly":field_readonly,
			"disabled":field_disabled
		};
		u.ae(field, "label", {"for":field_id, "html":field_label});
		u.ae(field, "input", u.f.verifyAttributes(attributes));
	}
	else if(field_type == "datetime") {
		attributes = {
			"type":field_type+"-local", 
			"id":field_id, 
			"value":field_value, 
			"name":field_name, 
			"max":field_max, 
			"min":field_min,
			"pattern":field_pattern,
			"readonly":field_readonly,
			"disabled":field_disabled
		};
		u.ae(field, "label", {"for":field_id, "html":field_label});
		u.ae(field, "input", u.f.verifyAttributes(attributes));
	}
	else if(field_type == "checkbox") {
		attributes = {
			"type":field_type, 
			"id":field_id, 
			"value":field_value ? field_value : "true", 
			"name":field_name, 
			"disabled":field_disabled,
			"checked":field_checked
		};
		u.ae(field, "input", {"name":field_name, "value":"false", "type":"hidden"});
		u.ae(field, "input", u.f.verifyAttributes(attributes));
		u.ae(field, "label", {"for":field_id, "html":field_label});
	}
	else if(field_type == "text") {
		attributes = {
			"id":field_id, 
			"html":field_value, 
			"name":field_name, 
			"maxlength":field_max, 
			"minlength":field_min,
			"pattern":field_pattern,
			"readonly":field_readonly,
			"disabled":field_disabled
		};
		u.ae(field, "label", {"for":field_id, "html":field_label});
		u.ae(field, "textarea", u.f.verifyAttributes(attributes));
	}
	else if(field_type == "select") {
		attributes = {
			"id":field_id, 
			"name":field_name, 
			"disabled":field_disabled
		};
		u.ae(field, "label", {"for":field_id, "html":field_label});
		var select = u.ae(field, "select", u.f.verifyAttributes(attributes));
		if(field_options) {
			var i, option;
			for(i = 0; i < field_options.length; i++) {
				option = field_options[i];
				if(option.value == field_value) {
					u.ae(select, "option", {"value":option.value, "html":option.text, "selected":"selected"});
				}
				else {
					u.ae(select, "option", {"value":option.value, "html":option.text});
				}
			}
		}
	}
	else if(field_type == "radiobuttons") {
		u.ae(field, "label", {"html":field_label});
		if(field_options) {
			var i, option;
			for(i = 0; i < field_options.length; i++) {
				option = field_options[i];
				var div = u.ae(field, "div", {"class":"item"});
				if(option.value == field_value) {
					u.ae(div, "input", {"value":option.value, "id":field_id+"-"+i, "type":"radio", "name":field_name, "checked":"checked"});
					u.ae(div, "label", {"for":field_id+"-"+i, "html":option.text});
				}
				else {
					u.ae(div, "input", {"value":option.value, "id":field_id+"-"+i, "type":"radio", "name":field_name});
					u.ae(div, "label", {"for":field_id+"-"+i, "html":option.text});
				}
			}
		}
	}
	else if(field_type == "files") {
		attributes = {
			"id":field_id, 
			"name":field_name, 
			"type":"file",
			"accept":field_allowed_formats ? "."+field_allowed_formats.replace(/,/, ",.") : false,
			"multiple":field_max && field_max === 1 ? false : true,
		};
		u.ae(field, "label", {"for":field_id, "html":field_label});
		u.ae(field, "input", u.f.verifyAttributes(attributes));
		var file, options;
		var ul = u.ae(field, "ul", {"class": "filelist"});
		if(field_value) {
			for(i = 0; i < field_value.length; i++) {
				file = field_value[i];
				options = {
					"class": "uploaded", 
					"html": file["name"],
					"data-id": file.id,
					"data-name": file.name,
					"data-description": file.description,
					"data-variant": file.variant,
					"data-format": file.format,
					"data-width": file.width,
					"data-height": file.height,
				};
				u.ae(ul, "li", options);
			}
		}
	}
	else {
		u.bug("input type not implemented")
	}
	if(field_hint_message || field_error_message) {
		var help = u.ae(field, "div", {"class":"help"});
		if (field_hint_message) {
			u.ae(help, "div", { "class": "hint", "html": field_hint_message });
		}
		if(field_error_message) {
			u.ae(help, "div", { "class": "error", "html": field_error_message });
		}
	}
	return field;
}
u.f.verifyAttributes = function(attributes) {
	for(attribute in attributes) {
		if(attributes[attribute] === undefined || attributes[attribute] === false || attributes[attribute] === null) {
			delete attributes[attribute];
		}
	}
	return attributes;
}
u.f.addAction = function(node, _options) {
	var action_type = "submit";
	var action_name = "js_name";
	var action_value = "";
	var action_class = "";
	if(obj(_options)) {
		var _argument;
		for(_argument in _options) {
			switch(_argument) {
				case "type"			: action_type			= _options[_argument]; break;
				case "name"			: action_name			= _options[_argument]; break;
				case "value"		: action_value			= _options[_argument]; break;
				case "class"		: action_class			= _options[_argument]; break;
			}
		}
	}
	var p_ul = node.nodeName.toLowerCase() == "ul" ? node : u.pn(node, {"include":"ul.actions"});
	if(!p_ul || !u.hc(p_ul, "actions")) {
		if(node.nodeName.toLowerCase() == "form") {
			p_ul = u.qs("ul.actions", node);
		}
		p_ul = p_ul ? p_ul : u.ae(node, "ul", {"class":"actions"});
	}
	var p_li = node.nodeName.toLowerCase() == "li" ? node : u.pn(node, {"include":"li"});
	if(!p_li || p_ul != p_li.parentNode) {
		p_li = u.ae(p_ul, "li", {"class":action_name});
	}
	else {
		p_li = node;
	}
	var action = u.ae(p_li, "input", {"type":action_type, "class":action_class, "value":action_value, "name":action_name})
	return action;
}


/*u-form-labelstyle-inject.js*/
Util.Form.customLabelStyle["inject"] = function(iN) {
	if(!iN.type || !iN.type.match(/file|radio|checkbox/)) {
		iN.default_value = u.text(iN.label);
		u.e.addEvent(iN, "focus", u.f._changed_state);
		u.e.addEvent(iN, "blur", u.f._changed_state);
		u.e.addEvent(iN, "change", u.f._changed_state);
		if(iN.type.match(/number|integer|password/)) {
			iN.pseudolabel = u.ae(iN.parentNode, "span", {"class":"pseudolabel", "html":iN.default_value});
			iN.pseudolabel.iN = iN;
			u.as(iN.pseudolabel, "top", iN.offsetTop+"px");
			u.as(iN.pseudolabel, "left", iN.offsetLeft+"px");
			u.ce(iN.pseudolabel)
			iN.pseudolabel.inputStarted = function(event) {
				u.e.kill(event);
				this.iN.focus();
			}
		}
		u.f.updateDefaultState(iN);
	}
}
u.f._changed_state = function() {
	u.f.updateDefaultState(this);
}
u.f.updateDefaultState = function(iN) {
	if(iN.is_focused || iN.val() !== "") {
		u.rc(iN, "default");
		if(iN.field.virtual_input) {
			u.rc(iN.field.virtual_input, "default");
		}
		if(iN.val() === "" && !iN.type.match(/date|datetime|select/)) {
			iN.val("");
		}
	}
	else {
		if(iN.val() === "") {
			u.ac(iN, "default");
			if(obj(iN.field.virtual_input)) {
				u.ac(iN.field.virtual_input, "default");
			}
			if(!iN.type.match(/date|datetime|select/)) {
				iN.val(iN.default_value);
			}
		}
	}
}


/*u-form-field-html.js*/
Util.Form.customInit["html"] = function(field) {
	field.type = "html";
	field.input = u.qs("textarea", field);
	field.input._form = field._form;
	field.input.label = u.qs("label[for='"+field.input.id+"']", field);
	field.input.field = field;
	field._html_value = function(value) {
		if(value !== undefined) {
			this.value = value;
			if(value !== this.default_value) {
				u.rc(this, "default");
			}
			u.f.validate(this);
		}
		var value_tester = document.createElement("div");
		value_tester.innerHTML = this.value.trim();
		return (this.value != this.default_value && u.text(value_tester)) ? this.value : "";
	}
	field.input.val = field._html_value;
	u.f.textEditor(field);
}
Util.Form.customValidate["html"] = function(iN) {
	min = Number(u.cv(iN.field, "min"));
	max = Number(u.cv(iN.field, "max"));
	min = min ? min : 1;
	max = max ? max : 1000000000;
	pattern = iN.getAttribute("pattern");
	var value_tester = document.createElement("div");
	value_tester.innerHTML = iN.val();
	if(
		u.text(value_tester) &&
		u.text(value_tester).length >= min && 
		u.text(value_tester).length <= max && 
		(!pattern || iN.val().match("^"+pattern+"$"))
	) {
		u.f.inputIsCorrect(iN);
	}
	else {
		u.f.inputHasError(iN);
	}
}
Util.Form.customHintPosition["html"] = function(field) {
	var input_middle = field.editor.offsetTop + (field.editor.offsetHeight / 2);
	var help_top = input_middle - field.help.offsetHeight / 2;
	u.ass(field.help, {
		"top": help_top + "px"
	});
}
u.f.textEditor = function(field) {
	field.text_support = "h1,h2,h3,h4,h5,h6,p";
	field.code_support = "code";
	field.list_support = "ul,ol";
	field.media_support = "png,jpg,mp4";
	field.ext_video_support = "youtube,vimeo";
	field.file_support = "download"; 
	field.button_support = "button";
	field.allowed_tags = u.cv(field, "tags");
	if(!field.allowed_tags) {
		u.bug("allowed_tags not specified")
		return;
	}
	field.filterAllowedTags = function(type) {
		tags = this.allowed_tags.split(",");
		this[type+"_allowed"] = new Array();
		var tag, i;
		for(i = 0; i < tags.length; i++) {
			tag = tags[i];
			if(tag.match("^("+this[type+"_support"].split(",").join("|")+")$")) {
				this[type+"_allowed"].push(tag);
			}
		}
	}
	field.filterAllowedTags("text");
	field.filterAllowedTags("list");
	field.filterAllowedTags("media");
	field.filterAllowedTags("ext_video");
	field.filterAllowedTags("file");
	field.filterAllowedTags("code");
	field.filterAllowedTags("button");
	field.file_add_action = field.getAttribute("data-file-add");
	field.file_delete_action = field.getAttribute("data-file-delete");
	field.file_update_metadata_action = field.getAttribute("data-file-update-metadata");
	field.file_media_info_action = field.getAttribute("data-file-media-info");
	field._item_id = field._form._item_id;
	field.viewer = u.ae(field, "div", {"class":"viewer"});
	field.insertBefore(field.viewer, field.help)
	field.viewer.field = field;
	field.editor = u.ae(field, "div", {"class":"editor"});
	field.insertBefore(field.editor, field.help)
	field.editor.field = field;
	field.selection_options = {};
	field.inline_options = {};
	if(!fun(u.f.fixFieldHTML)) {
		u.ae(field.editor, field.indicator);
	}
	field.editor.picked = function() {
		u.ac(this, "reordering");
	}
	field.editor.dropped = function(node) {
		u.rc(this, "reordering");
		this.field.update();
		this.field.returnFocus(node);
	}
	field.addViewHTMLButton = function() {
		this.bn_show_raw = u.ae(this.input.label, "span", {"html": "View HTML", "class": "help"});
		this.bn_show_raw.field = this;
		u.ce(this.bn_show_raw);
		this.bn_show_raw.clicked = function() {
			if(u.hc(this.field.input, "show")) {
				this.field.input.removeAttribute("readonly");
				u.rc(this.field.input, "show");
				this.innerHTML = "View HTML";
			}
			else {
				this.field.input.setAttribute("readonly", 1);
				u.ac(this.field.input, "show");
				this.innerHTML = "Hide HTML";
			}
		}
	}
	field.addHelpButton = function() {
		this.bn_help = u.ae(this.input.label, "span", {"html": "?", "class": "help"});
		this.bn_help.field = this;
		u.ce(this.bn_help);
		this.bn_help.clicked = function() {
			alert("implement help");
		}
	}
	field.update = function() {
		this.updateContent();
		if(fun(this.updated)) {
			this.updated(this.input);
		}
		if(fun(this.changed)) {
			this.changed(this.input);
		}
		if(this.input._form && fun(this.input._form.updated)) {
			this.input._form.updated(this.input);
		}
		if(this.input._form && fun(this.input._form.changed)) {
			this.input._form.changed(this.input);
		}
	}
	// 	
	// 	
	// 	
	// 		
	// 		
	// 			
	// 		
	// 			
	// 			
	// 		
	// 			
	// 		
	// 		
	// 			
	// 		
	// 			
	field.updateContent = function() {
		var tags = u.qsa("div.tag", this);
		this.input.val("");
		var i, node, tag, type, value, j, html = "";
		var list_started = false;
		var button_list_started = false;
		for(i = 0; i < tags.length; i++) {
			tag = tags[i];
			if(tag.type === "text") {
				type = tag.type_selector.val();
				value = tag.virtual_input.val();
				html += '<'+type + (tag._classname ? (' class="'+tag._classname+'"') : '')+'>'+value+'</'+type+'>'+"\n";
			}
			else if(tag.type === "li") {
				type = tag.type_selector.val();
				value = tag.virtual_input.val();
				if(!list_started) {
					html += "<"+type+">\n";
					list_started = type;
				}
				html += "\t<li"+(tag._classname ? (' class="'+tag._classname+'"') : '')+">"+value+"</li>\n";
				if(i === tags.length-1 || tags[i+1].type !== "li" || tags[i+1].type_selector.val() !== list_started) {
					html += "</"+type+">\n";
					list_started = false;
				}
			}
			else if(tag.type === "ext_video") {
				type = tag.type_selector.val();
				value = tag.virtual_input.val();
				html += '<div class="'+type+" ext_video"+(tag._classname ? " "+tag._classname : "")+'">\n';
				if(value.video_url) {
					html += '\t<ul class="metadata" data-variant="'+(value.variant ? value.variant : "")+'" data-poster-format="'+(value.poster_format ? value.poster_format : "")+'" itemprop="video" itemscope itemtype="http://schema.org/VideoObject">\n';
					html += '\t\t<li itemprop="contentUrl">'+value.video_url+'</li>\n';
					if(value.variant && value.poster_format) {
						html += '\t\t<li itemprop="thumbnailUrl">/images/'+this._item_id+'/'+value.variant+'/1200x.'+value.poster_format+'</li>\n';
					}
					if(value.poster_width && value.poster_format) {
						html += '\t\t<li itemprop="width">'+value.poster_width+'</li>\n';
					}
					if(value.poster_height && value.poster_format) {
						html += '\t\t<li itemprop="height">'+value.poster_height+'</li>\n';
					}
					if(value.file_name) {
						html += '\t\t<li itemprop="name">'+value.file_name+'</li>\n';
					}
					if(value.file_description) {
						html += '\t\t<li itemprop="description">'+value.file_description+'</li>\n';
					}
					html += '\t\t<li itemprop="uploadDate">'+value.created_at+'</li>\n';
					html += '\t</ul>\n';
				}
				html += '</div>\n';
			}
			else if(tag.type === "code") {
				type = tag.type_selector.val();
				value = tag.virtual_input.val();
				html += '<code'+(tag._classname ? (' class="'+tag._classname+'"') : '')+'>'+value+'</code>'+"\n";
			}
			else if(tag.type === "media") {
				type = tag.type_selector.val();
				value = tag.virtual_input.val();
				var media_type = "";
				if(value.variant && value.format) {
					media_type = value.format.match(/^(mov|mp4|ogv|3gp)$/i) ? "video" : "image";
				}
				html += '<div class="'+type+(tag._classname ? " "+tag._classname : "")+(media_type ? " "+media_type : "")+'">\n';
				if(media_type) {
					html += '\t<ul class="metadata" data-variant="'+value.variant+'" data-format="'+value.format+'" data-poster="'+(value.poster ? value.poster : "")+'" itemprop="'+media_type+'" itemscope itemtype="http://schema.org/'+media_type.replace(/^([a-z])/, function(a){return a.toUpperCase()})+'Object">\n';
					html += '\t\t<li itemprop="contentUrl">/'+media_type+"s/"+this._item_id+"/"+value.variant+'/1200x.'+value.format+'</li>\n';
					if(value.poster && media_type === "video") {
						html += '\t\t<li itemprop="thumbnailUrl">/images/'+this._item_id+'/'+value.variant+'/1200x.'+value.poster+'</li>\n';
					}
					else if(media_type === "image") {
						html += '\t\t<li itemprop="thumbnailUrl">/images/'+this._item_id+'/'+value.variant+'/1200x.'+value.format+'</li>\n';
					}
					if(value.width) {
						html += '\t\t<li itemprop="width">'+value.width+'</li>\n';
					}
					if(value.height) {
						html += '\t\t<li itemprop="height">'+value.height+'</li>\n';
					}
					if(value.file_name) {
						html += '\t\t<li itemprop="name">'+value.file_name+'</li>\n';
					}
					if(value.file_description) {
						html += '\t\t<li itemprop="description">'+value.file_description+'</li>\n';
					}
					html += '\t\t<li itemprop="uploadDate">'+value.created_at+'</li>\n';
					html += '\t</ul>\n';
				}
				html += '</div>\n';
			}
			else if(tag.type === "file") {
				type = tag.type_selector.val();
				value = tag.virtual_input.val();
				html += '<div class="'+type+(tag._classname ? " "+tag._classname : "")+'">\n';
				if(value.url && value.url && value.file_name) {
					html += '\t<p><a href="'+value.url+'">'+value.file_name+'</a></p>\n';
					html += '\t<ul class="metadata" data-variant="'+value.variant+'" data-format="'+value.format+'" itemprop="associatedMedia" itemscope itemtype="http://schema.org/MediaObject">\n';
					html += '\t\t<li itemprop="contentUrl">'+value.url+'</li>\n';
					if(value.file_name) {
						html += '\t\t<li itemprop="name">'+value.file_name+'</li>\n';
					}
					if(value.file_description) {
						html += '\t\t<li itemprop="description">'+value.file_description+'</li>\n';
					}
					if(value.filesize) {
						html += '\t\t<li itemprop="contentSize">'+value.filesize+'</li>\n';
					}
					html += '\t\t<li itemprop="uploadDate">'+value.created_at+'</li>\n';
					html += '\t</ul>\n';
				}
				html += '</div>\n';
			}
			else if(tag.type === "button") {
				type = tag.type_selector.val();
				value = tag.virtual_input.val();
				if(!button_list_started) {
					html += '<ul class="actions">\n';
					button_list_started = type;
				}
				html += '\t<li'+(tag._classname ? (' class="'+tag._classname+'"') : '')+'><a href="'+value.link+'">'+value.text+'</a></li>\n';
				if(i === tags.length-1 || tags[i+1].type !== "button" || tags[i+1].type_selector.val() !== button_list_started) {
					html += "</ul>\n";
					button_list_started = false;
				}
			}
		}
		this.input.val(html);
	}
	field.createTag = function(allowed_tags, type) {
		var tag = u.ae(this.editor, "div", {"class":"tag"});
		tag.field = this;
		tag._drag = u.ae(tag, "div", {"class":"drag"});
		tag._drag.field = this;
		tag._drag.tag = tag;
		this.createTagSelector(tag, allowed_tags);
		tag.type_selector.val(type);
		this.addTagOptions(tag);
		return tag;
	}
	field.deleteTag = function(tag) {
		if(u.qsa("div.tag", this).length > 1) {
			if(tag.type.match(/^(file|media|ext_video)$/)) {
				this.deleteFile(tag);
				u.k.removeKey(tag, "e");
			}
			else if(u.hc(tag, "media")) {
				this.deleteMedia(tag);
			}
			var prev = this.findPreviousTag(tag);
			tag.parentNode.removeChild(tag);
			this.editor.updateTargets();
			this.editor.updateDraggables();
			this.update();
			this._form.submit();
			if(prev && prev.virtual_input) {
				prev.virtual_input.focus();
			}
		}
	}
	field.classnameTag = function(tag) {
		if(!u.hc(tag.bn_classname, "open")) {
			var form = u.f.addForm(tag.bn_classname, {"class":"labelstyle:inject"});
			form.tag = tag;
			var fieldset = u.f.addFieldset(form);
			u.f.addField(fieldset, {"label":"classname", "name":"classname", "error_message":"", "value":tag._classname});
			u.f.addAction(form, {"name":"save", "value":"Save"});
			u.ac(tag.bn_classname, "open");
			u.ac(tag, "classname_open");
			u.f.init(form);
			form.submitted = function() {
				var classname = this.inputs["classname"].val();
				this.parentNode.removeChild(this);
				u.rc(this.tag.bn_classname, "open");
				u.rc(this.tag, "classname_open");
				if(classname && classname != "") {
					u.ac(this.tag.bn_classname, "modified");
					this.tag.updateClassName(classname);
				}
				else {
					u.rc(this.tag.bn_classname, "modified");
					this.tag.updateClassName();
				}
				this.tag.field.update();
				this.tag.field.returnFocus(this.tag);
			}
			form.inputs["classname"].blurred = function(event) {
				this._form.submit();
			}
			form.inputs["classname"].focus();
		}
	}
	field.createTagSelector = function(tag, allowed_tags) {
		var i, allowed_tag;
		tag.type_selector = u.ae(tag, "ul", {"class":"type"});
		tag.type_selector.field = this;
		tag.type_selector.tag = tag;
		for(i = 0; i < allowed_tags.length; i++) {
			allowed_tag = allowed_tags[i];
			u.ae(tag.type_selector, "li", {"html":allowed_tag, "class":allowed_tag});
			if(allowed_tags.length > 1) {
				var key = (isNaN(allowed_tag[allowed_tag.length -1]) ? allowed_tag[0] : allowed_tag[allowed_tag.length -1]);
				u.k.addKey(tag, key, {
					"callback": "switchTag",
					"focused": true,
					"value": allowed_tag,
				});
			}
		}
		tag.switchTag = function(event, value) {
			this.type_selector.val(value);
		}
		tag.type_selector.val = function(value) {
			if(value !== undefined) {
				var i, option;
				for(i = 0; i < this.childNodes.length; i++) {
					option = this.childNodes[i];
					if(u.text(option) == value) {
						if(this.selected_option) {
							u.rc(this.selected_option, "selected");
							u.rc(this.tag, u.text(this.selected_option));
						}
						u.ac(option, "selected");
						this.selected_option = option;
						u.ac(this.tag, value);
						return option;
					}
				}
				u.ac(this.childNodes[0], "selected");
				this.selected_option = this.childNodes[0];
				u.ac(this.tag, u.text(this.childNodes[0]));
				return this.childNodes[0];
			}
			else {
				return u.text(this.selected_option);
			}
		}
		if(allowed_tags.length > 1) {
			u.ce(tag.type_selector);
			tag.type_selector.inputStarted = function(event) {
				var selection = window.getSelection();
				if(selection && selection.type && u.contains(this.tag, selection.anchorNode)) {
					u.e.kill(event);
				}
			}
			tag.type_selector.clicked = function(event) {
				if(!this.field.inlineformatting) {
					u.t.resetTimer(this.t_autohide);
					if(u.hc(this, "open")) {
						u.rc(this, "open");
						u.rc(this.tag, "focus");
						u.ass(this.field, {
							"zIndex": this.field._base_z_index
						});
						u.as(this, "top", 0);
						if(event.target) {
							this.val(u.text(event.target));
						}
						u.e.removeEvent(this, "mouseout", this.autohide);
						u.e.removeEvent(this, "mouseover", this.delayautohide);
						this.field.returnFocus(this.tag);
						this.field.update();
					}
					else {
						u.ac(this, "open");
						u.ac(this.tag, "focus");
						u.ass(this.field, {
							"zIndex": this.field._form._focus_z_index,
						});
						u.as(this, "top", -(this.selected_option.offsetTop) + "px");
						u.e.addEvent(this, "mouseout", this.autohide);
						u.e.addEvent(this, "mouseover", this.delayautohide);
					}
				}
			}
			tag.type_selector.hide = function() {
				u.rc(this, "open");
				if(!this.field.is_focused) {
					u.rc(this.tag, "focus");
					u.ass(this.field, {
						"zIndex": this.field._base_z_index
					});
					this.field.returnFocus(this.tag	);
				}
				u.as(this, "top", 0);
				u.e.removeEvent(this, "mouseout", this.autohide);
				u.e.removeEvent(this, "mouseover", this.delayautohide);
				u.t.resetTimer(this.t_autohide);
			}
			tag.type_selector.autohide = function(event) {
				u.t.resetTimer(this.t_autohide);
				this.t_autohide = u.t.setTimer(this, this.hide, 800);
			}
			tag.type_selector.delayautohide = function(event) {
				u.t.resetTimer(this.t_autohide);
			}
		}
	}
	field.addTagOptions = function(tag) {
		tag.ul_tag_options = u.ae(tag, "ul", {"class":"tag_options"});
		tag.bn_add = u.ae(tag.ul_tag_options, "li", {"class":"add", "html":"+"});
		tag.bn_add.field = field;
		tag.bn_add.tag = tag;
		u.ce(tag.bn_add);
		tag.bn_add.clicked = function(event) {
			this.cleanupOptions = function(event) {
				u.k.removeKey(this, "ESC");
				if(this.field.ul_new_tag_options) {
					u.k.removeKey(this.field.ul_new_tag_options, "UP");
					u.k.removeKey(this.field.ul_new_tag_options, "DOWN");
					u.k.removeKey(this.field.ul_new_tag_options, "ENTER");
					this.field.ul_new_tag_options.parentNode.removeChild(this.field.ul_new_tag_options);
					delete this.field.ul_new_tag_options;
					if(this.start_event_id) {
						u.e.removeWindowStartEvent(this.start_event_id);
						delete this.start_event_id;
					}
				}
			}
			if(this.field.ul_new_tag_options) {
				this.cleanupOptions();
			}
			u.k.addKey(this, "ESC", {
				"callback": "cleanupOptions"
			});
			this.start_event_id = u.e.addWindowStartEvent(this, this.cleanupOptions);
			this.field.ul_new_tag_options = u.ae(this.field.editor, "ul", {"class":"new_tag_options"});
			u.ia(this.field.ul_new_tag_options, this.tag);
			this.field.ul_new_tag_options.optionFocus = function(event) {
				var option = event.target;
				if(this.selected_option) {
					u.rc(this.selected_option, "focus");
				}
				this.selected_option = option;
				u.ac(this.selected_option, "focus");
			}
			if(this.field.text_allowed.length) {
				this.bn_add_text = u.ae(this.field.ul_new_tag_options, "li", {"class":"text", "html":"Text ("+this.field.text_allowed.join(", ")+")"});
				this.bn_add_text.field = this.field;
				this.bn_add_text.tag = this.tag;
				u.e.addOverEvent(this.bn_add_text, this.field.ul_new_tag_options.optionFocus.bind(this.field.ul_new_tag_options));
				u.ce(this.bn_add_text);
				this.bn_add_text.inputStarted = function(event) {
					u.e.kill(event);
				}
				this.bn_add_text.clicked = function(event) {
					var tag = this.field.addTextTag(this.field.text_allowed[0]);
					u.ia(tag, this.tag);
					if(event.type === "keydown") {
						this.field.ul_new_tag_options.handleEnterKeyUp(tag);
					}
					else {
						this.field.returnFocus(tag);
					}
					this.tag.bn_add.cleanupOptions();
				}
			}
			if(this.field.list_allowed.length) {
				this.bn_add_list = u.ae(this.field.ul_new_tag_options, "li", {"class":"list", "html":"List ("+this.field.list_allowed.join(", ")+")"});
				this.bn_add_list.field = this.field;
				this.bn_add_list.tag = this.tag;
				u.e.addOverEvent(this.bn_add_list, this.field.ul_new_tag_options.optionFocus.bind(this.field.ul_new_tag_options));
				u.ce(this.bn_add_list);
				this.bn_add_list.inputStarted = function(event) {
					u.e.kill(event);
				}
				this.bn_add_list.clicked = function(event) {
					var tag = this.field.addListTag(this.field.list_allowed[0]);
					u.ia(tag, this.tag);
					if(event.type === "keydown") {
						this.field.ul_new_tag_options.handleEnterKeyUp(tag);
					}
					else {
						this.field.returnFocus(tag);
					}
					this.tag.bn_add.cleanupOptions();
				}
			}
			if(this.field.media_allowed.length && this.field._item_id && this.field.file_add_action && this.field.file_delete_action && !u.browser("IE", "<=9")) {
				this.bn_add_media = u.ae(this.field.ul_new_tag_options, "li", {"class":"list", "html":"Media ("+this.field.media_allowed.join(", ")+")"});
				this.bn_add_media.field = this.field;
				this.bn_add_media.tag = this.tag;
				u.e.addOverEvent(this.bn_add_media, this.field.ul_new_tag_options.optionFocus.bind(this.field.ul_new_tag_options));
				u.ce(this.bn_add_media);
				this.bn_add_media.inputStarted = function(event) {
					u.e.kill(event);
				}
				this.bn_add_media.clicked = function(event) {
					var tag = this.field.addMediaTag();
					u.ia(tag, this.tag);
					if(event.type === "keydown") {
						this.field.ul_new_tag_options.handleEnterKeyUp(tag);
					}
					else {
						this.field.returnFocus(tag);
					}
					this.tag.bn_add.cleanupOptions();
				}
			}
			else if(this.field.media_allowed.length) {
				u.bug("some information is missing to support media upload:\nitem_id="+this.field._item_id+"\nmedia_add_action="+this.field.media_add_action+"\nmedia_delete_action="+this.field.media_delete_action);
			}
			if(this.field.button_allowed.length) {
				this.bn_add_button = u.ae(this.field.ul_new_tag_options, "li", {"class":"button", "html":"Button"});
				this.bn_add_button.field = this.field;
				this.bn_add_button.tag = this.tag;
				u.e.addOverEvent(this.bn_add_button, this.field.ul_new_tag_options.optionFocus.bind(this.field.ul_new_tag_options));
				u.ce(this.bn_add_button);
				this.bn_add_button.inputStarted = function(event) {
					u.e.kill(event);
				}
				this.bn_add_button.clicked = function(event) {
					var tag = this.field.addButtonTag(this.field.button_allowed[0]);
					u.ia(tag, this.tag);
					if(event.type === "keydown") {
						this.field.ul_new_tag_options.handleEnterKeyUp(tag);
					}
					else {
						this.field.returnFocus(tag);
					}
					this.tag.bn_add.cleanupOptions();
				}
			}
			if(this.field.code_allowed.length) {
				this.bn_add_code = u.ae(this.field.ul_new_tag_options, "li", {"class":"code", "html":"Code"});
				this.bn_add_code.field = this.field;
				this.bn_add_code.tag = this.tag;
				u.e.addOverEvent(this.bn_add_code, this.field.ul_new_tag_options.optionFocus.bind(this.field.ul_new_tag_options));
				u.ce(this.bn_add_code);
				this.bn_add_code.inputStarted = function(event) {
					u.e.kill(event);
				}
				this.bn_add_code.clicked = function(event) {
					var tag = this.field.addCodeTag(this.field.code_allowed[0]);
					u.ia(tag, this.tag);
					if(event.type === "keydown") {
						this.field.ul_new_tag_options.handleEnterKeyUp(tag);
					}
					else {
						this.field.returnFocus(tag);
					}
					this.tag.bn_add.cleanupOptions();
				}
			}
			if(this.field.ext_video_allowed.length) {
				this.bn_add_ext_video = u.ae(this.field.ul_new_tag_options, "li", {"class":"video", "html":"External video ("+this.field.ext_video_allowed.join(", ")+")"});
				this.bn_add_ext_video.field = this.field;
				this.bn_add_ext_video.tag = this.tag;
				u.e.addOverEvent(this.bn_add_ext_video, this.field.ul_new_tag_options.optionFocus.bind(this.field.ul_new_tag_options));
				u.ce(this.bn_add_ext_video);
				this.bn_add_ext_video.inputStarted = function(event) {
					u.e.kill(event);
				}
				this.bn_add_ext_video.clicked = function(event) {
					var tag = this.field.addExternalVideoTag(this.field.ext_video_allowed[0]);
					u.ia(tag, this.tag);
					if(event.type === "keydown") {
						this.field.ul_new_tag_options.handleEnterKeyUp(tag);
					}
					else {
						this.field.returnFocus(tag);
					}
					this.tag.bn_add.cleanupOptions();
				}
			}
			if(this.field.file_allowed.length && this.field._item_id && this.field.file_add_action && this.field.file_delete_action && !u.browser("IE", "<=9")) {
				this.bn_add_file = u.ae(this.field.ul_new_tag_options, "li", {"class":"file", "html":"Downloadable file"});
				this.bn_add_file.field = this.field;
				this.bn_add_file.tag = this.tag;
				u.e.addOverEvent(this.bn_add_file, this.field.ul_new_tag_options.optionFocus.bind(this.field.ul_new_tag_options));
				u.ce(this.bn_add_file);
				this.bn_add_file.inputStarted = function(event) {
					u.e.kill(event);
				}
				this.bn_add_file.clicked = function(event) {
					var tag = this.field.addFileTag(this.field.file_allowed[0]);
					u.ia(tag, this.tag);
					if(event.type === "keydown") {
						this.field.ul_new_tag_options.handleEnterKeyUp(tag);
					}
					else {
						this.field.returnFocus(tag);
					}
					this.tag.bn_add.cleanupOptions();
				}
			}
			else if(this.field.file_allowed.length) {
				u.bug("some information is missing to support file upload:\nitem_id="+this.field._item_id+"\nfile_add_action="+this.field.file_add_action+"\nfile_delete_action="+this.field.file_delete_action);
			}
		}
		tag.addTagShortcut = function(event) {
			this.bn_add.clicked(event);
			this.field.ul_new_tag_options.setAttribute("tabindex", -1);
			this.field.ul_new_tag_options.focus();
			u.e.addEvent(this.field.ul_new_tag_options, "blur", this.bn_add.cleanupOptions.bind(this.bn_add));
			this.field.ul_new_tag_options.optionFocus({"target": this.field.ul_new_tag_options.firstChild});
			this.field.ul_new_tag_options.nextTagOption = function(event) {
				var next = u.ns(this.selected_option) || this.firstChild;
				this.optionFocus({"target": next});
			}
			this.field.ul_new_tag_options.prevTagOption = function(event) {
				var next = u.ps(this.selected_option) || this.lastChild;
				this.optionFocus({"target": next});
			}
			this.field.ul_new_tag_options.selectTagOption = function(event) {
				this.selected_option.clicked(event);
			}
			this.field.ul_new_tag_options.handleEnterKeyUp = function(tag) {
				window.temp_enter_event_tag = tag;
				window.temp_enter_event_handler = function(event) {
					u.e.removeEvent(document, "keyup", window.temp_enter_event_handler);
					window.temp_enter_event_tag.virtual_input.focus();
					delete window.temp_enter_event_handler;
					delete window.temp_enter_event_tag;
				}
				u.e.addEvent(document, "keyup", window.temp_enter_event_handler)
			}
			u.k.addKey(this.field.ul_new_tag_options, "UP", {
				"focused": true,
				"metakey": false,
				"callback": "prevTagOption"
			});
			u.k.addKey(this.field.ul_new_tag_options, "DOWN", {
				"focused": true,
				"metakey": false,
				"callback": "nextTagOption"
			});
			u.k.addKey(this.field.ul_new_tag_options, "ENTER", {
				"focused": true,
				"metakey": false,
				"callback": "selectTagOption"
			});
		}
		u.k.addKey(tag, "+", {
			"focused": true,
			"callback": "addTagShortcut"
		});
		tag.bn_remove = u.ae(tag.ul_tag_options, "li", {"class":"remove"});
		tag.bn_remove.field = this;
		tag.bn_remove.tag = tag;
		u.ce(tag.bn_remove);
		tag.bn_remove.clicked = function() {
			this.field.deleteTag(this.tag);
		}
		tag.bn_classname = u.ae(tag.ul_tag_options, "li", {"class":"classname"});
		tag.bn_classname.default_test = "CSS classname";
		tag.bn_classname.span = u.ae(tag.bn_classname, "span", {"html": tag.bn_classname.default_test});
		tag.updateClassName = function(classname) {
			if(classname) {
				this._classname = classname;
				this.bn_classname.span.innerHTML = classname;
			}
			else {
				this._classname = "";
				this.bn_classname.span.innerHTML = this.bn_classname.default_test
			}
		}
		tag.bn_classname.field = this;
		tag.bn_classname.tag = tag;
		u.ce(tag.bn_classname);
		tag.bn_classname.clicked = function(event) {
			this.field.classnameTag(this.tag);
		}
	}
	field.tagMetadataOverlay = function(tag, title, _options) {
		var width = 600;
		var height = 500;
		if(obj(_options)) {
			var _argument;
			for(_argument in _options) {
				switch(_argument) {
					case "width"               : width                = _options[_argument]; break;
					case "height"              : height               = _options[_argument]; break;
				}
			}
		}
		var overlay = u.overlay({
			"title": title,
			"width": width,
			"height": height,
			"esc": true
		});
		overlay.tag = tag;
		overlay.closed = function(event) {
			this.tag.field.returnFocus(this.tag);
			this.tag.field.update();
		}
		return overlay;
	}
	field.addEditMetadataButton = function(tag) {
		u.ac(tag, "edit_overlay");
		tag.bn_edit = u.ae(tag.virtual_input, "span", {"class":"edit"});
		tag.bn_edit.tag = tag;
		u.ce(tag.bn_edit);
		tag.bn_edit.clicked = function(event) {
			this.tag.editMetadata(this.tag);
		}
		u.k.addKey(tag, "e", {
			"callback": tag.bn_edit.clicked.bind(tag.bn_edit),
			"focused": true,
		});
		if(tag.virtual_input && tag.type.match(/ext_video|button/)) {
			u.ce(tag.virtual_input);
			tag.virtual_input.clicked = function(event) {
				this.tag.editMetadata(this.tag);
			}
		}
	}
	field.deleteFile = function(tag) {
		var existing_values = tag.virtual_input.val();
		if(existing_values && existing_values.variant) {
			var form_data = new FormData();
			form_data.append("item_id", this._item_id);
			form_data.append("file_variant", existing_values.variant);
			form_data.append("csrf-token", this._form.inputs["csrf-token"].val());
			tag.response = function(response) {
				page.notify(response);
				if(response.cms_status && response.cms_status == "success") {
				}
			}
			u.request(tag, this.file_delete_action, {
				"method":"post", 
				"data":form_data
			});
		}
	}
	field.addExternalVideoTag = function(type, node) {
		var tag = this.createTag(this.ext_video_allowed, type);
		tag.type = "ext_video";
		u.ac(tag, tag.type);
		tag.default_label = "Click to add button properties";
		tag.virtual_input = u.ae(tag, "div", {"class":"text", "tabindex": 0});
		tag.virtual_input.span_value = u.ae(tag.virtual_input, "span", {"class":"value", "html": tag.default_label});
		tag.virtual_input.tag = tag;
		tag.virtual_input.field = this;
		tag.virtual_input._form = this._form;
		tag.virtual_input.input_list = [
			"video_url", 
			"poster_format", 
			"variant", 
			"poster_width", 
			"poster_height", 
			"file_name", 
			"file_description", 
			"created_at"
		];
		tag.virtual_input.values = {};
		tag.editMetadata = this.externalVideoMetadata.bind(this);
		this.addEditMetadataButton(tag);
		tag.virtual_input.updateView = function() {
			if(this.values.video_url) {
				this.span_value.innerHTML = this.values.video_url;
				u.ac(this.tag, "done");
			}
			else {
				this.span_value.innerHTML = this.tag.default_label;
				u.rc(this.tag, "done");
			}
			if(this.values.poster_format && this.values.variant) {
				u.ac(this.tag, "previewing");
				var image_src = "/images/"+this.field._item_id+"/"+this.values.variant+"/"+this.tag.virtual_input.offsetWidth+"x."+this.values.poster_format;
				u.ass(this.tag, {
					"height": ((this.tag.virtual_input.offsetWidth / this.values.poster_width) * this.values.poster_height) + "px",
				});
				u.ass(this.tag.virtual_input, {
					"backgroundImage": "url("+image_src+"?"+u.randomString(4)+")"
				});
			}
			else {
				u.rc(this.tag, "previewing");
				u.ass(this.tag, {
					"height": "unset",
				});
				u.ass(this.tag.virtual_input, {
					"backgroundImage": "unset"
				});
			}
		}
		tag.virtual_input.val = function(value_object) {
			if(value_object !== undefined && obj(value_object)) {
				for(input in value_object) {
					if(this.input_list.includes(input)) {
						this.values[input] = value_object[input];
					}
				}
				this.updateView();
			}
			return {
				"video_url": this.values.video_url, 
				"poster_format": this.values.poster_format,
				"variant": this.values.variant,
				"poster_width": this.values.poster_width,
				"poster_height": this.values.poster_height,
				"file_name": this.values.file_name,
				"file_description": this.values.file_description,
				"created_at": this.values.created_at,
			};
		}
		if(node) {
			var meta_list = u.qs("ul.metadata", node);
			if(meta_list) {
				u.rc(node, "ext_video");
				if(node.className !== type) {
					var classname = node.className.replace(type, "").trim();
					tag.updateClassName(classname);
				}
				var meta_video = u.qs("li[itemprop=contentUrl]", meta_list);
				var meta_name = u.qs("li[itemprop=name]", meta_list);
				var meta_description = u.qs("li[itemprop=description]", meta_list);
				var meta_created_at = u.qs("li[itemprop=uploadDate]", meta_list);
				var meta_poster = u.qs("li[itemprop=thumbnailUrl]", meta_list);
				var meta_width = u.qs("li[itemprop=width]", meta_list);
				var meta_height = u.qs("li[itemprop=height]", meta_list);
				var video_url = meta_video ? meta_video.innerHTML.trim() : "";
				var file_name = meta_name ? meta_name.innerHTML.trim() : "";
				var file_description = meta_description ? meta_description.innerHTML.trim() : "";
				var created_at = meta_created_at ? meta_created_at.innerHTML.trim() : u.date("Y-m-d H:i:s");
				var poster_width = meta_width ? meta_width.innerHTML.trim() : "";
				var poster_height = meta_height ? meta_height.innerHTML.trim() : "";
				var variant = meta_list ? (meta_list.getAttribute("data-variant") || "") : "";
				var poster_format = meta_list ? (meta_list.getAttribute("data-poster-format") || "") : "";
				tag.virtual_input.val({
					"video_url": video_url,
					"poster_format": poster_format,
					"variant": variant,
					"poster_width": poster_width,
					"poster_height": poster_height,
					"file_name": file_name,
					"file_description": file_description,
					"created_at": created_at,
				});
			}
			else {
				var video_url;
				var video_id = u.cv(node, "video_id");
				if(video_id) {
					if(u.hc(node, "youtube")) {
						video_url = "https://www.youtube.com/watch?v="+video_id;
					}
					else if(u.hc(node, "vimeo")) {
						video_url = "https://vimeo.com/"+video_id;
					}
				}
				if(video_url) {
					tag.virtual_input.val({
						"video_url": video_url,
						"created_at": u.date("Y-m-d H:i:s")
					});
				}
			}
		}
		u.e.addEvent(tag.virtual_input, "keydown", this._changing_content);
		u.e.addEvent(tag.virtual_input, "keyup", this._ext_video_changed);
		u.e.addEvent(tag.virtual_input, "focus", tag.field._focused_content);
		u.e.addEvent(tag.virtual_input, "blur", tag.field._blurred_content);
		if(u.e.event_pref == "mouse") {
			u.e.addEvent(tag.virtual_input, "mouseenter", u.f._mouseenter);
			u.e.addEvent(tag.virtual_input, "mouseleave", u.f._mouseleave);
		}
		this.update();
		this.editor.updateTargets();
		this.editor.updateDraggables();
		return tag;
	}
	field.externalVideoMetadata = function(tag) {
		tag.overlay = this.tagMetadataOverlay(tag, "External video");
		var values = tag.virtual_input.val();
		var form = u.f.addForm(tag.overlay.div_content);
		form.tag = tag;
		form.setAttribute("data-item_id", tag.field._item_id);
		u.f.addField(form, {
			"name": "csrf-token",
			"type": "hidden",
			"value": tag.field._form.inputs["csrf-token"].val(),
		});
		var fieldset = u.f.addFieldset(form);
		u.f.addField(fieldset, {
			"name": "video_url",
			"type": "string",
			"required": true,
			"pattern": "http[s]?\:\/\/[a-zA-Z0-9]+[^$]+",
			"label": "Video URL",
			"value": values.video_url,
			"hint_message": "Enter the full link to the video",
			"error_message": "External Video URL must start with https:// or http:// and be a valid link to the video",
		});
		var poster_object = values.poster_format ? [{
			"name": "Poster", 
			"variant": values.variant,
			"format": values.poster_format,
			"width": values.poster_width,
			"height": values.poster_height,
		}] : false;
		u.f.addField(fieldset, {
			"name": "file_poster[0]",
			"type": "files",
			"allowed_formats": "jpg,png",
			"label": "Poster image in same dimensions as the external video",
			"value": poster_object,
			"max": 1,
			"file_delete": this.getAttribute("data-file-delete"),
			"is_poster": true,
		});
		u.f.addField(fieldset, {
			"name": "file_name",
			"type": "string",
			"label": "Video name / title",
			"required": true,
			"value": values.file_name,
			"hint_message": "Enter the name or title of the video. This will be provided to search engines for better indexing",
		});
		u.f.addField(fieldset, {
			"name": "created_at",
			"type": "datetime",
			"label": "Created date and time",
			"value": (values.created_at || u.date("Y-m-d H:i:s")),
			"hint_message": "When was the video created. This will be provided to search engines for better indexing",
		});
		u.f.addField(fieldset, {
			"name": "file_description",
			"type": "text",
			"label": "Description",
			"value": values.file_description,
			"hint_message": "Enter a meaningful description for the video. This will be provided to search engines for better indexing",
		});
		u.f.addAction(form, {
			"name": "update",
			"value": "Update",
			"class": "button primary",
		});
		u.f.init(form);
		form.inputs["file_poster[0]"].fileDeleted = function(file) {
			u.bug("file was delete", file);
			this._form.tag.virtual_input.val({
				"poster_format": "",
			});
		}
		u.k.addKey(form, "s", {
			"callback":"submit",
			"focused":true
		});
		form.inputs["video_url"].focus();
		form.submitted = function(iN) {
			var submitted_values = this.getData({"format":"object"});
			var existing_values = this.tag.virtual_input.val();
			u.ac(this, "submitting");
			this.tag.virtual_input.val(submitted_values);
			var form_data = this.getData();
			form_data.append("item_id", this.tag.field._item_id);
			if(existing_values.variant) {
				form_data.append("file_variant", existing_values.variant);
			}
			else {
				form_data.append("new_variant", "HTMLEDITOR-"+this.tag.field.input.name+"-"+this.tag.type+"-"+u.randomString(8));
			}
			form_data.append("type", this.tag.type);
			this.response = function(response) {
				page.notify(response);
				u.rc(this, "submitting");
				if(response.cms_status && response.cms_status == "success") {
					this.tag.virtual_input.val({
						"variant": response.cms_object.variant,
						"poster_format": (response.cms_object.poster ? response.cms_object.poster : ""),
						"poster_width": response.cms_object.width,
						"poster_height": response.cms_object.height,
						"created_at": response.cms_object.created_at,
					});
					this.tag.field.update();
					this.tag.field._form.submit();
				}
				this.tag.overlay.close();
			}
			u.request(this, this.tag.field.file_update_metadata_action, {
				"method":"post",
				"data":form_data
			});
		}
	}
	field._ext_video_changed = function(event) {
		if(event.keyCode == 13 || event.keyCode == 32) {
			u.e.kill(event);
			this.clicked();
		}
		else if(event.keyCode == 8) {
			if(this.is_deletable) {
				u.e.kill(event);
				this.field.deleteTag(this.tag);
			}
			else {
				this.is_deletable = true;
			}
		}
		else {
			this.is_deletable = false;
		}
		this.field.update();
	}
	field.addMediaTag = function(type, node) {
		var tag = this.createTag(["media"], "media");
		tag.type = "media";
		tag.default_label = "Drop file here";
		tag.virtual_input = u.ae(tag, "div", {"class":"text"});
		tag.virtual_input.span_value = u.ae(tag.virtual_input, "span", {"class":"value", "html": tag.default_label});
		tag.virtual_input.tag = tag;
		tag.virtual_input.field = this;
		tag.virtual_input._form = this._form;
		tag.file_input = u.ae(tag.virtual_input, "input", {"type":"file", "name":"htmleditor_media"});
		tag.file_input.tag = tag;
		tag.file_input.field = this;
		tag.virtual_input.input_list = [
			"file_name", 
			"file_description", 
			"filesize", 
			"created_at",
			"width",
			"height",
			"variant",
			"format",
			"poster"
		];
		tag.virtual_input.values = {};
		tag.editMetadata = this.mediaMetadata.bind(this);
		this.addEditMetadataButton(tag);
		tag.virtual_input.updateView = function() {
			if(this.values.file_name) {
				this.span_value.innerHTML = this.values.file_name;
				u.ac(this.tag, "done");
			}
			else {
				this.span_value.innerHTML = this.tag.default_label;
				u.rc(this.tag, "done");
			}
			u.rc(this, "preview_(image|video)");
			if(this.player) {
				this.player.parentNode.removeChild(this.player);
				delete this.player;
			}
			if(this.ul_controls) {
				this.ul_controls.parentNode.removeChild(this.ul_controls);
				delete this.ul_controls;
			}
			u.ass(this.tag, {
				"height": "auto",
			});
			u.ass(this.tag.virtual_input, {
				"backgroundImage": "unset",
			});
			if(this.values.format && this.values.variant && this.values.width && this.values.height) {
				if(this.values.format.match(/^(mov|mp4|ogv|3gp)$/i)) {
					u.ac(this.tag, "previewing");
					u.ac(this, "preview_video");
					var video_src = "/videos/"+this.field._item_id+"/"+this.values.variant+"/1000x."+this.values.format;
					this.player = u.videoPlayer({"muted":true, "loop":true, "preload":"metadata"});
					this.player.media.setAttribute("tabindex", -1);
					this.player.preview = this;
					u.ae(this, this.player);
					u.ac(this, "loading-preview");
					this.player.loadedmetadata = function(event) {
						u.rc(this.preview, "loading-preview");
					}
					this.player.load(video_src+"?"+u.randomString(4));
					if(this.values.poster) {
						var poster_src = "/images/"+this.field._item_id+"/"+this.values.variant+"/1000x."+this.values.poster;
						this.player.media.poster = poster_src;
					}
					this.controls_parent = this;
					u.f.addMediaPlayerControls(this.player);
					u.ass(this.tag, {
						"height": ((this.tag.virtual_input.offsetWidth / this.values.width) * this.values.height) + "px",
					});
				}
				else if(this.values.format.match(/^(jpg|png|gif)$/i)) {
					u.ac(this.tag, "previewing");
					u.ac(this, "preview_image");
					var image_src = "/images/"+this.field._item_id+"/"+this.values.variant+"/1000x."+this.values.format;
					u.ass(this.tag, {
						"height": ((this.tag.virtual_input.offsetWidth / this.values.width) * this.values.height) + "px",
					});
					u.ass(this.tag.virtual_input, {
						"backgroundImage": "url("+image_src+"?"+u.randomString(4)+")"
					});
				}
			}
		}
		tag.virtual_input.val = function(value_object) {
			if(value_object !== undefined && obj(value_object)) {
				for(input in value_object) {
					if(this.input_list.includes(input)) {
						this.values[input] = value_object[input];
					}
				}
				this.updateView();
			}
			return {
				"file_name": this.values.file_name, 
				"file_description": this.values.file_description,
				"created_at": this.values.created_at,
				"width": this.values.width,
				"height": this.values.height,
				"variant": this.values.variant,
				"format": this.values.format,
				"poster": this.values.poster,
			};
		}
		if(node) {
			var meta_list = u.qs("ul.metadata", node);
			if(meta_list) {
				var media_type = u.hc(node, "image") ? "image" : "video";
				u.rc(node, media_type);
				if(node.className !== type) {
					var classname = node.className.replace(type, "").trim();
					tag.updateClassName(classname);
				}
				var meta_name = u.qs("li[itemprop=name]", meta_list);
				var meta_description = u.qs("li[itemprop=description]", meta_list);
				var meta_width = u.qs("li[itemprop=width]", meta_list);
				var meta_height = u.qs("li[itemprop=height]", meta_list);
				var meta_created_at = u.qs("li[itemprop=uploadDate]", meta_list);
				var file_name = meta_name ? meta_name.innerHTML.trim() : "";
				var file_description = meta_description ? meta_description.innerHTML.trim() : "";
				var width = meta_width ? meta_width.innerHTML.trim() : "";
				var height = meta_height ? meta_height.innerHTML.trim() : "";
				var created_at = meta_created_at ? meta_created_at.innerHTML.trim() : u.date("Y-m-d H:i:s");
				var variant = meta_list ? (meta_list.getAttribute("data-variant") || "") : "";
				var format = meta_list ? (meta_list.getAttribute("data-format") || "") : "";
				var poster = meta_list ? (meta_list.getAttribute("data-poster") || "") : "";
				tag.virtual_input.val({
					"file_name": file_name,
					"file_description": file_description,
					"width": width,
					"height": height,
					"created_at": created_at,
					"variant": variant,
					"format": format,
					"poster": poster,
				});
			}
			else {
				var variant = u.cv(node, "variant");
				if(variant) {
					var data = new FormData();
					data.append("item_id", this._item_id);
					data.append("file_variant", variant);
					data.append("csrf-token", this._form.inputs["csrf-token"].val());
					tag.response = function(response) {
						if(response && response.cms_object) {
							tag.virtual_input.val({
								"url": "/download/"+response.cms_object.item_id+"/"+response.cms_object.variant+"/"+response.cms_object.name,
								"file_name": response.cms_object.name,
								"file_description": response.cms_object.description,
								"width": response.cms_object.width,
								"height": response.cms_object.height,
								"created_at": response.cms_object.created_at,
								"variant": response.cms_object.variant,
								"format": response.cms_object.format,
								"poster": response.cms_object.poster,
							});
						}
						else {
							tag.virtual_input.val({
								"url": false,
								"file_name": false,
								"file_description": false,
								"width": false,
								"height": false,
								"created_at": false,
								"variant": false,
								"format": false,
								"poster": false,
							});
						}
					}
					u.request(tag, this.file_media_info_action, {
						"method": "post",
						"data": data,
					});
				}
			}
		}
		u.e.addEvent(tag.file_input, "change", this._media_changed);
		// 	
		u.e.addEvent(tag.file_input, "focus", tag.field._focused_content);
		u.e.addEvent(tag.file_input, "blur", tag.field._blurred_content);
		if(u.e.event_support != "touch") {
			u.e.addEvent(tag.file_input, "dragenter", tag.field._focused_content);
			u.e.addEvent(tag.file_input, "dragleave", tag.field._blurred_content);
			u.e.addEvent(tag.file_input, "drop", tag.field._blurred_content);
		}
		if(u.e.event_pref == "mouse") {
			u.e.addEvent(tag.virtual_input, "mouseenter", u.f._mouseenter);
			u.e.addEvent(tag.virtual_input, "mouseleave", u.f._mouseleave);
		}
		this.update();
		this.editor.updateTargets();
		this.editor.updateDraggables();
		return tag;
	}
	field.mediaMetadata = function(tag) {
		tag.overlay = this.tagMetadataOverlay(tag, "Media");
		var values = tag.virtual_input.val();
		u.bug("mediaMetadata", tag, values);
		var form = u.f.addForm(tag.overlay.div_content);
		form.tag = tag;
		form.setAttribute("data-item_id", tag.field._item_id);
		u.f.addField(form, {
			"name": "csrf-token",
			"type": "hidden",
			"value": tag.field._form.inputs["csrf-token"].val(),
		});
		var media_type = "image";
		if(values.format.match(/^(mov|mp4|ogv|3gp)$/i)) {
			media_type = "video";
		}
		var fieldset = u.f.addFieldset(form);
		if(media_type === "video") {
			var poster_object = values.poster ? [{
				"name": "Poster", 
				"variant": values.variant,
				"format": values.poster,
				"width": values.width,
				"height": values.height,
			}] : false;
			u.f.addField(fieldset, {
				"name": "file_poster[0]",
				"type": "files",
				"allowed_formats": "jpg,png",
				"label": "Poster image in same dimensions as video",
				"value": poster_object,
				"max": 1,
				"file_delete": this.getAttribute("data-file-delete"),
				"is_poster": true,
			});
		}
		u.f.addField(fieldset, {
			"name": "file_name",
			"type": "string",
			"label": "Video name / title",
			"required": true,
			"value": values.file_name,
			"hint_message": "Enter the name or title of the media. This will be provided to search engines for better indexing",
		});
		u.f.addField(fieldset, {
			"name": "created_at",
			"type": "datetime",
			"label": "Created date and time",
			"value": (values.created_at || u.date("Y-m-d H:i:s")),
			"hint_message": "When was the media created. This will be provided to search engines for better indexing",
		});
		u.f.addField(fieldset, {
			"name": "file_description",
			"type": "text",
			"label": "Description",
			"value": values.file_description,
			"hint_message": "Enter a meaningful description for the media. This will be provided to search engines for better indexing",
		});
		u.f.addAction(form, {
			"name": "update",
			"value": "Update",
			"class": "button primary",
		});
		u.f.init(form);
		u.k.addKey(form, "s", {
			"callback":"submit",
			"focused":true
		});
		if(media_type === "video") {
			form.inputs["file_poster[0]"].fileDeleted = function(file) {
				u.bug("file was delete", file);
				this._form.tag.virtual_input.val({
					"poster_format": "",
				});
			}
			form.inputs["file_poster[0]"].focus();
		}
		else {
			form.inputs["file_name"].focus();
		}
		form.submitted = function(iN) {
			var submitted_values = this.getData({"format":"object"});
			var existing_values = this.tag.virtual_input.val();
			u.ac(this, "submitting");
			this.tag.virtual_input.val(submitted_values);
			var form_data = this.getData();
			form_data.append("item_id", this.tag.field._item_id);
			form_data.append("file_variant", existing_values.variant);
			form_data.append("type", this.tag.type);
			this.response = function(response) {
				page.notify(response);
				u.rc(this, "submitting");
				if(response.cms_status && response.cms_status == "success") {
					this.tag.virtual_input.val({
						"poster": (response.cms_object.poster ? response.cms_object.poster : ""),
					});
					this.tag.field.update();
					this.tag.field._form.submit();
				}
				this.tag.overlay.close();
			}
			u.request(this, this.tag.field.file_update_metadata_action, {
				"method":"post",
				"data":form_data
			});
		}
	}
	field._media_changed = function(event) {
		var form_data = new FormData();
		form_data.append(this.name+"[0]", this.files[0], this.value);
		form_data.append("csrf-token", this.field._form.inputs["csrf-token"].val());
		form_data.append("item_id", this.field._item_id);
		form_data.append("type", this.tag.type);
		form_data.append("input_name", this.name);
		form_data.append("new_variant", "HTMLEDITOR-"+this.field.input.name+"-"+this.tag.type+"-"+u.randomString(8));
		this.response = function(response) {
			page.notify(response);
			if(response.cms_status && response.cms_status == "success" && response.cms_object.length === 1) {
				this.field.deleteFile(this.tag);
				this.tag.virtual_input.val({
					"url": "/download/"+response.cms_object[0].item_id+"/"+response.cms_object[0].variant+"/"+response.cms_object[0].name,
					"file_name": response.cms_object[0].name,
					"file_description": response.cms_object[0].description,
					"filesize": response.cms_object[0].filesize,
					"width": response.cms_object[0].width,
					"height": response.cms_object[0].height,
					"created_at": response.cms_object[0].created_at,
					"variant": response.cms_object[0].variant,
					"format": response.cms_object[0].format,
				});
				this.tag.field.update();
				this.tag.field._form.submit();
				this.tag.file_input.focus();
			}
		}
		u.request(this, this.field.file_add_action, {
			"method":"post", 
			"data":form_data
		});
	}
	field.addFileTag = function(type, node) {
		var tag = this.createTag(["file"], "file");
		tag.type = "file";
		tag.default_label = "Drop file here";
		tag.virtual_input = u.ae(tag, "div", {"class":"text"});
		tag.virtual_input.span_value = u.ae(tag.virtual_input, "span", {"class":"value", "html": tag.default_label});
		tag.virtual_input.tag = tag;
		tag.virtual_input.field = this;
		tag.virtual_input._form = this._form;
		tag.file_input = u.ae(tag.virtual_input, "input", {"type":"file", "name":"htmleditor_file"});
		tag.file_input.tag = tag;
		tag.file_input.field = this;
		tag.virtual_input.input_list = [
			"url", 
			"file_name", 
			"file_description", 
			"filesize", 
			"created_at",
			"variant",
			"format"
		];
		tag.virtual_input.values = {};
		tag.editMetadata = this.fileMetadata.bind(this);
		this.addEditMetadataButton(tag);
		tag.virtual_input.updateView = function() {
			if(this.values.file_name) {
				this.span_value.innerHTML = this.values.file_name;
				u.ac(this.tag, "done");
			}
			else {
				this.span_value.innerHTML = this.tag.default_label;
				u.rc(this.tag, "done");
			}
		}
		tag.virtual_input.val = function(value_object) {
			if(value_object !== undefined && obj(value_object)) {
				for(input in value_object) {
					if(this.input_list.includes(input)) {
						this.values[input] = value_object[input];
					}
				}
				this.updateView();
			}
			return {
				"url": this.values.url,
				"file_name": this.values.file_name, 
				"file_description": this.values.file_description,
				"filesize": this.values.filesize,
				"created_at": this.values.created_at,
				"variant": this.values.variant,
				"format": this.values.format,
			};
		}
		if(node) {
			if(node.className !== type) {
				var classname = node.className.replace(type, "").trim();
				tag.updateClassName(classname);
			}
			var meta_list = u.qs("ul.metadata", node);
			if(meta_list) {
				var meta_url = u.qs("li[itemprop=contentUrl]", meta_list);
				var meta_name = u.qs("li[itemprop=name]", meta_list);
				var meta_description = u.qs("li[itemprop=description]", meta_list);
				var meta_created_at = u.qs("li[itemprop=uploadDate]", meta_list);
				var meta_filesize = u.qs("li[itemprop=contentSize]", meta_list);
				var url = meta_url ? meta_url.innerHTML.trim() : "";
				var file_name = meta_name ? meta_name.innerHTML.trim() : "";
				var file_description = meta_description ? meta_description.innerHTML.trim() : "";
				var created_at = meta_created_at ? meta_created_at.innerHTML.trim() : u.date("Y-m-d H:i:s");
				var filesize = meta_filesize ? meta_filesize.innerHTML.trim() : "";
				var variant = meta_list ? (meta_list.getAttribute("data-variant") || "") : "";
				var format = meta_list ? (meta_list.getAttribute("data-format") || "") : "";
				tag.virtual_input.val({
					"url": url,
					"file_name": file_name,
					"file_description": file_description,
					"filesize": filesize,
					"created_at": created_at,
					"variant": variant,
					"format": format,
				});
			}
			else {
				var variant = u.cv(node, "variant");
				if(variant) {
					var data = new FormData();
					data.append("item_id", this._item_id);
					data.append("file_variant", variant);
					data.append("csrf-token", this._form.inputs["csrf-token"].val());
					tag.response = function(response) {
						if(response && response.cms_object) {
							tag.virtual_input.val({
								"url": "/download/"+response.cms_object.item_id+"/"+response.cms_object.variant+"/"+response.cms_object.name,
								"file_name": response.cms_object.name,
								"file_description": response.cms_object.description,
								"filesize": response.cms_object.filesize,
								"created_at": response.cms_object.created_at,
								"variant": response.cms_object.variant,
								"format": response.cms_object.format,
							});
						}
						else {
							tag.virtual_input.val({
								"url": false,
								"file_name": false,
								"file_description": false,
								"filesize": false,
								"created_at": false,
								"variant": false,
								"format": false,
							});
						}
					}
					u.request(tag, this.file_media_info_action, {
						"method": "post",
						"data": data,
					});
				}
			}
		}
		// 	
		// 	
		// 	
		u.e.addEvent(tag.file_input, "change", this._file_changed);
		// 	
		u.e.addEvent(tag.file_input, "focus", tag.field._focused_content);
		u.e.addEvent(tag.file_input, "blur", tag.field._blurred_content);
		if(u.e.event_support != "touch") {
			u.e.addEvent(tag.file_input, "dragenter", tag.field._focused_content);
			u.e.addEvent(tag.file_input, "dragleave", tag.field._blurred_content);
			u.e.addEvent(tag.file_input, "drop", tag.field._blurred_content);
		}
		if(u.e.event_pref == "mouse") {
			u.e.addEvent(tag.virtual_input, "mouseenter", u.f._mouseenter);
			u.e.addEvent(tag.virtual_input, "mouseleave", u.f._mouseleave);
		}
		this.update();
		this.editor.updateTargets();
		this.editor.updateDraggables();
		return tag;
	}
	field.fileMetadata = function(tag) {
		tag.overlay = this.tagMetadataOverlay(tag, "File");
		var values = tag.virtual_input.val();
		var form = u.f.addForm(tag.overlay.div_content);
		form.tag = tag;
		form.setAttribute("data-item_id", tag.field._item_id);
		u.f.addField(form, {
			"name": "csrf-token",
			"type": "hidden",
			"value": tag.field._form.inputs["csrf-token"].val(),
		});
		var fieldset = u.f.addFieldset(form);
		u.f.addField(fieldset, {
			"name": "file_name",
			"type": "string",
			"required": true,
			"label": "File name",
			"value": values.file_name,
			"hint_message": "This will be the name of the file when downloaded.",
			"error_message": "The file must have a name.",
		});
		u.f.addField(fieldset, {
			"name": "file_description",
			"type": "text",
			"label": "Description",
			"value": values.file_description,
			"hint_message": "Enter a meaningful description for the file. This will be provided to search engines for better indexing.",
		});
		u.f.addField(fieldset, {
			"name": "created_at",
			"type": "datetime",
			"label": "Created date and time",
			"value": (values.created_at || u.date("Y-m-d H:i:s")),
			"hint_message": "When was the file uploaded. This will be provided to search engines for better indexing.",
		});
		u.f.addAction(form, {
			"name": "update",
			"value": "Update",
			"class": "button primary",
		});
		u.f.init(form);
		u.k.addKey(form, "s", {
			"callback":"submit",
			"focused":true
		});
		form.inputs["file_name"].focus();
		form.submitted = function(iN) {
			var submitted_values = this.getData({"format":"object"});
			var existing_values = this.tag.virtual_input.val();
			u.ac(this, "submitting");
			this.tag.virtual_input.val(submitted_values);
			var form_data = this.getData();
			form_data.append("item_id", this.tag.field._item_id);
			form_data.append("file_variant", existing_values.variant);
			form_data.append("type", this.tag.type);
			this.response = function(response) {
				page.notify(response);
				u.rc(this, "submitting");
				if(response.cms_status && response.cms_status == "success") {
					this.tag.field.update();
					this.tag.field._form.submit();
				}
				this.tag.overlay.close();
			}
			u.request(this, this.tag.field.file_update_metadata_action, {
				"method":"post", 
				"data":form_data
			});
		}
	}
	field._file_changed = function(event) {
		var form_data = new FormData();
		form_data.append(this.name+"[0]", this.files[0], this.value);
		form_data.append("csrf-token", this.field._form.inputs["csrf-token"].val());
		form_data.append("item_id", this.field._item_id);
		form_data.append("type", this.tag.type);
		form_data.append("input_name", this.name);
		form_data.append("new_variant", "HTMLEDITOR-"+this.field.input.name+"-"+this.tag.type+"-"+u.randomString(8));
		this.response = function(response) {
			page.notify(response);
			if(response.cms_status && response.cms_status == "success" && response.cms_object.length === 1) {
				this.field.deleteFile(this.tag);
				this.tag.virtual_input.val({
					"url": "/download/"+response.cms_object[0].item_id+"/"+response.cms_object[0].variant+"/"+response.cms_object[0].name,
					"file_name": response.cms_object[0].name,
					"file_description": response.cms_object[0].description,
					"filesize": response.cms_object[0].filesize,
					"created_at": response.cms_object[0].created_at,
					"variant": response.cms_object[0].variant,
					"format": response.cms_object[0].format,
				});
				this.tag.field.update();
				this.tag.field._form.submit();
				this.tag.file_input.focus();
			}
		}
		u.request(this, this.field.file_add_action, {
			"method":"post", 
			"data":form_data
		});
	}
	field.addCodeTag = function(type, node) {
		var tag = this.createTag(this.code_allowed, type);
		tag.type = "code";
		tag.virtual_input = u.ae(tag, "div", {"class":"text", "contentEditable":true});
		tag.virtual_input.tag = tag;
		tag.virtual_input.field = this;
		tag.virtual_input._form = this._form;
		tag.virtual_input.val = function(value) {
			if(value !== undefined) {
				this.innerHTML = value;
			}
			return this.innerHTML.replace(/<br>/, "");
		}
		if(node) {
			if(node.className !== type) {
				var classname = node.className.replace(type, "").trim();
				tag.updateClassName(classname);
			}
			value = node.innerHTML;
			if(value) {
				tag.virtual_input.val(value);
				this.activateInlineFormatting(tag.virtual_input, tag);
			}
		}
		u.e.addEvent(tag.virtual_input, "keydown", this._changing_code);
		u.e.addEvent(tag.virtual_input, "keyup", this._code_changed);
		u.e.addStartEvent(tag.virtual_input, this._code_selection_started);
		u.e.addEvent(tag.virtual_input, "focus", this._focused_content);
		u.e.addEvent(tag.virtual_input, "blur", this._blurred_content);
		if(u.e.event_pref == "mouse") {
			u.e.addEvent(tag.virtual_input, "mouseenter", u.f._mouseenter);
			u.e.addEvent(tag.virtual_input, "mouseleave", u.f._mouseleave);
		}
		u.e.addEvent(tag.virtual_input, "blur", this._code_changed);
		u.e.addEvent(tag.virtual_input, "paste", this._pasted_content);
		tag.addNew = function() {
			var new_tag = this.field.addTextTag(this.field.text_allowed[0]);
			u.insertAfter(new_tag, this);
			new_tag.virtual_input.focus();
		}
		this.update();
		this.editor.updateTargets();
		this.editor.updateDraggables();
		return tag;
	}
	field._code_selection_started = function(event) {
		this._selection_event_id = u.e.addWindowEndEvent(this, this.field._code_changed);
	}
	field._changing_code = function(event) {
		if(event.keyCode == 13) {
			u.e.kill(event);
		}
		this.tab_started_in_tag = false;
		if(event.keyCode == 9 && event.shiftKey) {
			this.field.backwards_tab = true;
		}
		else if(event.keyCode == 9) {
			u.e.kill(event);
			this.tab_started_in_tag = true;
		}
	}
	field._code_changed = function(event) {
		if(this._selection_event_id) {
			u.e.removeWindowEndEvent(this._selection_event_id);
			delete this._selection_event_id;
		}
		var selection = window.getSelection(); 
		if(event.keyCode == 13) {
			u.e.kill(event);
			if(event.shiftKey) {
				this.tag.addNew();
			}
			else if(selection && selection.isCollapsed) {
				var newline = document.createTextNode("\n");
				range = selection.getRangeAt(0);
				range.insertNode(newline);
				selection.addRange(range);
				selection.collapseToEnd();
				if(selection.anchorNode === this) {
					while(this.lastChild.nodeType === 3 && !this.lastChild.textContent && this.lastChild.previousSibling && this.lastChild.previousSibling.nodeType === 3 && !this.lastChild.previousSibling.textContent) {
						this.removeChild(this.lastChild);
					}
				}
				if(selection.anchorNode === this && selection.anchorOffset === this.childNodes.length-1 && this.lastChild.nodeName !== "BR") {
					br = document.createElement("br");
					this.appendChild(br);
				}
			}
		}
		else if(event.keyCode == 9) {
			if(this.tab_started_in_tag) {
				u.e.kill(event);
				if(selection && selection.isCollapsed) {
					var tab = document.createTextNode("\t");
					range = selection.getRangeAt(0);
					range.insertNode(tab);
					selection.addRange(range);
					selection.collapseToEnd();
				}
			}
		}
		if(event.keyCode == 8) {
			if(this.is_deletable) {
				u.e.kill(event);
				this.field.deleteTag(this.tag);
			}
			else if(!this.val() || !this.val().replace(/<br>/, "")) {
				this.is_deletable = true;
			}
			else if(selection.anchorNode != this && (selection.anchorNode.nodeType === 1 && selection.anchorNode.innerHTML == "")) {
				selection.anchorNode.parentNode.removeChild(selection.anchorNode);
			}
		}
		else {
			this.is_deletable = false;
		}
		if(selection && !selection.isCollapsed && u.containsOrIs(this, selection.anchorNode)) {
			this.field.showSelectionOptions(this.tag);
		}
		else {
			this.field.hideSelectionOptions(this.tag);
		}
		this.field.update();
	}
	field.addButtonTag = function(type, node) {
		var tag = this.createTag(this.button_allowed, type);
		tag.type = "button";
		tag.default_label = "Click to add button properties";
		tag.field = this;
		tag.virtual_input = u.ae(tag, "div", {"class":"text", "tabindex": 0});
		tag.virtual_input.span_value = u.ae(tag.virtual_input, "span", {"class":"value", "html": tag.default_label});
		tag.virtual_input.tag = tag;
		tag.virtual_input.field = this;
		tag.virtual_input._form = this._form;
		tag.virtual_input.input_list = [
			"text", 
			"link", 
		];
		tag.virtual_input.values = {};
		tag.editMetadata = this.buttonMetadata.bind(this);
		this.addEditMetadataButton(tag);
		tag.virtual_input.updateView = function() {
			if(this.values.text) {
				this.span_value.innerHTML = this.values.text;
			}
			else {
				this.span_value.innerHTML = this.tag.default_label;
			}
			if(this.values.link) {
				this.span_value.title = this.values.link;
			}
			else {
				this.span_value.title = "";
			}
			if(this.values.text && this.values.link) {
				u.ac(this.tag, "done");
			}
			else {
				u.rc(this.tag, "done");
			}
		}
		tag.virtual_input.val = function(value_object) {
			if(value_object !== undefined && obj(value_object)) {
				for(input in value_object) {
					if(this.input_list.includes(input)) {
						this.values[input] = value_object[input];
					}
				}
				this.updateView();
			}
			return {
				"text": this.values.text ? this.values.text : "",
				"link": this.values.link ? this.values.link : "", 
				"target": this.values.target ? this.values.target : "", 
			};
		}
		if(node) {
			if(node.className !== type) {
				var classname = node.className.replace(type, "").trim();
				tag.updateClassName(classname);
			}
			var a = u.qs("a", node);
			if(a) {
				var link = a.getAttribute("href");
				var target = a.target;
				var text = a.innerHTML;
				tag.virtual_input.val({
					"text": text,
					"target": target,
					"link": link,
				});
			}
		}
		u.e.addEvent(tag.virtual_input, "keydown", this._changing_content);
		u.e.addEvent(tag.virtual_input, "keyup", this._button_changed);
		u.e.addEvent(tag.virtual_input, "focus", tag.field._focused_content);
		u.e.addEvent(tag.virtual_input, "blur", tag.field._blurred_content);
		if(u.e.event_pref == "mouse") {
			u.e.addEvent(tag.virtual_input, "mouseenter", u.f._mouseenter);
			u.e.addEvent(tag.virtual_input, "mouseleave", u.f._mouseleave);
		}
		this.update();
		this.editor.updateTargets();
		this.editor.updateDraggables();
		return tag;
	}
	field.buttonMetadata = function(tag) {
		tag.overlay = this.tagMetadataOverlay(tag, "Button", {
			"height": 410,
		});
		var values = tag.virtual_input.val();
		var form = u.f.addForm(tag.overlay.div_content);
		form.tag = tag;
		var fieldset = u.f.addFieldset(form);
		u.f.addField(fieldset, {
			"name": "text",
			"type": "string",
			"label": "Button text",
			"required": true,
			"value": values.text,
			"hint_message": "Enter the text of the button",
		});
		u.f.addField(fieldset, {
			"name": "link",
			"type": "string",
			"required": true,
			"pattern": "^((http[s]?:\/\/|mailto:|tel:)[^$]+|(\/|#)[^$]*)",
			"label": "Button link",
			"value": values.link,
			"hint_message": "Enter the link of the button",
			"error_message": "A button link must start with https://, http://, mailto:, tel:, / or # and be a valid link",
		});
		u.f.addField(fieldset, {
			"name": "target",
			"type": "checkbox",
			"label": "Open in new window?",
			"value": values.target,
			"hint_message": "Should this link open a new window/tab?",
		});
		u.f.addAction(form, {
			"name": "update",
			"value": "Update",
			"class": "button primary",
		});
		u.f.init(form);
		u.k.addKey(form, "s", {
			"callback":"submit",
			"focused":true
		});
		form.inputs["text"].focus();
		form.submitted = function(iN) {
			var submitted_values = this.getData({"format":"object"});
			var existing_values = this.tag.virtual_input.val();
			u.ac(this, "submitting");
			u.bug("submitted", submitted_values, existing_values);
			this.tag.virtual_input.val(submitted_values);
			this.tag.field.update();
			this.tag.field._form.submit();
			this.tag.overlay.close();
		}
	}
	field._button_changed = function(event) {
		if(event.keyCode == 13 || event.keyCode == 32) {
			u.e.kill(event);
			this.clicked();
		}
		else if(event.keyCode == 8) {
			if(this.is_deletable) {
				u.e.kill(event);
				this.field.deleteTag(this.tag);
			}
			else {
				this.is_deletable = true;
			}
		}
		else {
			this.is_deletable = false;
		}
		this.field.update();
	}
	field.addListTag = function(type, node) {
		var tag = this.createTag(this.list_allowed, type);
		tag.type = "li";
		tag.field = this;
		u.ae(tag, "div", {"class":"li", "html": "li"});
		tag.virtual_input = u.ae(tag, "div", {"class":"text", "contentEditable":true});
		tag.virtual_input.tag = tag;
		tag.virtual_input.field = this;
		tag.virtual_input._form = this._form;
		tag.virtual_input.val = function(value) {
			if(value !== undefined) {
				this.innerHTML = value;
			}
			return this.innerHTML;
		}
		if(node) {
			if(node.className !== type) {
				var classname = node.className.replace(type, "").trim();
				tag.updateClassName(classname);
			}
			value = node.innerHTML.trim().replace(/(<br>|<br \/>)$/, "").replace(/\n\r|\n|\r/g, "<br>");
			if(value) {
				tag.virtual_input.val(value);
				this.activateInlineFormatting(tag.virtual_input, tag);
			}
		}
		u.e.addEvent(tag.virtual_input, "keydown", this._changing_content);
		u.e.addEvent(tag.virtual_input, "keyup", this._changed_content);
		u.e.addStartEvent(tag.virtual_input, this._selection_started);
		u.e.addEvent(tag.virtual_input, "focus", this._focused_content);
		u.e.addEvent(tag.virtual_input, "blur", this._blurred_content);
		if(u.e.event_pref == "mouse") {
			u.e.addEvent(tag.virtual_input, "mouseenter", u.f._mouseenter);
			u.e.addEvent(tag.virtual_input, "mouseleave", u.f._mouseleave);
		}
		u.e.addEvent(tag.virtual_input, "paste", this._pasted_content);
		tag.addNew = function() {
			var new_tag = this.field.addListTag(this.field.text_allowed[0]);
			u.insertAfter(new_tag, this);
			new_tag.virtual_input.focus();
		}
		this.update();
		this.editor.updateTargets();
		this.editor.updateDraggables();
		return tag;
	}
	field.addTextTag = function(type, node) {
		var tag = this.createTag(this.text_allowed, type);
		tag.type = "text";
		tag.field = this;
		tag.virtual_input = u.ae(tag, "div", {"class":"text", "contentEditable":true});
		tag.virtual_input.tag = tag;
		tag.virtual_input.field = this;
		tag.virtual_input._form = this._form;
		tag.virtual_input.val = function(value) {
			if(value !== undefined) {
				this.innerHTML = value;
			}
			return this.innerHTML;
		}
		if(node) {
			if(node.className !== type) {
				var classname = node.className.replace(type, "").trim();
				tag.updateClassName(classname);
			}
			value = node.innerHTML.trim().replace(/(<br>|<br \/>)$/, "").replace(/\n\r|\n|\r/g, "<br>");
			if(value) {
				tag.virtual_input.val(value);
				this.activateInlineFormatting(tag.virtual_input, tag);
			}
		}
		u.e.addEvent(tag.virtual_input, "keydown", this._changing_content);
		u.e.addEvent(tag.virtual_input, "keyup", this._changed_content);
		u.e.addStartEvent(tag.virtual_input, this._selection_started);
		u.e.addEvent(tag.virtual_input, "focus", this._focused_content);
		u.e.addEvent(tag.virtual_input, "blur", this._blurred_content);
		u.e.addEvent(tag.virtual_input, "blur", this._changed_content);
		if(u.e.event_pref == "mouse") {
			u.e.addEvent(tag.virtual_input, "mouseenter", u.f._mouseenter);
			u.e.addEvent(tag.virtual_input, "mouseleave", u.f._mouseleave);
		}
		u.e.addEvent(tag.virtual_input, "paste", this._pasted_content);
		tag.addNew = function() {
			var new_tag = this.field.addTextTag(this.field.text_allowed[0]);
			u.insertAfter(new_tag, this);
			new_tag.virtual_input.focus();
		}
		this.update();
		this.editor.updateTargets();
		this.editor.updateDraggables();
		return tag;
	}
	field._selection_started = function(event) {
		this._selection_event_id = u.e.addWindowEndEvent(this, this.field._changed_content);
	}
	field._changing_content = function(event) {
		if(event.keyCode == 13) {
			u.e.kill(event);
		}
		else if(event.keyCode == 9 && event.shiftKey) {
			this.field.backwards_tab = true;
		}
	}
	field._changed_content = function(event) {
		if(this._selection_event_id) {
			u.e.removeWindowEndEvent(this._selection_event_id);
			delete this._selection_event_id;
		}
		var selection = window.getSelection();
		if(event.keyCode == 13) {
			u.e.kill(event);
			if(!event.shiftKey) {
				this.tag.addNew();
			}
			else {
				if(selection && selection.isCollapsed) {
					var range, br;
					range = selection.getRangeAt(0);
					br = document.createElement("br");
					range.insertNode(br);
					selection.addRange(range);
					selection.collapseToEnd();
					if(selection.anchorNode === this && selection.anchorOffset === this.childNodes.length-1 && this.lastChild.nodeName !== "BR") {
						br = document.createElement("br");
						this.appendChild(br);
					}
					else if(selection.anchorNode === this && selection.anchorOffset == this.childNodes.length && this.lastChild.nodeName === "BR" && this.lastChild.previousSibling.nodeName !== "BR") {
						br = document.createElement("br");
						this.appendChild(br);
					}
				}
			}
		}
		if(event.keyCode == 8) {
			if(this.is_deletable) {
				u.e.kill(event);
				this.field.deleteTag(this.tag);
			}
			else if(!this.val() || !this.val().replace(/<br>/, "")) {
				this.is_deletable = true;
			}
			else if(selection.anchorNode != this && (selection.anchorNode.nodeType === 1 && selection.anchorNode.innerHTML == "")) {
				selection.anchorNode.parentNode.removeChild(selection.anchorNode);
			}
		}
		else {
			this.is_deletable = false;
		}
		if(selection && !selection.isCollapsed && u.containsOrIs(this, selection.anchorNode)) {
			this.field.showSelectionOptions(this.tag);
		}
		else {
			this.field.hideSelectionOptions(this.tag);
		}
		this.field.update();
	}
	field._focused_content = function(event) {
		this.field.is_focused = true;
		u.ac(this.tag, "focus");
		u.ac(this.field, "focus");
		u.as(this.field, "zIndex", this.field._form._focus_z_index);
		u.f.positionHint(this.field);
		if(this.field.backwards_tab) {
			this.field.backwards_tab = false;
			var range = document.createRange();
			range.selectNodeContents(this);
			range.collapse(false);
			var selection = window.getSelection();
			selection.removeAllRanges();
			selection.addRange(range);
		}
	}
	field._blurred_content = function() {
		this.field.is_focused = false;
		u.rc(this.tag, "focus");
		u.rc(this.field, "focus");
		u.as(this.field, "zIndex", this.field._base_z_index);
		u.f.positionHint(this.field);
	}
	field._pasted_content = function(event) {
		u.e.kill(event);
		var i, node, text, range, new_tag, current_tag, selection, paste_parts, text_parts, text_nodes;
		var paste_content = event.clipboardData.getData("text/plain");
		if(paste_content !== "") {
			selection = window.getSelection();
			if(!selection.isCollapsed) {
				selection.deleteFromDocument();
			}
			if(u.hc(this.tag, "ul|ol")) {
				u.bug("must be handled – paste in list input");
			}
			if(u.hc(this.tag, "code")) {
				paste_parts = [paste_content];
			}
			else {
				paste_parts = paste_content.trim().split(/\n\r\n\r|\n\n|\r\r/g);
			}
			text_tags = [];
			for(i = 0; i < paste_parts.length; i++) {
				text_block = paste_parts[i].trim();
				if(text_block) {
					nodes = [];
					text_parts = text_block.split(/\n\r|\n|\r/g);
					for(j = 0; j < text_parts.length; j++) {
						text = text_parts[j];
						nodes.push(document.createTextNode(text));
						if(j < text_parts.length - 1) {
							nodes.push(document.createElement("br"));
						}
					}
					text_tags.push(nodes);
				}
			}
			current_tag = this.tag;
			for(i = 0; i < text_tags.length; i++) {
				nodes = text_tags[i];
				for(j = 0; j < nodes.length; j++) {
					node = nodes[j];
					selection = window.getSelection();
					range = selection.getRangeAt(0);
					range.insertNode(node);
					selection.addRange(range);
					selection.collapseToEnd();
				}
				if(i < text_tags.length - 1) {
					new_tag = this.field.addTextTag(this.field.text_allowed[0]);
					u.ia(new_tag, current_tag);
					current_tag = new_tag;
					current_tag.virtual_input.focus();
				}
			}
		}
	}
	field.findPreviousTag = function(tag) {
		var prev = false;
		if(u.hc(tag, this.list_allowed.join("|"))) {
			prev = u.ps(tag, {"exclude":".drag,.remove,.type"});
		}
		if(!prev) {
			prev = u.ps(tag);
			if(prev && u.hc(prev, this.list_allowed.join("|"))) {
				var items = u.qsa("div.li", prev);
				prev = items[items.length-1];
			}
			else if(prev && u.hc(prev, "file")) {
				if(!prev._variant) {
					prev = this.findPreviousTag(prev);
				}
			}
		}
		if(!prev) {
			prev = u.qs("div.tag", this);
			if(u.hc(prev, this.list_allowed.join("|"))) {
				prev = u.qs("div.li", prev);
			}
			else if(prev && u.hc(prev, "file")) {
				if(!prev._variant) {
					prev = this.findPreviousTag(prev);
				}
			}
		}
		return prev && prev != tag ? prev : false;
	}
	field.returnFocus = function(tag) {
		if(tag.type.match(/^(text|code|ext_video|li|button)$/)) {
			tag.virtual_input.blur();
			tag.virtual_input.focus();
		}
		else if(tag.type.match(/^(file|media)$/)) {
			tag.file_input.blur();
			tag.file_input.focus();
		}
	}
	field.updateInlineFormattingState = function() {
		if(!Object.keys(this.selection_options).length && !Object.keys(this.inline_options).length) {
			u.rc(this, "inlineformatting");
			this.inlineformatting = false;
		}
		else {
			u.ac(this, "inlineformatting");
			this.inlineformatting = true;
		}
	}
	field.showSelectionOptions = function(tag) {
		this.hideDeleteOrEditOptions();
		this.hideInlineOptions();
		if(!this.selection_options[tag]) {
			this.selection_options[tag] = u.ae(this.editor, "div", {"class":"selection_options"});
			this.editor.insertBefore(this.selection_options[tag], tag);
			this.updateInlineFormattingState();
			var ul = u.ae(this.selection_options[tag], "ul", {"class":"options"});
			var link = u.ae(ul, "li", {"class":"link", "html":"Link"});
			link.tag = tag;
			u.ce(link);
			link.inputStarted = function(event) {
				u.e.kill(event);
			}
			link.clicked = function(event) {
				u.e.kill(event);
				this.tag.field.addAnchorTag(this.tag);
			}
			var em = u.ae(ul, "li", {"class":"em", "html":"Italic"});
			em.tag = tag;
			u.ce(em);
			em.inputStarted = function(event) {
				u.e.kill(event);
			}
			em.clicked = function(event) {
				u.e.kill(event);
				this.tag.field.addEmTag(this.tag);
			}
			this.selection_options[tag].em = em;
			u.k.addKey(em, "i");
			var strong = u.ae(ul, "li", {"class":"strong", "html":"Bold"});
			strong.tag = tag;
			u.ce(strong);
			strong.inputStarted = function(event) {
				u.e.kill(event);
			}
			strong.clicked = function(event) {
				u.e.kill(event);
				this.tag.field.addStrongTag(this.tag);
			}
			this.selection_options[tag].strong = strong;
			u.k.addKey(strong, "b");
			var sup = u.ae(ul, "li", {"class":"sup", "html":"Superscript"});
			sup.tag = tag;
			u.ce(sup);
			sup.inputStarted = function(event) {
				u.e.kill(event);
			}
			sup.clicked = function(event) {
				u.e.kill(event);
				this.tag.field.addSupTag(this.tag);
			}
			var span = u.ae(ul, "li", {"class":"span", "html":"CSS class"});
			span.tag = tag;
			u.ce(span);
			span.inputStarted = function(event) {
				u.e.kill(event);
			}
			span.clicked = function(event) {
				u.e.kill(event);
				this.tag.field.addSpanTag(this.tag);
			}
		}
	}
	field.hideSelectionOptions = function(tag) {
		if(this.selection_options[tag]) {
			this.selection_options[tag].parentNode.removeChild(this.selection_options[tag]);
			u.k.removeKey(this.selection_options[tag].strong, "b");
			u.k.removeKey(this.selection_options[tag].em, "i");
			delete this.selection_options[tag];
		}
		this.updateInlineFormattingState();
		this.update();
	}
	field.hideInlineOptions = function(tag = false) {
		if(tag) {
			if(this.inline_options[tag]) {
				this.inline_options[tag].parentNode.removeChild(this.inline_options[tag]);
				if(this.inline_options[tag].inline_tag.temp_class) {
					this.removeEditingHighlight(this.inline_options[tag].inline_tag);
				}
				delete this.inline_options[tag];
			}
		}
		else {
			for(tag in this.inline_options) {
				this.hideInlineOptions(tag);
			}
		}
	}
	field.hideDeleteOrEditOptions = function(except_inline_tag = false) {
		var options = u.qsa(".delete_inline_tag, .edit_inline_tag");
		var i, option;
		for(i = 0; i < options.length; i++) {
			option = options[i];
			if(!except_inline_tag || option.inline_tag !== except_inline_tag) {
				option.inline_tag.out();
			}
		}
	}
	field.deleteOrEditOption = function(inline_tag) {
		inline_tag.over = function(event) {
			this.field.hideDeleteOrEditOptions(this);
			if(!this.is_editing && (this.nodeName.toLowerCase() == "a" || this.nodeName.toLowerCase() == "span")) {
				if(!this.bn_edit) {
					this.bn_edit = u.ae(document.body, "span", {"class":"edit_inline_tag", "html":"?"});
					this.bn_edit.inline_tag = this;
					this.bn_edit.over = function(event) {
						u.t.resetTimer(this.inline_tag.t_out);
					}
					u.e.addEvent(this.bn_edit, "mouseover", this.bn_edit.over);
					u.ce(this.bn_edit);
					this.bn_edit.clicked = function() {
						u.e.kill(event);
						this.inline_tag.field.hideInlineOptions();
						if(this.inline_tag.nodeName.toLowerCase() == "span") {
							this.inline_tag.field.spanOptions(this.inline_tag);
						}
						else if(this.inline_tag.nodeName.toLowerCase() == "a") {
							this.inline_tag.field.anchorOptions(this.inline_tag);
						}
						this.inline_tag.out();
					}
				}
				u.ass(this.bn_edit, {
					"top": (u.absY(this)-7)+"px",
					"left": (u.absX(this)-23)+"px",
				});
			}
			if(!this.bn_delete) {
				this.bn_delete = u.ae(document.body, "span", {"class":"delete_inline_tag", "html":"X"});
				this.bn_delete.inline_tag = this;
				this.bn_delete.over = function(event) {
					u.t.resetTimer(this.inline_tag.t_out);
				}
				u.e.addEvent(this.bn_delete, "mouseover", this.bn_delete.over);
				u.ce(this.bn_delete);
				this.bn_delete.clicked = function() {
					u.e.kill(event);
					this.inline_tag.field.hideInlineOptions();
					var fragment = document.createTextNode(this.inline_tag.innerHTML);
					this.inline_tag.parentNode.replaceChild(fragment, this.inline_tag);
					this.inline_tag.out();
					this.inline_tag.field.update();
				}
			}
			u.ass(this.bn_delete, {
				"top": (u.absY(this)-7)+"px",
				"left": (u.absX(this)-7)+"px",
			});
		}
		inline_tag.out = function(event) {
			if(this.bn_edit) {
				document.body.removeChild(this.bn_edit);
				delete this.bn_edit;
			}
			if(this.bn_delete) {
				document.body.removeChild(this.bn_delete);
				delete this.bn_delete;
			}
		}
		u.e.hover(inline_tag, {"delay":500});
	}
	field.activateInlineFormatting = function(input, tag) {
		var i, inline_tag;
		var inline_tags = u.qsa("a,strong,em,span,sup", input);
		for(i = 0; i < inline_tags.length; i++) {
			inline_tag = inline_tags[i];
			inline_tag.field = input.field;
			inline_tag.tag = tag;
			if(!u.text(inline_tag)) {
				inline_tag.parentNode.removeChild(inline_tag);
			}
			else {
				this.deleteOrEditOption(inline_tag);
			}
		}
	}
	field.addAnchorTag = function(tag) {
		var selection = window.getSelection();
		if(u.containsOrIs(tag, selection.anchorNode)) {
			var a = document.createElement("a");
			a.field = this;
			a.tag = tag;
			range = selection.getRangeAt(0);
			try {
				range.surroundContents(a);
				selection.collapseToEnd();
				this.deleteOrEditOption(a);
				this.hideSelectionOptions(tag);
				this.anchorOptions(a);
				this.update();
			}
			catch(exception) {
				selection.removeAllRanges();
				this.hideSelectionOptions(tag);
				u.bug("exception", exception)
				alert("You cannot cross the boundaries of another selection. Yet.");
			}
		}
	}
	field.anchorOptions = function(a) {
		u.ac(this, "inlineformatting");
		this.inlineformatting = false;
		this.inline_options[a.tag] = u.ae(a.tag.field.editor, "div", {"class":"inline_options a"});
		a.tag.field.editor.insertBefore(this.inline_options[a.tag], a.tag);
		this.inline_options[a.tag].inline_tag = a;
		this.addEditingHighlight(a);
		var form = u.f.addForm(this.inline_options[a.tag], {"class":"labelstyle:inject"});
		form.a = a;
		var fieldset = u.f.addFieldset(form);
		var input_url = u.f.addField(fieldset, {
			"label":"url", 
			"name":"url",
			"required": true,
			// "value":a.href.replace(location.protocol + "//" + document.domain, ""),
			"value":a.getAttribute("href"), 
			"pattern":"^((http[s]?:\/\/|mailto:|tel:)[^$]+|(\/|#)[^$]*)",
			"error_message":"Must start with /, http:// or https://, mailto:, tel: or #"
		});
		var input_target = u.f.addField(fieldset, {
			"type":"checkbox", 
			"label":"Open in new window?", 
			"checked":(a.target ? "checked" : false), 
			"name":"target", 
			"error_message":""
		});
		var input_rel = u.f.addField(fieldset, {
			"type":"checkbox",
			"label":"No follow link?", 
			"checked": (a.rel ? "checked" : false),
			"name":"rel", 
			"error_message":""
		});
		var bn_save = u.f.addAction(form, {
			"value":"Save link", 
			"class":"button"
		});
		u.f.init(form);
		form.inputs["url"].focus();
		form.submitted = function() {
			if(this.inputs["url"].val()) {
				this.a.href = this.inputs["url"].val();
			}
			else {
				this.a.removeAttribute("href");
			}
			if(this.inputs["rel"].val()) {
				this.a.setAttribute("rel", this.inputs["rel"].val());
			}
			else {
				this.a.removeAttribute("rel");
			}
			if(this.inputs["target"].val()) {
				this.a.target = "_blank";
			}
			else {
				this.a.removeAttribute("target");
			}
			this.a.tag.field.hideInlineOptions(this.a.tag);
			this.a.tag.field.update();
		}
	}
	field.addStrongTag = function(tag) {
		var selection = window.getSelection();
		if(u.containsOrIs(tag, selection.anchorNode)) {
			var strong = document.createElement("strong");
			strong.field = this;
			strong.tag = tag;
			var range = selection.getRangeAt(0);
			try {
				range.surroundContents(strong);
				selection.collapseToEnd();
				this.deleteOrEditOption(strong);
				this.hideSelectionOptions(tag);
				this.update();
			}
			catch(exception) {
				selection.removeAllRanges();
				this.hideSelectionOptions(tag);
				alert("You cannot cross the boundaries of another selection. Yet.");
			}
		}
	}
	field.addEmTag = function(tag) {
		var selection = window.getSelection();
		if(u.containsOrIs(tag, selection.anchorNode)) {
			var em = document.createElement("em");
			em.field = this;
			em.tag = tag;
			var range = selection.getRangeAt(0);
			try {
				range.surroundContents(em);
				selection.collapseToEnd();
				this.deleteOrEditOption(em);
				this.hideSelectionOptions(tag);
				this.update();
			}
			catch(exception) {
				selection.removeAllRanges();
				this.hideSelectionOptions(tag);
				alert("You cannot cross the boundaries of another selection. Yet.");
			}
		}
	}
	field.addSupTag = function(tag) {
		var selection = window.getSelection();
		if(u.containsOrIs(tag, selection.anchorNode)) {
			var sup = document.createElement("sup");
			sup.field = this;
			sup.tag = tag;
			var range = selection.getRangeAt(0);
			try {
				range.surroundContents(sup);
				selection.collapseToEnd();
				this.deleteOrEditOption(sup);
				this.hideSelectionOptions(tag);
				this.update();
			}
			catch(exception) {
				selection.removeAllRanges();
				this.hideSelectionOptions(tag);
				alert("You cannot cross the boundaries of another selection. Yet.");
			}
		}
	}
	field.addSpanTag = function(tag) {
		var selection = window.getSelection();
		if(u.containsOrIs(tag, selection.anchorNode)) {
			var span = document.createElement("span");
			span.field = this;
			span.tag = tag;
			var range = selection.getRangeAt(0);
			try {
				range.surroundContents(span);
				selection.collapseToEnd();
				this.deleteOrEditOption(span);
				this.hideSelectionOptions(tag);
				this.spanOptions(span);
				this.update();
			}
			catch(exception) {
				selection.removeAllRanges();
				this.hideSelectionOptions(tag);
				alert("You cannot cross the boundaries of another selection. Yet.");
			}
		}
	}
	field.spanOptions = function(span) {
		u.ac(this, "inlineformatting");
		this.inlineformatting = false;
		this.inline_options[span.tag] = u.ae(span.tag.field.editor, "div", {"class":"inline_options span"});
		span.tag.field.editor.insertBefore(this.inline_options[span.tag], span.tag);
		this.inline_options[span.tag].inline_tag = span;
		this.addEditingHighlight(span);
		var form = u.f.addForm(this.inline_options[span.tag], {"class":"labelstyle:inject"});
		form.span = span;
		var fieldset = u.f.addFieldset(form);
		var input_classname = u.f.addField(fieldset, {"label":"CSS class", "name":"classname", "value":span.className.replace(span.temp_class, "").replace(/  /, " ").trim(), "error_message":""});
		var bn_save = u.f.addAction(form, {"value":"Save class", "class":"button"});
		u.f.init(form);
		form.inputs["classname"].focus();
		form.submitted = function() {
			if(this.inputs["classname"].val()) {
				this.span.className = this.inputs["classname"].val();
			}
			else {
				this.span.removeAttribute("class");
			}
			this.span.tag.field.hideInlineOptions(this.span.tag);
			this.span.tag.field.update();
		}
	}
	field.addEditingHighlight = function(inline_tag) {
		inline_tag.temp_class = u.randomString(4);
		inline_tag.is_editing = true;
		if(!this.style_tag) {
			this.style_tag = document.createElement("style");
			this.style_tag.setAttribute("media", "all")
			this.style_tag.setAttribute("type", "text/css")
			u.ae(document.head, this.style_tag);
		}
		this.style_tag.sheet.insertRule("."+inline_tag.temp_class+"{}", 0);
		this.style_tag.rule = this.style_tag.sheet.cssRules[0]
		this.style_tag.rule.style.setProperty("background-color", "#5c5c5c" , "important");
		this.style_tag.rule.style.setProperty("color", "#ffffff" , "important");
		u.ac(inline_tag, inline_tag.temp_class);
	}
	field.removeEditingHighlight = function(inline_tag) {
		this.style_tag.sheet.deleteRule(0);
		inline_tag.is_editing = false;
		u.rc(inline_tag, inline_tag.temp_class);
		if(!inline_tag.className) {
			inline_tag.removeAttribute("class");
		}
		delete inline_tag.temp_class;
	}
	field.viewer.innerHTML = field.input.value;
	u.sortable(field.editor, {"draggables":"div.tag", "targets":"div.editor"});
	var value, node, i, tag, j, p, lis, li;
	field.initial_indexing = true;
	var nodes = u.cn(field.viewer, {"exclude":"br"});
	if(nodes.length) {
		for(i = 0; i < field.viewer.childNodes.length; i++) {
			node = field.viewer.childNodes[i];
			if(node.nodeName == "#text") {
				if(node.nodeValue.trim()) {
					var fragments = node.nodeValue.trim().split(/\n\r\n\r|\n\n|\r\r/g);
					if(fragments) {
						for(index in fragments) {
							value = fragments[index].replace(/\n\r|\n|\r/g, "<br>");
							p = document.createElement("p");
							p.innerHTML = value;
							field.addTextTag("p", p);
						}
					}
					else {
						value = node.nodeValue; 
						p = document.createElement("p");
						p.innerHTML = value;
						field.addTextTag("p", p);
					}
				}
			}
			else if(field.text_allowed && node.nodeName.toLowerCase().match(field.text_allowed.join("|"))) {
				field.addTextTag(node.nodeName.toLowerCase(), node);
			}
			else if(node.nodeName.toLowerCase() == "code") {
				field.addCodeTag(node.nodeName.toLowerCase(), node);
			}
			else if(field.button_allowed.length && node.nodeName.toLowerCase() === "ul" && node.className === "actions") {
				var lis = u.qsa("li", node);
				for(j = 0; j < lis.length; j++) {
					li = lis[j];
					field.addButtonTag(node.nodeName.toLowerCase(), li);
				}
			}
			else if(field.list_allowed.length && node.nodeName.toLowerCase().match(field.list_allowed.join("|"))) {
				var lis = u.qsa("li", node);
				for(j = 0; j < lis.length; j++) {
					li = lis[j];
					field.addListTag(node.nodeName.toLowerCase(), li);
				}
			}
			else if(field.ext_video_allowed && u.hc(node, field.ext_video_allowed.join("|"))) {
				field.addExternalVideoTag(node.className.match(field.ext_video_allowed.join("|"))[0], node);
			}
			else if(u.hc(node, "file")) {
 				field.addFileTag("file", node);
			}
			else if(u.hc(node, "media")) {
				field.addMediaTag("media", node);
			}
			else if(node.nodeName.toLowerCase().match(/dl|ul|ol/)) {
				u.bug("found denied list node", node);
				var children = u.cn(node);
				for(j = 0; j < children.length; j++) {
					child = children[j];
					value = child.innerHTML.replace(/\n\r|\n|\r/g, "");
					p = document.createElement("p");
					p.innerHTML = value;
					field.addTextTag("p", p);
					u.bug("convert content to p", p);
				}
			}
			else if(node.nodeName.toLowerCase().match(/h1|h2|h3|h4|h5|code/)) {
				value = node.innerHTML.replace(/\n\r|\n|\r/g, "");
				p = document.createElement("p");
				p.innerHTML = value;
				field.addTextTag("p", p);
				u.bug("convert content to p", p);
			}
			else {
				alert("HTML contains unautorized node:" + node.nodeName + "\nIt has been altered to conform with SEO and design.");
			}
		}
	}
	else {
		u.bug("single unformatted textnode", field.viewer.innerHTML);
		var p = document.createElement("p");
		p.innerHTML = field.viewer.innerHTML.replace(/\<br[\/]?\>/g, "\n");
		field.addTextTag(field.text_allowed[0], p);
	}
	field.editor.updateTargets();
	field.editor.updateDraggables();
	field.editor.detectSortableLayout();
	field.updateContent();
	field.addViewHTMLButton();
	field.addHelpButton();
}


/*u-form-field-location.js*/
Util.Form.customInit["location"] = function(field) {
	field.type = "location";
	field.inputs = u.qsa("input", field);
	field.input = field.inputs[0];
	for(j = 0; j < field.inputs.length; j++) {
		input = field.inputs[j];
		input._form = field._form;
		input.label = u.qs("label[for='"+input.id+"']", field);
		input.field = field;
		input.val = u.f._value;
		u.e.addEvent(input, "keyup", u.f._updated);
		u.e.addEvent(input, "change", u.f._changed);
		u.f.inputOnEnter(input);
		u.f.activateInput(input);
	}
	if(navigator.geolocation) {
		u.f.location(field);
	}
}
Util.Form.customValidate["location"] = function(iN) {
	var loc_fields = 0;
	if(iN.field.input) {
		loc_fields++;
		min = 1;
		max = 255;
		if(
			iN.field.input.val().length >= min &&
			iN.field.input.val().length <= max
		) {
			u.f.inputIsCorrect(iN.field.input);
		}
		else {
			u.f.inputHasError(iN.field.input);
		}
	}
	if(iN.field.lat_input) {
		loc_fields++;
		min = -90;
		max = 90;
		if(
			!isNaN(iN.field.lat_input.val()) && 
			iN.field.lat_input.val() >= min && 
			iN.field.lat_input.val() <= max
		) {
			u.f.inputIsCorrect(iN.field.lat_input);
		}
		else {
			u.f.inputHasError(iN.field.lat_input);
		}
	}
	if(iN.field.lon_input) {
		loc_fields++;
		min = -180;
		max = 180;
		if(
			!isNaN(iN.field.lon_input.val()) && 
			iN.field.lon_input.val() >= min && 
			iN.field.lon_input.val() <= max
		) {
			u.f.inputIsCorrect(iN.field.lon_input);
		}
		else {
			u.f.inputHasError(iN.field.lon_input);
		}
	}
	if(u.qsa("input.error", iN.field).length) {
		u.rc(iN.field, "correct");
		u.ac(iN.field, "error");
	}
	else if(u.qsa("input.correct", iN.field).length == loc_fields) {
		u.ac(iN.field, "correct");
		u.rc(iN.field, "error");
	}
}
Util.Form.location = function(field) {
	u.ac(field, "geolocation");
	field.lat_input = u.qs("div.latitude input", field);
	field.lat_input.autocomplete = "off";
	field.lat_input.field = field;
	field.lon_input = u.qs("div.longitude input", field);
	field.lon_input.autocomplete = "off";
	field.lon_input.field = field;
	field.showMap = function() {
		if(u.gapi_key) {
			if(!this.iframe_maps) {
				var lat = this.lat_input.val() !== "" ? this.lat_input.val() : 0;
				var lon = this.lon_input.val() !== "" ? this.lon_input.val() : 0;
				var maps_url = "https://maps.googleapis.com/maps/api/js?key="+u.gapi_key+"&libraries=marker&callback=initMap&loading=async";
				var html = '<!DOCTYPE html><html><head>';
				html += '</head><body><div id="map"></div><div id="close"></div>';
				html += '<style type="text/css">body {margin: 0;} #map {width: 100%; height: 300px;} #close {width: 25px; height: 25px; position: absolute; top: 0; left: 0; background: #ffffff; z-index: 10; border-bottom-right-radius: 10px; cursor: pointer;} gmp-advanced-marker {outline: none;}</style>';
				html += '<script type="text/javascript">';
				html += 'var map, marker;';
				html += 'var initMap = function() {';
				html += '	window._map_loaded = true;';
				html += '	var close = document.getElementById("close");';
				html += '	close.onclick = function() {field.hideMap();};';
				html += '	var mapOptions = {center: new google.maps.LatLng('+lat+', '+lon+'),zoom: 15, streetViewControl: false, zoomControlOptions: {position: google.maps.ControlPosition.LEFT_CENTER}, mapId:"map"};';
				html += '	map = new google.maps.Map(document.getElementById("map"), mapOptions);';
				html += '	document.getElementById("map").addEventListener("mousedown", function(){clearTimeout(document.field.t_hide_map)});';
				html += '	marker = new google.maps.marker.AdvancedMarkerElement({position: new google.maps.LatLng('+lat+', '+lon+'), map:map, gmpDraggable: true});';
				html += '	marker.field = document.field;';
				html += '	marker.dragend = function(event_type) {';
				html += '		var lat_marker = Math.round(marker.position.lat*100000)/100000;';
				html += '		var lon_marker = Math.round(marker.position.lng*100000)/100000;';
				html += '		this.field.lon_input.val(lon_marker);';
				html += '		this.field.lat_input.val(lat_marker);';
				html += '	};';
				html += '	marker.addListener("dragend", marker.dragend);';
				html += '};';
				html += 'var centerMap = function(lat, lon) {';
				html += '	var loc = new google.maps.LatLng(lat, lon);';
				html += '	map.setCenter(loc);';
				html += '	marker.position = loc;';
				html += '};';
				html += '</script>';
				html += '<script type="text/javascript" src="'+maps_url+'" async></script>';
				html += '</body></html>';
				this.iframe_maps = u.ae(this, "iframe", {"class": "geolocationmap"});
				this.iframe_maps.doc = this.iframe_maps.contentDocument ? this.iframe_maps.contentDocument : this.iframe_maps.contentWindow.document;
				this.iframe_maps.doc.field = this;
				this.iframe_maps.doc.open();
				this.iframe_maps.doc.write(html);
				this.iframe_maps.doc.close();
				u.e.addEvent(this.iframe_maps.doc, "focus", function() {u.t.resetTimer(this.field.t_hide_map)});
				u.e.addEvent(this.iframe_maps.doc, "blur", function() {this.field.startHideTimer();});
			}
		}
		else {
			u.bug("Attempted to include map, but u.gapi_key is missing");
		}
	}
	field.updateMap = function() {
		if(this.iframe_maps && this.iframe_maps.contentWindow && this.iframe_maps.contentWindow._map_loaded) {
			this.iframe_maps.contentWindow.centerMap(this.lat_input.val(), this.lon_input.val());
		}
	}
	field.moveMap = function(event) {
		var factor;
		if(this._move_direction) {
			if(event && event.shiftKey) {
				factor = 0.001;
			}
			else {
				factor = 0.0001;
			}
			if(this._move_direction == "38") {
				this.lat_input.val(u.round(parseFloat(this.lat_input.val())+factor, 6));
			}
			else if(this._move_direction == "40") {
				this.lat_input.val(u.round(parseFloat(this.lat_input.val())-factor, 6));
			}
			else if(this._move_direction == "39") {
				this.lon_input.val(u.round(parseFloat(this.lon_input.val())+factor, 6));
			}
			else if(this._move_direction == "37") {
				this.lon_input.val(u.round(parseFloat(this.lon_input.val())-factor, 6));
			}
			this.updateMap();
		}
	}
	field.hideMap = function() {
		u.t.resetTimer(this.t_hide_map);
		if(this.iframe_maps) {
			this.removeChild(this.iframe_maps);
			delete this.iframe_maps;
		}
	}
	field._end_move_map = function(event) {
		this.field._move_direction = false;
	}
	field._start_move_map = function(event) {
		if(event.keyCode.toString().match(/37|38|39|40/)) {
			this.field._move_direction = event.keyCode;
			this.field.moveMap(event);
		}
	}
	u.e.addEvent(field.lat_input, "keydown", field._start_move_map);
	u.e.addEvent(field.lon_input, "keydown", field._start_move_map);
	u.e.addEvent(field.lat_input, "keyup", field._end_move_map);
	u.e.addEvent(field.lon_input, "keyup", field._end_move_map);
	field.lat_input.updated = field.lon_input.updated = function() {
		this.field.updateMap();
	}
	field.lat_input.focused = field.lon_input.focused = function() {
		u.t.resetTimer(this.field.t_hide_map);
		this.field.showMap();
	}
	field.lat_input.blurred = field.lon_input.blurred = function() {
		this.field.startHideTimer();
	}
	field.startHideTimer = function() {
		this.t_hide_map = u.t.setTimer(this, this.hideMap, 800);
	}
	field.bn_geolocation = u.ae(field, "div", {"class":"geolocation", "title":"Select current location"});
	field.bn_geolocation.field = field;
	u.ce(field.bn_geolocation);
	field.bn_geolocation.clicked = function() {
		this.transitioned = function() {
			var new_scale;
			if(this._scale == 1.4) {
				new_scale = 1;
			}
			else {
				new_scale = 1.4;
			}
			u.a.scale(this, new_scale);
		}
		this.transitioned();
		window._locationField = this.field;
		window._foundLocation = function(position) {
			var lat = position.coords.latitude;
			var lon = position.coords.longitude;
			window._locationField.lat_input.val(u.round(lat, 6));
			window._locationField.lon_input.val(u.round(lon, 6));
			window._locationField.lat_input.focus();
			window._locationField.lon_input.focus();
			u.a.transition(window._locationField.bn_geolocation, "none");
			u.a.scale(window._locationField.bn_geolocation, 1);
			window._locationField.showMap();
			window._locationField.updateMap();
		}
		window._noLocation = function() {
			window._locationField.lat_input.val(55.676098);
			window._locationField.lon_input.val(12.568337);
			window._locationField.lat_input.focus();
			window._locationField.lon_input.focus();
			u.a.transition(window._locationField.bn_geolocation, "none");
			u.a.scale(window._locationField.bn_geolocation, 1);
			window._locationField.showMap();
			window._locationField.updateMap();
		}
		navigator.geolocation.getCurrentPosition(window._foundLocation, window._noLocation);
	}
}


/*u-form-field-dropdown.js*/
Util.Form.customInit["dropdown"] = function(field) {
	field.type = "dropdown";
	field.input = u.qs("select", field);
	field.input._form = field._form;
	field.input.label = u.qs("label[for='"+field.input.id+"']", field);
	field.input.field = field;
	field._value_select = function(value) {
		if(value !== undefined) {
			var i, option;
			for(i = 0; i < this.options.length; i++) {
				option = this.options[i];
				if(option.value == value) {
					this.selectedIndex = i;
					u.f.validate(this);
					this.field.virtual_input.val(option.text);
					return i;
				}
			}
			if(value) {
				if(!this.field.new_option) {
					this.field.new_option = document.createElement("option");
					this.add(this.field.new_option);
				}
				this.field.new_option.value = value;
				this.field.new_option.text = value;
				this.selectedIndex = this.options.length-1;
				u.f.validate(this);
				this.field.virtual_input.val(value);
				return this.selectedIndex;
			}
			if(value === "") {
				this.selectedIndex = -1;
				u.f.validate(this);
				this.field.virtual_input.val("");
				return -1;
			}
			return false;
		}
		else {
			return (this.selectedIndex >= 0 && this.default_value != this.options[this.selectedIndex].value) ? this.options[this.selectedIndex].value : "";
		}
	}
	field.input.val = field._value_select;
	u.e.addEvent(field.input, "change", u.f._changed);
	u.e.addEvent(field.input, "update", u.f._updated);
	var virtual_input_wrapper = u.ae(field, "div", {"class": "virtual"});
	field.virtual_input = u.ae(virtual_input_wrapper, "div", {"class": "input", "contentEditable": "true"});
	field.insertBefore(virtual_input_wrapper, field.input);
	field.virtual_input._form = field._form;
	field.virtual_input.field = field;
	field._value_virtual = function(value) {
		if(value !== undefined) {
			if(!this.is_focused) {
				this.innerHTML = value;
			}
			this.field.selected_option = false;
			u.rc(this, "selection");
			var i, option;
			for(i = 0; i < this.field.dropdown_options.nodes.length; i++) {
				option = this.field.dropdown_options.nodes[i];
				if(option.option_text == value) {
					u.ac(this, "selection");
					this.field.selected_option = option;
					return;
				}
			}
		}
		else {
			return u.text(this);
		}
	}
	field.virtual_input.val = field._value_virtual;
	field.virtual_input.clicked = function(event) {
		if(!this.is_focused) {
			this.field.activateSearchCursor();
		}
	}
	u.e.click(field.virtual_input);
	field.virtual_input.preKeyEvent = function (event) {
		if(event.keyCode == 27) {
			u.e.kill(event);
			this.field._show_all = false;
			this.field.activateSearchCursor();
		}
		else if(event.keyCode == 13) {
			u.e.kill(event);
			if(this.field.highlighted_option) {
				this.field.selectOption(this.field.highlighted_option);
			}
		}
		else if(event.shiftKey && event.keyCode == 9) {
			this._blur({"target": document.body});
		}
		else if(event.keyCode == 9) {
			if(this.field.highlighted_option) {
				u.e.kill(event);
				this.field.selectOption(this.field.highlighted_option);
			}
			else {
				this._blur({"target": document.body});
			}
		}
		else if(event.keyCode == 32) {
			if(this.field.highlighted_option) {
				u.e.kill(event);
				this.field.selectOption(this.field.highlighted_option);
			}
		}
		else if(event.keyCode == 38 && !event.shiftKey) {
			u.e.kill(event);
			this.field.highlightPreviousOption();
		}
		else if(event.keyCode == 40 && !event.shiftKey) {
			u.e.kill(event);
			if(this.field.available_options) {
				this.field.highlightNextOption();
			}
			else {
				this.field._show_all = true;
			}
		}
	}
	field.virtual_input.postKeyEvent = function(event) {
		var value = this.field.virtual_input.val();
		if(!value) {
			this.field._show_all = true;
		}
		else if(event.keyCode == 8 || event.keyCode == 32 || event.keyCode >= 65) {
			this.field._show_all = false;
		}
		if(!this.field.selected_option || this.field.selected_option.option_text != value) {
			this.field.selected_option = false;
			var i, option;
			for(i = 0; i < this.field.dropdown_options.nodes.length; i++) {
				option = this.field.dropdown_options.nodes[i];
				if(option.option_text.toLowerCase() == value.toLowerCase()) {
					this.field.selected_option = option;
					break;
				}
			}
			this.field.updateDropdownValue();
		}
		u.t.resetTimer(this.field.t_search);
		this.field.t_search = u.t.setTimer(this.field, this.field.searchOptions, 200);
	}
	u.e.addEvent(field.virtual_input, "keyup", field.virtual_input.postKeyEvent);
	u.e.addEvent(field.virtual_input, "keydown", field.virtual_input.preKeyEvent);
	// 
	// 
	field.virtual_input._focus = function(event) {
		if(!this.is_focused) {
			this.field.blur_event_id = u.e.addWindowStartEvent(this, this._blur);
		}
		this.field.is_focused = true;
		this.field.input.is_focused = true;
		this.is_focused = true;
		u.ac(this.field, "focus");
		u.ac(this, "focus");
		u.ac(this.field.input, "focus");
		u.as(this.field, "zIndex", this._form._focus_z_index);
		u.f.positionHint(this.field);
		this.field.activateSearchCursor();
		if(typeof(this.focused) == "function") {
			this.focused();
		}
		else if(this.field.input && typeof(this.field.input.focused) == "function") {
			this.field.input.focused(this);
		}
		if(typeof(this._form.focused) == "function") {
			this._form.focused(this);
		}
	}
	field.virtual_input._blur = function(event) {
		if(!u.contains(this.field, event.target)) {
			u.e.removeWindowStartEvent(this, this.field.blur_event_id);
			this.field.is_focused = false;
			this.field.input.is_focused = false;
			this.is_focused = false;
			u.rc(this.field, "focus");
			u.rc(this, "focus");
			u.rc(this.field.input, "focus");
			u.as(this.field, "zIndex", this.field._base_z_index);
			this.field.input._used = true;
			this.field._show_all = false;
			this.field.updateDropdownValue();
			this.field.hideOptions();
			u.f.validate(this.field.input);
			if(typeof(this.blurred) == "function") {
				this.blurred();
			}
			else if(this.field.input && typeof(this.field.input.blurred) == "function") {
				this.field.input.blurred(this);
			}
			if(typeof(this._form.blurred) == "function") {
				this._form.blurred(this);
			}
		}
	}
	u.e.addEvent(field.virtual_input, "focus", field.virtual_input._focus);
	if(u.e.event_support != "touch") {
		u.e.addEvent(field.virtual_input, "mouseenter", u.f._mouseenter.bind(field.input));
		u.e.addEvent(field.virtual_input, "mouseleave", u.f._mouseleave.bind(field.input));
	}
	field.bn_dropdown = u.ae(virtual_input_wrapper, "div", {"class": "button"});
	field.bn_dropdown.arrow = u.svg({
		"name":"arrow",
		"node":field.bn_dropdown,
		"class":"arrow",
		"width":30,
		"height":30,
		"viewBox": "0 0 30 30",
		"shapes":[
			{
				"type": "line",
				"x1": 8,
				"y1": 12,
				"x2": 15,
				"y2": 19
			},
			{
				"type": "line",
				"x1": 22,
				"y1": 12,
				"x2": 15,
				"y2": 19
			}
		]
	});
	field.bn_dropdown.field = field;
	u.ce(field.bn_dropdown);
	field.bn_dropdown.clicked = function() {
		if(this.field.is_expanded) {
			this.field._show_all = false;
			this.field.hideOptions();
		}
		else {
			this.field._show_all = true;
			this.field.searchOptions();
		}
		this.field.virtual_input.focus();
	}
	field.bn_dropdown.keyEvent = function(event) {
		if (event.keyCode == 13 || event.keyCode == 32 || event.keyCode == 40) {
			u.e.kill(event);
			this.clicked();
		}
	}
	u.e.addEvent(field.bn_dropdown, "keydown", field.bn_dropdown.keyEvent);
	field.dropdown_options = u.ae(virtual_input_wrapper, "div", {"class": "options"});
	field.dropdown_options_list = u.ae(field.dropdown_options, "ul", {"class": "options"});
	field.dropdown_options.field = field;
	field.activateSearchCursor = function() {
		this.virtual_input.focus();
		var range = document.createRange();
		range.selectNodeContents(this.virtual_input);
		if(this.input.val()) {
			range.collapse(false);
		}
		var selection = window.getSelection();
		selection.removeAllRanges();
		selection.addRange(range);
	}
	field.showOptions = function() {
		u.ass(this.dropdown_options, {
			transition: "all 0.2s ease-in-out",
			height: this.dropdown_options_list.offsetHeight + "px"
		});
		u.ac(this, "open");
		this.is_expanded = true;
	}
	field.hideOptions = function() {
		if(this.is_expanded) {
			this.dropdown_options.transitioned = function() {
				if(fun(this.field.optionsHidden)) {
					this.field.optionsHidden();
				}
			}
			u.ass(this.dropdown_options, {
				transition: "all 0.1s ease-in-out",
				height: "0px"
			});
			u.rc(this, "open");
			this.is_expanded = false;
		}
		else if(fun(this.optionsHidden)) {
			this.optionsHidden();
		}
		if(this.highlighted_option) {
			u.rc(this.highlighted_option, "hover");
			this.highlighted_option = false;
		}
	}
	field.highlightNextOption = function() {
		if(this.available_options) {
			var node;
			if(this.highlighted_option) {
				node = u.ns(this.highlighted_option);
			}
			else {
				node = this.dropdown_options.nodes[0];
			}
			while(node && node.is_hidden) {
				node = u.ns(node);
			}
			if(node) {
				if(this.highlighted_option) {
					u.rc(this.highlighted_option, "hover");
				}
				u.ac(node, "hover");
				this.highlighted_option = node;
				this.showHighlighedOption();
			}
		}
	}
	field.highlightPreviousOption = function() {
		var node;
		if(this.highlighted_option) {
			node = u.ps(this.highlighted_option);
		}
		while(node && node.is_hidden) {
			node = u.ps(node);
		}
		if(node) {
			if(this.highlighted_option) {
				u.rc(this.highlighted_option, "hover");
			}
			u.ac(node, "hover");
			this.highlighted_option = node;
			this.showHighlighedOption()
		}
		else {
			if(this.highlighted_option) {
				u.rc(this.highlighted_option, "hover");
				this.highlighted_option = false;
			}
		}
	}
	field.showHighlighedOption = function() {
		if(this.highlighted_option) {
			if(this.highlighted_option.offsetTop + this.highlighted_option.offsetHeight > this.dropdown_options_list.offsetHeight + this.dropdown_options_list.scrollTop) {
				this.dropdown_options_list.scrollTop = (this.highlighted_option.offsetTop + this.highlighted_option.offsetHeight) - this.dropdown_options_list.offsetHeight;
			}
			else if(this.highlighted_option.offsetTop < this.dropdown_options_list.scrollTop) {
				this.dropdown_options_list.scrollTop = this.highlighted_option.offsetTop;
			}
		}
	}
	field.searchOptions = function() {
		var i, node;
		this.available_options = false;
		if(this._show_all) {
			for(i = 0; i < this.dropdown_options.nodes.length; i++) {
				node = this.dropdown_options.nodes[i];
				if(this.selected_option == node) {
					u.ass(node, {
						"display": "none",
					});
					if(node == this.highlighted_option) {
						this.highlighted_option = false;
						u.rc(node, "hover");
					}
					node.is_hidden = true;
				}
				else {
					u.ass(node, {
						"display": "block",
					});
					node.innerHTML = node.option_text.replace(value_reg_exp, "<span>$1</span>");
					node.is_hidden = false;
					this.available_options = true;
				}
			}
		}
		else {
			var value = this.virtual_input.val();
			var value_reg_exp = new RegExp("(" + RegExp.escape(value) + ")", "gi");
			for(i = 0; i < this.dropdown_options.nodes.length; i++) {
				node = this.dropdown_options.nodes[i];
				if(value && node.option_text.match(value_reg_exp) && this.selected_option != node) {
					u.ass(node, {
						"display": "block",
					});
					node.innerHTML = node.option_text.replace(value_reg_exp, "<span>$1</span>");
					node.is_hidden = false;
					this.available_options = true;
				}
				else {
					u.ass(node, {
						"display": "none",
					});
					if(node == this.highlighted_option) {
						this.highlighted_option = false;
						u.rc(node, "hover");
					}
					node.is_hidden = true;
				}
			}
		}
		if(this.available_options) {
			this.showOptions();
		}
		else {
			this.hideOptions();
		}
	}
	field.selectOption = function(li) {
		this._show_all = false;
		this.virtual_input.innerHTML = li.option_text;
		if(this.highlighted_option) {
			u.rc(this.highlighted_option, "hover");
			this.highlighted_option = false;
		}
		this.selected_option = li;
		this.updateDropdownValue();
		this.hideOptions();
		this.activateSearchCursor();
	}
	field.updateDropdownValue = function() {
		var value = this.virtual_input.val();
		if(this.selected_option) {
			this.input.val(this.selected_option.option_value);
		}
		else if(value) {
			this.input.val(value);
		}
		else {
			this.input.val("");
		}
		this.input.dispatchEvent(new Event("change"));
	}
	// 
	field.addOption = function (node) {
		if(node.text) {
			var li = u.ae(this.dropdown_options_list, "li", {"class": "option"+(!node.value ? " default" : ""), "html": node.text});
			li.field = this;
			li.option_value = node.value;
			li.option_text = node.text;
			u.ce(li);
			li.inputStarted = function (event) {
				u.e.kill(event);
				this.field.selectOption(this);
			}
			u.e.hover(li);
			li.over = function (event) {
				if(this.field.highlighted_option) {
					u.rc(this.field.highlighted_option, "hover");
				}
				u.ac(this, "hover");
				this.field.highlighted_option = this;
			}
			this.dropdown_options.nodes.push(li);
		}
	}
	field.loadOptions = function() {
		var existing_value = this.input.val();
		this.dropdown_options.nodes = [];
		var i, node;
		for(i = 0; i < this.input.options.length; i++) {
			node = this.input.options[i];
			this.addOption(node);
		}
		this.input.val(existing_value);
	}
	u.f.activateInput(field.input);
	u.f.validate(field.input);
	field.loadOptions();
}
Util.Form.customValidate["dropdown"] = function(iN) {
	if(iN.val() !== "" && !iN.custom_error) {
		u.f.inputIsCorrect(iN);
	}
	else {
		u.f.inputHasError(iN);
	}
}
Util.Form.customBuild["dropdown"] = function(node, _options) {
	var field_name = "js_name";
	var field_label = "Label";
	var field_type = "string";
	var field_value = "";
	var field_options = [];
	var field_class = "";
	var field_id = "";
	var field_disabled = false;
	var field_required = false;
	var field_error_message = "There is an error in your input";
	var field_hint_message = "";
	if(typeof(_options) == "object") {
		var _argument;
		for(_argument in _options) {
			switch(_argument) {
				case "name"					: field_name			= _options[_argument]; break;
				case "label"				: field_label			= _options[_argument]; break;
				case "type"					: field_type			= _options[_argument]; break;
				case "value"				: field_value			= _options[_argument]; break;
				case "options"				: field_options			= _options[_argument]; break;
				case "class"				: field_class			= _options[_argument]; break;
				case "id"					: field_id				= _options[_argument]; break;
				case "disabled"				: field_disabled		= _options[_argument]; break;
				case "required"				: field_required		= _options[_argument]; break;
				case "error_message"		: field_error_message	= _options[_argument]; break;
				case "hint_message"			: field_hint_message	= _options[_argument]; break;
			}
		}
	}
	field_id = field_id ? field_id : "input_"+field_type+"_"+field_name;
	field_class += field_disabled ? (!field_class.match(/(^| )disabled( |$)/) ? " disabled" : "") : "";
	field_class += field_required ? (!field_class.match(/(^| )required( |$)/) ? " required" : "") : "";
	var field = u.ae(node, "div", {"class":"field "+field_type+" "+field_class});
	attributes = {
		"id":field_id, 
		"name":field_name, 
		"disabled":field_disabled
	};
	u.ae(field, "label", {"for":field_id, "html":field_label});
	var select = u.ae(field, "select", u.f.verifyAttributes(attributes));
	if(field_options) {
		if(field_options.length) {
			var i, option, selected_option;
			for(i = 0; option = field_options[i]; i++) {
				if(option.value == field_value) {
					selected_option = u.ae(select, "option", { "value": option.value, "html": option.text, "selected": "selected" });
				}
				else {
					u.ae(select, "option", { "value": option.value, "html": option.text });
				}
			}
			if(!selected_option && !field_value) {
				select.selectedIndex = -1;
			}
		}
	}
	if(field_hint_message || field_error_message) {
		var help = u.ae(field, "div", {"class":"help"});
		if(field_hint_message) {
			u.ae(help, "div", { "class": "hint", "html": field_hint_message });
		}
		if(field_error_message) {
			u.ae(help, "div", { "class": "error", "html": field_error_message });
		}
	}
	return field;
}


/*u-form-field-range.js*/
Util.Form.customInit["range"] = function(field) {
	field.type = "range";
	field.input = u.qs("input", field);
	field.input._form = field._form;
	field.input.label = u.qs("label[for='"+field.input.id+"']", field);
	field.input.field = field;
	field.input.val = u.f._value;
	field._virtual_input_wrapper = u.ae(field, "div", {"class":"virtual"});
	field.insertBefore(field._virtual_input_wrapper, field.input);
	field._virtual_input = u.ae(field._virtual_input_wrapper, "div", {"class":"input", "contentEditable":"true"});
	field.min = field.input.getAttribute("min");
	field.max = field.input.getAttribute("max");
	field.postfix = field.input.getAttribute("postfix");
	field.locale = field.input.getAttribute("locale") || document.documentElement.lang;
	field._min = u.ae(field._virtual_input_wrapper, "div", {"class":"min", "html": field.min + (field.postfix ? " "+field.postfix : "")});
	field._max = u.ae(field._virtual_input_wrapper, "div", {"class":"max", "html": field.max + (field.postfix ? " "+field.postfix : "")});
	field._virtual_input._form = field._form;
	field._virtual_input.field = field;
	field._input_range_updated = function() {
		var range_value = this.val();
		var formatted_range = Number(range_value).toLocaleString(this.field.locale) + (this.field.postfix ? " "+this.field.postfix : "");
		this.field._virtual_input.innerHTML = formatted_range;
	}
	field._virtual_input_keydown = function(event) {
		if (event.keyCode == 13) {
			u.e.kill(event);
		}
	}
	field._virtual_input_updated = function(event) {
		var range_value = this.innerHTML.replace(/[\., a-zA-Z\(\)\;\$\<\>]+/g, "");
		if(!range_value) {
			range_value = this.field.min;
		}
		this.field.input.val(range_value);
		u.f._updated.bind(this.field.input)(event);
	}
	field._virtual_input_blurred = function() {
		u.rc(this.field, "focus");
		u.rc(this, "focus");
		var range_value = Number(this.innerHTML.replace(/[\., a-zA-Z\(\)\;\$\<\>]+/g, ""));
		var min = this.field.input.getAttribute("min");
		var max = this.field.input.getAttribute("max");
		if(range_value < this.field.min) {
			range_value = this.field.min;
		}
		else if(range_value > this.field.max) {
			range_value = this.field.max;
		}
		var formatted_price = Number(range_value).toLocaleString(this.field.locale) + (this.field.postfix ? " "+this.field.postfix : "");
		this.innerHTML = formatted_price;
	}
	field._virtual_input_focused = function() {
		u.ac(this.field, "focus");
		u.ac(this, "focus");
		var range_value = this.innerHTML.replace(/[\., a-zA-Z\(\)\;\$\<\>]+/g, "");
		this.innerHTML = range_value;
		var selection = window.getSelection();
		var range = document.createRange();
		range.selectNodeContents(this);
		selection.removeAllRanges();
		selection.addRange(range);
	}
	u.e.addEvent(field._virtual_input, "keydown", field._virtual_input_keydown);
	u.e.addEvent(field._virtual_input, "input", field._virtual_input_updated);
	u.e.addEvent(field._virtual_input, "blur", field._virtual_input_blurred);
	u.e.addEvent(field._virtual_input, "focus", field._virtual_input_focused);
	u.e.addEvent(field.input, "input", field._input_range_updated);
	u.e.addEvent(field.input, "change", field._input_range_changed);
	u.e.addEvent(field.input, "input", u.f._updated);
	u.e.addEvent(field.input, "change", u.f._changed);
	u.f.activateInput(field.input);
	field._input_range_updated.bind(field.input)();
}
Util.Form.customValidate["range"] = function(iN) {
	if(
		!isNaN(iN.val())
	) {
		u.f.inputIsCorrect(iN);
	}
	else {
		u.f.inputHasError(iN);
	}
}


/*u-geometry.js*/
Util.absoluteX = u.absX = function(node) {
	if(node.offsetParent) {
		return node.offsetLeft + u.absX(node.offsetParent);
	}
	return node.offsetLeft;
}
Util.absoluteY = u.absY = function(node) {
	if(node.offsetParent) {
		return node.offsetTop + u.absY(node.offsetParent);
	}
	return node.offsetTop;
}
Util.relativeX = u.relX = function(node) {
	if(u.gcs(node, "position").match(/absolute/) == null && node.offsetParent && u.gcs(node.offsetParent, "position").match(/relative|absolute|fixed/) == null) {
		return node.offsetLeft + u.relX(node.offsetParent);
	}
	return node.offsetLeft;
}
Util.relativeY = u.relY = function(node) {
	if(u.gcs(node, "position").match(/absolute/) == null && node.offsetParent && u.gcs(node.offsetParent, "position").match(/relative|absolute|fixed/) == null) {
		return node.offsetTop + u.relY(node.offsetParent);
	}
	return node.offsetTop;
}
Util.actualWidth = u.actualW = function(node) {
	return parseInt(u.gcs(node, "width"));
}
Util.actualHeight = u.actualH = function(node) {
	return parseInt(u.gcs(node, "height"));
}
Util.eventX = function(event){
	return (event.targetTouches && event.targetTouches.length ? event.targetTouches[0].pageX : event.pageX);
}
Util.eventY = function(event){
	return (event.targetTouches && event.targetTouches.length ? event.targetTouches[0].pageY : event.pageY);
}
Util.browserWidth = u.browserW = function() {
	return document.documentElement.clientWidth;
}
Util.browserHeight = u.browserH = function() {
	return document.documentElement.clientHeight;
}
Util.htmlWidth = u.htmlW = function() {
	return document.body.offsetWidth + parseInt(u.gcs(document.body, "margin-left")) + parseInt(u.gcs(document.body, "margin-right"));
}
Util.htmlHeight = u.htmlH = function() {
	return document.body.offsetHeight + parseInt(u.gcs(document.body, "margin-top")) + parseInt(u.gcs(document.body, "margin-bottom"));
}
Util.pageScrollX = u.scrollX = function() {
	return window.pageXOffset;
}
Util.pageScrollY = u.scrollY = function() {
	return window.pageYOffset;
}


/*u-history.js*/
Util.History = u.h = new function() {
	this.popstate = ("onpopstate" in window);
	this.callbacks = [];
	this.is_listening = false;
	this.navigate = function(url, node, silent) {
		silent = silent || false;
		if((!url.match(/^http[s]?\:\/\//) || url.match(document.domain)) && (!node || !node._a || !node._a.target)) {
			if(this.popstate) {
				history.pushState({}, url, url);
				if(!silent) {
					this.callback(url);
				}
			}
			else {
				if(silent) {
					this.next_hash_is_silent = true;
				}
				location.hash = u.h.getCleanUrl(url);
			}
		}
		else {
			if(!node || !node._a || !node._a.target) {
				location.href = url;
			}
			else {
				window.open(this.url);
			}
		}
	}
	this.callback = function(url) {
		var i, recipient;
		for(i = 0; i < this.callbacks.length; i++) {
			recipient = this.callbacks[i];
			if(fun(recipient.node[recipient.callback])) {
				recipient.node[recipient.callback](url);
			}
		}
	}
	this.removeEvent = function(node, _options) {
		var callback_urlchange = "navigate";
		if(obj(_options)) {
			var argument;
			for(argument in _options) {
				switch(argument) {
					case "callback"		: callback_urlchange		= _options[argument]; break;
				}
			}
		}
		var i, recipient;
		for(i = 0; recipient = this.callbacks[i]; i++) {
			if(recipient.node == node && recipient.callback == callback_urlchange) {
				this.callbacks.splice(i, 1);
				break;
			}
		}
	}
	this.addEvent = function(node, _options) {
		var callback_urlchange = "navigate";
		if(obj(_options)) {
			var argument;
			for(argument in _options) {
				switch(argument) {
					case "callback"		: callback_urlchange		= _options[argument]; break;
				}
			}
		}
		if(!this.is_listening) {
			this.is_listening = true;
			if(this.popstate) {
				u.e.addEvent(window, "popstate", this._urlChanged);
			}
			else if("onhashchange" in window && !u.browser("explorer", "<=7")) {
				u.e.addEvent(window, "hashchange", this._hashChanged);
			}
			else {
				u.h._current_hash = window.location.hash;
				window.onhashchange = this._hashChanged;
				setInterval(
					function() {
						if(window.location.hash !== u.h._current_hash) {
							u.h._current_hash = window.location.hash;
							window.onhashchange();
						}
					}, 200
				);
			}
		}
		this.callbacks.push({"node":node, "callback":callback_urlchange});
	}
	this._urlChanged = function(event) {
		var url = u.h.getCleanUrl(location.href);
		if(event.state || (!event.state && event.path)) {
			u.h.callback(url);
		}
		else {
			history.replaceState({}, url, url);
		}
	}
	this._hashChanged = function(event) {
		if(!location.hash || !location.hash.match(/^#\//)) {
			location.hash = "#/"
			return;
		}
		var url = u.h.getCleanHash(location.hash);
		if(u.h.next_hash_is_silent) {
			delete u.h.next_hash_is_silent;
		}
		else {
			u.h.callback(url);
		}
	}
	this.trail = [];
	this.addToTrail = function(url, node) {
		this.trail.push({"url":url, "node":node});
	}
	this.getCleanUrl = function(string, levels) {
		string = string.replace(location.protocol+"//"+document.domain, "") ? string.replace(location.protocol+"//"+document.domain, "").match(/[^#$]+/)[0] : "/";
		if(!levels) {
			return string;
		}
		else {
			var i, return_string = "";
			var path = string.split("/");
			levels = levels > path.length-1 ? path.length-1 : levels;
			for(i = 1; i <= levels; i++) {
				return_string += "/" + path[i];
			}
			return return_string;
		}
	}
	this.getCleanHash = function(string, levels) {
		string = string.replace("#", "");
		if(!levels) {
			return string;
		}
		else {
			var i, return_string = "";
			var hash = string.split("/");
			levels = levels > hash.length-1 ? hash.length-1 : levels;
			for(i = 1; i <= levels; i++) {
				return_string += "/" + hash[i];
			}
			return return_string;
		}
	}
	this.resolveCurrentUrl = function() {
		return !location.hash ? this.getCleanUrl(location.href) : this.getCleanHash(location.hash);
	}
}


/*u-init.js*/
Util.Modules = u.m = new Object();
Util.init = function(scope) {
	var i, node, nodes, module;
	scope = scope && scope.nodeName ? scope : document;
	nodes = u.ges("i\:([_a-zA-Z0-9])+", scope);
	for(i = 0; i < nodes.length; i++) {
		node = nodes[i];
		while((module = u.cv(node, "i"))) {
			u.rc(node, "i:"+module);
			if(module && obj(u.m[module])) {
				u.m[module].init(node);
			}
		}
	}
}


/*u-keyboard.js*/
Util.Keyboard = u.k = new function() {
	this.shortcuts = {};
	this.onkeydownCatcher = function(event) {
		u.k.catchKey(event);
	}
	this.addKey = function(node, key, _options) {
		var shortcut = {"node": node};
		shortcut.callback_keyboard = "clicked";
		shortcut.metakey_required = true;
		shortcut.focus_required = false;
		shortcut.value = false;
		if(obj(_options)) {
			var argument;
			for(argument in _options) {
				switch(argument) {
					case "callback"		: shortcut.callback_keyboard	= _options[argument]; break;
					case "metakey"		: shortcut.metakey_required		= _options[argument]; break;
					case "focused"		: shortcut.focus_required		= _options[argument]; break;
					case "value"		: shortcut.value				= _options[argument]; break;
				}
			}
		}
		var key_index = key.toString().toUpperCase();
		if(!this.shortcuts.length) {
			u.e.addEvent(document, "keydown", this.onkeydownCatcher);
		}
		if(!this.shortcuts[key_index]) {
			this.shortcuts[key_index] = new Array();
		}
		this.shortcuts[key_index].push(shortcut);
	}
	this.catchKey = function(event) {
		event = event ? event : window.event;
		var key = String.fromCharCode(event.keyCode);
		if(event.keyCode == 27) {
			key = "ESC";
		}
		else if(event.keyCode == 38) {
			key = "UP";
		}
		else if(event.keyCode == 39) {
			key = "RIGHT";
		}
		else if(event.keyCode == 40) {
			key = "DOWN";
		}
		else if(event.keyCode == 37) {
			key = "LEFT";
		}
		else if(event.keyCode == 13) {
			key = "ENTER";
		}
		else if(event.keyCode == 9) {
			key = "TAB";
		}
		else if(event.keyCode == 8) {
			key = "DELETE";
		}
		else if(event.keyCode == 171) {
			key = "+";
		}
		if(this.shortcuts[key]) {
			var shortcuts, shortcut, i;
			shortcuts = this.shortcuts[key];
			for(i = 0; i < shortcuts.length; i++) {
				shortcut = shortcuts[i];
				if(u.contains(document.body, shortcut.node)) {
					if(shortcut.node.offsetHeight && ((event.ctrlKey || event.metaKey) || (!shortcut.metakey_required || key == "ESC"))) {
						if(!shortcut.focus_required || u.containsOrIs(shortcut.node, event.target)) {
							u.e.kill(event);
							if(fun(shortcut.callback_keyboard)) {
								shortcut.callback_keyboard(event, shortcut.value);
							}
							else if(fun(shortcut.node[shortcut.callback_keyboard])) {
								shortcut.node[shortcut.callback_keyboard](event, shortcut.value);
							}
						}
					}
				}
				else {
					this.shortcuts[key].splice(i, 1);
					if(!this.shortcuts[key].length) {
						delete this.shortcuts[key];
						break;
					}
					else {
						i--;
					}
				}
			}
		}
	}
	this.removeKey = function(node, key) {
		var key_index = key.toString().toUpperCase();
		if(this.shortcuts && this.shortcuts[key_index]) {
			var index = u.arrayKeyValue(this.shortcuts[key_index], "node", node);
			if(index !== false) {
				this.shortcuts[key_index].splice(index, 1);
			}
		}
	}
}


/*u-math.js*/
Util.random = function(min, max) {
	return Math.round((Math.random() * (max - min)) + min);
}
Util.numToHex = function(num) {
	return num.toString(16);
}
Util.hexToNum = function(hex) {
	return parseInt(hex,16);
}
Util.round = function(number, decimals) {
	var round_number = number*Math.pow(10, decimals);
	return Math.round(round_number)/Math.pow(10, decimals);
}

/*u-media.js*/
Util.audioPlayer = function(_options) {
	_options = _options || {};
	_options.type = "audio";
	return u.mediaPlayer(_options);
}
Util.videoPlayer = function(_options) {
	_options = _options || {};
	_options.type = "video";
	return u.mediaPlayer(_options);
}
Util.mediaPlayer = function(_options) {
	var player = document.createElement("div");
	player.type = _options && _options.type || "video";
	u.ac(player, player.type+"player");
	player._autoplay = false;
	player._muted = false;
	player._loop = false;
	player._preload = false;
	player._playsinline = false;
	player._crossorigin = "anonymous";
	player._controls = false;
	player._controls_playpause = false;
	player._controls_play = false;
	player._controls_pause = false;
	player._controls_stop = false;
	player._controls_zoom = false;
	player._controls_volume = false;
	player._controls_search = false;
	player._ff_skip = 2;
	player._rw_skip = 2;
	player.media = u.ae(player, player.type);
	if(player.media && fun(player.media.play)) {
		player.load = function(src, _options) {
			if(u.hc(this, "playing")) {
				this.stop();
			}
			u.setupMedia(this, _options);
			if(src) {
				this.media.src = u.correctMediaSource(this, src);
				this.media.load();
			}
		}
		player.play = function(position) {
			if(this.media.currentTime && position !== undefined) {
				this.media.currentTime = position;
			}
			if(this.media.src) {
				return this.media.play();
			}
		}
		player.loadAndPlay = function(src, _options) {
			var position = 0;
			if(obj(_options)) {
				var _argument;
				for(_argument in _options) {
					switch(_argument) {
						case "position"		: position		= _options[_argument]; break;
					}
				}
			}
			this.load(src, _options);
			return this.play(position);
		}
		player.pause = function() {
			this.media.pause();
		}
		player.stop = function() {
			this.media.pause();
			if(this.media.currentTime) {
				this.media.currentTime = 0;
			}
		}
		player.ff = function() {
			if(this.media.src && this.media.currentTime && this.mediaLoaded) {
				this.media.currentTime = (this.media.duration - this.media.currentTime >= this._ff_skip) ? (this.media.currentTime + this._ff_skip) : this.media.duration;
				this.media._timeupdate();
			}
		}
		player.rw = function() {
			if(this.media.src && this.media.currentTime && this.mediaLoaded) {
				this.media.currentTime = (this.media.currentTime >= this._rw_skip) ? (this.media.currentTime - this._rw_skip) : 0;
				this.media._timeupdate();
			}
		}
		player.togglePlay = function() {
			if(u.hc(this, "playing")) {
				this.pause();
			}
			else {
				this.play();
			}
		}
		player.volume = function(value) {
			this.media.volume = value;
			if(value === 0) {
				u.ac(this, "muted");
			}
			else {
				u.rc(this, "muted");
			}
		}
		player.toggleSound = function() {
			if(this.media.volume) {
				this.media.volume = 0;
				u.ac(this, "muted");
			}
			else {
				this.media.volume = 1;
				u.rc(this, "muted");
			}
		}
		player.mute = function() {
			this._muted = true;
			this.media.muted = true;
		}
		player.unmute = function() {
			this._muted = false;
			this.media.muted = false;
		}
	}
	else {
		player.load = function() {}
		player.play = function() {}
		player.loadAndPlay = function() {}
		player.pause = function() {}
		player.stop = function() {}
		player.ff = function() {}
		player.rw = function() {}
		player.togglePlay = function() {}
	}
	u.setupMedia(player, _options);
	u.detectMediaAutoplay(player);
	return player;
}
u.setupMedia = function(player, _options) {
	if(obj(_options)) {
		var _argument;
		for(_argument in _options) {
			switch(_argument) {
				case "autoplay"     : player._autoplay               = _options[_argument]; break;
				case "muted"        : player._muted                  = _options[_argument]; break;
				case "loop"         : player._loop                   = _options[_argument]; break;
				case "preload"      : player._preload                = _options[_argument]; break;
				case "playsinline"  : player._playsinline            = _options[_argument]; break;
				case "controls"     : player._controls               = _options[_argument]; break;
				case "ff_skip"      : player._ff_skip                = _options[_argument]; break;
				case "rw_skip"      : player._rw_skip                = _options[_argument]; break;
			}
		}
	}
	player.media.autoplay = player._autoplay;
	player.media.loop = player._loop;
	player.media.muted = player._muted;
	player.media.playsinline = player._playsinline;
	player.media.setAttribute("playsinline", player._playsinline);
	player.media.setAttribute("preload", player._preload);
	player.media.setAttribute("crossorigin", player._crossorigin);
	u.setupMediaControls(player, player._controls);
	player.currentTime = 0;
	player.duration = 0;
	player.mediaLoaded = false;
	player.metaLoaded = false;
	if(!player.media.player) {
		player.media.player = player;
		player.media._loadstart = function(event) {
			u.ac(this.player, "loading");
			if(fun(this.player.loading)) {
				this.player.loading(event);
			}
		}
		u.e.addEvent(player.media, "loadstart", player.media._loadstart);
		player.media._canplaythrough = function(event) {
			u.rc(this.player, "loading");
			if(fun(this.player.canplaythrough)) {
				this.player.canplaythrough(event);
			}
		}
		u.e.addEvent(player.media, "canplaythrough", player.media._canplaythrough);
		player.media._playing = function(event) {
			u.rc(this.player, "loading|paused");
			u.ac(this.player, "playing");
			if(fun(this.player.playing)) {
				this.player.playing(event);
			}
		}
		u.e.addEvent(player.media, "playing", player.media._playing);
		player.media._paused = function(event) {
			u.rc(this.player, "playing|loading");
			u.ac(this.player, "paused");
			if(fun(this.player.paused)) {
				this.player.paused(event);
			}
		}
		u.e.addEvent(player.media, "pause", player.media._paused);
		player.media._stalled = function(event) {
			u.rc(this.player, "playing|paused");
			u.ac(this.player, "loading");
			if(fun(this.player.stalled)) {
				this.player.stalled(event);
			}
		}
		u.e.addEvent(player.media, "stalled", player.media._paused);
		player.media._error = function(event) {
			if(fun(this.player.error)) {
				this.player.error(event);
			}
		}
		u.e.addEvent(player.media, "error", player.media._error);
		player.media._ended = function(event) {
			u.rc(this.player, "playing|paused");
			if(fun(this.player.ended)) {
				this.player.ended(event);
			}
		}
		u.e.addEvent(player.media, "ended", player.media._ended);
		player.media._loadedmetadata = function(event) {
			this.player.duration = this.duration;
			this.player.currentTime = this.currentTime;
			this.player.metaLoaded = true;
			if(fun(this.player.loadedmetadata)) {
				this.player.loadedmetadata(event);
			}
		}
		u.e.addEvent(player.media, "loadedmetadata", player.media._loadedmetadata);
		player.media._loadeddata = function(event) {
			this.player.mediaLoaded = true;
			if(fun(this.player.loadeddata)) {
				this.player.loadeddata(event);
			}
		}
		u.e.addEvent(player.media, "loadeddata", player.media._loadeddata);
		player.media._timeupdate = function(event) {
			this.player.currentTime = this.currentTime;
			if(fun(this.player.timeupdate)) {
				this.player.timeupdate(event);
			}
		}
		u.e.addEvent(player.media, "timeupdate", player.media._timeupdate);
	}
}
u.correctMediaSource = function(player, src) {
	var param = src.match(/(\?|\#)[^$]+/) ? src.match(/((\?|\#)[^$]+)/)[1] : "";
	src = src.replace(/(\?|\#)[^$]+/, "");
	if(player.type == "video") {
		src = src.replace(/(\.m4v|\.mp4|\.webm|\.ogv|\.3gp|\.mov)$/, "");
		if(player.flash) {
			return src+".mp4"+param;
		}
		else if(player.media.canPlayType("video/mp4")) {
			return src+".mp4"+param;
		}
		else if(player.media.canPlayType("video/ogg")) {
			return src+".ogv"+param;
		}
		else if(player.media.canPlayType("video/3gpp")) {
			return src+".3gp"+param;
		}
		else {
			return src+".mov"+param;
		}
	}
	else {
		src = src.replace(/(.mp3|.ogg|.wav)$/, "");
		if(player.flash) {
			return src+".mp3"+param;
		}
		if(player.media.canPlayType("audio/mpeg")) {
			return src+".mp3"+param;
		}
		else if(player.media.canPlayType("audio/ogg")) {
			return src+".ogg"+param;
		}
		else {
			return src+".wav"+param;
		}
	}
}
u.setupMediaControls = function(player, _options) {
	if(obj(_options)) {
		var _argument;
		for(_argument in _options) {
			switch(_argument) {
				case "playpause"    : player._controls_playpause     = _options[_argument]; break;
				case "play"         : player._controls_play          = _options[_argument]; break;
				case "stop"         : player._controls_stop          = _options[_argument]; break;
				case "pause"        : player._controls_pause         = _options[_argument]; break;
				case "volume"       : player._controls_volume        = _options[_argument]; break;
				case "search"       : player._controls_search        = _options[_argument]; break;
			}
		}
	}
	player._custom_controls = obj(_options) && (
		player._controls_playpause ||
		player._controls_play ||
		player._controls_stop ||
		player._controls_pause ||
		player._controls_volume ||
		player._controls_search
	) || false;
	if(player._custom_controls || !_options) {
		player.media.removeAttribute("controls");
	}
	else {
		player.media.controls = player._controls;
	}
	if(!player._custom_controls && player.controls) {
		player.removeChild(player.controls);
		delete player.controls;
	}
	else if(player._custom_controls) {
		if(!player.controls) {
			player.controls = u.ae(player, "div", {"class":"controls"});
			player.controls.player = player;
			player.controls.out = function() {
				u.a.transition(this, "all 0.3s ease-out");
				u.ass(this, {
					"opacity":0
				});
			}
			player.controls.over = function() {
				u.a.transition(this, "all 0.5s ease-out");
				u.ass(this, {
					"opacity":1
				});
			}
			u.e.hover(player.controls);
		}
		if(player._controls_playpause) {
			if(!player.controls.playpause) {
				player.controls.playpause = u.ae(player.controls, "a", {"class":"playpause"});
				player.controls.playpause.player = player;
				u.e.click(player.controls.playpause);
				player.controls.playpause.clicked = function(event) {
					this.player.togglePlay();
				}
			}
		}
		else if(player.controls.playpause) {
			player.controls.playpause.parentNode.removeChild(player.controls.playpause);
			delete player.controls.playpause;
		}
		if(player._controls_play) {
			if(!player.controls.play) {
				player.controls.play = u.ae(player.controls, "a", {"class":"play"});
				player.controls.play.player = player;
				u.e.click(player.controls.play);
				player.controls.play.clicked = function(event) {
					this.player.togglePlay();
				}
			}
		}
		else if(player.controls.play) {
			player.controls.play.parentNode.removeChild(player.controls.play);
			delete player.controls.play;
		}
		if(player._controls_pause) {
			if(!player.controls.pause) {
				player.controls.pause = u.ae(player.controls, "a", {"class":"pause"});
				player.controls.pause.player = player;
				u.e.click(player.controls.pause);
				player.controls.pause.clicked = function(event) {
					this.player.togglePlay();
				}
			}
		}
		else if(player.controls.pause) {
			player.controls.pause.parentNode.removeChild(player.controls.pause);
			delete player.controls.pause;
		}
		if(player._controls_stop) {
			if(!player.controls.stop) {
				player.controls.stop = u.ae(player.controls, "a", {"class":"stop" });
				player.controls.stop.player = player;
				u.e.click(player.controls.stop);
				player.controls.stop.clicked = function(event) {
					this.player.stop();
				}
			}
		}
		else if(player.controls.stop) {
			player.controls.stop.parentNode.removeChild(player.controls.stop);
			delete player.controls.stop;
		}
		if(player._controls_search) {
			if(!player.controls.search) {
				player.controls.search_ff = u.ae(player.controls, "a", {"class":"ff"});
				player.controls.search_ff._default_display = u.gcs(player.controls.search_ff, "display");
				player.controls.search_ff.player = player;
				player.controls.search_rw = u.ae(player.controls, "a", {"class":"rw"});
				player.controls.search_rw._default_display = u.gcs(player.controls.search_rw, "display");
				player.controls.search_rw.player = player;
				u.e.click(player.controls.search_ff);
				player.controls.search_ff.ffing = function() {
					this.t_ffing = u.t.setTimer(this, this.ffing, 100);
					this.player.ff();
				}
				player.controls.search_ff.inputStarted = function(event) {
					this.ffing();
				}
				player.controls.search_ff.clicked = function(event) {
					u.t.resetTimer(this.t_ffing);
				}
				u.e.click(player.controls.search_rw);
				player.controls.search_rw.rwing = function() {
					this.t_rwing = u.t.setTimer(this, this.rwing, 100);
					this.player.rw();
				}
				player.controls.search_rw.inputStarted = function(event) {
					this.rwing();
				}
				player.controls.search_rw.clicked = function(event) {
					u.t.resetTimer(this.t_rwing);
					this.player.rw();
				}
				player.controls.search = true;
			}
			else {
				u.as(player.controls.search_ff, "display", player.controls.search_ff._default_display);
				u.as(player.controls.search_rw, "display", player.controls.search_rw._default_display);
			}
		}
		else if(player.controls.search) {
			u.as(player.controls.search_ff, "display", "none");
			u.as(player.controls.search_rw, "display", "none");
		}
		if(player._controls_zoom && !player.controls.zoom) {}
		else if(player.controls.zoom) {}
		if(player._controls_volume && !player.controls.volume) {}
		else if(player.controls.volume) {}
		// 
	}
}
u.detectMediaAutoplay = function(player) {
	if(!u.media_autoplay_detection) {
		u.media_autoplay_detection = [player];
		u.test_autoplay = document.createElement("video");
		u.test_autoplay.check = function() {
			if(u.media_can_autoplay !== undefined && u.media_can_autoplay_muted !== undefined) {
				for(var i = 0, player; i < u.media_autoplay_detection.length; i++) {
					player = u.media_autoplay_detection[i];
					player.can_autoplay = u.media_can_autoplay;
					player.can_autoplay_muted = u.media_can_autoplay_muted;
					if(fun(player.ready)) {
						player.ready();
					}
				}
				u.media_autoplay_detection = true;
				u.test_autoplay.pause();
				delete u.test_autoplay;
			}
		}
		u.test_autoplay.playing = function(event) {
			u.media_can_autoplay = true;
			u.media_can_autoplay_muted = true;
			this.check();
		}
		u.test_autoplay.notplaying = function() {
			u.media_can_autoplay = false;
			u.test_autoplay.muted = true;
			var promise = u.test_autoplay.play();
			if(promise && fun(promise.then)) {
				promise.then(
					function(){
						if(u.test_autoplay) {
							u.t.resetTimer(window.u.test_autoplay.t_check);
							u.test_autoplay.playing_muted();
						}
					}
				).catch(
					function() {
						if(u.test_autoplay) {
							u.t.resetTimer(window.u.test_autoplay.t_check)
							u.test_autoplay.notplaying_muted();
						}
					}
				);
				u.test_autoplay.t_check = u.t.setTimer(u.test_autoplay, function(){
					u.test_autoplay.pause();
				}, 1000);
			}
		}
		u.test_autoplay.playing_muted = function() {
			u.media_can_autoplay_muted = true;
			this.check();
		}
		u.test_autoplay.notplaying_muted = function() {
			u.media_can_autoplay_muted = false;
			this.check();
		}
		u.test_autoplay.error = function(event) {
			u.media_can_autoplay = false;
			u.media_can_autoplay_muted = false;
			this.check();
		}
		u.e.addEvent(u.test_autoplay, "playing", u.test_autoplay.playing);
		u.e.addEvent(u.test_autoplay, "error", u.test_autoplay.error);
		if(u.test_autoplay.canPlayType("video/mp4")) {
			var data = "data:audio/aac;base64,//FQgAPf/N4CAExhdmM1OC45MS4xMDAAQiAIwRg4//FQgAG//CEQBGCMHP/xUIABv/whEARgjBz/8VCAAb/8IRAEYIwc//FQgAG//CEQBGCMHP/xUIABv/whEARgjBw=";
			// var data = "data:video/mp4;base64,AAAAIGZ0eXBpc29tAAACAGlzb21pc28yYXZjMW1wNDEAAAAIZnJlZQAAAxJtZGF0AAACoAYF//+c3EXpvebZSLeWLNgg2SPu73gyNjQgLSBjb3JlIDE1NyAtIEguMjY0L01QRUctNCBBVkMgY29kZWMgLSBDb3B5bGVmdCAyMDAzLTIwMTggLSBodHRwOi8vd3d3LnZpZGVvbGFuLm9yZy94MjY0Lmh0bWwgLSBvcHRpb25zOiBjYWJhYz0xIHJlZj0zIGRlYmxvY2s9MTowOjAgYW5hbHlzZT0weDM6MHgxMTMgbWU9aGV4IHN1Ym1lPTcgcHN5PTEgcHN5X3JkPTEuMDA6MC4wMCBtaXhlZF9yZWY9MSBtZV9yYW5nZT0xNiBjaHJvbWFfbWU9MSB0cmVsbGlzPTEgOHg4ZGN0PTEgY3FtPTAgZGVhZHpvbmU9MjEsMTEgZmFzdF9wc2tpcD0xIGNocm9tYV9xcF9vZmZzZXQ9LTIgdGhyZWFkcz0xIGxvb2thaGVhZF90aHJlYWRzPTEgc2xpY2VkX3RocmVhZHM9MCBucj0wIGRlY2ltYXRlPTEgaW50ZXJsYWNlZD0wIGJsdXJheV9jb21wYXQ9MCBjb25zdHJhaW5lZF9pbnRyYT0wIGJmcmFtZXM9MyBiX3B5cmFtaWQ9MiBiX2FkYXB0PTEgYl9iaWFzPTAgZGlyZWN0PTEgd2VpZ2h0Yj0xIG9wZW5fZ29wPTAgd2VpZ2h0cD0yIGtleWludD0yNTAga2V5aW50X21pbj0yNSBzY2VuZWN1dD00MCBpbnRyYV9yZWZyZXNoPTAgcmNfbG9va2FoZWFkPTQwIHJjPWNyZiBtYnRyZWU9MSBjcmY9MjMuMCBxY29tcD0wLjYwIHFwbWluPTAgcXBtYXg9NjkgcXBzdGVwPTQgaXBfcmF0aW89MS40MCBhcT0xOjEuMDAAgAAAABpliIQAM//+9uy+BTYUyFCXESoMDuxA1w9RcQAAAAlBmiJsQr/+RRgAAAAIAZ5BeQr/IeHeAgBMYXZjNTguMzUuMTAwAEIgCMEYOCEQBGCMHCEQBGCMHCEQBGCMHCEQBGCMHAAABXNtb292AAAAbG12aGQAAAAAAAAAAAAAAAAAAAPoAAAAeAABAAABAAAAAAAAAAAAAAAAAQAAAAAAAAAAAAAAAAAAAAEAAAAAAAAAAAAAAAAAAEAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAADAAACb3RyYWsAAABcdGtoZAAAAAMAAAAAAAAAAAAAAAEAAAAAAAAAeAAAAAAAAAAAAAAAAAAAAAAAAQAAAAAAAAAAAAAAAAAAAAEAAAAAAAAAAAAAAAAAAEAAAAAAMAAAADAAAAAAACRlZHRzAAAAHGVsc3QAAAAAAAAAAQAAAHgAAAQAAAEAAAAAAedtZGlhAAAAIG1kaGQAAAAAAAAAAAAAAAAAADIAAAAGAFXEAAAAAAAtaGRscgAAAAAAAAAAdmlkZQAAAAAAAAAAAAAAAFZpZGVvSGFuZGxlcgAAAAGSbWluZgAAABR2bWhkAAAAAQAAAAAAAAAAAAAAJGRpbmYAAAAcZHJlZgAAAAAAAAABAAAADHVybCAAAAABAAABUnN0YmwAAACmc3RzZAAAAAAAAAABAAAAlmF2YzEAAAAAAAAAAQAAAAAAAAAAAAAAAAAAAAAAMAAwAEgAAABIAAAAAAAAAAEAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAY//8AAAAwYXZjQwFkAAr/4QAXZ2QACqzZTewEQAAAAwBAAAAMg8SJZYABAAZo6+PLIsAAAAAQcGFzcAAAAAEAAAABAAAAGHN0dHMAAAAAAAAAAQAAAAMAAAIAAAAAFHN0c3MAAAAAAAAAAQAAAAEAAAAoY3R0cwAAAAAAAAADAAAAAQAABAAAAAABAAAGAAAAAAEAAAIAAAAAHHN0c2MAAAAAAAAAAQAAAAEAAAADAAAAAQAAACBzdHN6AAAAAAAAAAAAAAADAAACwgAAAA0AAAAMAAAAFHN0Y28AAAAAAAAAAQAAADAAAAIudHJhawAAAFx0a2hkAAAAAwAAAAAAAAAAAAAAAgAAAAAAAAB1AAAAAAAAAAAAAAABAQAAAAABAAAAAAAAAAAAAAAAAAAAAQAAAAAAAAAAAAAAAAAAQAAAAAAAAAAAAAAAAAAAJGVkdHMAAAAcZWxzdAAAAAAAAAABAAAAdQAAAAAAAQAAAAABpm1kaWEAAAAgbWRoZAAAAAAAAAAAAAAAAAAArEQAABQAVcQAAAAAAC1oZGxyAAAAAAAAAABzb3VuAAAAAAAAAAAAAAAAU291bmRIYW5kbGVyAAAAAVFtaW5mAAAAEHNtaGQAAAAAAAAAAAAAACRkaW5mAAAAHGRyZWYAAAAAAAAAAQAAAAx1cmwgAAAAAQAAARVzdGJsAAAAZ3N0c2QAAAAAAAAAAQAAAFdtcDRhAAAAAAAAAAEAAAAAAAAAAAACABAAAAAArEQAAAAAADNlc2RzAAAAAAOAgIAiAAIABICAgBRAFQAAAAAAEX4AAAymBYCAgAISEAaAgIABAgAAABhzdHRzAAAAAAAAAAEAAAAFAAAEAAAAABxzdHNjAAAAAAAAAAEAAAABAAAABQAAAAEAAAAoc3RzegAAAAAAAAAAAAAABQAAABcAAAAGAAAABgAAAAYAAAAGAAAAFHN0Y28AAAAAAAAAAQAAAwsAAAAac2dwZAEAAAByb2xsAAAAAgAAAAH//wAAABxzYmdwAAAAAHJvbGwAAAABAAAABQAAAAEAAABidWR0YQAAAFptZXRhAAAAAAAAACFoZGxyAAAAAAAAAABtZGlyYXBwbAAAAAAAAAAAAAAAAC1pbHN0AAAAJal0b28AAAAdZGF0YQAAAAEAAAAATGF2ZjU4LjIwLjEwMA==";
			u.test_autoplay.volume = 0.01;
			u.test_autoplay.autoplay = true;
			u.test_autoplay.playsinline = true;
			u.test_autoplay.setAttribute("playsinline", true);
			u.test_autoplay.src = data;
			var promise = u.test_autoplay.play();
			if(promise && fun(promise.then)) {
				u.e.removeEvent(u.test_autoplay, "playing", u.test_autoplay.playing);
				u.e.removeEvent(u.test_autoplay, "error", u.test_autoplay.error);
				promise.then(
					u.test_autoplay.playing.bind(u.test_autoplay)
				).catch(
					u.test_autoplay.notplaying.bind(u.test_autoplay)
				);
			}
		}
		else {
			u.media_can_autoplay = true;
			u.media_can_autoplay_muted = true;
			u.t.setTimer(u.test_autoplay, function() {
				this.check();
			}, 20);
		}
	}
	else if(u.media_autoplay_detection !== true) {
		u.media_autoplay_detection.push(player);
	}
	else {
		u.t.setTimer(player, function() {
			this.can_autoplay = u.media_can_autoplay;
			this.can_autoplay_muted = u.media_can_autoplay_muted;
			if(fun(this.ready)){
				this.ready();
			}
		}, 20);
	}
}


/*u-navigation.js*/
u.navigation = function(_options) {
	var navigation_node = page;
	var callback_navigate = "_navigate";
	var initialization_scope = page.cN;
	if(obj(_options)) {
		var argument;
		for(argument in _options) {
			switch(argument) {
				case "callback"       : callback_navigate           = _options[argument]; break;
				case "node"           : navigation_node             = _options[argument]; break;
				case "scope"          : initialization_scope        = _options[argument]; break;
			}
		}
	}
	window._man_nav_path = window._man_nav_path ? window._man_nav_path : u.h.getCleanUrl(location.href, 1);
	navigation_node._navigate = function(url) {
		var clean_url = u.h.getCleanUrl(url);
		u.stats.pageView(url);
		if(
			!window._man_nav_path || 
			(!u.h.popstate && window._man_nav_path != u.h.getCleanHash(location.hash, 1)) || 
			(u.h.popstate && window._man_nav_path != u.h.getCleanUrl(location.href, 1))
		) {
			if(this.cN && fun(this.cN.navigate)) {
				this.cN.navigate(clean_url, url);
			}
		}
		else {
			if(this.cN.scene && this.cN.scene.parentNode && fun(this.cN.scene.navigate)) {
				this.cN.scene.navigate(clean_url, url);
			}
			else if(this.cN && fun(this.cN.navigate)) {
				this.cN.navigate(clean_url, url);
			}
		}
		if(!u.h.popstate) {
			window._man_nav_path = u.h.getCleanHash(location.hash, 1);
		}
		else {
			window._man_nav_path = u.h.getCleanUrl(location.href, 1);
		}
	}
	if(location.hash.length && location.hash.match(/^#!/)) {
		location.hash = location.hash.replace(/!/, "");
	}
	var callback_after_init = false;
	if(!this.is_initialized) {
		this.is_initialized = true;
		if(!u.h.popstate) {
			if(location.hash.length < 2) {
				window._man_nav_path = u.h.getCleanUrl(location.href);
				u.h.navigate(window._man_nav_path);
			}
			else if(location.hash.match(/^#\//) && u.h.getCleanHash(location.hash) != u.h.getCleanUrl(location.href)) {
				callback_after_init = u.h.getCleanHash(location.hash);
			}
			else {
			}
		}
		else {
			if(u.h.getCleanHash(location.hash) != u.h.getCleanUrl(location.href) && location.hash.match(/^#\//)) {
				window._man_nav_path = u.h.getCleanHash(location.hash);
				u.h.navigate(window._man_nav_path);
				callback_after_init = window._man_nav_path;
			}
			else {
			}
		}
		var random_string = u.randomString(8);
		if(callback_after_init) {
			eval('navigation_node._initNavigation_'+random_string+' = function() {u.h.addEvent(this, {"callback":"'+callback_navigate+'"});u.h.callback("'+callback_after_init+'");}');
		}
		else {
			eval('navigation_node._initNavigation_'+random_string+' = function() {u.h.addEvent(this, {"callback":"'+callback_navigate+'"});}');
		}
		u.t.setTimer(navigation_node, "_initNavigation_"+random_string, 100);
	}
	else {
		u.h.callbacks.push({"node":navigation_node, "callback":callback_navigate});
	}
}


/*u-object.js*/
u.objectValues = function(obj) {
	var key, values = [];
	for(key in obj) {
		if(obj.hasOwnProperty(key)) {
			values.push(obj[key]);
		}
	}
	return values;
}
u.arrayKeyValue = function(array, key, value) {
	var i, object;
	if(array && array.length) {
		for(i = 0; i < array.length; i++) {
			object = array[i];
			if(obj(object) && object[key] === value) {
				return i
			}
		}
	}
	return false;
}

/*u-overlay.js*/
u.overlay = function (_options) {
	var title = "Overlay";
	var drag = true;
	var width = 400;
	var height = 400;
	var content_scroll = false;
	var classname = "";
	var esc_to_close = false;
	if(obj(_options)) {
		var _argument;
		for(_argument in _options) {
			switch(_argument) {
				case "title"            : title             = _options[_argument]; break;
				case "drag"             : drag              = _options[_argument]; break;
				case "class"            : classname         = _options[_argument]; break;
				case "width"            : width             = _options[_argument]; break;
				case "height"           : height            = _options[_argument]; break;
				case "content_scroll"   : content_scroll    = _options[_argument]; break;
				case "esc"              : esc_to_close      = _options[_argument]; break;
			}
		}
	}
	if (width > 500) {
		classname = " large " + classname;
	}
	else {
		classname = " small " + classname;
	}
	if(content_scroll) {
		classname += "content_scroll"
	}
	var overlay = u.ae(document.body, "div", {
		"class": "overlay" + classname, 
		"tabindex": "-1"
	});
	overlay.protection = u.ae(document.body, "div", {
		"class": "overlay_protection"
	});
	u.ass(overlay, {
		"opacity": 0,
		"width": width + "px",
		"height": height + "px",
		"left": ((u.browserW() - width) / 2) + "px",
		"top": ((u.browserH() - height) / 2) + "px",
	});
	overlay.w = width;
	overlay.h = height;
	if (window._overlay_stack_index) {
		u.ass(overlay.protection, { "z-index": window._overlay_stack_index});
		u.ass(overlay, { "z-index": window._overlay_stack_index + 1 });
	}
	window._overlay_stack_index = Number(u.gcs(overlay, "z-index")) + 2;
	u.as(document.body, "overflow", "hidden");
	overlay._resized = function (event) {
		u.ass(this, {
			"left": ((u.browserW() - this.w) / 2) + "px",
			"top": ((u.browserH() - this.h) / 2) + "px",
		});
		u.ass(this.div_content, {
			"height": ((this.offsetHeight - this.div_header.offsetHeight) - this.div_footer.offsetHeight - parseInt(u.gcs(this, "border-bottom")) - parseInt(u.gcs(this, "border-top"))) + "px"
		});
		if(fun(this.resized)) {
			this.resized(event);
		}
	}
	u.e.addWindowEvent(overlay, "resize", overlay._resized);
	overlay.div_header = u.ae(overlay, "div", {class:"header"});
	if(title) {
		overlay.div_header.h2 = u.ae(overlay.div_header, "h2", {html: title});
		overlay.div_header.overlay = overlay;
	}
	overlay.div_content = u.ae(overlay, "div", {class: "content"});
	overlay.div_content.overlay = overlay;
	overlay.div_footer = u.ae(overlay, "div", {class: "footer"});
	overlay.div_footer.overlay = overlay;
	if (drag) {
		u.e.drag(overlay.div_header, overlay.div_header);
		overlay._x = 0;
		overlay._y = 0;
		overlay.div_header.moved = function (event) {
			var new_x = this.overlay._x + this.current_x;
			var new_y = this.overlay._y + this.current_y;
			u.ass(this.overlay, {
				"transform": "translate(" + new_x + "px, " + new_y + "px)",
			});
		}
		overlay.div_header.dropped = function (event) {
			this.overlay._x += this.current_x;
			this.overlay._y += this.current_y;
		}
	}
	overlay.close = function (event) {
		if(this.esc_to_close && obj(u.k)) {
			u.k.removeKey(this.x_close, "ESC");
		}
		u.as(document.body, "overflow", "auto");
		document.body.removeChild(this);
		document.body.removeChild(this.protection);
		if(fun(this.closed)) {
			this.closed(event);
		}
	}
	overlay.x_close = u.ae(overlay.div_header, "div", {class: "close"});
	overlay.x_close.overlay = overlay;
	u.ce(overlay.x_close);
	overlay.x_close.clicked = function (event) {
		this.overlay.close(event);
	}
	if(esc_to_close && obj(u.k)) {
		overlay.esc_to_close;
		u.k.addKey(overlay.x_close, "ESC");
	}
	overlay._resized();
	u.ass(overlay, {
		"transition": "opacity .4s ease-in-out .1s",
		"opacity": 1,
	});
	return overlay;
}


/*u-preloader.js*/
u.preloader = function(node, files, _options) {
	var callback_preloader_loaded = "loaded";
	var callback_preloader_loading = "loading";
	var callback_preloader_waiting = "waiting";
	node._callback_min_delay = 0;
	if(obj(_options)) {
		var _argument;
		for(_argument in _options) {
			switch(_argument) {
				case "loaded"               : callback_preloader_loaded       = _options[_argument]; break;
				case "loading"              : callback_preloader_loading      = _options[_argument]; break;
				case "waiting"              : callback_preloader_waiting      = _options[_argument]; break;
				case "callback_min_delay"   : node._callback_min_delay              = _options[_argument]; break;
			}
		}
	}
	if(!u._preloader_queue) {
		u._preloader_queue = document.createElement("div");
		u._preloader_processes = 0;
		if(u.e && u.e.event_support == "touch") {
			u._preloader_max_processes = 1;
		}
		else {
			u._preloader_max_processes = 2;
		}
	}
	if(node && files) {
		var entry, file;
		var new_queue = u.ae(u._preloader_queue, "ul");
		new_queue._callback_loaded = callback_preloader_loaded;
		new_queue._callback_loading = callback_preloader_loading;
		new_queue._callback_waiting = callback_preloader_waiting;
		new_queue._node = node;
		new_queue._files = files;
		new_queue.nodes = new Array();
		new_queue._start_time = new Date().getTime();
		for(i = 0; i < files.length; i++) {
			file = files[i];
			entry = u.ae(new_queue, "li", {"class":"waiting"});
			entry.i = i;
			entry._queue = new_queue
			entry._file = file;
		}
		u.ac(node, "waiting");
		if(fun(node[new_queue._callback_waiting])) {
			node[new_queue._callback_waiting](new_queue.nodes);
		}
	}
	u._queueLoader();
	return u._preloader_queue;
}
u._queueLoader = function() {
	if(u.qs("li.waiting", u._preloader_queue)) {
		while(u._preloader_processes < u._preloader_max_processes) {
			var next = u.qs("li.waiting", u._preloader_queue);
			if(next) {
				if(u.hc(next._queue._node, "waiting")) {
					u.rc(next._queue._node, "waiting");
					u.ac(next._queue._node, "loading");
					if(fun(next._queue._node[next._queue._callback_loading])) {
						next._queue._node[next._queue._callback_loading](next._queue.nodes);
					}
				}
				u._preloader_processes++;
				u.rc(next, "waiting");
				u.ac(next, "loading");
				if(next._file.match(/png|jpg|gif|svg|avif|webp/)) {
					next.loaded = function(event) {
						this.image = event.target;
						this._image = this.image;
						this._queue.nodes[this.i] = this;
						u.rc(this, "loading");
						u.ac(this, "loaded");
						u._preloader_processes--;
						if(!u.qs("li.waiting,li.loading", this._queue)) {
							u.rc(this._queue._node, "loading");
							if(fun(this._queue._node[this._queue._callback_loaded])) {
								this._queue._node[this._queue._callback_loaded](this._queue.nodes);
							}
							// 
						}
						u._queueLoader();
					}
					u.loadImage(next, next._file);
				}
				else if(next._file.match(/mp3|aac|wav|ogg/)) {
					next.loaded = function(event) {
						console.log(event);
						this._queue.nodes[this.i] = this;
						u.rc(this, "loading");
						u.ac(this, "loaded");
						u._preloader_processes--;
						if(!u.qs("li.waiting,li.loading", this._queue)) {
							u.rc(this._queue._node, "loading");
							if(fun(this._queue._node[this._queue._callback_loaded])) {
								this._queue._node[this._queue._callback_loaded](this._queue.nodes);
							}
						}
						u._queueLoader();
					}
					if(fun(u.audioPlayer)) {
						next.audioPlayer = u.audioPlayer();
						next.load(next._file);
					}
					else {
						u.bug("You need u.audioPlayer to preload MP3s");
					}
				}
				else {
				}
			}
			else {
				break
			}
		}
	}
}
u.loadImage = function(node, src) {
	var image = new Image();
	image.node = node;
	u.ac(node, "loading");
    u.e.addEvent(image, 'load', u._imageLoaded);
	u.e.addEvent(image, 'error', u._imageLoadError);
	image.src = src;
}
u._imageLoaded = function(event) {
	u.rc(this.node, "loading");
	if(fun(this.node.loaded)) {
		this.node.loaded(event);
	}
}
u._imageLoadError = function(event) {
	u.rc(this.node, "loading");
	u.ac(this.node, "error");
	if(fun(this.node.loaded) && typeof(this.node.failed) != "function") {
		this.node.loaded(event);
	}
	else if(fun(this.node.failed)) {
		this.node.failed(event);
	}
}
u._imageLoadProgress = function(event) {
	u.bug("progress")
	if(fun(this.node.progress)) {
		this.node.progress(event);
	}
}
u._imageLoadDebug = function(event) {
	u.bug("event:" + event.type);
	u.xInObject(event);
}


/*u-request.js*/
Util.createRequestObject = function() {
	return new XMLHttpRequest();
}
Util.request = function(node, url, _options) {
	var request_id = u.randomString(6);
	node[request_id] = {};
	node[request_id].request_url = url;
	node[request_id].request_method = "GET";
	node[request_id].request_async = true;
	node[request_id].request_data = "";
	node[request_id].request_headers = false;
	node[request_id].request_credentials = false;
	node[request_id].response_type = false;
	node[request_id].callback_response = "response";
	node[request_id].callback_error = "responseError";
	node[request_id].jsonp_callback = "callback";
	node[request_id].request_timeout = false;
	if(obj(_options)) {
		var argument;
		for(argument in _options) {
			switch(argument) {
				case "method"				: node[request_id].request_method			= _options[argument]; break;
				case "params"				: node[request_id].request_data				= _options[argument]; break;
				case "data"					: node[request_id].request_data				= _options[argument]; break;
				case "async"				: node[request_id].request_async			= _options[argument]; break;
				case "headers"				: node[request_id].request_headers			= _options[argument]; break;
				case "credentials"			: node[request_id].request_credentials		= _options[argument]; break;
				case "responseType"			: node[request_id].response_type			= _options[argument]; break;
				case "callback"				: node[request_id].callback_response		= _options[argument]; break;
				case "error_callback"		: node[request_id].callback_error			= _options[argument]; break;
				case "jsonp_callback"		: node[request_id].jsonp_callback			= _options[argument]; break;
				case "timeout"				: node[request_id].request_timeout			= _options[argument]; break;
			}
		}
	}
	if(node[request_id].request_method.match(/GET|POST|PUT|PATCH/i)) {
		node[request_id].HTTPRequest = this.createRequestObject();
		node[request_id].HTTPRequest.node = node;
		node[request_id].HTTPRequest.request_id = request_id;
		if(node[request_id].request_async) {
			node[request_id].HTTPRequest.statechanged = function() {
				if(this.readyState == 4 || this.IEreadyState) {
					u.validateResponse(this);
				}
			}
			if(fun(node[request_id].HTTPRequest.addEventListener)) {
				u.e.addEvent(node[request_id].HTTPRequest, "readystatechange", node[request_id].HTTPRequest.statechanged);
			}
		}
		try {
			if(node[request_id].request_method.match(/GET/i)) {
				var params = u.JSONtoParams(node[request_id].request_data);
				node[request_id].request_url += params ? ((!node[request_id].request_url.match(/\?/g) ? "?" : "&") + params) : "";
				node[request_id].HTTPRequest.open(node[request_id].request_method, node[request_id].request_url, node[request_id].request_async);
				if(node[request_id].response_type) {
					node[request_id].HTTPRequest.responseType = node[request_id].response_type;
				}
				if(node[request_id].request_timeout) {
					node[request_id].HTTPRequest.timeout = node[request_id].request_timeout;
				}
				if(node[request_id].request_credentials) {
					node[request_id].HTTPRequest.withCredentials = true;
				}
				if(typeof(node[request_id].request_headers) != "object" || (!node[request_id].request_headers["Content-Type"] && !node[request_id].request_headers["content-type"])) {
					node[request_id].HTTPRequest.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
				}
				if(obj(node[request_id].request_headers)) {
					var header;
					for(header in node[request_id].request_headers) {
						node[request_id].HTTPRequest.setRequestHeader(header, node[request_id].request_headers[header]);
					}
				}
				node[request_id].HTTPRequest.setRequestHeader("X-Requested-With", "XMLHttpRequest");
				node[request_id].HTTPRequest.send("");
			}
			else if(node[request_id].request_method.match(/POST|PUT|PATCH|DELETE/i)) {
				var params;
				if(obj(node[request_id].request_data) && node[request_id].request_data.constructor.toString().match(/function Object/i)) {
					params = JSON.stringify(node[request_id].request_data);
				}
				else {
					params = node[request_id].request_data;
				}
				node[request_id].HTTPRequest.open(node[request_id].request_method, node[request_id].request_url, node[request_id].request_async);
				if(node[request_id].response_type) {
					node[request_id].HTTPRequest.responseType = node[request_id].response_type;
				}
				if(node[request_id].request_timeout) {
					node[request_id].HTTPRequest.timeout = node[request_id].request_timeout;
				}
				if(node[request_id].request_credentials) {
					node[request_id].HTTPRequest.withCredentials = true;
				}
				if(!params.constructor.toString().match(/FormData/i) && (typeof(node[request_id].request_headers) != "object" || (!node[request_id].request_headers["Content-Type"] && !node[request_id].request_headers["content-type"]))) {
					node[request_id].HTTPRequest.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
				}
				if(obj(node[request_id].request_headers)) {
					var header;
					for(header in node[request_id].request_headers) {
						node[request_id].HTTPRequest.setRequestHeader(header, node[request_id].request_headers[header]);
					}
				}
				node[request_id].HTTPRequest.setRequestHeader("X-Requested-With", "XMLHttpRequest");
				node[request_id].HTTPRequest.send(params);
			}
		}
		catch(exception) {
			node[request_id].HTTPRequest.exception = exception;
			u.validateResponse(node[request_id].HTTPRequest);
			return;
		}
		if(!node[request_id].request_async) {
			u.validateResponse(node[request_id].HTTPRequest);
		}
	}
	else if(node[request_id].request_method.match(/SCRIPT/i)) {
		if(node[request_id].request_timeout) {
			node[request_id].timedOut = function(requestee) {
				this.status = 0;
				delete this.timedOut;
				delete this.t_timeout;
				Util.validateResponse({node: requestee.node, request_id: requestee.request_id, status:this.status});
			}
			node[request_id].t_timeout = u.t.setTimer(node[request_id], "timedOut", node[request_id].request_timeout, {node: node, request_id: request_id});
		}
		var key = u.randomString();
		document[key] = new Object();
		document[key].key = key;
		document[key].node = node;
		document[key].request_id = request_id;
		document[key].responder = function(response) {
			var response_object = new Object();
			response_object.node = this.node;
			response_object.request_id = this.request_id;
			response_object.responseText = response;
			u.t.resetTimer(this.node[this.request_id].t_timeout);
			delete this.node[this.request_id].timedOut;
			delete this.node[this.request_id].t_timeout;
			u.qs("head").removeChild(this.node[this.request_id].script_tag);
			delete this.node[this.request_id].script_tag;
			delete document[this.key];
			u.validateResponse(response_object);
		}
		var params = u.JSONtoParams(node[request_id].request_data);
		node[request_id].request_url += params ? ((!node[request_id].request_url.match(/\?/g) ? "?" : "&") + params) : "";
		node[request_id].request_url += (!node[request_id].request_url.match(/\?/g) ? "?" : "&") + node[request_id].jsonp_callback + "=document."+key+".responder";
		node[request_id].script_tag = u.ae(u.qs("head"), "script", ({"type":"text/javascript", "src":node[request_id].request_url}));
	}
	return request_id;
}
Util.JSONtoParams = function(json) {
	if(obj(json)) {
		var params = "", param;
		for(param in json) {
			params += (params ? "&" : "") + param + "=" + json[param];
		}
		return params
	}
	var object = u.isStringJSON(json);
	if(object) {
		return u.JSONtoParams(object);
	}
	return json;
}
Util.evaluateResponseText = function(responseText) {
	var object;
	if(obj(responseText)) {
		responseText.isJSON = true;
		return responseText;
	}
	else {
		var response_string;
		if(responseText.trim().substr(0, 1).match(/[\"\']/i) && responseText.trim().substr(-1, 1).match(/[\"\']/i)) {
			response_string = responseText.trim().substr(1, responseText.trim().length-2);
		}
		else {
			response_string = responseText;
		}
		var json = u.isStringJSON(response_string);
		if(json) {
			return json;
		}
		var html = u.isStringHTML(response_string);
		if(html) {
			return html;
		}
		return responseText;
	}
}
Util.validateResponse = function(HTTPRequest){
	var object = false;
	if(HTTPRequest) {
		var node = HTTPRequest.node;
		var request_id = HTTPRequest.request_id;
		var request = node[request_id];
		request.response_url = HTTPRequest.responseURL || request.request_url;
		delete request.HTTPRequest;
		if(request.finished) {
			return;
		}
		request.finished = true;
		try {
			request.status = HTTPRequest.status;
			if(HTTPRequest.status && !HTTPRequest.status.toString().match(/[45][\d]{2}/)) {
				if(HTTPRequest.responseType && HTTPRequest.response) {
					object = HTTPRequest.response;
				}
				else if(HTTPRequest.responseText) {
					object = u.evaluateResponseText(HTTPRequest.responseText);
				}
			}
			else if(HTTPRequest.responseText && typeof(HTTPRequest.status) == "undefined") {
				object = u.evaluateResponseText(HTTPRequest.responseText);
			}
		}
		catch(exception) {
			request.exception = exception;
		}
	}
	else {
		console.log("Lost track of this request. There is no way of routing it back to requestee.")
		return;
	}
	if(object !== false) {
		if(fun(request.callback_response)) {
			request.callback_response(object, request_id);
		}
		else if(fun(node[request.callback_response])) {
			node[request.callback_response](object, request_id);
		}
	}
	else {
		if(fun(request.callback_error)) {
			request.callback_error({error:true,status:request.status}, request_id);
		}
		else if(fun(node[request.callback_error])) {
			node[request.callback_error]({error:true,status:request.status}, request_id);
		}
		else if(fun(request.callback_response)) {
			request.callback_response({error:true,status:request.status}, request_id);
		}
		else if(fun(node[request.callback_response])) {
			node[request.callback_response]({error:true,status:request.status}, request_id);
		}
	}
}


/*u-scrollto.js*/
u.scrollTo = function(node, _options) {
	node._callback_scroll_to = "scrolledTo";
	node._callback_scroll_cancelled = "scrollToCancelled";
	var offset_y = 0;
	var offset_x = 0;
	var scroll_to_x = 0;
	var scroll_to_y = 0;
	var to_node = false;
	node._force_scroll_to = false;
	if(obj(_options)) {
		var _argument;
		for(_argument in _options) {
			switch(_argument) {
				case "callback"             : node._callback_scroll_to            = _options[_argument]; break;
				case "callback_cancelled"   : node._callback_scroll_cancelled     = _options[_argument]; break;
				case "offset_y"             : offset_y                           = _options[_argument]; break;
				case "offset_x"             : offset_x                           = _options[_argument]; break;
				case "node"                 : to_node                            = _options[_argument]; break;
				case "x"                    : scroll_to_x                        = _options[_argument]; break;
				case "y"                    : scroll_to_y                        = _options[_argument]; break;
				case "scrollIn"             : scrollIn                           = _options[_argument]; break;
				case "force"                : node._force_scroll_to              = _options[_argument]; break;
			}
		}
	}
	if(to_node) {
		node._to_x = u.absX(to_node);
		node._to_y = u.absY(to_node);
	}
	else {
		node._to_x = scroll_to_x;
		node._to_y = scroll_to_y;
	}
	node._to_x = offset_x ? node._to_x - offset_x : node._to_x;
	node._to_y = offset_y ? node._to_y - offset_y : node._to_y;
	if (Util.support("scrollBehavior")) {
		var test = node.scrollTo({top:node._to_y, left:node._to_x, behavior: 'smooth'});
	}
	else {
		if(node._to_y > (node == window ? document.body.scrollHeight : node.scrollHeight)-u.browserH()) {
			node._to_y = (node == window ? document.body.scrollHeight : node.scrollHeight)-u.browserH();
		}
		if(node._to_x > (node == window ? document.body.scrollWidth : node.scrollWidth)-u.browserW()) {
			node._to_x = (node == window ? document.body.scrollWidth : node.scrollWidth)-u.browserW();
		}
		node._to_x = node._to_x < 0 ? 0 : node._to_x;
		node._to_y = node._to_y < 0 ? 0 : node._to_y;
		node._x_scroll_direction = node._to_x - u.scrollX();
		node._y_scroll_direction = node._to_y - u.scrollY();
		node._scroll_to_x = u.scrollX();
		node._scroll_to_y = u.scrollY();
		node._ignoreWheel = function(event) {
			u.e.kill(event);
		}
		if(node._force_scroll_to) {
			u.e.addEvent(node, "wheel", node._ignoreWheel);
		}
		node._scrollToHandler = function(event) {
			u.t.resetTimer(this.t_scroll);
			this.t_scroll = u.t.setTimer(this, this._scrollTo, 25);
		}
		node._cancelScrollTo = function() {
			if(!this._force_scroll_to) {
				u.t.resetTimer(this.t_scroll);
				this._scrollTo = null;
			}
		}
		node._scrollToFinished = function() {
			u.t.resetTimer(this.t_scroll);
			u.e.removeEvent(this, "wheel", this._ignoreWheel);
			this._scrollTo = null;
		}
		node._ZoomScrollFix = function(s_x, s_y) {
			if(Math.abs(this._scroll_to_y - s_y) <= 2 && Math.abs(this._scroll_to_x - s_x) <= 2) {
				return true;
			}
			return false;
		}
		node._scrollTo = function(start) {
			var s_x = u.scrollX();
			var s_y = u.scrollY();
			if((s_y == this._scroll_to_y && s_x == this._scroll_to_x) || this._force_scroll_to || this._ZoomScrollFix(s_x, s_y)) {
				if(this._x_scroll_direction > 0 && this._to_x > s_x) {
					this._scroll_to_x = Math.ceil(this._scroll_to_x + (this._to_x - this._scroll_to_x)/6);
				}
				else if(this._x_scroll_direction < 0 && this._to_x < s_x) {
					this._scroll_to_x = Math.floor(this._scroll_to_x - (this._scroll_to_x - this._to_x)/6);
				}
				else {
					this._scroll_to_x = this._to_x;
				}
				if(this._y_scroll_direction > 0 && this._to_y > s_y) {
					this._scroll_to_y = Math.ceil(this._scroll_to_y + (this._to_y - this._scroll_to_y)/6);
				}
				else if(this._y_scroll_direction < 0 && this._to_y < s_y) {
					this._scroll_to_y = Math.floor(this._scroll_to_y - (this._scroll_to_y - this._to_y)/6);
				}
				else {
					this._scroll_to_y = this._to_y;
				}
				if(this._scroll_to_x == this._to_x && this._scroll_to_y == this._to_y) {
					this._scrollToFinished();
					this.scrollTo(this._to_x, this._to_y);
					if(fun(this[this._callback_scroll_to])) {
						this[this._callback_scroll_to]();
					}
					return;
				}
				this.scrollTo(this._scroll_to_x, this._scroll_to_y);
				this._scrollToHandler();
			}
			else {
				this._cancelScrollTo();
				if(fun(this[this._callback_scroll_cancelled])) {
					this[this._callback_scroll_cancelled]();
				}
			}	
		}
		node._scrollTo();
	}
}

/*u-sortable.js*/
u.sortable = function(scope, _options) {
	scope._callback_picked = "picked";
	scope._callback_moved = "moved";
	scope._callback_dropped = "dropped";
	scope._draggable_selector;
	scope._target_selector;
	scope._layout;
	scope._allow_clickpick = false;
	scope._allow_nesting = false;
	scope._sorting_disabled = false;
	scope._distance_to_pick = 2;
	if(obj(_options)) {
		var _argument;
		for(_argument in _options) {
			switch(_argument) {
				case "picked"				: scope._callback_picked		= _options[_argument]; break;
				case "moved"				: scope._callback_moved			= _options[_argument]; break;
				case "dropped"				: scope._callback_dropped		= _options[_argument]; break;
				case "draggables"			: scope._draggable_selector		= _options[_argument]; break;
				case "targets"				: scope._target_selector		= _options[_argument]; break;
				case "layout"				: scope._layout					= _options[_argument]; break;
				case "allow_clickpick"		: scope._allow_clickpick		= _options[_argument]; break;
				case "allow_nesting"		: scope._allow_nesting			= _options[_argument]; break;
				case "sorting_disabled"		: scope._sorting_disabled		= _options[_argument]; break;
				case "distance_to_pick"		: scope._distance_to_pick		= _options[_argument]; break;
			}
		}
	}
	if(!fun(scope.resetSortableEvents)) {
		scope._sortableInputStart = function(event) {
			if(!this.draggable_node.scope._sorting_disabled) {
				this.draggable_node._start_event_x = u.eventX(event);
				this.draggable_node._start_event_y = u.eventY(event);
				this.draggable_node.current_xps = 0;
				this.draggable_node.current_yps = 0;
				this.draggable_node._move_timestamp = event.timeStamp;
				this.draggable_node._move_last_x = 0;
				this.draggable_node._move_last_y = 0;
				u.e.addMoveEvent(this.draggable_node, this.draggable_node.scope._sortablePick);
				u.e.addEndEvent(this.draggable_node, this.draggable_node.scope._cancelSortablePick);
				if(event.type.match(/mouse/)) {
		 			u.e.addOutEvent(this.draggable_node.drag, this.draggable_node.scope._sortableOut);
				}
				this.draggable_node.scope._org_css_user_select = document.body.style.userSelect;
				u.ass(document.body, {
					"user-select": "none"
				});
			}
		}
		scope._cancelSortablePick = function(event) {
			if(!this.scope._allow_clickpick) {
				this.scope.resetSortableEvents(this);
				u.ass(document.body, {
					"user-select": this.scope._org_css_user_select
				});
			}
		}
		scope._sortableOut = function(event) {
			var edoi = this.draggable_node._event_drop_out_id = u.randomString();
			document["_DroppedOutNode" + edoi] = this.draggable_node;
			eval('document["_DroppedOutMove' + edoi + '"] = function(event) {document["_DroppedOutNode' + edoi + '"].scope._sortablePick.bind(document["_DroppedOutNode' + edoi + '"])(event);}');
			u.e.addEvent(document, "mousemove", document["_DroppedOutMove" + edoi]);
			eval('document["_DroppedOutOver' + edoi + '"] = function(event) {document["_DroppedOutNode' + edoi + '"].scope.resetSortableOutEvents(document["_DroppedOutNode' + edoi + '"]);}');
			u.e.addEvent(this.draggable_node, "mouseover", document["_DroppedOutOver" + edoi]);
			eval('document["_DroppedOutEnd' + edoi + '"] = function(event) {u.bug("### up save");document["_DroppedOutNode' + edoi + '"].scope._cancelSortablePick.bind(document["_DroppedOutNode' + edoi + '"])(event);}');
			u.e.addEvent(document, "mouseup", document["_DroppedOutEnd" + edoi]);
		}
		scope._sortablePick = function(event) {
			var event_x = u.eventX(event);
			var event_y = u.eventY(event);
			this.current_x = event_x - this._start_event_x;
			this.current_y = event_y - this._start_event_y;
			var init_distance_x = Math.abs(this.current_x);
			var init_distance_y = Math.abs(this.current_y);
			if((init_distance_x > this.scope._distance_to_pick || init_distance_y > this.scope._distance_to_pick)) {
				this.scope.resetNestedSortableEvents(this);
				u.e.kill(event);
				this.scope._dragged_node = this;
				this._mouse_ox = event_x - u.absX(this);
				this._mouse_oy = event_y - u.absY(this);
				this.current_xps = Math.round(((this.current_x - this._move_last_x) / (event.timeStamp - this._move_timestamp)) * 1000);
				this.current_yps = Math.round(((this.current_y - this._move_last_y) / (event.timeStamp - this._move_timestamp)) * 1000);
				this._move_timestamp = event.timeStamp;
				this._move_last_x = this.current_x;
				this._move_last_y = this.current_y;
				this.scope._shadow_node = u.ae(this.parentNode, this.cloneNode(true));
				this.parentNode.insertBefore(this.scope._shadow_node, this);
				u.ac(this.scope._shadow_node, "shadow");
				this.scope._recalculateRelativeShadowOffset();
				var _start_width = u.gcs(this, "width");
				var _z_index;
				if(this._z_index != "auto") {
					_z_index = this._z_index + 1;
				}
				else {
					_z_index = 55;
				}
				u.ass(this.scope._shadow_node, {
					width: _start_width,
					position: "absolute",
					left: ((event_x - this.scope._shadow_node._rel_ox) - this._mouse_ox) + "px",
					top: ((event_y - this.scope._shadow_node.rel_oy) - this._mouse_oy) + "px",
					"z-index": _z_index,
				});
				u.ac(this, "dragged");
				this._event_move_id = u.e.addWindowMoveEvent(this, this.scope._sortableDrag);
				this._event_end_id = u.e.addWindowEndEvent(this, this.scope._sortableDrop);
				if(fun(this.scope[this.scope._callback_picked])) {
					this.scope[this.scope._callback_picked](this);
				}
			}
		}
		scope._sortableDrag = function(event) {
			var i, node;
			var event_x = u.eventX(event);
			var event_y = u.eventY(event);
			var d_left = event_x - this._mouse_ox;
			var d_top = event_y - this._mouse_oy;
			this.current_x = event_x - this._start_event_x;
			this.current_y = event_y - this._start_event_y;
			this.current_xps = Math.round(((this.current_x - this._move_last_x) / (event.timeStamp - this._move_timestamp)) * 1000);
			this.current_yps = Math.round(((this.current_y - this._move_last_y) / (event.timeStamp - this._move_timestamp)) * 1000);
			this._move_timestamp = event.timeStamp;
			this._move_last_x = this.current_x;
			this._move_last_y = this.current_y;
			// 	
			// 		
			// 		
			// 
			// 	
			// 		
			// 		
			// 
			this.scope._detectAndInject(event_x, event_y);
			u.ass(this.scope._shadow_node, {
				"position": "absolute",
				"left": (d_left - this.scope._shadow_node._rel_ox)+"px",
				"top": (d_top - this.scope._shadow_node._rel_oy)+"px",
				"bottom": "auto"
			});
			if(fun(this.scope[this.scope._callback_moved])) {
				this.scope[this.scope._callback_moved](this);
			}
		}
		scope._sortableDrop = function(event) {
			u.e.kill(event);
			this.scope.resetSortableEvents(this);
			this.scope._shadow_node.parentNode.removeChild(this.scope._shadow_node);
			delete this.scope._shadow_node;
			u.rc(this, "dragged");
			this.scope._dragged_node = false;
			this.current_xps = 0;
			this.current_yps = 0;
			this._move_timestamp = event.timeStamp;
			this._move_last_x = 0;
			this._move_last_y = 0;
			this.scope.updateDraggables();
			u.ass(document.body, {
				"user-select": this.scope._org_css_user_select
			});
			if(fun(this.scope[this.scope._callback_dropped])) {
				this.scope[this.scope._callback_dropped](this);
			}
		}
		scope._recalculateRelativeShadowOffset = function() {
			if(this._shadow_node) {
				this._shadow_node._rel_ox = u.absX(this._shadow_node) - u.relX(this._shadow_node);
				this._shadow_node._rel_oy = u.absY(this._shadow_node) - u.relY(this._shadow_node);
			}
		}
		scope._detectAndInject = function(event_x, event_y) {
			for(i = this.draggable_nodes.length-1; i >= 0; i--) {
				node = this.draggable_nodes[i];
				if(this.target_nodes.indexOf(node.parentNode) !== -1) {
					if(node.parentNode._layout == "multiline") {
						var o_left = u.absX(node);
						var o_top = u.absY(node);
						var o_width = node.offsetWidth;
						var o_height = node.offsetHeight;
					 	if(event_x > o_left && event_x < o_left + o_width && event_y > o_top && event_y < o_top + o_height) {
							if(node !== this._dragged_node) {
								if(event_x < o_left + o_width/2) {
									node.parentNode.insertBefore(this._dragged_node, node);
								}
								else {
									var next = u.ns(node, {exclude: ".target,.dragged"});
									if(next) {
										node.parentNode.insertBefore(this._dragged_node, next);
									}
									else {
										node.parentNode.appendChild(this._dragged_node);
									}
								}
								this._recalculateRelativeShadowOffset();
								break;
							}
						}
					}
					else if(node.parentNode._layout == "horizontal") {
						var o_left = u.absX(node);
						var o_width = node.offsetWidth;
					 	if(event_x > o_left && event_x < o_left + o_width) {
							if(node !== this._dragged_node && !u.pn(node, {include:".dragged"})) {
								if(event_x < o_left + o_width/2) {
									node.parentNode.insertBefore(this._dragged_node, node);
								}
								else {
									var next = u.ns(node, {exclude: ".target,.dragged"});
									if(next) {
										node.parentNode.insertBefore(this._dragged_node, next);
									}
									else {
										node.parentNode.appendChild(this._dragged_node);
									}
								}
							}
							this._recalculateRelativeShadowOffset();
							break;
						}
					}
					else {
						var o_top, o_height;
						if(this._allow_nesting) {
							o_top = u.absY(node) - node._extra_height_top;
							o_height = node._top_node_height + node._extra_height_top + node._extra_height_bottom;
						}
						else {
							o_top = u.absY(node);
							o_height = node._top_node_height;
						}
					 	if(event_y >= o_top && event_y <= o_top + o_height) {
							if(node !== this._dragged_node && !u.pn(node, {include:".dragged"})) {
								if(this._allow_nesting) {
									if(event_y < o_top + (o_height / 3) && (!node.sub_target || !node.sub_target.childNodes.length || this._dragged_node.current_yps < 0)) {
										node.parentNode.insertBefore(this._dragged_node, node);
									}
									else if(event_y > o_top + ((o_height / 3) * 2)) {
										var next = u.ns(node, {exclude:".target,.dragged"});
										if(next) {
											node.parentNode.insertBefore(this._dragged_node, next);
										}
										else {
											node.parentNode.appendChild(this._dragged_node);
										}
									}
									else {
										if(!node.sub_target) {
											node.sub_target = u.ae(node, "ul", {"class":this._target_selector.replace(/([a-z]*.?)/, "").replace(/\./g, " ")});
											this.target_nodes.push(node.sub_target);
										}
										node.sub_target.insertBefore(this._dragged_node, node.sub_target.firstChild);
									}
								}
								else {
									if(event_y < o_top + o_height/2) {
										node.parentNode.insertBefore(this._dragged_node, node);
									}
									else {
										var next = u.ns(node);
										if(next) {
											node.parentNode.insertBefore(this._dragged_node, next);
										}
										else {
											node.parentNode.appendChild(this._dragged_node);
										}
									}
								}
								this._recalculateRelativeShadowOffset();
								break;
							}
							else {
								break;
							}
						}
					}
				}
			}
		}
		scope.resetSortableEvents = function(node) {
			u.e.removeMoveEvent(node, this._sortablePick);
			u.e.removeEndEvent(node, this._cancelSortablePick);
			u.e.removeOverEvent(node, this._sortableOver);
			if(node._event_move_id) {
				u.e.removeWindowMoveEvent(node._event_move_id);
				delete node._event_move_id;
			}
			if(node._event_end_id) {
				u.e.removeWindowEndEvent(node._event_end_id);
				delete node._event_end_id;
			}
			u.e.removeOutEvent(node.drag, this._sortableOut);
			this.resetSortableOutEvents(node);
		}
		scope.resetSortableOutEvents = function(node) {
			if(node._event_drop_out_id) {
				u.e.removeEvent(document, "mousemove", document["_DroppedOutMove" + node._event_drop_out_id]);
				u.e.removeEvent(node, "mouseover", document["_DroppedOutOver" + node._event_drop_out_id]);
				u.e.removeEvent(document, "mouseup", document["_DroppedOutEnd" + node._event_drop_out_id]);
				delete document["_DroppedOutMove" + node._event_drop_out_id];
				delete document["_DroppedOutOver" + node._event_drop_out_id];
				delete document["_DroppedOutEnd" + node._event_drop_out_id];
				delete document["_DroppedOutNode" + node._event_drop_out_id];
				delete node._event_drop_out_id;
			}
		}
		scope.resetNestedSortableEvents = function(node) {
			while(node && node != this) {
				if(node.drag) {
					this.resetSortableEvents(node);
				}
				node = node.parentNode;
			}
		}
		scope.getNodeOrder = function(_options) {
			var class_var = "item_id";
			var data_attribute = false;
			var node_property = false;
			if(obj(_options)) {
				var _argument;
				for(_argument in _options) {
					switch(_argument) {
						case "class_var"			: class_var 				= _options[_argument]; break;
						case "data_attribute"		: data_attribute 			= _options[_argument]; break;
						case "node_property"		: node_property 			= _options[_argument]; break;
					}
				}
			}
			this.updateDraggables();
			var order = [];
			var i, node, id;
			for(i = 0; i < this.draggable_nodes.length; i++) {
				node = this.draggable_nodes[i];
				if(node_property) {
					id = node[node_property];
				}
				else if(data_attribute) {
					id = node.getAttribute("data-"+data_attribute);
				}
				else {
					id = u.cv(node, class_var);
				}
				if(id) {
					order.push(id);
				}
				else {
					order.push(node);
				}
			}
			return order;
		}
		scope.getNodeRelations = function(_options) {
			var class_var = "item_id";
			if(obj(_options)) {
				var _argument;
				for(_argument in _options) {
					switch(_argument) {
						case "class_var"			: class_var 		= _options[_argument]; break;
					}
				}
			}
			this.updateDraggables();
			var structure = [];
			var i, node, id, relation, position;
			for(i = 0; i < this.draggable_nodes.length; i++) {
				node = this.draggable_nodes[i];
				id = u.cv(node, class_var);
				relation = this.getNodeRelation(node);
				position = this.getNodePositionInList(node);
				if(id) {
					structure.push({"id": id, "relation": relation, "position": position});
				}
				else {
					structure.push({"node": node, "relation": relation, "position": position});
				}
			}
			return structure;
		}
		scope.getNodePositionInList = function(node) {
			var pos = 1;
			var test_node = node;
			while(u.ps(test_node)) {
				test_node = u.ps(test_node);
				pos++;
			}
			return pos;
		}
		scope.getNodeRelation = function(node) {
			var relation = 0;
			var relation_node = u.pn(node, {"include":(this._draggable_selector ? this._draggable_selector : "li")});
			if(u.inNodeList(relation_node, this.draggable_nodes)) {
				var id = u.cv(relation_node, "item_id");
				if(id) {
					relation = id;
				}
				else {
					relation = relation_node;
				}
			}
			return relation;
		}
		scope.detectSortableLayout = function() {
			var i, target;
			for(i = 0; i < this.target_nodes.length; i++) {
				target = this.target_nodes[i];
					if((target._n_top || target._n_bottom) && (u.cn(target, {include: this._draggable_selector}).length > 1 || target._n_display != "block")) {
						target._layout = "horizontal";
					}
					else if(target._n_left || target._n_right) {
						target._layout = "vertical";
					}
					else {
						target._layout = "multiline";
					}
			}
		}
		scope.updateDraggables = function() {
			var i, target, draggable_node;
			if(this.draggable_nodes && this.draggable_nodes.length) {
				for(i = 0; i < this.draggable_nodes.length; i++) {
					draggable_node = this.draggable_nodes[i];
					if(draggable_node && draggable_node.drag) {
						this.resetSortableEvents(draggable_node);
						u.e.removeStartEvent(draggable_node.drag, this._sortableInputStart);
						u.e.removeOverEvent(draggable_node, this._sortableOver);
						delete draggable_node.drag;
						delete draggable_node.sub_target;
						delete draggable_node.draggable_node;
					}
				}
			}
			delete scope.draggable_nodes;
			if(this._draggable_selector) {
				this.draggable_nodes = Array.prototype.slice.call(u.qsa(this._draggable_selector, this));
			}
			else {
				if(this.nodeName.toLowerCase() === "ul") {
					this.draggable_nodes = u.cn(this, {include:"li"});
				}
				else {
					this.draggable_nodes = [];
					for(i = 0; i < this.target_nodes.length; i++) {
						target = this.target_nodes[i];
						this.draggable_nodes = this.draggable_nodes.concat(u.cn(target, {include:"li"}));
					}
				}
			}
			for(i = 0; i < this.draggable_nodes.length; i++) {
				draggable_node = this.draggable_nodes[i];
				draggable_node.scope = this;
				draggable_node.drag = u.qs(".drag", draggable_node);
				if(!draggable_node.drag) {
					draggable_node.drag = draggable_node;
				}
				draggable_node.drag.draggable_node = draggable_node;
				draggable_node.draggable_node = draggable_node;
				var _top = draggable_node.offsetTop;
				var _height = draggable_node.offsetHeight;
				var _left = draggable_node.offsetLeft;
				var _width = draggable_node.offsetWidth;
				var _display = u.gcs(draggable_node, "display");
				draggable_node.parentNode._n_top = draggable_node.parentNode._n_top === undefined ? _top : (draggable_node.parentNode._n_top == _top ? draggable_node.parentNode._n_top : false);
				draggable_node.parentNode._n_left = draggable_node.parentNode._n_left === undefined ? _left : (draggable_node.parentNode._n_left == _left ? draggable_node.parentNode._n_left : false);
				draggable_node.parentNode._n_bottom = draggable_node.parentNode._n_bottom === undefined ? _top + _height : (draggable_node.parentNode._n_bottom == _top + _height ? draggable_node.parentNode._n_bottom : false);
				draggable_node.parentNode._n_right = draggable_node.parentNode._n_right === undefined ? _left + _width : (draggable_node.parentNode._n_right == _left + _width ? draggable_node.parentNode._n_right : false);
				draggable_node.parentNode._n_display = draggable_node.parentNode._n_display === undefined ? _display : (draggable_node.parentNode._n_display == _display ? draggable_node.parentNode._n_display : false);
				draggable_node._z_index = u.gcs(draggable_node, "zIndex");
				if(this._allow_nesting) {
					draggable_node.sub_target = u.qs(this._target_selector, draggable_node);
					if(draggable_node.sub_target) {
						var _position = u.gcs(draggable_node, "position");
						var node_height = _height - draggable_node.sub_target.offsetHeight;
						if(_position !== "static") {
							draggable_node._top_node_height = node_height - (node_height - draggable_node.sub_target.offsetTop);
						}
						else {
							draggable_node._top_node_height = node_height - (node_height - (draggable_node.sub_target.offsetTop - _top));
						}
					}
					else {
						draggable_node._top_node_height = _height;
					}
					var _margin_top = parseInt(u.gcs(draggable_node, "margin-top"));
					var _margin_bottom = parseInt(u.gcs(draggable_node, "margin-bottom"));
					var _box_sizing = u.gcs(draggable_node, "box-sizing");
					if(_box_sizing == "content-box") {
						var _border_top_width = parseInt(u.gcs(draggable_node, "border-top-width"));
						var _border_bottom_width = parseInt(u.gcs(draggable_node, "border-bottom-width"));
						draggable_node._extra_height_top = _margin_top + _border_top_width;
						draggable_node._extra_height_bottom = _margin_bottom + _border_bottom_width;
					}
					else {
						draggable_node._extra_height_top = _start_margin_top;
						draggable_node._extra_height_bottom = _start_margin_bottom;
					}
				}
				else {
					draggable_node._top_node_height = _height;
				}
				u.e.addStartEvent(draggable_node.drag, this._sortableInputStart);
			}
		}
		scope.updateTargets = function() {
			if(this._target_selector) {
				this.target_nodes = Array.prototype.slice.call(u.qsa(this._target_selector, this));
				if(u.elementMatches(this, this._target_selector)) {
					this.target_nodes.unshift(this);
				}
			}
			else {
				if(this.nodeName.toLowerCase() === "ul") {
					this.target_nodes = [this];
				}
				else {
					var i, target, target_nodes, parent_ul;
					this.target_nodes = [];
					target_nodes = u.qsa("ul", this);
					for(i = 0; i < target_nodes.length; i++) {
						target = target_nodes[i];
						if(this._allow_nesting) {
							this.target_nodes.push(target);
						}
						else {
							parent_ul = u.pn(target, {include:"ul"});
							if(!parent_ul || !u.contains(this, parent_ul)) {
								this.target_nodes.push(target);
							}
						}
					}
				}
			}
		}
	}
	scope.updateTargets();
	scope.updateDraggables();
	scope.detectSortableLayout();
	if(!scope.draggable_nodes.length || !scope.target_nodes.length) {
		return;
	}
}


/*u-string.js*/
Util.cutString = function(string, length) {
	var matches, match, i;
	if(string.length <= length) {
		return string;
	}
	else {
		length = length-3;
	}
	matches = string.match(/\&[\w\d]+\;/g);
	if(matches) {
		for(i = 0; i < matches.length; i++){
			match = matches[i];
			if(string.indexOf(match) < length){
				length += match.length-1;
			}
		}
	}
	return string.substring(0, length) + (string.length > length ? "..." : "");
}
Util.prefix = function(string, length, prefix) {
	string = string.toString();
	prefix = prefix ? prefix : "0";
	while(string.length < length) {
		string = prefix + string;
	}
	return string;
}
Util.randomString = function(length) {
	var key = "", i;
	length = length ? length : 8;
	var pattern = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ".split('');
	for(i = 0; i < length; i++) {
		key += pattern[u.random(0,35)];
	}
	return key;
}
Util.uuid = function() {
	var chars = '0123456789abcdef'.split('');
	var uuid = [], rnd = Math.random, r, i;
	uuid[8] = uuid[13] = uuid[18] = uuid[23] = '-';
	uuid[14] = '4';
	for(i = 0; i < 36; i++) {
		if(!uuid[i]) {
			r = 0 | rnd()*16;
			uuid[i] = chars[(i == 19) ? (r & 0x3) | 0x8 : r & 0xf];
		}
 	}
	return uuid.join('');
}
Util.stringOr = u.eitherOr = function(value, replacement) {
	if(value !== undefined && value !== null) {
		return value;
	}
	else {
		return replacement ? replacement : "";
	}	
}
Util.getMatches = function(string, regex) {
	var match, matches = [];
	while(match = regex.exec(string)) {
		matches.push(match[1]);
	}
	return matches;
}
Util.upperCaseFirst = u.ucfirst = function(string) {
	return string.replace(/^(.){1}/, function($1) {return $1.toUpperCase()});
}
Util.lowerCaseFirst = u.lcfirst = function(string) {
	return string.replace(/^(.){1}/, function($1) {return $1.toLowerCase()});
}
Util.normalize = function(string) {
	var table = {
		'À':'A',  'à':'a',
		'Á':'A',  'á':'a',
		'Â':'A',  'â':'a',
		'Ã':'A',  'ã':'a',
		'Ä':'A',  'ä':'a',
		'Å':'Aa', 'å':'aa',
		'Æ':'Ae', 'æ':'ae',
		'Ç':'C',  'ç':'c',
		'Č':'C',  'ć':'c',
		'Ć':'C',  'č':'c',
		'Đ':'D',  'đ':'d',  'ð':'d',
		'È':'E',  'è':'e',
		'É':'E',  'é':'e',
		'Ê':'E',  'ê':'e',
		'Ë':'E',  'ë':'e',
		'Ģ':'G',  'ģ':'g',
		'Ğ':'G',  'ğ':'g',
		'Ì':'I',  'ì':'i',
		'Í':'I',  'í':'i',
		'Î':'I',  'î':'i',
		'Ï':'I',  'ï':'i',
		'Ī':'I',  'ī':'i',
		'Ķ':'K',  'ķ':'k',
		'Ļ':'L',  'ļ':'l',
		'Ñ':'N',  'ñ':'n',
		'Ņ':'N',  'ņ':'n',
		'Ò':'O',  'ò':'o',
		'Ó':'O',  'ó':'o',
		'Ô':'O',  'ô':'o',
		'Õ':'O',  'õ':'o',
		'Ö':'O',  'ö':'o',
		'Ō':'O',  'ō':'o',
		'Ø':'Oe', 'ø':'oe',
		'Ŕ':'R',  'ŕ':'r',
		'Š':'S',  'š':'s',
		'Ş':'S',  'ş':'s',
		'Ṩ':'S',  'ṩ':'s',
		'Ù':'U',  'ù':'u',
		'Ú':'U',  'ú':'u',
		'Û':'U',  'û':'u',
		'Ü':'U',  'ü':'u',
		'Ū':'U',  'ū':'u',
		'Ų':'U',  'ų':'u',
		'Ŭ':'U',  'ŭ':'u',
		'Ý':'Y',  'ý':'y',
		'Ÿ':'Y',  'ÿ':'y',
		'Ž':'Z',  'ž':'z',
		'Þ':'B',  'þ':'b',
		'ß':'Ss',
		'@':' at ',
		'&':'and',
		'%':' percent',
		'\\$':'USD',
		'¥':'JPY',
		'€':'EUR',
		'£':'GBP',
		'™':'trademark',
		'©':'copyright',
		'§':'s',
		'\\*':'x',
		'×':'x'
	}
	var char, regex;
	for(char in table) {
		regex = new RegExp(char, "g");
		string = string.replace(regex, table[char]);
	}
	return string;
}
Util.superNormalize = function(string) {
	string = u.normalize(string);
	string = string.toLowerCase();
	string = u.stripTags(string);
	string = string.replace(/[^a-z0-9\_]/g, '-');
	string = string.replace(/-+/g, '-');
	string = string.replace(/^-|-$/g, '');
	return string;
}
Util.stripTags = function(string) {
	var node = document.createElement("div");
	node.innerHTML = string;
	return u.text(node);
}
Util.pluralize = function(count, singular, plural) {
	if(count != 1) {
		return count + " " + plural;
	}
	return count + " " + singular;
}
Util.isStringJSON = function(string) {
	if(string.trim().substr(0, 1).match(/[\{\[]/i) && string.trim().substr(-1, 1).match(/[\}\]]/i)) {
		try {
			var test = JSON.parse(string);
			if(obj(test)) {
				test.isJSON = true;
				return test;
			}
		}
		catch(exception) {
			console.log(exception)
		}
	}
	return false;
}
Util.isStringHTML = function(string) {
	if(string.trim().substr(0, 1).match(/[\<]/i) && string.trim().substr(-1, 1).match(/[\>]/i)) {
		try {
			var test = document.createElement("div");
			test.innerHTML = string;
			if(test.childNodes.length) {
				var body_class = string.match(/<body class="([a-z0-9A-Z_: ]+)"/);
				test.body_class = body_class ? body_class[1] : "";
				var head_title = string.match(/<title>([^$]+)<\/title>/);
				test.head_title = head_title ? head_title[1] : "";
				test.isHTML = true;
				return test;
			}
		}
		catch(exception) {}
	}
	return false;
}


/*u-svg.js*/
Util.svg = function(svg_object) {
	var svg, shape, svg_shape;
	if(svg_object.name && u._svg_cache && u._svg_cache[svg_object.name]) {
		svg = u._svg_cache[svg_object.name].cloneNode(true);
	}
	if(!svg) {
		svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
		for(shape in svg_object.shapes) {
			Util.svgShape(svg, svg_object.shapes[shape]);
		}
		if(svg_object.name) {
			if(!u._svg_cache) {
				u._svg_cache = {};
			}
			u._svg_cache[svg_object.name] = svg.cloneNode(true);
		}
	}
	if(svg_object.title) {
		svg.setAttributeNS(null, "title", svg_object.title);
	}
	if(svg_object["class"]) {
		svg.setAttributeNS(null, "class", svg_object["class"]);
	}
	if(svg_object.width) {
		svg.setAttributeNS(null, "width", svg_object.width);
	}
	if(svg_object.height) {
		svg.setAttributeNS(null, "height", svg_object.height);
	}
	if(svg_object.id) {
		svg.setAttributeNS(null, "id", svg_object.id);
	}
	if(svg_object.viewBox) {
		svg.setAttributeNS(null, "viewBox", svg_object.viewBox);
	}
	if(svg_object.node) {
		svg.node = svg_object.node;
	}
	if(svg_object.node) {
		svg_object.node.appendChild(svg);
	}
	return svg;
}
Util.svgShape = function(svg, svg_object) {
	var detail, svg_shape;
	svg_shape = document.createElementNS("http://www.w3.org/2000/svg", svg_object["type"]);
	delete svg_object["type"];
	for(detail in svg_object) {
		svg_shape.setAttributeNS(null, detail, svg_object[detail]);
	}
	return svg.appendChild(svg_shape);
}


/*u-system.js*/
Util.browser = function(model, version) {
	var current_version = false;
	if(model.match(/\bedge\b/i)) {
		if(navigator.userAgent.match(/Windows[^$]+Gecko[^$]+Edge\/(\d+.\d)/i)) {
			current_version = navigator.userAgent.match(/Edge\/(\d+)/i)[1];
		}
	}
	if(model.match(/\bexplorer\b|\bie\b/i)) {
		if(window.ActiveXObject && navigator.userAgent.match(/MSIE (\d+.\d)/i)) {
			current_version = navigator.userAgent.match(/MSIE (\d+.\d)/i)[1];
		}
		else if(navigator.userAgent.match(/Trident\/[\d+]\.\d[^$]+rv:(\d+.\d)/i)) {
			current_version = navigator.userAgent.match(/Trident\/[\d+]\.\d[^$]+rv:(\d+.\d)/i)[1];
		}
	}
	if(model.match(/\bfirefox\b|\bgecko\b/i) && !u.browser("ie,edge")) {
		if(navigator.userAgent.match(/Firefox\/(\d+\.\d+)/i)) {
			current_version = navigator.userAgent.match(/Firefox\/(\d+\.\d+)/i)[1];
		}
	}
	if(model.match(/\bwebkit\b/i)) {
		if(navigator.userAgent.match(/WebKit/i) && !u.browser("ie,edge")) {
			current_version = navigator.userAgent.match(/AppleWebKit\/(\d+.\d)/i)[1];
		}
	}
	if(model.match(/\bchrome\b/i)) {
		if(window.chrome && !u.browser("ie,edge")) {
			current_version = navigator.userAgent.match(/Chrome\/(\d+)(.\d)/i)[1];
		}
	}
	if(model.match(/\bsafari\b/i)) {
		u.bug(navigator.userAgent);
		if(!window.chrome && navigator.userAgent.match(/WebKit[^$]+Version\/(\d+)(.\d)/i) && !u.browser("ie,edge")) {
			current_version = navigator.userAgent.match(/Version\/(\d+)(.\d)/i)[1];
		}
	}
	if(model.match(/\bopera\b/i)) {
		if(window.opera) {
			if(navigator.userAgent.match(/Version\//)) {
				current_version = navigator.userAgent.match(/Version\/(\d+)(.\d)/i)[1];
			}
			else {
				current_version = navigator.userAgent.match(/Opera[\/ ]{1}(\d+)(.\d)/i)[1];
			}
		}
	}
	if(current_version) {
		if(!version) {
			return current_version;
		}
		else {
			if(!isNaN(version)) {
				return current_version == version;
			}
			else {
				return eval(current_version + version);
			}
		}
	}
	else {
		return false;
	}
}
Util.segment = function(segment) {
	if(!u.current_segment) {
		var scripts = document.getElementsByTagName("script");
		var script, i, src;
		for(i = 0; i < scripts.length; i++) {
			script = scripts[i];
			seg_src = script.src.match(/\/seg_([a-z_]+)/);
			if(seg_src) {
				u.current_segment = seg_src[1];
			}
		}
	}
	if(segment) {
		return segment == u.current_segment;
	}
	return u.current_segment;
}
Util.system = function(os, version) {
	var current_version = false;
	if(os.match(/\bwindows\b/i)) {
		if(navigator.userAgent.match(/(Windows NT )(\d+.\d)/i)) {
			current_version = navigator.userAgent.match(/(Windows NT )(\d+.\d)/i)[2];
		}
	}
	else if(os.match(/\bmac\b/i)) {
		if(navigator.userAgent.match(/(Macintosh; Intel Mac OS X )(\d+[._]{1}\d)/i)) {
			current_version = navigator.userAgent.match(/(Macintosh; Intel Mac OS X )(\d+[._]{1}\d)/i)[2].replace("_", ".");
		}
	}
	else if(os.match(/\blinux\b/i)) {
		if(navigator.userAgent.match(/linux|x11/i) && !navigator.userAgent.match(/android/i)) {
			current_version = true;
		}
	}
	else if(os.match(/\bios\b/i)) {
		if(navigator.userAgent.match(/(OS )(\d+[._]{1}\d+[._\d]*)( like Mac OS X)/i)) {
			current_version = navigator.userAgent.match(/(OS )(\d+[._]{1}\d+[._\d]*)( like Mac OS X)/i)[2].replace(/_/g, ".");
		}
	}
	else if(os.match(/\bandroid\b/i)) {
		if(navigator.userAgent.match(/Android[ ._]?(\d+.\d)/i)) {
			current_version = navigator.userAgent.match(/Android[ ._]?(\d+.\d)/i)[1];
		}
	}
	else if(os.match(/\bwinphone\b/i)) {
		if(navigator.userAgent.match(/Windows[ ._]?Phone[ ._]?(\d+.\d)/i)) {
			current_version = navigator.userAgent.match(/Windows[ ._]?Phone[ ._]?(\d+.\d)/i)[1];
		}
	}
	if(current_version) {
		if(!version) {
			return current_version;
		}
		else {
			if(!isNaN(version)) {
				return current_version == version;
			}
			else {
				return eval(current_version + version);
			}
		}
	}
	else {
		return false;
	}
}
Util.support = function(property) {
	if(document.documentElement) {
		var style_property = u.lcfirst(property.replace(/^(-(moz|webkit|ms|o)-|(Moz|webkit|Webkit|ms|O))/, "").replace(/(-\w)/g, function(word){return word.replace(/-/, "").toUpperCase()}));
		if(style_property in document.documentElement.style) {
			return true;
		}
		else if(u.vendorPrefix() && (u.vendorPrefix()+u.ucfirst(style_property)) in document.documentElement.style) {
			return true;
		}
	}
	return false;
}
Util.vendor_properties = {};
Util.vendorProperty = function(property) {
	if(!Util.vendor_properties[property]) {
		Util.vendor_properties[property] = property.replace(/(-\w)/g, function(word){return word.replace(/-/, "").toUpperCase()});
		if(document.documentElement) {
			var style_property = u.lcfirst(property.replace(/^(-(moz|webkit|ms|o)-|(Moz|webkit|Webkit|ms|O))/, "").replace(/(-\w)/g, function(word){return word.replace(/-/, "").toUpperCase()}));
			if(style_property in document.documentElement.style) {
				Util.vendor_properties[property] = style_property;
			}
			else if(u.vendorPrefix() && (u.vendorPrefix()+u.ucfirst(style_property)) in document.documentElement.style) {
				Util.vendor_properties[property] = u.vendorPrefix()+u.ucfirst(style_property);
			}
		}
	}
	return Util.vendor_properties[property];
}
Util.vendor_prefix = false;
Util.vendorPrefix = function() {
	if(Util.vendor_prefix === false) {
		Util.vendor_prefix = "";
		if(document.documentElement && fun(window.getComputedStyle)) {
			var styles = window.getComputedStyle(document.documentElement, "");
			if(styles.length) {
				var i, style, match;
				for(i = 0; i < styles.length; i++) {
					style = styles[i];
					match = style.match(/^-(moz|webkit|ms)-/);
					if(match) {
						Util.vendor_prefix = match[1];
						if(Util.vendor_prefix == "moz") {
							Util.vendor_prefix = "Moz";
						}
						break;
					}
				}
			}
			else {
				var x, match;
				for(x in styles) {
					match = x.match(/^(Moz|webkit|ms|OLink)/);
					if(match) {
						Util.vendor_prefix = match[1];
						if(Util.vendor_prefix === "OLink") {
							Util.vendor_prefix = "O";
						}
						break;
					}
				}
			}
		}
	}
	return Util.vendor_prefix;
}


/*u-textscaler.js*/
u.textscaler = function(node, _settings) {
	if(typeof(_settings) != "object") {
		_settings = {
			"*":{
				"unit":"rem",
				"min_size":1,
				"min_width":200,
				"min_height":200,
				"max_size":40,
				"max_width":3000,
				"max_height":2000
			}
		};
	}
	node.text_key = u.randomString(8);
	u.ac(node, node.text_key);
	node.text_settings = JSON.parse(JSON.stringify(_settings));
	node.scaleText = function() {
		var tag;
		for(tag in this.text_settings) {
			var settings = this.text_settings[tag];
			var width_wins = false;
			var height_wins = false;
			if(settings.width_factor && settings.height_factor) {
				if(window._man_text._height - settings.min_height < window._man_text._width - settings.min_width) {
					height_wins = true;
				}
				else {
					width_wins = true;
				}
			}
			if(settings.width_factor && !height_wins) {
				if(settings.min_width <= window._man_text._width && settings.max_width >= window._man_text._width) {
					var font_size = settings.min_size + (settings.size_factor * (window._man_text._width - settings.min_width) / settings.width_factor);
					settings.css_rule.style.setProperty("font-size", font_size + settings.unit, "important");
				}
				else if(settings.max_width < window._man_text._width) {
					settings.css_rule.style.setProperty("font-size", settings.max_size + settings.unit, "important");
				}
				else if(settings.min_width > window._man_text._width) {
					settings.css_rule.style.setProperty("font-size", settings.min_size + settings.unit, "important");
				}
			}
			else if(settings.height_factor) {
				if(settings.min_height <= window._man_text._height && settings.max_height >= window._man_text._height) {
					var font_size = settings.min_size + (settings.size_factor * (window._man_text._height - settings.min_height) / settings.height_factor);
					settings.css_rule.style.setProperty("font-size", font_size + settings.unit, "important");
				}
				else if(settings.max_height < window._man_text._height) {
					settings.css_rule.style.setProperty("font-size", settings.max_size + settings.unit, "important");
				}
				else if(settings.min_height > window._man_text._height) {
					settings.css_rule.style.setProperty("font-size", settings.min_size + settings.unit, "important");
				}
			}
		}
	}
	node.cancelTextScaling = function() {
		u.e.removeEvent(window, "resize", window._man_text.scale);
	}
	if(!window._man_text) {
		var man_text = {};
		man_text.nodes = [];
		var style_tag = document.createElement("style");
		style_tag.setAttribute("media", "all")
		style_tag.setAttribute("type", "text/css")
		man_text.style_tag = u.ae(document.head, style_tag);
		man_text.style_tag.appendChild(document.createTextNode(""))
		window._man_text = man_text;
		window._man_text._width = u.browserW();
		window._man_text._height = u.browserH();
		window._man_text.scale = function() {
			var _width = u.browserW();
			var _height = u.browserH();
			window._man_text._width = u.browserW();
			window._man_text._height = u.browserH();
			var i, node;
			for(i = 0; i < window._man_text.nodes.length; i++) {
				node = window._man_text.nodes[i];
				if(node.parentNode) { 
					node.scaleText();
				}
				else {
					window._man_text.nodes.splice(window._man_text.nodes.indexOf(node), 1);
					if(!window._man_text.nodes.length) {
						u.e.removeEvent(window, "resize", window._man_text.scale);
						window._man_text = false;
						break;
					}
				}
			}
		}
		u.e.addEvent(window, "resize", window._man_text.scale);
		window._man_text.precalculate = function() {
			var i, node, tag;
			for(i = 0; i < window._man_text.nodes.length; i++) {
				node = window._man_text.nodes[i];
				if(node.parentNode) { 
					var settings = node.text_settings;
					for(tag in settings) {
						if(settings[tag].max_width && settings[tag].min_width) {
							settings[tag].width_factor = settings[tag].max_width-settings[tag].min_width;
						}
						else if(node._man_text.max_width && node._man_text.min_width) {
							settings[tag].max_width = node._man_text.max_width;
							settings[tag].min_width = node._man_text.min_width;
							settings[tag].width_factor = node._man_text.max_width-node._man_text.min_width;
						}
						else {
							settings[tag].width_factor = false;
						}
						if(settings[tag].max_height && settings[tag].min_height) {
							settings[tag].height_factor = settings[tag].max_height-settings[tag].min_height;
						}
						else if(node._man_text.max_height && node._man_text.min_height) {
							settings[tag].max_height = node._man_text.max_height;
							settings[tag].min_height = node._man_text.min_height;
							settings[tag].height_factor = node._man_text.max_height-node._man_text.min_height;
						}
						else {
							settings[tag].height_factor = false;
						}
						settings[tag].size_factor = settings[tag].max_size-settings[tag].min_size;
						if(!settings[tag].unit) {
							settings[tag].unit = node._man_text.unit;
						}
					}
				}
			}
		}
	}
	var tag;
	node._man_text = {};
	for(tag in node.text_settings) {
		if(tag == "min_height" || tag == "max_height" || tag == "min_width" || tag == "max_width" || tag == "unit" || tag == "ref") {
			node._man_text[tag] = node.text_settings[tag];
			node.text_settings[tag] = null;
			delete node.text_settings[tag];
		}
		else {
			selector = "."+node.text_key + ' ' + tag + ' ';
			node.css_rules_index = window._man_text.style_tag.sheet.insertRule(selector+'{}', 0);
			node.text_settings[tag].css_rule = window._man_text.style_tag.sheet.cssRules[0];
		}
	}
	window._man_text.nodes.push(node);
	window._man_text.precalculate();
	node.scaleText();
}

/*u-timer.js*/
Util.Timer = u.t = new function() {
	this._timers = new Array();
	this.setTimer = function(node, action, timeout, param) {
		var id = this._timers.length;
		param = param != undefined ? param : {"target":node, "type":"timeout"};
		this._timers[id] = {"_a":action, "_n":node, "_p":param, "_t":setTimeout("u.t._executeTimer("+id+")", timeout)};
		return id;
	}
	this.resetTimer = function(id) {
		if(this._timers[id]) {
			clearTimeout(this._timers[id]._t);
			this._timers[id] = false;
		}
	}
	this._executeTimer = function(id) {
		var timer = this._timers[id];
		this._timers[id] = false;
		var node = timer._n;
		if(fun(timer._a)) {
			node._timer_action = timer._a;
			node._timer_action(timer._p);
			node._timer_action = null;
		}
		else if(fun(node[timer._a])) {
			node[timer._a](timer._p);
		}
	}
	this.setInterval = function(node, action, interval, param) {
		var id = this._timers.length;
		param = param ? param : {"target":node, "type":"timeout"};
		this._timers[id] = {"_a":action, "_n":node, "_p":param, "_i":setInterval("u.t._executeInterval("+id+")", interval)};
		return id;
	}
	this.resetInterval = function(id) {
		if(this._timers[id]) {
			clearInterval(this._timers[id]._i);
			this._timers[id] = false;
		}
	}
	this._executeInterval = function(id) {
		var node = this._timers[id]._n;
		if(fun(this._timers[id]._a)) {
			node._interval_action = this._timers[id]._a;
			node._interval_action(this._timers[id]._p);
			node._interval_action = null;
		}
		else if(fun(node[this._timers[id]._a])) {
			node[this._timers[id]._a](this._timers[id]._p);
		}
	}
	this.valid = function(id) {
		return this._timers[id] ? true : false;
	}
	this.resetAllTimers = function() {
		var i, t;
		for(i = 0; i < this._timers.length; i++) {
			if(this._timers[i] && this._timers[i]._t) {
				this.resetTimer(i);
			}
		}
	}
	this.resetAllIntervals = function() {
		var i, t;
		for(i = 0; i < this._timers.length; i++) {
			if(this._timers[i] && this._timers[i]._i) {
				this.resetInterval(i);
			}
		}
	}
}


/*u-txt.js*/
u.txt = function(index) {
	if(!u.translations) {
	}
	if(index == "assign") {
		u.bug("USING RESERVED INDEX: assign");
		return "";
	}
	if(u.txt[index]) {
		return u.txt[index];
	}
	u.bug("MISSING TEXT: "+index);
	return "";
}
u.txt["assign"] = function(obj) {
	for(x in obj) {
		u.txt[x] = obj[x];
	}
}


/*u-url.js*/
Util.getVar = function(param, url) {
	var string = url ? url.split("#")[0] : location.search;
	var regexp = new RegExp("(?:^|\b|&|\\?)"+param.replace(/[\[\]\(\)]{1}/g, "\\$&")+"\=([^\&\b]+)");
	var match = string.match(regexp);
	if(match && match.length > 1) {
		return decodeURIComponent(match[1]);
	}
	else {
		return "";
	}
}

