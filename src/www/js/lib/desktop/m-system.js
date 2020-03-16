Util.Modules["cacheList"] = new function() {
	this.init = function(div) {

		u.bug("div cacheList")

		// CMS interaction urls
		div.csrf_token = div.getAttribute("data-csrf-token");
		div.flush_url = div.getAttribute("data-flush-url");


		var entries = u.qsa("li.item", div);
		var i, entry;
		for(i = 0; entry = entries[i]; i++) {

			var actions = u.ae(entry, "ul", {"class":"actions"});

			var bn_view = u.f.addAction(actions, {"type":"button", "class":"button", "value":"Details"});
			bn_view.entry = entry;
			u.ce(bn_view);
			bn_view.clicked = function() {
				if(u.hc(this.entry, "show")) {
					u.rc(this.entry, "show");
				}
				else {
					u.ac(this.entry, "show");
				}
			}

			var bn_flush = u.f.addAction(actions, {"type":"button", "class":"button", "value":"Flush"});
			bn_flush.div = div;
			bn_flush.entry = entry;
			bn_flush.cache_key = entry.getAttribute("data-cache-key");
			u.ce(bn_flush);
			bn_flush.clicked = function() {

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