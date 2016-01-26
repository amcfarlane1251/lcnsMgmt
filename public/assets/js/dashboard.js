$(function(){
    $('#unit-selector').change(function(){
        var url = window.location.href ;
        var unitId = $(this).val();
        var licenseTable = $('#license-info tbody');
		var assetTable = $('#asset-info tbody');
        $.ajax({
            type:"GET",
            url:url,
            data:{
              unitId: unitId,  
            },
            success:function(data){
                data = JSON.parse(data);
                if(data.success) {
                    var licenses = data.licenses;
					var assets = data.assets;
                    
                    licenseTable.fadeOut("fast", function(){
                        $(this).empty();
                        
                        $.each(licenses, function(i, val) {
                            licenseTable.append("<tr>\n\
                                    <td>\n\
                                        "+val.name+"\n\
                                    </td>\n\
                                    <td>\n\
                                        "+val.allocated+"\n\
                                    </td>\n\
                                    <td>\n\
                                        "+val.used+"\n\
                                    </td>\n\
                                    <td>\n\
                                        "+val.remaining+"\n\
                                    </td>\n\
                                </tr>").fadeIn();
                        });
                    });
					
					assetTable.fadeOut("fast", function(){
                        $(this).empty();
                        
                        $.each(assets, function(i, val) {
                            assetTable.append("<tr>\n\
                                    <td>\n\
                                        "+val.model+"\n\
                                    </td>\n\
                                    <td>\n\
                                        "+val.assetTag+"\n\
                                    </td>\n\
                                    <td>\n\
                                        "+val.numOfLcns+"\n\
                                    </td>\n\
                                    <td>\n\
                                        "+val.location+"\n\
                                    </td>\n\
                                </tr>").fadeIn();
                        });
                    });

                    $('#unit-name').html(data.unit.name);
               }
            },
            error:function(data){
                console.log('error');
            }
        });
    });
});
