(function ( $ ) {
	$.fn.getQueryVariable = function(variable)
	{	
	   var query = $(this).attr('href');
	   var vars = query.split("&");
	   for (var i=0;i<vars.length;i++) {
	           var pair = vars[i].split("=");
	           if(pair[0] == variable){return pair[1];}
	   }
	   return(false);
	}
}( jQuery ));