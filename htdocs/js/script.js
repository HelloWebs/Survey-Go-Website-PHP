$(document).ready(function(){
    $('#qtype').change(
        function(){
                console.log($('#qtype').val())
            if($('#qtype').val()==="number"||$('#qtype').val()==="text" ){

                $(".qoptions").hide();
            }else{
                $(".qoptions").show();

            }
        }
    );

        //Source: https://developers.google.com/chart/interactive/docs/quick_start





    

    


});