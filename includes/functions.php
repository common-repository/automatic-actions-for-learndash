<?php

function traa_aefl_enroll_by_type($user_id, $post, $type) {
	$post_ids = learndash_get_posts_by_price_type( $post, $type, $bypass_transient = false );
	if ( empty( $posts ) ) {
		return;
	}
	if($type == 'sfwd-courses') {
		foreach($post_ids as $id) {
			ld_update_course_access( $user_id, $id, $remove = false );
		}
		return;
	}
	if($type == 'sfwd-groups') {
		foreach($post_ids as $id) {
			ld_update_group_access( $user_id, $id );
		}
		return;
	}
}

function traa_ldactions_get_ld_course_categories() {
	//get ld_course_category terms in form of anarray with id and name

	$terms = get_terms( array(
		'taxonomy' => 'ld_course_category',
		'hide_empty' => false,
	) );
	$terms_array = array();
	foreach($terms as $term) {
		$terms_array[$term->term_id] = $term->name;
	}	
	return $terms_array;
}

function traa_ldactions_get_ld_group_categories() {
	$terms = get_terms( array(
		'taxonomy' => 'ld_group_category',
		'hide_empty' => false,
	) );
	$terms_array = array();
	foreach($terms as $term) {
		$terms_array[$term->term_id] = $term->name;
	}	
	return $terms_array;
}

function traa_ldactions_get_ld_quiz_categories() {
	$terms = get_terms( array(
		'taxonomy' => 'ld_quiz_category',
		'hide_empty' => false,
	) );
	$terms_array = array();
	foreach($terms as $term) {
		$terms_array[$term->term_id] = $term->name;
	}	
	return $terms_array;
}

function traa_ldactions_log($output, $file_name = 'ldaction') {
		//write to file
		$msg = print_r($output, true) . PHP_EOL;
		$file = fopen(TRAA_LDACTIONS_DIR . '/logs/' . $file_name . '_log.txt', 'a');
		fwrite($file, $msg);
		fclose($file);
		return;
}

function traa_ldactions_get_role_names() {

	global $wp_roles;
	
	if ( ! isset( $wp_roles ) )
		$wp_roles = new WP_Roles();

	$output = $wp_roles->get_names();

	//unset the 'administrator' role
	unset($output['administrator']);
	
	return $output;
}


function traa_ldactions_get_value_from_key($array, $key) {
	if(!empty($array[$key]) && is_array($array[$key])) {
		return maybe_unserialize($array[$key][0]);
	}
	return '';
}

function traa_ldactions_get_ldaction_metadata_by_post_id($post_id) {
    
	$post = get_post($post_id);
    if ($post->post_type != 'ld-actions') {
        return [];
    }

	$output_array = [];
	$output_array['post_id'] = $post_id;
	$output_array['enabled'] = false;
	$output_array['action'] = [];
	$output_array['trigger'] = [];
	
	$output_array['action']['target'] = '';
	$output_array['action']['by'] = [];

	$output_array['trigger']['target'] = '';
	$output_array['trigger']['which'] = [];
	$output_array['trigger']['which']['kind'] = '';

	$post_meta = get_post_meta($post_id);
	if(empty($post_meta) || !is_array($post_meta)) {
		return $output_array;
	}

	//ACTIVATION
	$post_meta_activation = traa_ldactions_get_value_from_key($post_meta, 'traa_ldactions_activation');
	if(!empty($post_meta_activation) && $post_meta_activation == 'enabled') {
		$output_array['enabled'] = true;
	}
	
	//ACTION
	$post_meta_action_target = traa_ldactions_get_value_from_key($post_meta, 'traa_ldactions_action_target');
	if(empty($post_meta_action_target)) {
		return $output_array;
	}
	$output_array['action']['target'] = $post_meta_action_target;
	
	$post_meta_action_section = traa_ldactions_get_value_from_key($post_meta, 'traa_ldactions_action_' . $post_meta_action_target .  '_section');
	if(empty($post_meta_action_section)){
		return $output_array;
	}
	
	foreach($post_meta_action_section as $section ) {
		if(!empty($section['traa_ldactions_select_' . $post_meta_action_target])) {	
			$sources = $section['traa_ldactions_select_' . $post_meta_action_target];
			if($post_meta_action_target != 'role_change') {
				foreach($sources as $source) {
					if(!empty($section['traa_ldactions_' . $post_meta_action_target . '_by_' .  $source])) {
						$output_array['action']['by'][$source] = $section['traa_ldactions_' . $post_meta_action_target . '_by_' .  $source];
					} else {
						$output_array['action']['by'][$source] = [];
					}
				}
			} else {
				$output_array['action']['role_change'] = [
					'new_roles' => $sources,
					'keep_current' => (!empty($section['traa_ldactions_role_change_current']) && $section['traa_ldactions_role_change_current'] == 'keep')
				];
			}
		}
	}

	//TRIGGER
	$post_meta_trigger_target = traa_ldactions_get_value_from_key($post_meta, 'traa_ldactions_trigger_target');
	if(empty($post_meta_trigger_target)) {
		return $output_array;
	}
	$output_array['trigger']['target'] = $post_meta_trigger_target;

	$post_meta_trigger_section = traa_ldactions_get_value_from_key($post_meta, 'traa_ldactions_trigger_' . $post_meta_trigger_target .  '_section');
	if(empty($post_meta_trigger_section)) {
		return $output_array;
	}
	if(is_array($post_meta_trigger_section)) {
		$post_meta_trigger_section = $post_meta_trigger_section[0];
	}
	if(empty($post_meta_trigger_section['traa_ldactions_trigger_' . $post_meta_trigger_target . '_which'])) {
		return $output_array;
	} 

	$which = $post_meta_trigger_section['traa_ldactions_trigger_' . $post_meta_trigger_target . '_which'];
	$output_array['trigger']['which']['kind'] = $which;
	
	// var_dump($post_meta_trigger_section); exit;

	//get login checkbox value if target is register
	if($post_meta_trigger_target == 'register') {
		$output_array['trigger']['which']['login'] = (isset($post_meta_trigger_section['traa_ldactions_trigger_register_login']) && $post_meta_trigger_section['traa_ldactions_trigger_register_login'] == 'on');
	}

	//register or any for all
	if($which == 'any') {
		return $output_array;
	}
	if($post_meta_trigger_target == 'register') {
		//special URL. get the unique code
		$output_array['trigger']['which']['unique_code'] = traa_ldactions_get_value_from_key($post_meta, 'traa_ldactions_unique_code');
		return $output_array;
	}

	$output_array['trigger']['which']['by'] = [];
	//course completion or quiz completion as trigger, with special ones as which
	$sel_item = str_replace("complete_","",$post_meta_trigger_target);

	$select_by = $post_meta_trigger_section['traa_ldactions_trigger_select_' . $sel_item];
	if(empty($select_by) || !is_array($select_by)) {
		return $output_array;
	}

	foreach($select_by as $sb) {
		if(!empty($post_meta_trigger_section['traa_ldactions_trigger_' . $sel_item . '_by_' . $sb])) {
			$output_array['trigger']['which']['by'][$sb] = $post_meta_trigger_section['traa_ldactions_trigger_' . $sel_item . '_by_' . $sb];
		}		
	}
	
	
	traa_ldactions_log($output_array);

	// traa_ldactions_remove_unselected_values($output_array);
	return $output_array;
}

//function to remove values from target not chosen
function traa_ldactions_remove_unselected_values($output_array) {
	if(empty($output_array || $output_array['post_id'])) {
		return;
	}
	$post_id = $output_array['post_id'];
	$target = trim($output_array['target']);
	$sources = $output_array['by'];
	
	//if course is targeted, everything in group must be removed from meta (and vice-versa)
	foreach (LDACTIONSMETAKEYS as $meta_key) {
		if($meta_key == 'traa_ldactions_action_target') {
			continue;
		}
		//if target is not a substring of key, update with empty array
		if(strpos('course_enroll', $target) === false) {
			update_post_meta( $post_id, 'traa_ldactions_action_course_enroll_section', []);
		} else if(strpos('group_add', $target) === false) {
			update_post_meta( $post_id, 'traa_ldactions_action_group_add_section', []);
		}
	}
}

function traa_ldaction_generate_code($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function traaa_ldaction_generate_url($unique_code, $login = false) {
	if(!$login) {
		return esc_url( add_query_arg( array(
			'traa_ldaction' => $unique_code,
		), wp_registration_url() ) );
	}
	return esc_url( add_query_arg( array(
		'traa_ldaction' => $unique_code,
	), wp_login_url() ) );
}

function traa_display_registration_url_for_the_action($field_args, $field) {
	$unique_code = get_post_meta( $field->object_id, 'traa_ldactions_unique_code', true );
	$output = '<p id="traa-registration-url-paragraph"><strong>For Registration:</strong><br>';
	if(empty($unique_code)) {
		$output .= 'Save the action to generate the registration URL for it.</p>';
	} else {
		$output .= '<code>' . traaa_ldaction_generate_url($unique_code) . '</code></p>';
	}
	$output .= '<p id="traa-login-url-paragraph"><strong>For Login:</strong><br>';
	if(empty($unique_code)) {
		$output .= 'Publish the action to generate the login URL for it.</p>';
	} else {
		$output .= '<code>' . traaa_ldaction_generate_url($unique_code, $login = true) . '</code></p>';
	}
	return $output;	
}

function traa_display_execution_times_for_the_action ($field_args, $field) {
	$times = get_post_meta( $field->object_id, 'traa_ldactions_times_executed', true );
	$output = 'Not yet triggered.';
	if($times == 1) {
		$output = 'Triggered <strong><code>1</code></strong> time';
	} elseif($times > 1) {
		$output = 'Triggered <strong><code>' . $times . '</code></strong> times';
	}
	return '<p id="traa-execution-times-paragraph">' . $output . '</p>';
}

function traa_ldactions_return_early_for_save_hook($post_id, $post, $post_type = 'ld-actions') {
	if ( $post->post_type !== $post_type ) {
		return true; 
	}
    if (isset($post->post_status) && 'auto-draft' == $post->post_status) {
        return true;
    }
    // Autosave, do nothing
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return true;
    }
    // AJAX? Not used here
    if (defined('DOING_AJAX') && DOING_AJAX) {
        return true;
    }
    if( wp_is_post_revision( $post_id) || wp_is_post_autosave( $post_id ) ) {
		return true;
	}
	return false;
}

function traa_ldactions_add_to_all_actions_option($ld_option = 'traa_ldactions_all_actions') {
    $all_actions = absint(get_option($ld_option));
    $new_total = $all_actions + 1;
    update_option( $ld_option, $new_total );
}

function traa_ldactions_get_total_number($ld_option = 'traa_ldactions_all_actions') {
    return get_option($ld_option);
}