$(function(){
	//confirm license checkin
	$('.checkin-license').confirm({
		text: "Are you sure you want to check in this license?",
		title: "Confirmation Required",
		confirm: function(b){
			checkin(b);
		},
		cancel: function(b){

		},
		confirmButton:"Yes I am",
		cancelButton:"No",
		confirmButtonClass: "btn-danger",
    	cancelButtonClass: "btn-default",
	});

	//confirm license checkin
	$('.cancel-request').confirm({
		text: "Are you sure you want to check in this license?",
		title: "Confirmation Required",
		confirm: function(b){
			checkin(b);
		},
		cancel: function(b){

		},
		confirmButton:"Yes I am",
		cancelButton:"No",
		confirmButtonClass: "btn-danger",
    	cancelButtonClass: "btn-default",
	});
	//checkin a license via ajax and update the display listing
	function checkin(b)
	{
		$.ajax({
			url:b.attr('href'),
			type:'PUT',
			data:{
				action:'checkin',
			},
			success:function(data){
				data = JSON.parse(data);
				if(data.success) {
					updateRow(b);
				}
			},
			error:function(data){
				console.log('error');
			}
		});
	}

	//update the ui
	function updateRow(b)
	{
		b.parent('td').replaceWith('<td><a href="'+b.attr('href')+'" class="cancel-request btn btn-primary btn-xs">Cancel Request?</a></td>');
	}
});