var Requests = function(){
	this.setProperties();

	//bind to jQuery
	$(this.init);
}

Requests.prototype.setProperties = function()
{
	this.container = $('.content');
	this.reqType = $('.btn-group').data('type');
	this.url = $('.btn-group').data('url');
	this.role = $('.btn-group').data('role');
	this.table = $('#requests table');
	this.deleteBtn = $('.delete-request');
	this.toggleBtn = '.btn-group button';
	
	that = this;
}

Requests.prototype.init = function()
{
	that.populateRequests();
	that.container.on('click', that.toggleBtn, that.toggleTypes);
	that.container.on('click', that.deleteBtn, that.delete);
}

Requests.prototype.populateRequests = function()
{
	that.getRequests();
}

Requests.prototype.getRequests = function()
{	
	$.ajax({
		url:that.url,
		data:{
			'roleId':that.role,
			'type':that.reqType,
		},
		type:'GET',
		dataType:'json',
		success:function(data){
			that.populateTable(data);
		}
	});
}

Requests.prototype.toggleTypes = function(e)
{
	e.preventDefault() ? e.preventDefault() : e.returnValue = false;

	var url = $(this).data('url');
	//var type = $(this).data('type');
	that.reqType = $(this).data('type');
	that.clearTable();
	that.getRequests();
}

Requests.prototype.populateTable = function(data)
{
	that.populateTableHeader(data);
	that.populateTableBody(data);
}

Requests.prototype.clearTable = function()
{
	that.table.fadeOut();
	that.table.find('tbody').empty();
}

Requests.prototype.populateTableHeader = function()
{	
	that.table.find('thead').empty().append('<tr><td>Requester</td><td>Computer Name</td><td>EC</td><td>Type(s)</td><td>Date Requested</td><td>Actions</td></tr>');
}

Requests.prototype.populateTableBody = function(data)
{
	var requests = data.requests;
	var roleId = data.roleId; 

	for(var i=0;i<requests.length;i++){
		var obj = requests[i];
		//format the license names
		obj['lcnsNames'] = '';
		for(x=0;x<obj['lcnsTypes'].length;x++){
			obj['lcnsNames'] += obj['lcnsTypes'][x]['name'];
			(x == (obj['lcnsTypes'].length -1) ? '' : obj['lcnsNames'] += ', ' );
		}

		var tableRow = "<tr>"+
				"<td>"+obj['requester']+"</td>"+
				"<td>"+obj['pc_name']+"</td>"+
				"<td>"+obj['role']+"</td>"+
				"<td>"+obj['lcnsNames']+"</td>"+
				"<td>"+obj['created_at']+"</td>";
		if(roleId == obj['role_id'] || roleId == 1)
		{
			tableRow += "<td>"+(obj['actions'] ? obj['actions'] : "")+"</td>";
		}
		tableRow +="</tr>";
		that.table.append(tableRow);
	}

	that.table.fadeIn();
}

Requests.prototype.delete = function(e)
{
	e.preventDefault() ? e.preventDefault() : e.returnValue = false;
	var reqId = $(this).attr('id');
	var url = $(this).attr('href');
	/*
	$.ajax({
		url:url,
		type:'DELETE',
		dataType:'json',
		success:function(data){
			if(!data.error)
			{
				var row = $('tr#request-'+reqId);
				row.find('td').css('background-color', 'transparent');
				
				row.addClass('delete-highlight');
				row.fadeOut('slow');
			}
		},
		error:function(response){
			//
		}
	});*/
}