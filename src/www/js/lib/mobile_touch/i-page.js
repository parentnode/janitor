u.bug_console_only = true;

Util.Objects["page"] = new function() {
	this.init = function(page) {

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

		// adds notifier and page.notify function
		u.notifier(page);


		// page is ready
		u.addClass(page, "ready");
	}
}

u.e.addDOMReadyEvent(u.init)
