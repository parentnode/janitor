Util.Modules["ticketEvent"] = new function() {
	this.init = function(div) {

		div.item_id = u.cv(div, "item_id");

		// CMS interaction urls
		div.remove_event_url = div.getAttribute("data-event-remove");
		div.csrf_token = div.getAttribute("data-csrf-token");

		div._form_event = u.qs("form.event", div);

		div._list_events = u.qs("ul.events", div);


		if(div._form_event) {
			div._form_event.div = div;

			u.f.init(div._form_event);

			// new editor submitted
			div._form_event.submitted = function(iN) {

				this.response = function(response) {
					page.notify(response);

					if(response.cms_status == "success" && response.cms_object) {

						if(!this.div._list_events) {
							this.div._list_events = u.ae(this.parentNode, "ul", {"class":"items events"});
							this.parentNode.insertBefore(this.div._list_events, this);

							// Remove no editors text if it exists
							var p_no_events = u.qs("p", this.div);
							if(p_no_events) {
								p_no_events.parentNode.removeChild(p_no_events);
							}
						}

						this.div._list_events.innerHTML = "";

						var event_li = u.ae(this.div._list_events, "li", {"class":"event event_id:"+response.cms_object["event_id"]});
						u.ae(event_li, "h3", {"html":response.cms_object["event_name"]});


						this.div.initEvent(event_li);

						// reset form input
						this.reset();

					}

				}
				u.request(this, this.action, {"method":"post", "data" : this.getData()});

			}

		}

		// add remove form to event
		div.initEvent = function(node) {

			node.div = this;

			if(this.remove_event_url) {

				node.event_id = u.cv(node, "event_id");
				node._ul_actions = u.ae(node, "ul", {"class":"actions"});
				node._li_remove = u.ae(node._ul_actions, "li", {"class":"remove"});


				// Create remove form
				node._form_remove = u.f.addForm(node._li_remove, {
					"action":this.remove_event_url, 
					"class":"remove"
				});
				node._form_remove.node = node;

				// Add csrf-token
				u.f.addField(node._form_remove, {
					"type":"hidden",
					"name":"csrf-token", 
					"value":div.csrf_token
				});
				u.f.addField(node._form_remove, {
					"type":"hidden",
					"name":"event_id", 
					"value":node.event_id
				});
				// Add button
				u.f.addAction(node._form_remove, {
					"value":"Remove from event",
					"class":"button remove"
				});

				// Add oneButtonForm properties
				node._form_remove.setAttribute("data-success-function", "removed");
				node._form_remove.setAttribute("data-confirm-value", "Are you sure?");

				// Initialize oneButtonForm
				u.m.oneButtonForm.init(node._form_remove);


				node._form_remove.removed = function(response) {
					this.node.parentNode.removeChild(this.node);
				}

			}

		}


		// initalize existing event
		div.events = u.qsa("li.event", div._list_events);
		var i, node;
		for(i = 0; node = div.events[i]; i++) {
			div.initEvent(node);
		}

	}
}

Util.Modules["eventTickets"] = new function() {
	this.init = function(div) {

		div.item_id = u.cv(div, "item_id");

		// CMS interaction urls
		div.remove_ticket_url = div.getAttribute("data-ticket-remove");
		div.csrf_token = div.getAttribute("data-csrf-token");

		div._form_ticket = u.qs("form.ticket", div);

		div._list_tickets = u.qs("ul.tickets", div);


		if(div._form_ticket) {
			div._form_ticket.div = div;

			u.f.init(div._form_ticket);

			// new editor submitted
			div._form_ticket.submitted = function(iN) {

				this.response = function(response) {
					page.notify(response);

					if(response.cms_status == "success" && response.cms_object) {

						if(!this.div._list_tickets) {
							this.div._list_tickets = u.ae(this.parentNode, "ul", {"class":"items tickets"});
							this.parentNode.insertBefore(this.div._list_tickets, this);

							// Remove no editors text if it exists
							var p_no_tickets = u.qs("p", this.div);
							if(p_no_tickets) {
								p_no_tickets.parentNode.removeChild(p_no_tickets);
							}
						}

						// this.div._list_tickets.innerHTML = "";

						var event_li = u.ae(this.div._list_tickets, "li", {"class":"ticket ticket_id:"+response.cms_object["ticket_id"]});
						u.ae(event_li, "h3", {"html":response.cms_object["ticket_name"]});

						// Remove ticket from select
						this.inputs["item_ticket"].removeChild(this.inputs["item_ticket"].options[this.inputs["item_ticket"].selectedIndex]);

						this.div.initTicket(event_li);

						// reset form input
						this.reset();

					}

				}
				u.request(this, this.action, {"method":"post", "data" : this.getData()});

			}

		}

		// add remove form to ticket
		div.initTicket = function(node) {

			node.div = this;

			if(this.remove_ticket_url) {

				node.ticket_id = u.cv(node, "ticket_id");
				node._ul_actions = u.ae(node, "ul", {"class":"actions"});
				node._li_remove = u.ae(node._ul_actions, "li", {"class":"remove"});


				// Create remove form
				node._form_remove = u.f.addForm(node._li_remove, {
					"action":this.remove_ticket_url, 
					"class":"remove"
				});
				node._form_remove.node = node;

				// Add csrf-token
				u.f.addField(node._form_remove, {
					"type":"hidden",
					"name":"csrf-token", 
					"value":div.csrf_token
				});
				u.f.addField(node._form_remove, {
					"type":"hidden",
					"name":"ticket_id", 
					"value":node.ticket_id
				});
				// Add button
				u.f.addAction(node._form_remove, {
					"value":"Remove ticket",
					"class":"button remove"
				});

				// Add oneButtonForm properties
				node._form_remove.setAttribute("data-success-function", "removed");
				node._form_remove.setAttribute("data-confirm-value", "Are you sure?");

				// Initialize oneButtonForm
				u.m.oneButtonForm.init(node._form_remove);


				node._form_remove.removed = function(response) {

					// Re-add ticket to select
					u.ae(this.node.div._form_ticket.inputs["item_ticket"], "option", {"html": node.innerHTML, "value": node.ticket_id});

					this.node.parentNode.removeChild(this.node);

				}

			}

		}


		// initalize existing tickets
		div.tickets = u.qsa("li.ticket", div._list_tickets);
		var i, node;
		for(i = 0; node = div.tickets[i]; i++) {
			div.initTicket(node);
		}

	}
}
