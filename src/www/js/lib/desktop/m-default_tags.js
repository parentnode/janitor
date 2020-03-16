Util.Modules["defaultTags"] = new function() {
	this.init = function(div) {

		div._item_id = u.cv(div, "item_id");

		// CMS interaction urls
		div.csrf_token = div.getAttribute("data-csrf-token");
		div.add_tag_url = div.getAttribute("data-tag-add");
		div.delete_tag_url = div.getAttribute("data-tag-delete");
		div.get_tags_url = div.getAttribute("data-tag-get");

		// map a data-div reference to share tag-functionality between edit and list pages
		div.data_div = div;


		// do we have required info 
		if(div.csrf_token && div.get_tags_url && div.delete_tag_url && div.add_tag_url) {


			div._tags = u.qs("ul.tags", div);
			if(!div._tags) {
				div._tags = u.ae(div._tags, "ul", {"class":"tags"});
			}
			div._tags.div = div;


			// Only get tags with allowed contexts
			div._tags_context = div._tags.getAttribute("data-context");
			
			// tags received
			div.tagsResponse = function(response) {

				// valid tags response
				if(response.cms_status == "success" && response.cms_object) {
					this.all_tags = response.cms_object;

				}
				// error getting tags (could be no tags exists in system)
				else {
					page.notify(response);
					this.all_tags = [];
				}

				// ensure tag-list existence (or add button cannot be added)


				// minimum work in first run
				// only inject add-button in first run
				this._bn_add = u.ae(this._tags, "li", {"class":"add","html":"+"});
				this._bn_add.node = this;

				// enable tagging
				u.enableTagging(this);

			}
			// get all tags from server
			u.request(div, div.get_tags_url + (div._tags_context ? "/"+div._tags_context : ""), {"callback":"tagsResponse", "method":"post", "params":"csrf-token=" + div.csrf_token});

		}

	}

}
