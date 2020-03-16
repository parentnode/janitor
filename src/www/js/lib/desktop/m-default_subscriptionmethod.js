Util.Modules["defaultSubscriptionmethod"] = new function() {
	this.init = function(div) {

		div.item_id = u.cv(div, "item_id");

		// CMS interaction urls
		div.csrf_token = div.getAttribute("data-csrf-token");

		// add form
		div._sm_form = u.qs("form", div);
		div._sm_change_div = u.qs("div.change_subscription_method", div);

		// add status indicator
		div._sm_setting = u.qs("dl.info dd.subscription_method", div);


		if(div._sm_form) {
			div._sm_form.div = div;

			// add change button
			div.actions_change = u.ae(div, "ul", {"class":"actions change"});
			var li = u.ae(div.actions_change, "li", {"class":"change"});
			div.bn_change = u.ae(li, "a", {"class":"button primary", "html":"Change period"});
			div.bn_change.div = div;

			u.ce(div.bn_change);
			div.bn_change.clicked = function() {
				u.ass(this.div._sm_change_div, {
					"display":"block"
				});
				u.ass(this.div.actions_change, {
					"display":"none"
				});
			}


			u.f.init(div._sm_form);



			// new subscription method submitted
			div._sm_form.submitted = function(iN) {

				this.response = function(response) {
					page.notify(response);

					if(response.cms_status == "success" && response.cms_object) {
						if(typeof(response.cms_object) == "object") {
							this.div._sm_setting.innerHTML = response.cms_object["name"];
						}
						else {
							this.div._sm_setting.innerHTML = "No renewal";
						}

						u.ass(this.div._sm_change_div, {
							"display":"none"
						});
						u.ass(this.div.actions_change, {
							"display":"block"
						});

					}
				}
				u.request(this, this.action, {"method":"post", "params" : u.f.getParams(this)});
			}
			
		}

	}
}
