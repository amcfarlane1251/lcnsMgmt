$(function(){
   $('.datepicker').datepicker({
       format:'yyyy/mm/dd'
   });
   
   var isValid;
   $('#lastName, #firstName, #dob').change(function(){
      isValid = true;
      $('#lastName, #firstName, #dob').each(function(){
           if($(this).val()==''){
               isValid = false;
           }
      });

      if(isValid){
        var sfn = "X"+$('#lastName').val().substring(0,2) + $('#firstName').val().substring(0,1) + $('#dob').val().substring(8,10) +
                $('#dob').val().substring(5,7) + $('#dob').val().substring(3,4) + "@";
        $('#sfn').val(sfn.toUpperCase());
      }
   });
});