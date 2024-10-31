jQuery(document).ready(function(){
    var $ = jQuery;
    $('.ohmylead_map_field').on('change',function(){
//            console.log($('.ohmylead_map_field').find('option').val());
  //          $('.ohmylead_map_field').find('option').val();
        MapFieldOhmylead($(this).val());
    });

    function MapFieldOhmylead(val){
        $('.ohmylead_map_field').each(function(){
            $(this).find('option').each(function(){
                if( $(this).val() == val){
                    console.log($(this).val());
                    $(this).attr('disabled','disabled');
                }
            });

        });
    }
});