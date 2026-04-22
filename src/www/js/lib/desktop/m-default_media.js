// Add images form
Util.Modules["addMedia"] = new function() {
	this.init = function(div) {
		// u.bug("addMedia init:", div);

		// return;
		
		
		div.form = u.qs("form.upload", div);
		div.form.div = div;


		// base media info
		// div.item_id = u.cv(div, "item_id");
		// div.variant = u.cv(div, "variant");


		u.f.init(div.form);


		// div.csrf_token = div.form.inputs["csrf-token"].val();
		// div.delete_url = div.getAttribute("data-media-delete");
		// div.update_name_url = div.getAttribute("data-media-name");
		// div.save_order_url = div.getAttribute("data-media-order");
		//
		//
		// // Create easy reference to file input
		// div.media_input = div.form.inputs[div.variant+"[]"];
		// div.filelist = div.media_input.field.filelist;
		// div.previewlist = u.ae(div, "ul", {class:"previewlist"});
		// div.previewlist.div = div;


		// Submit on change
		div.form.changed = function() {
			this.submit();
		}

		// upload form submitted
		div.form.submitted = function() {
		//
		// 	this.div.media_input.blur();
		// 	// Enter loading state
		// 	u.ac(this.div.media_input.field, "loading");
		//
		//
			this.response = function(response) {
				page.notify(response);

				// inject/update image if everything went well
				if(response.cms_status == "success" && response.cms_object) {

					// Update file list status
					u.f.updateFormAfterResponse(this, response);

					// update preview
					// this.div.updatePreviews();

				}

				// Leave loading state
				// u.rc(this.div.media_input.field, "loading");
				// Reset file queue
				// this.reset();
				// this.div.media_input.val("");

			}
			u.request(this, this.action, {"method":"post", "data":this.getData()});

		}
		//
		//
		// // set media type for form
		// // previews are different for different types
		// // add preview based on current media format
		// div.updatePreviews = function() {
		//
		// 	// Look for something to preview
		// 	var nodes = u.qsa("li.uploaded,li.new", this.filelist);
		// 	if(nodes) {
		//
		// 		var i, node, li;
		// 		for(i = 0; i < nodes.length; i++) {
		//
		// 			node = nodes[i];
		//
		// 			// Only do something if node doesn't already have preview
		// 			if(!node.li_preview) {
		//
		//
		// 				// Get base properties
		// 				node.media_format = u.cv(node, "format");
		// 				node.media_variant = u.cv(node, "variant");
		// 				node.media_id = u.cv(node, "media_id");
		// 				node.media_name = node.innerHTML;
		// 				node.media_description = "";
		// 				node.div = this;
		//
		//
		// 				node.li_preview = u.ae(this.previewlist, "li", {class: "preview media_id:" + node.media_id});
		// 				node.preview = u.ae(node.li_preview, "div", {class: "preview " + node.media_format});
		// 				node.preview.node = node;
		//
		//
		// 				// set media type
		// 				if(node.media_format.match(/^(jpg|png|gif)$/i)) {
		//
		// 					u.addImagePreview(node);
		// 				}
		// 				else if(node.media_format.match(/^(mp3|ogg|wav|aac)$/i)) {
		//
		// 					u.addAudioPreview(node);
		// 				}
		// 				else if(node.media_format.match(/^(mov|mp4|ogv|3gp)$/i)) {
		//
		// 					u.addVideoPreview(node);
		// 				}
		// 				else if(node.media_format.match(/^zip$/i)) {
		//
		// 					u.addZipPreview(node);
		// 				}
		// 				else if(node.media_format.match(/^pdf$/i)) {
		//
		// 					u.addPdfPreview(node);
		// 				}
		//
		//
		// 				// Add rename form
		// 				u.addRenameMediaForm(div, node);
		//
		// 				// Add delete form
		// 				u.addDeleteMediaForm(div, node);
		//
		//
		// 				// Callback on successful delete
		// 				node.delete_form.deleted = function(response) {
		//
		// 					// Remove from filelist
		// 					this.node.div.filelist.removeChild(this.node);
		//
		// 					// Remove from preview list
		// 					this.node.div.previewlist.removeChild(this.node.li_preview);
		//
		// 					// Update uploaded_files list
		// 					this.node.div.media_input.field.uploaded_files = u.qsa("li.uploaded", this.node.div.filelist);
		//
		// 					// Update sortable
		// 					if(fun(this.node.div.previewlist.updateDraggables)) {
		// 						this.node.div.previewlist.updateDraggables();
		// 					}
		//
		// 				}
		//
		// 			}
		// 			// Maintain order
		// 			else {
		// 				u.ae(this.previewlist, node.li_preview);
		// 			}
		//
		// 		}
		//
		// 	}
		//
		// 	// Update sortable
		// 	if(fun(this.previewlist.updateDraggables)) {
		// 		this.previewlist.updateDraggables();
		// 	}
		//
		// }
		//
		//
		//
		// div.updatePreviews();
		//
		//
		// // sortable list
		// if(u.hc(div, "sortable") && div.save_order_url) {
		//
		// 	u.sortable(div.previewlist);
		//
		// 	div.previewlist.picked = function(node) {}
		// 	div.previewlist.dropped = function(node) {
		//
		// 		// Get node order
		// 		var order = this.getNodeOrder({class_var:"media_id"});
		//
		// 		this.response = function(response) {
		// 			// Notify of event
		// 			page.notify(response);
		// 		}
		// 		u.request(this, this.div.save_order_url+"/"+this.div.item_id, {
		// 			"method":"post",
		// 			"data":"csrf-token=" + this.div.csrf_token + "&order=" + order.join(",")
		// 		});
		// 	}
		// }
		// // no save url
		// else {
		// 	u.rc(div, "sortable");
		// }

	}
}

// Add images form
Util.Modules["addMediaSingle"] = new function() {
	this.init = function(div) {
		// u.bug("addMediaSingle init:", div);
		
		// return;

		div.form = u.qs("form.upload", div);
		div.form.div = div;

		// base media info
		// div.item_id = u.cv(div, "item_id");
		// div.variant = u.cv(div, "variant");


		u.f.init(div.form);


		// div.csrf_token = div.form.inputs["csrf-token"].val();
		// div.delete_url = div.getAttribute("data-media-delete");
		// div.update_name_url = div.getAttribute("data-media-name");
		// div.media_info_url = div.getAttribute("data-media-info");
		//
		//
		// // Create easy reference to file input
		// div.media_input = div.form.inputs[div.variant+"[]"];
		// div.filelist = div.media_input.field.filelist;

		// Submit on change
		div.form.changed = function() {
			this.submit();
		}

		// Handle upload
		div.form.submitted = function() {
			u.bug("subm");

			// this.div.media_input.blur();
			// // Enter loading state
			// u.ac(this.div.media_input.field, "loading");


			// hide existing preview while waiting for response
			if(this.div.preview) {
				u.as(this.div.preview, "display", "none");
			}

			this.response = function(response) {
				page.notify(response);

				// inject/update preview if everything went well
				if(response.cms_status == "success" && response.cms_object) {

					// Update file list status
					u.f.updateFormAfterResponse(this, response);

					// // update preview
					// this.div.updatePreview();

				}

				// Leave loading state
				// u.rc(this.div.media_input.field, "loading");
				// // Reset file queue
				// this.div.media_input.val("");

			}
			u.request(this, this.action, {"method":"post", "data":this.getData()});
		}


		// // set media type for form
		// // previews are different for different types
		// // add preview based on current media format
		// div.updatePreview = function() {
		//
		// 	// remove existing preview
		// 	if(this.preview) {
		// 		this.preview.parentNode.removeChild(this.preview);
		// 		delete this.preview;
		// 	}
		//
		// 	// Look for something to preview
		// 	var node = u.qs("li.uploaded", this.filelist);
		// 	if(node) {
		//
		//
		// 		// Get base properties
		// 		node.media_format = u.cv(node, "format");
		// 		node.media_variant = u.cv(node, "variant");
		// 		node.media_name = node.innerHTML;
		// 		node.media_description = "";
		// 		node.div = this;
		//
		// 		// Create preview node
		// 		node.preview = u.ae(this, "div", {"class": "preview " + node.media_format});
		// 		node.preview.node = node;
		// 		// map current preview to div
		// 		this.preview = node.preview;
		//
		// 		// Adjust preview width
		// 		// u.ass(node.preview, {
		// 		// 	"width": this.filelist.offsetWidth + "px"
		// 		// });
		//
		//
		// 		// set media type
		// 		if(node.media_format.match(/^(jpg|png|gif)$/i)) {
		//
		// 			u.addImagePreview(node);
		// 		}
		// 		else if(node.media_format.match(/^(mp3|ogg|wav|aac)$/i)) {
		//
		// 			u.addAudioPreview(node);
		// 		}
		// 		else if(node.media_format.match(/^(mov|mp4|ogv|3gp)$/i)) {
		//
		// 			u.addVideoPreview(node);
		// 		}
		// 		else if(node.media_format.match(/^zip$/i)) {
		//
		// 			u.addZipPreview(node);
		// 		}
		// 		else if(node.media_format.match(/^pdf$/i)) {
		//
		// 			u.addPdfPreview(node);
		// 		}
		//
		//
		// 		// Add rename form
		// 		u.addRenameMediaForm(div, node);
		//
		// 		// Add delete form
		// 		u.addDeleteMediaForm(div, node);
		//
		// 		// Callback on successful delete
		// 		node.delete_form.deleted = function(response) {
		//
		// 			// Reset uploaded files list
		// 			this.node.div.media_input.field.uploaded_files = false;
		//
		// 			// Force validation
		// 			this.node.div.media_input.val("");
		//
		// 			// Update preview
		// 			this.node.div.updatePreview();
		//
		// 		}
		//
		// 	}
		//
		// }
		//
		// // add start preview
		// div.updatePreview();

	}
}


// IMAGE preview
u.addImagePreview = function(node) {

	u.ac(node, "preview_image");

	// Get additional image properties
	node.media_width = u.cv(node, "width");
	node.media_height = u.cv(node, "height");


	node.media = u.ie(node, "div", {"class":"view"});


	// Adjust preview height
	u.ass(node.media, {
		"width": ((node.media.offsetHeight/node.media_height) * node.media_width) + "px",
		"backgroundImage": "url(/images/"+node.div.item_id+"/"+node.media_variant+"/x"+node.media.offsetHeight+"."+node.media_format+"?"+u.randomString(4)+")"
	});

}

// PDF preview
u.addPdfPreview = function(node) {

	u.ac(node, "preview_pdf");


	node.media = u.ie(node, "div", {"class":"view"});


	// Adjust preview height
	u.ass(node.media, {
		"backgroundImage": "url(/images/0/pdf/30x.png)"
	});

}

// ZIP preview
u.addZipPreview = function(node) {

	u.ac(node, "preview_zip");


	node.media = u.ie(node, "div", {"class":"view"});


	// Adjust preview height
	u.ass(node.media, {
		"backgroundImage": "url(/images/0/zip/30x.png)"
	});

}

// AUDIO preview
u.addAudioPreview = function(node) {

	u.ac(node, "preview_audio");

	// enable playback
	node.preview.audio_url = "/audios/"+node.div.item_id+"/"+node.media_variant+"/128."+node.media_format+"?"+u.randomString(4);

	// Add play button
	u.addPlayMedia(this, node);

}

// VIDEO preview
u.addVideoPreview = function(node) {

	u.ac(node, "preview_video");

	// Get additional image properties
	node.media_width = u.cv(node, "width");
	node.media_height = u.cv(node, "height");

	// enable playback
	node.video_url = "/videos/"+node.div.item_id+"/"+node.media_variant+"/x300x."+node.media_format+"?"+u.randomString(4);

	// Add play button
	u.addPlayMedia(node.div, node);

	// Adjust preview height
	// u.ass(node.preview, {
	// 	"width": ((node.preview.offsetHeight/node.media_height) * node.media_width) + "px",
	// });


}



// add play form
u.addPlayMedia = function(div, node) {

	if(!node.media) {
		node.media = node.preview.audio_url ? u.audioPlayer() : u.videoPlayer({"muted":true, "loop":true, "preload":"metadata"});
		node.media.node = node;
		u.ac(node.media, "view");

		// inject player
		u.ae(node.preview, node.media);

	}


	// Load url to show first frame
	node.media.load(node.preview.audio_url ? node.preview.audio_url : node.preview.video_url);

	// var controls = u.ae(node.preview, "div", {"class":"controls"});
	var controls = u.ae(node.media, "div", {"class":"controls"});

	// Create play button
	node.bn_play = u.ae(controls, "div", {"class":"play"});
	// node.bn_play.preview = node.preview;
	node.bn_play.node = node;


	u.ce(node.bn_play);
	node.bn_play.clicked = function(event) {
		if(!u.hc(this.node.preview, "playing")) {
			this.node.play_preview();
		}
		else {
			this.node.pause_preview();
		}
	}
	node.play_preview = function() {
		this.player.play();
		u.ac(this.preview, "playing");
	}
	node.pause_preview = function() {
		this.player.pause();
		u.rc(this.preview, "playing");
	}

	u.e.hover(node.media);
	node.media.over = function() {
		this.node.play_preview();
	}
	node.preview.out = function() {
		this.node.pause_preview();
	}


	// Create mute button
	node.bn_mute = u.ae(controls, "div", {"class":"mute"});
	// node.bn_play.preview = node.preview;
	node.bn_mute.node = node;

	u.ce(node.bn_mute);
	node.bn_mute.clicked = function(event) {
		if(!u.hc(this.node.media, "muted")) {
			this.node.media.mute();
			u.ac(this.node.media, "muted");
		}
		else {
			this.node.media.unmute();
			u.rc(this.node.media, "muted");
		}
	}
	u.ac(node.media, "muted");


}

// add delete form
u.addDeleteMediaForm = function(div, node) {

	// Create delete form
	node.delete_form = u.f.addForm(node.preview, {
		"action":div.delete_url+"/"+div.item_id+"/"+node.media_variant, 
		"class":"delete"
	});
	node.delete_form.node = node;

	// Add csrf-token
	u.f.addField(node.delete_form, {
		"type":"hidden",
		"name":"csrf-token", 
		"value":div.csrf_token
	});
	// Add button
	u.f.addAction(node.delete_form, {
		"class":"button delete"
	});

	// Add oneButtonForm properties
	node.delete_form.setAttribute("data-confirm-value", "Confirm");
	node.delete_form.setAttribute("data-success-function", "deleted");

	// Initialize oneButtonForm
	u.m.oneButtonForm.init(node.delete_form);

}

// add delete form
u.addRenameMediaForm = function(div, node) {

	// add update form
	node.update_name_form = u.f.addForm(node.preview, {
		"action":div.update_name_url+"/"+div.item_id+"/"+node.media_variant, 
		"class":"edit"
	});
	node.update_name_form.node = node;

	// Add csrf-token
	u.f.addField(node.update_name_form, {
		"type":"hidden",
		"name":"csrf-token", 
		"value":div.csrf_token
	});
	// Add name input
	u.f.addField(node.update_name_form, {
		"type":"string",
		"name":"name", 
		"value":node.media_name, 
		"required":true
	});
	// Add name input
	u.f.addField(node.update_name_form, {
		"type":"text",
		"name":"description", 
		"value":node.media_description, 
		"required":false
	});

	// init form
	u.f.init(node.update_name_form);

	// enable editing activation on whole update form
	u.ce(node.update_name_form);

	// eliminate dragging if sorting is also enable
	node.update_name_form.inputStarted = function(event) {
		if(!u.hc(this.node.preview, "edit")) {

			u.e.kill(event);
		}

	}
	// Enter edit state
	node.update_name_form.clicked = function(event) {
		u.ac(this.node.preview, "edit");

		this.inputs["name"].focus();
	}

	// submit on blur
	node.update_name_form.inputs["name"].blurred = function() {

		// Update if input validates
		if(this.is_correct) {
			this._form.updateName();
		}
	}

	// submit is handled on blur
	node.update_name_form.submitted = function() {

		this.inputs["name"].blur();
	}

	// update name
	node.update_name_form.updateName = function() {

		u.rc(this.node.preview, "edit");

		// submit new image name
		this.response = function(response) {

			page.notify(response);

			// reset media name back to starting value if something went wrong
			if(response.cms_status !== "success") {
				this.inputs["name"].val(this.node.media_name);
			}

		}
		u.request(this, this.action, {"method":this.method, "data":this.getData()});

	}
}

