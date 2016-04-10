u.f.fixFieldHTML = function(field) {
	u.bug("fixFieldHTML");
	var label = u.qs("label", field);
	if(label) {
		u.ae(label, field._indicator);
	}
}