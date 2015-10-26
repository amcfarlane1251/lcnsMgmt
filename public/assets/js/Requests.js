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
	this.tableRow = '';
	this.deleteBtn = '.delete-request';
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
	//that.populateTableHeader(data);
	that.populateTableBody(data);
}

Requests.prototype.clearTable = function()
{
	that.table.fadeOut();
	that.table.find('tbody').empty();
}

Requests.prototype.populateTableHeader = function(data)
{	
	that.tableRow = "<tr>";
	for(var key in data.header){
		that.tableRow += "<td>"+data.header[key]+"</td>";
	}
	that.tableRow += "</tr>";

	that.table.find('thead').empty().append(that.tableRow);
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
		delete obj['lcnsTypes'];

		//create the table row for the request
		that.tableHeader = "<tr id='request-'"+obj['id']+">";
		var tableRow = "<tr id='request-"+obj['id']+"'>";
		for(var key in obj){
			if(key!='actions' && key!='id'){
				that.tableHeader += "<td>"+key+"</td>";
				tableRow += "<td>"+obj[key]+"</td>";
			}
		}
		that.tableHeader += "<td>actions</td>";
		that.tableHeader += "</tr>";
		tableRow += "<td class='actions'>"+obj['actions']+"</td>";
		tableRow +="</tr>";

		that.table.find('thead').empty().append(that.tableHeader);
		that.table.append(tableRow);
	}

	that.table.fadeIn();
}

Requests.prototype.delete = function(e)
{
	e.preventDefault() ? e.preventDefault() : e.returnValue = false;
	var reqId = $(e.target).parents('tr').attr('id');
	var url = $(e.target).parent('a').attr('href');

	$.ajax({
		url:url,
		type:'DELETE',
		dataType:'json',
		success:function(data){
			if(!data.error)
			{
				var row = $('tr#'+reqId);
			
				row.find('td').css('background-color', 'transparent');
				row.addClass('delete-highlight').delay(500).queue(function(next){
					row.fadeOut('slow');
					next();
				});
			}
		},
		error:function(response){
			//
		}
	});
}