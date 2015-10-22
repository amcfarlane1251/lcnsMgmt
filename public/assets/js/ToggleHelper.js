function ToggleHelper(c, t, e, s)
{
	var container = c;
	var toggle = t;
	var element = e;
	var shared = s;
	var self = this;

	this.init = function()
	{
		//event on toggle elements change
		$("#"+container).on('change', 'select', this.trigger);
	}

	this.trigger = function()
	{	
		$(this).find('option:selected').each(function(){
			if( $(this).text().trim()==toggle ) {
				self.show();
				return false;
			}
			else{
				self.hide();
			}
		});
	}

	//show the new element
	this.show = function()
	{	
		var elemToShow = $('*[data-toggle='+element+']');
		elemToShow.removeClass('hidden');
	}

	//hide the new element
	this.hide = function()
	{
		var flag = false;
		$("#"+container).find('option:selected').each(function(){
			for(var key in shared){
				if($(this).text().trim()==key){
					if(element==shared[key]){
						flag = true;
					}
				}
			}
		});

		var elemToHide = $('*[data-toggle='+element+']');
		if(!flag){elemToHide.addClass('hidden');}
	}
}