Util.Modules["pull"] = new function() {
	this.init = function(scene) {
		// console.log("pull init")

		scene.p_pull_result = u.qs("p.pull_result", scene);
		scene.form_pull = u.qs("form.pull", scene);

		if(scene.form_pull) {
			u.f.init(scene.form_pull);


			scene.form_pull.scene = scene;


			if(scene.form_pull.actions["pull"]) {

				scene.form_pull.actions["pull"].org_value = scene.form_pull.actions["pull"].value;

				scene.form_pull.restore = function(event) {
					this.actions["pull"].value = this.actions["pull"].org_value;
					u.rc(this.actions["pull"], "confirm");
				}

				scene.form_pull.submitted = function() {

					// first click
					if(!u.hc(this.actions["pull"], "confirm")) {
						u.ac(this.actions["pull"], "confirm");
						this.actions["pull"].value = "Confirm";
						this.t_confirm = u.t.setTimer(this, this.restore, 3000);
					}
					// confirm click
					else {
						u.t.resetTimer(this.t_confirm);

						this.scene.p_pull_result.innerHTML = "Pulling...";

						u.ac(this.actions["pull"], "disabled");
						u.ac(this, "submitting");
						this.actions["pull"].value = u.stringOr(this.actions["pull"].wait_value, "Wait");


						this.response = function(response) {
							u.rc(this, "submitting");
							u.rc(this.actions["pull"], "disabled");

							// show notification
							page.notify(response);

							// Restore button
							this.restore();

							if(response.cms_status == "success") {

								// does default callback exist
								if(typeof(this.confirmed) == "function") {
									u.bug("confirmed");
									this.confirmed(response);
								}
								else {
									u.bug("default return handling" + this.success_location)
								}
							}
							else {
							
								// does default callback exist
								if(typeof(this.confirmedError) == "function") {
									u.bug("confirmedError");
									this.confirmedError(response);
								}
							
							}

						}


						u.request(this, this.action, {"method":"post", "params":u.f.getParams(this)});
					}
				}
				scene.form_pull.confirmed = function(response) {

	//				console.log(response);
					this.scene.p_pull_result.innerHTML = response.cms_object;
				}

				scene.form_pull.confirmedError = function() {
					this.scene.p_pull_result.innerHTML = "Source code could not be pulled. Contact system admin.";
					u.ac(this.scene.p_pull_result, "system_error");

					// Remove pull button
					this.parentNode.removeChild(this);
				}

			}
		}

// 		scene.bn_pull = u.qs("li.pull", scene);
//
// 		if(scene.p_pull_result && scene.bn_pull) {
// 			scene.bn_pull.scene = scene;
//
// 			scene.bn_pull.submitted = function(response) {
// 				this.scene.p_pull_result.innerHTML = "Pulling...";
// 			}
//
// 			scene.bn_pull.confirmed = function(response) {
//
// //				console.log(response);
// 				this.scene.p_pull_result.innerHTML = response.cms_object;
// 			}
//
// 			scene.bn_pull.confirmedError = function() {
// 				this.scene.p_pull_result.innerHTML = "Source code could not be pulled. Contact system admin.";
// 				u.ac(this.scene.p_pull_result, "system_error");
//
// 				// Remove pull button
// 				this.parentNode.removeChild(this);
// 			}
// 		}

	}

}
