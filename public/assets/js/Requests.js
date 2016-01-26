var Requests = function(){
	this.setProperties();

	//bind to jQuery
	$(this.init);
}

Requests.prototype.setProperties = function()
{
	this.siteUrl = 'localhost/snipe/public';
	this.container = $('.content');
	this.reqType = $('.btn-group').data('type');
	this.url = $('.btn-group').data('url');
	this.role = $('.btn-group').data('role');
	this.table = $('#requests table');
	this.tableRow = '';
	this.deleteBtn = '.del-request';
	this.toggleBtn = '.btn-group button';
        this.clear = false;
	
	that = this;
}

Requests.prototype.init = function()
{
	that.populateRequests();
	
	that.checkDomChange();
	
	that.container.on('click', that.toggleBtn, that.toggleTypes);
}

Requests.prototype.populateRequests = function()
{
	that.getRequests();
}

Requests.prototype.checkDomChange = function()
{
	$(that.deleteBtn).confirm({
		text: "Are you sure you want to delete this request?",
		title: "Confirmation Required",
		confirm: function(b){
			that.delete(b);
		},
		cancel: function(b){

		},
		confirmButton:"Yes I am",
		cancelButton:"No",
		confirmButtonClass: "btn-danger",
		cancelButtonClass: "btn-default",
	});
	
	setTimeout(that.checkDomChange,500);
}

Requests.prototype.toggleTypes = function(e)
{   
    if(that.clear){
        e.preventDefault() ? e.preventDefault() : e.returnValue = false;
        $(this).siblings('.active').removeClass('active');
        $(this).addClass('active');

        var url = $(this).data('url');
        //var type = $(this).data('type');
        that.reqType = $(this).data('type');
        that.clearTable(function(){
           that.clear = false;
           that.getRequests();
        });
    }
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
                        that.clear = true;
		}
	});
}

Requests.prototype.populateTable = function(data)
{
	var requests = data.requests;
	var roleId = data.roleId; 

	if( Object.prototype.toString.call( requests ) === '[object Array]' ) {
            for(var i=0;i<requests.length;i++){
                    var obj = requests[i];

                    //format the license names
                    if(obj['lcnsTypes']) {
                            obj['lcnsNames'] = '';
                            for(x=0;x<obj['lcnsTypes'].length;x++){
                                    obj['lcnsNames'] += obj['lcnsTypes'][x]['name'];
                                    (x == (obj['lcnsTypes'].length -1) ? '' : obj['lcnsNames'] += ', ' );
                            }
                            delete obj['lcnsTypes'];
                    }

                    //create the table heade and the table rows for each request
                    that.tableHeader = "<tr id='request-'"+obj['id']+">";
                    var tableRow = "<tr id='request-"+obj['id']+"'>";

                    for(var key in obj){
                            if(key!='actions'){
                                    that.tableHeader += "<td>"+key+"</td>";
                                    if(key=='id') {
                                            tableRow += "<td><a href='request/"+obj[key]+"'>"+obj[key]+"</td>"
                                    }
                                    else{
                                            tableRow += "<td>"+obj[key]+"</td>";
                                    }
                            }
                    }

                    that.tableHeader += "<td>actions</td>";
                    that.tableHeader += "</tr>";

                    tableRow += "<td class='actions'>"+obj['actions']+"</td>";
                    tableRow +="</tr>";

                    that.table.append(tableRow);
            }
            that.table.find('thead').empty().append(that.tableHeader);
	}
	else{
            that.table.find('tbody').append("<td>"+requests+"</td>");
	}

	that.table.fadeIn();
}

Requests.prototype.clearTable = function(_callback)
{
	that.table.fadeOut();
	that.table.find('thead').empty();
	$.when(that.table.find('tbody').empty()).then(_callback());
}

Requests.prototype.delete = function(b)
{	
	var reqId = $(b).parents('tr').attr('id');
	var url = $(b).attr('href');

	$.ajax({
		url:url,
		type:'DELETE',
		dataType:'json',
		success:function(data, status, xhr){
			if(!data.error && xhr.status == 200) {
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