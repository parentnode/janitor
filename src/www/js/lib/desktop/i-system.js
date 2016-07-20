Util.Objects["cacheList"] = new function() {
	this.init = function(div) {

		u.bug("div cacheList")

		// CMS interaction urls
		div.csrf_token = div.getAttribute("data-csrf-token");
		div.flush_url = div.getAttribute("data-flush-url");


		var entries = u.qsa("li.item", div);
		var i, entry;
		for(i = 0; entry = entries[i]; i++) {

			var actions = u.ae(entry, "ul", {"class":"actions"});
			var action = u.f.addAction(actions, {"type":"button", "class":"button", "value":"Flush"});
			action.div = div;
			action.entry = entry;
			action.cache_key = entry.getAttribute("data-cache-key");
			u.ce(action);
			action.clicked = function() {

				this.response = function(response) {
					page.notify(response);
					
					if(response.cms_status == "success") {
						this.entry.parentNode.removeChild(this.entry);
					}
				}
				u.request(this, this.div.flush_url, {"method":"post", "params" : "csrf-token="+this.div.csrf_token+"&cache-key="+this.cache_key});
				
			}
		}

	}
}