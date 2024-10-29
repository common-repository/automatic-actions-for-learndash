<?php

//TODO record the triggers-actions on the db

function traa_ldactions_perform_action($action_trigger, $user_id, $post_id) {

    //Checking the action to execute
    if(empty($action_trigger['action']['target']) || empty($user_id)) {
        return;
    }
    $target = $action_trigger['action']['target'];
    
    //role_change to be resolved here
    if($target == 'role_change') {
        if(empty($action_trigger['action']['role_change']) || !is_array($action_trigger['action']['role_change'])) {
            return;
        }
        $role_change = $action_trigger['action']['role_change'];
        if(empty($role_change['new_roles']) || !is_array($role_change['new_roles'])) {
            return;
        }
        $new_roles = $role_change['new_roles'];
        $keep_current = ( !empty($role_change['keep_current']) );

        $user = get_user_by('id', $user_id);
        if(empty($user)) {
            return;
        }
        //if $keep_current is true, we keep the current roles; else, we remove the current roles
        if(!$keep_current) { 
            $user->set_role(''); //remove all roles
        }
        //add the $new_roles to the user
        foreach($new_roles as $new_role) {
            $user->add_role($new_role);
        }
        return;
    } 

    //Checking the action to execute: course_enroll or group_add
    if(empty($action_trigger['action']['by'])) {
        return;
    }
    $target = $action_trigger['action']['target'];
    $action_by = $action_trigger['action']['by'];

    if(!is_array($action_by)) {
        return;
    }
    
    foreach($action_by as $by => $items_array) {
        if(empty($items_array)) {
            continue;
        }
        if($by == 'category') {
            traa_ldactions_action_by_cat_id($user_id, $items_array, $target);
        }
        if($by == 'access') {
            traa_ldactions_action_by_access_mode($user_id, $items_array, $target);
        }
        if($by == 'title') {
            foreach($items_array as $id) {
                if($target == 'course_enroll') {
                    ld_update_course_access( $user_id, absint($id), $remove = false );
                } else if($target == 'group_add') {
                    ld_update_group_access( $user_id, absint($id), $remove = false );
                }
            }
        }
    }
    do_action('traa_ldactions_action_performed', $action_trigger, $user_id, $post_id);
    return;
}

function traa_ldactions_get_by_ld_cat_id($array_ids, $type) {
    $post_type = '';
    if($type == 'course') {
        $post_type = 'sfwd-courses';
    } else if($type = 'group') {
        $post_type = 'groups';
    } else {
        return;
    }

    $taxonomy = 'ld_' . $type . '_category';

    $ids = get_posts(array(
        'fields'          => 'ids',
        'posts_per_page'  => -1,
        'post_type' => $post_type,
        'tax_query' => array(
            array(
                'taxonomy' => $taxonomy,
                'field' => 'term_id',
                'terms' => $array_ids
            )
        )
    ));
    return $ids;
}

function traa_ldactions_action_by_cat_id($user_id, $array_ids, $target) {
    $type = '';
    if($target == 'course_enroll') {
        $type = 'course';
    } else if($target == 'group_add') {
        $type = 'group';
    } else {
        return;
    }

    $ids = traa_ldactions_get_by_ld_cat_id($array_ids, $type);
    if(empty($ids)) {
        return;
    }
    foreach($ids as $id) {
        if($target == 'course_enroll') {
            ld_update_course_access( $user_id, absint($id), $remove = false );
        } else if($target == 'group_add') {
            ld_update_group_access( $user_id, absint($id), $remove = false );
        }
    }
    return;
}

function traa_ldactions_action_by_access_mode($user_id, $access_mode, $action_target) {
    $possible_modes = ['paynow', 'subscribe', 'closed', 'free'];
    if(!is_array($access_mode)) {
        if(!in_array($access_mode, $possible_modes)) {
            return;
        }
        $access_mode = [$access_mode];
    }
    //just in case, remove from array the not possible modes
    $access_mode = array_intersect($access_mode, $possible_modes);
    if(empty($access_mode)) {
        return;
    }
    $post_type = '';
    if($action_target == 'course_enroll') {
        $post_type = 'sfwd-courses';
    } else if($action_target == 'group_add') {
        $post_type = 'groups';
    } else {
        return;
    }

    foreach($access_mode as $mode) {
        $ids = learndash_get_posts_by_price_type( $post_type, $mode );
        if(empty($ids)) {
            continue;
        }
        foreach($ids as $id) {
            if($action_target == 'course_enroll') {
                ld_update_course_access( $user_id, absint($id), $remove = false );
            } else if($action_target == 'group_add') {
                ld_update_group_access( $user_id, absint($id), $remove = false );
            }
        }
    }
    return;
}

function traa_ldactions_is_enabled($action_trigger) {
    return isset($action_trigger['enabled']) && $action_trigger['enabled'];
}

function traa_ldactions_perform_action_for_register_or_login_trigger($user_id, $trigger = 'register') {
    //TODO: transients, so we won't need to get all posts everytime

    //get wp posts ids by ld-actions post_type
    $ldactions_ids = get_posts(array(
        'fields'          => 'ids',
        'posts_per_page'  => -1,
        'post_type' => 'ld-actions',
        'status' => 'publish'
    ));

    if(empty($ldactions_ids)) {
        return;
    }

    foreach($ldactions_ids as $post_id) {
        $action_trigger = traa_ldactions_get_ldaction_metadata_by_post_id($post_id);
        if(empty($action_trigger) || !is_array($action_trigger)) {
            continue;
        }

        if(!traa_ldactions_is_enabled($action_trigger)) {
            continue;
        }

        //get trigger target. Not register? Goodbye
        if(empty($action_trigger['trigger']['target']) || $action_trigger['trigger']['target'] != 'register') {
            continue;
        }
        //register! Kind?
        if(empty($action_trigger['trigger']['which']['kind'])) {
            continue; 
        }

        //if trigger login, see if the login checkbox is clicked
        if($trigger == 'login' && empty($action_trigger['trigger']['which']['login']) ) { 
            continue;
        }

        if($action_trigger['trigger']['which']['kind'] != 'any') { 
            //Special. let's check the unique code
            global $_POST;
            //validation
            if(empty($_POST['traa_ldaction']) || strlen($_POST['traa_ldaction']) !== 10) { 
                continue;
            }
            //sanitization
            $traa_ldaction = trim(sanitize_text_field($_POST['traa_ldaction']));

            if(empty($action_trigger['trigger']['which']['unique_code'])) {
                continue;
            }
            if($traa_ldaction !== trim($action_trigger['trigger']['which']['unique_code'])) {
                continue;
            }

            //If we came here, the unique code is correct. Let's keep going
        }

        //if we came here, perform the action
        traa_ldactions_perform_action($action_trigger, $user_id, $post_id);
    } 

}

//User Register Action (Any or Special)
function traa_ldactions_trigger_action_for_register($user_id) {
    traa_ldactions_perform_action_for_register_or_login_trigger($user_id);
}
add_action('user_register', 'traa_ldactions_trigger_action_for_register');

//User Register Form (Special)
//Insert a hidden field in the registration form with a traa_ldaction value if there is a query var called "traa_ld_action" not empty
add_action( 'register_form', function() {
	if(!empty($_GET['traa_ldaction']) && strlen($_GET['traa_ldaction']) === 10) { 
        $traa_ldaction = trim(sanitize_text_field( $_GET['traa_ldaction'] ));
        ?>
		<input type="hidden" name="traa_ldaction" value="<?php echo esc_attr($traa_ldaction); ?>" />
	<?php }
});

//User Login Action (Any or Special)
function traa_ldactions_trigger_action_for_login($user_login, WP_User $user) {
    $user_id = $user->ID;
    if(!$user_id) {
        return;
    }
    traa_ldactions_perform_action_for_register_or_login_trigger($user_id, 'login');
}
add_action('wp_login', 'traa_ldactions_trigger_action_for_login', 10, 2);

//User Login Form (Special)
//Insert a hidden field in the login form with a traa_ldaction value if there is a query var called "traa_ld_action" not empty
add_action( 'login_form', function() {
	if(!empty($_GET['traa_ldaction']) && strlen($_GET['traa_ldaction']) === 10) { 
        $traa_ldaction = trim(sanitize_text_field( $_GET['traa_ldaction'] ));
        ?>
		<input type="hidden" name="traa_ldaction" value="<?php echo esc_attr($traa_ldaction); ?>" />
	<?php }
});

//Complete course action
function traa_trigger_action_for_course_complete($data) {

    $user_id = $data['user']->ID;
    $course_id = $data['course']->ID;

    //TODO: transients, so we won't need to get all posts everytime

    //get wp posts ids by ld-actions post_type
    $ldactions_ids = get_posts(array(
        'fields'          => 'ids',
        'posts_per_page'  => -1,
        'post_type' => 'ld-actions',
        'status' => 'publish'
    ));

    if(empty($ldactions_ids)) {
        return;
    }

    foreach($ldactions_ids as $post_id) {
        $action_trigger = traa_ldactions_get_ldaction_metadata_by_post_id($post_id);
        if(empty($action_trigger) || !is_array($action_trigger)) {
            continue;
        }

        if(!traa_ldactions_is_enabled($action_trigger)) {
            continue;
        }

        //get trigger target. Not complete_course? Goodbye
        if(empty($action_trigger['trigger']['target']) || $action_trigger['trigger']['target'] != 'complete_course') {
            continue;
        }
        //complete_course! Kind?
        if(empty($action_trigger['trigger']['which']['kind'])) {
            continue; 
        }

        if($action_trigger['trigger']['which']['kind'] != 'any') { 
            //Special. Let's check if the course is the one we want
            if(empty($action_trigger['trigger']['which']['by']) || !is_array($action_trigger['trigger']['which']['by'])) {
                continue;
            }
            $go = false;
            $items_by = $action_trigger['trigger']['which']['by'];
            foreach($items_by as $by => $array_by) {
                if($by == 'category') {
                    //check if $course_id is in the ld category array
                    if(has_term($array_by,'ld_course_category', $course_id)) {
                        $go = true;
                        continue; //we don't need to check the other $by criteria
                    }
                }
                if($by == 'access') {
                    
                    $course_price = learndash_get_course_price( $course_id );

                    if(empty($course_price['type'])) {
                        continue; 
                    }

                    if(in_array($course_price['type'], $array_by)) {
                        $go = true;
                        continue; //we don't need to check the other $by criteria
                    }
                }
                if($by == 'title') {
                    if(in_array($course_id, $array_by)) {
                        $go = true;
                        continue; //we don't need to check the other $by criteria
                    }
                }
            }
            if(!$go) {
                continue;
            }
        }
        
        //if we came here, the course is a target. perform the action
        traa_ldactions_perform_action($action_trigger, $user_id, $post_id);
    } 
}
add_action('learndash_course_completed', 'traa_trigger_action_for_course_complete', 20);

//hook action performed
add_action('traa_ldactions_action_performed', 'traa_ldactions_action_performed_save_postmeta', 999, 3);
function traa_ldactions_action_performed_save_postmeta($action_trigger, $user_id, $post_id) {

    //See if they want us to ignore execution data
	$ignore_meta = get_post_meta($post_id, 'traa_ldactions_executions_ignore_clean', true);
	if( !empty($ignore_meta) && in_array('ignore', $ignore_meta) ) {
        //abort...
		return;
	}

    //let's save the action execution data
    $times_executed = get_post_meta($post_id, 'traa_ldactions_times_executed', true);
    $executions = get_post_meta($post_id, 'traa_ldactions_executions');
    if(empty($times_executed)) {
        $times_executed = 1;
        $executions = [$action_trigger];
    } else {
        $times_executed = absint($times_executed) + 1;
        $executions[] = $action_trigger;
    }
    
    update_post_meta($post_id, 'traa_ldactions_times_executed', $times_executed);
    update_post_meta($post_id, 'traa_ldactions_executions', $executions);

    //let's save the user execution data on the general counter
    traa_ldactions_add_to_all_actions_option();
}

//delete post meta on save ld-actions custom post type if 'clean' is checked
add_action( 'save_post', 'traa_ldactions_executions_clean', 9999, 3 );
function traa_ldactions_executions_clean( $post_id, $post, $update ) {

	if(!$update || traa_ldactions_return_early_for_save_hook($post_id, $post)) {
		return;
	}

	//See if the clean executions checkbox is checked
	$clean_meta = get_post_meta($post_id, 'traa_ldactions_executions_ignore_clean', true);
	if( empty($clean_meta) || !in_array('clean', $clean_meta) ) {
		return;
	}

	//Remove data from 'traa_ldactions_times_executed' and 'traa_ldactions_executions' meta keys
	delete_post_meta($post_id, 'traa_ldactions_times_executed');
	delete_post_meta($post_id, 'traa_ldactions_executions');

	//uncheck the clean executions checkbox
	$clean_meta = array_diff($clean_meta, ['clean']);
	update_post_meta($post_id, 'traa_ldactions_executions_ignore_clean', $clean_meta);
}