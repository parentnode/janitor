Util.Objects["pull"] = new function() {
	this.init = function(scene) {
		// console.log("pull init")

		scene.p_pull_result = u.qs("p.pull_result", scene);
		scene.bn_pull = u.qs("li.pull", scene);

		if(scene.p_pull_result && scene.bn_pull) {
			scene.bn_pull.scene = scene;

			scene.bn_pull.submitted = function(response) {
				this.scene.p_pull_result.innerHTML = "Pulling...";
			}
			
			scene.bn_pull.confirmed = function(response) {

//				console.log(response);
				this.scene.p_pull_result.innerHTML = response.cms_object;
			}

			scene.bn_pull.confirmedError = function() {
				this.scene.p_pull_result.innerHTML = "Source code could not be pulled. Contact system admin.";
				u.ac(this.scene.p_pull_result, "system_error");

				// Remove pull button
				this.parentNode.removeChild(this);
			}
		}

	}

}
