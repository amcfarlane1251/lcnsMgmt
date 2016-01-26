function isJsonString(str) {
	try {
		JSON.parse(str);
	} catch (e) {
		return false;
	}
	return true;
}

$(function(){
	
	//confirm create check-in request
	$('.checkin-license').confirm({
		text: "Are you sure you want to check in this license?",
		title: "Confirmation Required",
		confirm: function(b){
			sendRequest(b, 'checkin');
		},
		cancel: function(b){

		},
		confirmButton:"Yes I am",
		cancelButton:"No",
		confirmButtonClass: "btn-danger",
		cancelButtonClass: "btn-default",
	});
	
	//confirm delete request
	$('.delete-request').confirm({
		text: "Are you sure you want to delete this request?",
		title: "Confirmation Required",
		confirm: function(b){
			sendRequest(b, 'delete');
		},
		cancel: function(b){

		},
		confirmButton:"Yes I am",
		cancelButton:"No",
		confirmButtonClass: "btn-danger",
		cancelButtonClass: "btn-default",
	});
        
	//checkin a license via ajax and update the display listing
	function sendRequest(b, action)
	{	
		if(action=='checkin') {
			$.ajax({
				url:b.attr('href'),
				type:'PUT',
				data:{
					action:'checkin',
				}
			}).done(function(data){
				var obj;
				obj = JSON.parse(data);
				if(obj.success) {
					updateTable(b);
				}
			}).fail(function(data){
				
			});
		}
		else if(action=='delete') {
			$.ajax({
				url:b.attr('href'),
				type:'DELETE',
				dataType:'json'
			}).done(function(data) {
				updateTable(b);
			}).fail(function(data) {

			});
		}
	}

	//update the ui
	function updateTable(b)
	{
		//set local vars and empty table body
		var tbody = b.parent().parent('tr').parent('tbody');
		var table = tbody.parent('table');
		tbody.empty();
		
		$.ajax({
			url: tbody.data('license-url'),
			type:'GET'
		}).done(function(data){
			var obj, licenses, baseUrl;
			
			if(isJsonString(data)) {
				obj = JSON.parse(data);
			}
			else{
				obj = data;
			}
			licenses = obj.licenses;
			baseUrl = tbody.data('base-url');

			for(i=0;i < licenses.length; i++) {
				var licensedTo, userAssigned, action;
				//format the licensed to and user assigned fields
				if(licenses[i].request){
					licensedTo = "<a href='request/"+licenses[i].request.id+"'>Awaiting Request Approval</a>";
					userAssigned = "<a href='request/"+licenses[i].request.id+"'>Awaiting Request Approval</a>";
				}
				else{
					licensedTo = licenses[i].assignedAsset;
					userAssigned = licenses[i].assignedUser;
				}
				//format the action buttons
				if(licenses[i].request) {
					action = "<a href='"+baseUrl+"/request/"+licenses[i].request.id+"' class='delete-request btn btn-primary btn-xs'>Cancel Request?</a>";
				}
				else if(licenses[i].assignedUser || licenses[i].assignedAsset) {
					action = "<a href='"+baseUrl+"/licenses/"+licenses[i].id+"' class='checkin-license btn btn-primary btn-xs'>Check In</a>"+
									"<a href='"+baseUrl+"/request/create?type=move&lcnsId="+licenses[i].id+"&accntId="+licenses[i].assignedUserId+"' class='move-license btn btn-primary btn-xs'>Move</a>";
				}
				else{
					action = '';
				}
				
				//append row depending on view
				if(table.attr('id') == 'asset-licenses') {
					tbody.append(
						"<tr>"+
							"<td>"+licenses[i].name+"</td>"+
							"<td>"+licenses[i].updatedAt+"</td>"+
							"<td>"+action+"</td>"+
						"</tr>"
					);
				}
				else{
					tbody.append(
						"<tr>"+
							"<td>"+licenses[i].name+"</td>"+
							"<td>"+licensedTo+"</td>"+
							"<td>"+userAssigned+"</td>"+
							"<td>"+licenses[i].updatedAt+"</td>"+
							"<td>"+action+"</td>"+
						"</tr>"
					);
				}
			}
		}).fail(function(data){
			
		});
	}
	
	function checkDOMChange()
	{
		// check for any new element being inserted here,
		// or a particular node being modified

		$('.delete-request').confirm({
			text: "Are you sure you want to delete this request?",
			title: "Confirmation Required",
			confirm: function(b){
				sendRequest(b, 'delete');
			},
			cancel: function(b){

			},
			confirmButton:"Yes I am",
			cancelButton:"No",
			confirmButtonClass: "btn-danger",
			cancelButtonClass: "btn-default",
		});
		
		$('.checkin-license').confirm({
			text: "Are you sure you want to check in this license?",
			title: "Confirmation Required",
			confirm: function(b){
				sendRequest(b, 'checkin');
			},
			cancel: function(b){

			},
			confirmButton:"Yes I am",
			cancelButton:"No",
			confirmButtonClass: "btn-danger",
			cancelButtonClass: "btn-default",
		});

		// call the function again after 100 milliseconds
		setTimeout( checkDOMChange, 500 );
	}

	checkDOMChange();
});