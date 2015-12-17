function ToggleHelper(c, e)
{
	var container = c;
	var element = e;
	var self = this;

	this.init = function()
	{
		//event on toggle elements change
		$("#"+container).on('change', 'select', this.trigger);
	}

	this.trigger = function()
	{	
		$(this).find('option:selected').each(function(){
			if( $(this).data('asset-flag')==true ) {
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
		var elemToHide = $('*[data-toggle='+element+']');
		elemToHide.addClass('hidden');
	}
}