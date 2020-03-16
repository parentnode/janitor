Util.Modules["taglist_tags"] = new function() {
	this.init = function(div) {
		//u.bug("scene init:");

		var items = u.qsa("li.item", div);

		for(var i = 0; i < items.length; i++) {
			li = items[i];

			var add = u.qs("ul.actions li.add", li);
			add.li = li;
			var remove = u.qs("ul.actions li.remove", li);
			remove.li = li;

			add.added = function(response) {
				console.log(response);

				u.addClass(this.li, "added");
			}

			remove.removed = function(response) {
				u.removeClass(this.li, "added");
			}

		}

		//var allButtons = u.qsa("#content .scene.taglistList .all_items ul.items");
/*
*/
		// scene is ready
		//taglist_tags.ready();
	}
}