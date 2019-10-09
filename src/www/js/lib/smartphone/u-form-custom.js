u.f.fixFieldHTML = function(field) {
	// u.bug("fixFieldHTML");
	if(field.indicator && field.label) {
		u.ae(field.label, field.indicator);
	}
}