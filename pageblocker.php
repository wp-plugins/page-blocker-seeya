<?php
/*
Plugin Name: Pageblocker "Seeya"
Plugin URI: http://pageblocker.seeya.by
Description: A plugin that allows you to block access to static pages by one or more security questions.
Version: 1.0
Author: datarumx
Author URI: http://pageblocker.seeya.by
License: GPLv2 or later.
*/

define('SEEYA_PLUGIN_URL', plugin_dir_url(__FILE__));
define('SEEYA_PLUGIN_DIR', plugin_dir_path(__FILE__));




function seeya_install(){
    
    global $wpdb;
    $table_questions = $wpdb->prefix.'seeya_questions_table';
    $table_assoc_questions = $wpdb->prefix.'seeya_assoc_questions';
    $query = "CREATE TABLE IF NOT EXISTS $table_questions(
                question_id smallint(5) NOT NULL AUTO_INCREMENT,
                question_slug varchar(255) NOT NULL,
                question_text text NOT NULL,
                question_answer varchar(255) NOT NULL,
                PRIMARY KEY  (question_id)
              )";
    $wpdb->query($query);
    $query = "CREATE TABLE IF NOT EXISTS $table_assoc_questions(
                page_id smallint(5) NOT NULL,
                question_id smallint(5) NOT NULL
              )";
    
    $wpdb->query($query);
    
    add_option('seeya_uninstall_option', '0');
    
}

function seeya_uninstall(){
    
    global $wpdb;
    $table_questions = $wpdb->prefix.'seeya_questions_table';
    $table_assoc_questions = $wpdb->prefix.'seeya_assoc_questions';
    $un_option = get_option('seeya_uninstall_option');
    switch($un_option){
        case '0':
            break;
        case '1':
            $query = "DROP TABLE $table_assoc_questions";
            $wpdb->query($query);
            break;
        case '2':
            $query = "DROP TABLE $table_questions";
            $wpdb->query($query);
            $query = "DROP TABLE $table_assoc_questions";
            $wpdb->query($query);
            delete_option('seeya_uninstall_option');
            break;
        default:
            break;
            
    }
    
    
}



function seeya_css_and_js(){
    
    wp_register_style('pageBlockerSeeyaStyle', SEEYA_PLUGIN_URL.'view/pageBlockerSeeyaStyle.css');
    wp_register_script('scripts', SEEYA_PLUGIN_URL.'view/scripts.js');
    wp_enqueue_style('pageBlockerSeeyaStyle');
    wp_enqueue_script('scripts');
    wp_enqueue_script('jquery');
  
}


function seeya_settings_page(){
    
    global $wpdb;
    
    $table_questions = $wpdb->prefix.'seeya_questions_table';
    $table_assoc_questions = $wpdb->prefix.'seeya_assoc_questions';
    $valid = true;
    $slug_error = '';
    $text_error = '';
    $answer_error = '';
    $slug_success = '';
    $checked_0 = '';
    $checked_1 = '';
    $checked_2 = '';
    
    if($_POST['seeya_approve_submit'] && check_admin_referer('seeya_que_action', 'seeya_nonce')){
        
        $un_option = intval($_POST['seeya_on_deactivation']);
        update_option('seeya_uninstall_option', $un_option);
        $approve_success = 'Your choice has been saved';
    }
        
    $un_option = get_option('seeya_uninstall_option');
    $un_option = 'checked_'.$un_option;
    $$un_option = 'checked';

    if($_POST['seeya_question_submit'] && check_admin_referer('seeya_que_action', 'seeya_nonce')){
        
        $_POST['question_slug']   = esc_html(trim($_POST['question_slug'])); 
        $_POST['question_text']   = esc_html(trim($_POST['question_text']));
        $_POST['question_answer'] = esc_html(strtolower(trim($_POST['question_answer'])));
        
        if($_POST['question_slug']==''){
            $slug_error = '       you have forgotten to enter question slug';
            $valid = false;
        }
        if(strlen($_POST['question_slug'])>30){
            $slug_error = '       question slug is too long, up to 30 characters allowed';
            $valid = false;
        }
        if($_POST['question_text']==''){
            $text_error = '       please, enter the question';
            $valid = false;
        }
        if($_POST['question_answer']==''){
            $answer_error = '       the question must have answer';
            $valid = false;
        }
        
        if($valid == true){
            $wpdb->query($wpdb->prepare("INSERT INTO $table_questions VALUES(NULL, %s, %s, %s)", $_POST['question_slug'], $_POST['question_text'], $_POST['question_answer'] ));
            $slug_success = '       question has been added to the database';
            $_POST['question_slug']   = ''; 
            $_POST['question_text']   = '';
            $_POST['question_answer'] = '';
        }
     
    }
    
        
   
    
    
    if($_POST['seeya_edit_question_submit'] && check_admin_referer('seeya_que_action', 'seeya_nonce')){
        
        global $wpdb;
    
        $table_questions = $wpdb->prefix.'seeya_questions_table';
        $valid = true;
        $text_error = '';
        $answer_error = '';
        $edit_success = '';
        
        $seeya_edit_question_id = intval($_POST['seeya_edit_question_id']);
        $_POST['question_text']   = esc_html(trim($_POST['question_text']));
        $_POST['question_answer'] = esc_html(strtolower(trim($_POST['question_answer'])));
        
        if($_POST['question_text']==''){
            $text_error = "       please, don't leave the field empty";
            $valid = false;
        }
        if($_POST['question_answer']==''){
            $answer_error = '       the question must have an answer';
            $valid = false;
        }
        
        if($valid == true){
            $wpdb->query($wpdb->prepare("UPDATE $table_questions SET question_text=%s, question_answer=%s WHERE question_id=%d",  $_POST['question_text'], $_POST['question_answer'], $seeya_edit_question_id ));
            $edit_success = '       the changes have been saved';
        }
        
        $_POST['question_text']   = '';
        $_POST['question_answer'] = '';
        
        
    }
    
    
    if($_POST['seeya_delete_question_submit'] && check_admin_referer('seeya_que_action', 'seeya_nonce')){
    
        global $wpdb;
        $table_questions = $wpdb->prefix.'seeya_questions_table';
        $table_assoc_questions = $wpdb->prefix.'seeya_assoc_questions';
                
        $seeya_edit_question_id = intval($_POST['seeya_edit_question_id']);
        
        $wpdb->query($wpdb->prepare("DELETE FROM $table_questions WHERE question_id=%d", $seeya_edit_question_id ));
        $wpdb->query($wpdb->prepare("DELETE FROM $table_assoc_questions WHERE question_id=%d", $seeya_edit_question_id ));
        $edit_success = 'Question has been deleted';
        
    }
    
  
    
    
    if($_POST['add_question'] && check_admin_referer('seeya_que_action', 'seeya_nonce')){
        
        $seeya_page_id     = intval($_POST['seeya_page_id']);
        $seeya_question_id = intval($_POST['seeya_question_id']);
        
        $check = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_assoc_questions WHERE page_id=%d AND question_id=%d", $seeya_page_id, $seeya_question_id));
        if($check == 0){
            $wpdb->query($wpdb->prepare("INSERT INTO $table_assoc_questions VALUES(%d, %d)", $seeya_page_id, $seeya_question_id));
        }
  
    }
    
    $questions = $wpdb->get_results("SELECT * FROM $table_questions", OBJECT);
    
    
    $pages = get_pages();
    foreach($pages as $key => $page){
       $level = count(get_ancestors($page->ID, 'page'));
       $level_prefix = '';
       for($i=0; $i<$level; $i++){
           $level_prefix .= '-';
       }
       $page->post_title = $level_prefix.$page->post_title;
    }

    include SEEYA_PLUGIN_DIR.'view/settingsView.phtml';
    
}



function seeya_settings_menu(){
    add_submenu_page("plugins.php", "\"Seeya\" settings", "\"Seeya\" settings", 8, "seeya-settings", 'seeya_settings_page');
}


function ajax_assoc_list(){
    
    global $wpdb;
    $table_questions       = $wpdb->prefix.'seeya_questions_table';
    $table_assoc_questions = $wpdb->prefix.'seeya_assoc_questions';
    $seeya_page_id         = intval($_POST['page_id']);
    $seeya_question_id     = intval($_POST['question_id']);
    
    if($_POST['question_id']){
        
        $wpdb->query($wpdb->prepare("DELETE FROM $table_assoc_questions WHERE page_id=%d AND question_id=%d", $seeya_page_id, $seeya_question_id));
  
    }
    
    if($_POST['seeya_edit_question_slug']){
        $seeya_edit_question_slug = intval($_POST['seeya_edit_question_slug']);
        
        $row = $wpdb->get_row($wpdb->prepare("SELECT question_text, question_answer FROM $table_questions WHERE question_id=%d", $seeya_edit_question_slug), OBJECT);
        echo json_encode($row);
        die();
    }
    
    
    
    $rows = $wpdb->get_results($wpdb->prepare("SELECT q.question_id, q.question_slug 
                                                FROM  $table_questions q 
                                                RIGHT JOIN $table_assoc_questions a 
                                                ON q.question_id=a.question_id 
                                                WHERE a.page_id=%d", $seeya_page_id), OBJECT);
    
    include SEEYA_PLUGIN_DIR.'view/assocView.phtml';
    
    die();
}



function seeya_content_blocker($content){
    
    
    global $wpdb, $wp_query;
    $table_questions       = $wpdb->prefix.'seeya_questions_table';
    $table_assoc_questions = $wpdb->prefix.'seeya_assoc_questions';
    $page_id = $wp_query->queried_object->ID;
    $correct_answers = array();
    
    if($_POST['seeya_submit_given_answers']){
    
        $answers = $wpdb->get_results($wpdb->prepare("SELECT q.question_answer
                                                    FROM $table_questions q
                                                    RIGHT JOIN $table_assoc_questions a
                                                    ON q.question_id = a.question_id
                                                    WHERE a.page_id=%d", $page_id), OBJECT);

        foreach($answers as $answer){
            $correct_answers[] = strtolower($answer->question_answer);
        }
        
        $difference = array_diff($correct_answers, $_POST['seeya_given_answers']);
        if(count($difference) == 0){
            $_SESSION['seeya_proceed_ok'][$page_id] = 'ok';
        }
   
    }
    
    
    
    if($_SESSION['seeya_proceed_ok'][$page_id]=='ok') return $content;
        
    $questions = $wpdb->get_results($wpdb->prepare("SELECT q.question_text
                                                    FROM $table_questions q
                                                    RIGHT JOIN $table_assoc_questions a
                                                    ON q.question_id = a.question_id
                                                    WHERE a.page_id=%d", $page_id), OBJECT);
    if(!$questions){
        return $content;
    }
    
        
    if(is_page()){
        ob_start();
        include SEEYA_PLUGIN_DIR.'view/blockerView.phtml';
        $content = ob_get_contents();
        ob_end_clean();
    }
    return $content;
}


function seeya_comments_blocker($content){
    
            
    global $wpdb, $wp_query;
    $table_assoc_questions = $wpdb->prefix.'seeya_assoc_questions';
    $page_id = $wp_query->queried_object->ID;
    
    if($_SESSION['seeya_proceed_ok'][$page_id]=='ok') return $content;
    
    $count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_assoc_questions WHERE page_id=%d", $page_id));
    
    if($count == 0){
        return $content;
    }
    
    if(is_page()){
        return array();
    }
    return $content;
    
}



function seeya_comment_form_blocker($open, $post_id){
    
        
    if($_SESSION['seeya_proceed_ok'][$post_id]=='ok') return $open;
    
    global $wpdb;
    $table_assoc_questions = $wpdb->prefix.'seeya_assoc_questions';
        
    $count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_assoc_questions WHERE page_id=%d", $post_id));
    if($count == 0){
        return $open;
    }
 
    $post = get_post($post_id);
    
    if ($post->post_type=='page')
        return false;
    
    return $open;
}



function seeya_session_start(){

    if(!session_id()) session_start();
      
}




add_action('init', 'seeya_session_start');
add_action('admin_enqueue_scripts', 'seeya_css_and_js');
add_action('admin_menu', 'seeya_settings_menu');
add_action('wp_ajax_assoc_list', 'ajax_assoc_list');


add_filter('the_content', 'seeya_content_blocker');
add_filter('comments_array', 'seeya_comments_blocker');
add_filter('comments_open', 'seeya_comment_form_blocker', 10, 2);

register_activation_hook(SEEYA_PLUGIN_DIR.'pageblocker.php', 'seeya_install');
register_deactivation_hook(SEEYA_PLUGIN_DIR.'pageblocker.php', 'seeya_uninstall');

