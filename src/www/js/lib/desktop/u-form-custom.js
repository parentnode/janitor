u.f.fixFieldHTML = function(field) {
	if(u.hc(field, "checkbox|boolean|radiobuttons") && field.indicator && field.label) {
		u.ae(field.label, field.indicator);
	}
}
