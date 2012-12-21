jQuery(document).ready(function($){
    
    var add_params = {action: 'assoc_list', page_id: $('.seeya_pages_list').val()};
    $('.seeya_assoc_ajax_frame').load(ajaxurl, add_params);
    

    $('.seeya_pages_list').on('change', function(){
        add_params = {action: 'assoc_list', page_id: $(this).val()};
        $('.seeya_assoc_ajax_frame').load(ajaxurl, add_params);
    });
    
    
    
    


    var edit_params = {action: 'assoc_list', seeya_edit_question_slug: $('.seeya_edit_question_slug').val()};
    $.post( ajaxurl, 
            edit_params, 
            seeya_edit_question_callback,
            'json'
            );
    
    $('.seeya_edit_question_slug').on('change', function(){
        $('.seeya_preview_frame .seeya_success').html('');
        edit_params = {action: 'assoc_list', seeya_edit_question_slug: $(this).val()};
        $.post(ajaxurl, 
               edit_params, 
               seeya_edit_question_callback,
               'json'
               );

        });
    
    function seeya_edit_question_callback(response){
        
        $('.seeya_editquestion_text').val(response.question_text);
        $('.seeya_editquestion_answer').val(response.question_answer);
        
    }

    $('input#seeya_radio').on('change', function(){
        $('.seeya_approve_success').html('');
    });
        
   
 });

