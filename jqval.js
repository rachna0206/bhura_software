// JavaScript Document
jQuery('#vf1').validate({

	rules:
	{
		ntxt: "required",
		dob: "required",
		etxt : {
			required : true,
			email : true	
		},
		ptxt : {
			required : true,
			digits : true,
			minlength: 10
		},
		gtxt: "required",
		test:"required",
		
	},
	messages:
	{
		ntxt : "Name field can not be left blank",
		dob : "Please select the date of birth",
		etxt : "Please enter proper email format",
		ptxt : "Please enter proper phone number",
		gtxt : "Please select your gender",
		test: "Please Enter your age",
	},
	errorPlacement: function(error, element) {
		console.log(element.attr("name"));
	  if(element.attr("name") == "gtxt") {
	  	
	  	element.parent().append(error);
	    
	  }
	  else {
	  	
	    
	    element.after(error);
	  }
	}

});