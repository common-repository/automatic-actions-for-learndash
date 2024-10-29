<?php

require_once TRAA_LDACTIONS_DIR . '/includes/cmb2/init.php';
require_once TRAA_LDACTIONS_DIR . '/includes/cmb2-attached-posts/cmb2-attached-posts-field.php';
define("LDACTIONSMETAKEYS", [
	'traa_ldactions_action_target', //target

	'traa_ldactions_action_course_enroll_section', //course_enroll
	'traa_ldactions_select_course_enroll', //course_enroll
	'traa_ldactions_course_enroll_by_category', //course_enroll
	'traa_ldactions_course_enroll_by_access', //course_enroll
	'traa_ldactions_course_enroll_by_title', //course_enroll

	'traa_ldactions_action_group_add_section', //group_add
	'traa_ldactions_select_group_add', //group_add
	'traa_ldactions_group_add_by_category', //group_add
	'traa_ldactions_group_add_by_access', //group_add
	'traa_ldactions_group_add_by_title', //group_add

	'traa_ldactions_action_role_change_section', //role_change
	'traa_ldactions_select_role_change', //role_change
	'traa_ldactions_role_change_current', //role_change
]);

add_action( 'cmb2_admin_init', 'traa_ldactions_metaboxes' );
function traa_ldactions_metaboxes() {

    $unique_code = traa_ldaction_generate_code(); 

	//ACTIVATION - ACTIVATION - ACTIVATION
	$cmb_activation = new_cmb2_box( array(
		'id'            => 'traa_ldactions_activation_section',
		'title'         => __( 'Activation', 'automatic-actions-for-learndash' ),
		'object_types'  => array( 'ld-actions', ), // Post type
		'context'       => 'side',
		'priority'      => 'low',
		'show_names'    => false, // Show field names on the left
		// 'cmb_styles' => false, // false to disable the CMB stylesheet
		// 'closed'     => true, // Keep the metabox closed by default
	) );

	$cmb_activation->add_field( array(
		'name'    => __( 'Activation', 'automatic-actions-for-learndash' ),
		'id'      => 'traa_ldactions_activation',
		'type'    => 'radio_inline',
		'options' => array(
			'enabled' => __( 'Enabled', 'automatic-actions-for-learndash' ),
			'disabled'   => __( 'Disabled', 'automatic-actions-for-learndash' ),
		),
		'default' => 'enable',
	) );
	//ACTIVATION - ACTIVATION - ACTIVATION

	//EXECTUTION - EXECUTION - EXEcUTION
	$cmb_execution = new_cmb2_box( array(
		'id'            => 'traa_ldactions_execution_section',
		'title'         => __( 'Execution', 'automatic-actions-for-learndash' ),
		'object_types'  => array( 'ld-actions', ), // Post type
		'context'       => 'side',
		'priority'      => 'low',
		'show_names'    => false, // Show field names on the left
		// 'cmb_styles' => false, // false to disable the CMB stylesheet
		// 'closed'     => true, // Keep the metabox closed by default
	) );

	$cmb_execution->add_field( array(
		// 'name'    => __( 'Execution', 'automatic-actions-for-learndash' ),
		'id'      => 'traa_ldactions_times_executed',
		'type'    => 'title',
		'after' => 'traa_display_execution_times_for_the_action',
		'classes' => 'traa-ldactions-execution-row',
	) );

	$cmb_execution->add_field( array(
		// 'name'    => __( '', 'automatic-actions-for-learndash' ),
		// 'desc'    => __( '', 'automatic-actions-for-learndash' ),
		'id'      => 'traa_ldactions_executions_ignore_clean',
		'type'    => 'multicheck_inline',
		'select_all_button' => false,
		'options' => array(
			'ignore' => __( 'Ignore executions', 'automatic-actions-for-learndash' ),
			'clean' => __( 'Clean executions data', 'automatic-actions-for-learndash' ),
		),
		'classes' => 'traa-ldactions-executions-save-remove-row',
	) );
	//EXECTUTION - EXECUTION - EXEcUTION


    //ACTIONS - ACTIONS - ACTIONS
	$cmb_actions = new_cmb2_box( array(
		'id'            => 'traa_ldactions_action_settings',
		'title'         => __( 'Action Settings', 'automatic-actions-for-learndash' ),
		'object_types'  => array( 'ld-actions', ), // Post type
		'context'       => 'normal',
		'priority'      => 'high',
		'show_names'    => true, // Show field names on the left
		// 'cmb_styles' => false, // false to disable the CMB stylesheet
		// 'closed'     => true, // Keep the metabox closed by default
		'classes' => array( 'traa-ldactions-box', 'traa-ldactions-box-action' ),
	) );

	$cmb_actions->add_field( array(
		'name' => 'Define Action',
		'desc' => 'In this box you can stablish an action to be performed when the user reaches the specified conditions.',
		'type' => 'title',
		'id'   => 'traa_ldactions_action_settings_title',
	) );

    //TARGET - TARGET - TARGET
	$cmb_actions->add_field( array(
		'name'    => __( 'Target', 'automatic-actions-for-learndash' ),
		'id'      => 'traa_ldactions_action_target',
		'type'    => 'radio_inline',
		'options' => array(
			'course_enroll' => __( 'Enroll user in COURSE', 'automatic-actions-for-learndash' ),
			'group_add'   => __( 'Add user to GROUP', 'automatic-actions-for-learndash' ),
			'role_change'   => __( 'Change user ROLE', 'automatic-actions-for-learndash' ),
		),
		'default' => 'course_enroll',
		'column' => array(
			'position' => 2,
			'name'     => 'Action',
		),
	) );
    //TARGET - TARGET - TARGET


    //COURSE ENROLL - COURSE ENROLL - COURSE ENROLL
	$course_enroll_section = $cmb_actions->add_field( array(
		'id'          => 'traa_ldactions_action_course_enroll_section',
		'type'        => 'group',
		'description' => __( 'Select Course(s) in This Section', 'automatic-actions-for-learndash' ),
		'repeatable'  => false,
		'options'     => array(
			'group_title'       => __( 'Course(s) Selection', 'automatic-actions-for-learndash' ),
			// 'add_button'        => __( 'Add Another Entry', 'cmb2' ),
			// 'remove_button'     => __( 'Remove Selection', 'automatic-actions-for-learndash' ),
			'sortable'          => false,
			// 'closed'         => true, // true to have the groups closed by default
			// 'remove_confirm' => esc_html__( 'Are you sure you want to remove?', 'cmb2' ), // Performs confirmation before removing group.
		),
		'classes' => 'traa-ldactions-action-course-enroll-section-row',
	) );

	$cmb_actions->add_group_field( $course_enroll_section, array(
		'name'    => __( 'Select course by', 'automatic-actions-for-learndash' ),
		'desc'    => __( 'Choose how to select course(s)', 'automatic-actions-for-learndash' ),
		'id'      => 'traa_ldactions_select_course_enroll',
		'type'    => 'multicheck_inline',
		'select_all_button' => false,
		'options' => array(
			'category' => __( 'LD Category', 'automatic-actions-for-learndash' ),
			'access' => __( 'LD Access Mode', 'automatic-actions-for-learndash' ),
			'title' => __( 'Title', 'automatic-actions-for-learndash' ),
		),
		'classes' => 'traa-ldactions-select-course-enroll-row',
	) );

	$cmb_actions->add_group_field( $course_enroll_section, array(
		'name'           => __( 'Course by LD Category','automatic-actions-for-learndash' ),
		'desc'           => __( 'Select one or more LearnDash Course Categories','automatic-actions-for-learndash' ),
		'id'             => 'traa_ldactions_course_enroll_by_category',
		'type'           => 'multicheck_inline',
		'select_all_button' => false,
		'options_cb'     => 'traa_ldactions_get_ld_course_categories',
		'classes' => 'traa-ldactions-course-enroll-by-category-row',
	) );

	$cmb_actions->add_group_field( $course_enroll_section, array(
		'name'           => __( 'Course by LD Access Mode','automatic-actions-for-learndash' ),
		'desc'           => __( 'Select one or more LearnDash Access Modes','automatic-actions-for-learndash' ),
		'id'             => 'traa_ldactions_course_enroll_by_access',
		'type'           => 'multicheck_inline',
		'select_all_button' => false,
		'options' => array(
			'free' => __(  'Free', 'automatic-actions-for-learndash' ),
			'paynow' => __(  'Buy Now', 'automatic-actions-for-learndash' ),
			'subscribe' => __(  'Recurring', 'automatic-actions-for-learndash' ),
			'closed' => __(  'Closed',  'automatic-actions-for-learndash' ),
		),
		'classes' => 'traa-ldactions-course-enroll-by-access-row',
	) );

	$cmb_actions->add_group_field( $course_enroll_section, array(
		'name'    => __( 'Course by Title', 'automatic-actions-for-learndash' ),
		'desc'    => __( 'Drag courses from the left column to the right column to attach them to this action.', 'automatic-actions-for-learndash' ),
		'id'      => 'traa_ldactions_course_enroll_by_title',
		'type'    => 'custom_attached_posts',
		'column'  => true, // Output in the admin post-listing as a custom column. https://github.com/CMB2/CMB2/wiki/Field-Parameters#column
		'options' => array(
			'show_thumbnails' => false, // Show thumbnails on the left
			'filter_boxes'    => true, // Show a text box for filtering the results
			'query_args'      => array(
				'posts_per_page' => 10,
				'post_type'      => 'sfwd-courses',
			), // override the get_posts args
		),
		'classes' => 'traa-ldactions-course-enroll-by-title-row',
	) );
    //COURSE ENROLL - COURSE ENROLL - COURSE ENROLL


    //GROUP ADD - GROUP ADD - GROUP ADD
	$group_add_section = $cmb_actions->add_field( array(
		'id'          => 'traa_ldactions_action_group_add_section',
		'type'        => 'group',
		'description' => __( 'Select Group(s) in This Section', 'automatic-actions-for-learndash' ),
		'repeatable'  => false,
		'options'     => array(
			'group_title'       => __( 'Group(s) Selection', 'automatic-actions-for-learndash' ),
			// 'add_button'        => __( 'Add Another Entry', 'cmb2' ),
			// 'remove_button'     => __( 'Remove Selection', 'automatic-actions-for-learndash' ),
			'sortable'          => false,
			// 'closed'         => true, // true to have the groups closed by default
			// 'remove_confirm' => esc_html__( 'Are you sure you want to remove?', 'cmb2' ), // Performs confirmation before removing group.
		),
		'classes' => 'traa-ldactions-action-group-add-section-row',
	) );

	$cmb_actions->add_group_field( $group_add_section, array(
		'name'    => __( 'Select group by', 'automatic-actions-for-learndash' ),
		'desc'    => __( 'Choose how to select group(s)', 'automatic-actions-for-learndash' ),
		'id'      => 'traa_ldactions_select_group_add',
		'type'    => 'multicheck_inline',
		'select_all_button' => false,
		'options' => array(
			'category' => __( 'LD Category','automatic-actions-for-learndash' ),
			'access' => __( 'LD Access Mode','automatic-actions-for-learndash' ),
			'title' => __( 'Title','automatic-actions-for-learndash' ),
		),
		'classes' => 'traa-ldactions-select-group-add-row',
	) );

	$cmb_actions->add_group_field( $group_add_section, array(
		'name'           => __( 'Group by Category', 'automatic-actions-for-learndash' ),
		'desc'           => __( 'Select one or more LearnDash Group Categories', 'automatic-actions-for-learndash' ),
		'id'             => 'traa_ldactions_group_add_by_category',
		'type'           => 'multicheck_inline',
		'select_all_button' => false,
		'options_cb'     => 'traa_ldactions_get_ld_group_categories',
		'classes' => 'traa-ldactions-group-add-by-category-row',
	) );

	$cmb_actions->add_group_field( $group_add_section, array(
		'name'           => __( 'Group by Access Mode','automatic-actions-for-learndash' ),
		'desc'           => __( 'Select one or more LearnDash Access Modes','automatic-actions-for-learndash' ),
		'id'             => 'traa_ldactions_group_add_by_access',
		'type'           => 'multicheck_inline',
		'select_all_button' => false,
		'options' => array(
			'free' => __( 'Free','automatic-actions-for-learndash' ),
			'paynow' => __( 'Buy Now','automatic-actions-for-learndash' ),
			'subscribe' => __( 'Recurring','automatic-actions-for-learndash' ),
			'closed' => __( 'Closed','automatic-actions-for-learndash' ),
		),
		'classes' => 'traa-ldactions-group-add-by-access-row',
	) );

	$cmb_actions->add_group_field( $group_add_section, array(
		'name'    => __( 'Group by Title', 'automatic-actions-for-learndash' ),
		'desc'    => __( 'Drag groups from the left column to the right column to attach them to this action.', 'automatic-actions-for-learndash' ),
		'id'      => 'traa_ldactions_group_add_by_title',
		'type'    => 'custom_attached_posts',
		'column'  => true, // Output in the admin post-listing as a custom column. https://github.com/CMB2/CMB2/wiki/Field-Parameters#column
		'options' => array(
			'show_thumbnails' => false, // Show thumbnails on the left
			'filter_boxes'    => true, // Show a text box for filtering the results
			'query_args'      => array(
				'posts_per_page' => 10,
				'post_type'      => 'groups',
			), // override the get_posts args
		),
		'classes' => 'traa-ldactions-group-add-by-title-row',
	) );
    //GROUP ADD - GROUP ADD - GROUP ADD



    //ROLE CHANGE - ROLE CHANGE - ROLE CHANGE
	$role_change_section = $cmb_actions->add_field( array(
		'id'          => 'traa_ldactions_action_role_change_section',
		'type'        => 'group',
		'description' => __( 'Select User Role(s) in This Section', 'automatic-actions-for-learndash' ),
		'repeatable'  => false,
		'options'     => array(
			'group_title'       => __( 'Role(s) Selection', 'automatic-actions-for-learndash' ),
			// 'add_button'        => __( 'Add Another Entry', 'cmb2' ),
			// 'remove_button'     => __( 'Remove Selection', 'automatic-actions-for-learndash' ),
			'sortable'          => false,
			// 'closed'         => true, // true to have the groups closed by default
			// 'remove_confirm' => esc_html__( 'Are you sure you want to remove?', 'cmb2' ), // Performs confirmation before removing group.
		),
		'classes' => 'traa-ldactions-action-role-change-section-row',
	) );
	
	$cmb_actions->add_group_field( $role_change_section, array(
		'name'    => __( 'Roles to Add', 'automatic-actions-for-learndash' ),
		'desc'    => __( 'Check one or more user roles to add.', 'automatic-actions-for-learndash' ),
		'id'      => 'traa_ldactions_select_role_change',
		'type'    => 'multicheck_inline',
		'select_all_button' => false,
		'column'  => true, // Output in the admin post-listing as a custom column. https://github.com/CMB2/CMB2/wiki/Field-Parameters#column
		'options_cb' => 'traa_ldactions_get_role_names',
		'classes' => 'traa-ldactions-select-role-change-row',
	) );

    $cmb_actions->add_group_field( $role_change_section, array(
		'name'    => __( 'Current User Roles', 'automatic-actions-for-learndash' ),
		'desc'    => '',
		'id'      => 'traa_ldactions_role_change_current',
		'type'    => 'radio',
		'options' => array(
			'keep' => __( 'Keep', 'automatic-actions-for-learndash' ),
			'replace' => __( 'Replace', 'automatic-actions-for-learndash' ),
		),
        'default' => 'replace',
		'classes' => 'traa-ldactions-role-change-current-row',
	) );
    //ROLE CHANGE - ROLE CHANGE - ROLE CHANGE
    //ACTIONS - ACTIONS - ACTIONS


    //TRIGGERS - TRIGGERS - TRIGGERS
	$cmb_triggers = new_cmb2_box( array(
		'id'            => 'traa_ldactions_trigger_settings',
		'title'         => __( 'Trigger Settings', 'automatic-actions-for-learndash' ),
		'object_types'  => array( 'ld-actions', ), // Post type
		'context'       => 'side',
		'priority'      => 'high',
		'show_names'    => true, // Show field names on the left
		// 'cmb_styles' => false, // false to disable the CMB stylesheet
		// 'closed'     => true, // Keep the metabox closed by default
		'classes' => array( 'traa-ldactions-box', 'traa-ldactions-box-trigger' ),
	) );

	$cmb_triggers->add_field( array(
		'name' => 'Define Trigger',
		'desc' => 'In this box, you can define the trigger for this action. The trigger is the condition that must be met for the action to be executed.',
		'type' => 'title',
		'id'   => 'traa_ldactions_trigger_settings_title',
	) );

    
	$cmb_triggers->add_field( array(
		'name'    => 'Trigger',
		'id'      => 'traa_ldactions_trigger_target',
		'type'    => 'select',
		'options' => array(
			'register' => __( 'User Registers', 'automatic-actions-for-learndash' ),
            'complete_course' => __( 'Student Completes a Course', 'automatic-actions-for-learndash' ),
            // 'complete_quiz' => __( 'Student Completes a Quiz', 'automatic-actions-for-learndash' ),
		),
		'default' => 'register',
		'column' => array(
			'position' => 3,
			'name'     => 'Trigger',
		),
	) );

    //REGISTRATION - REGISTRATION - REGISTRATION
	$registration_section = $cmb_triggers->add_field( array(
		'id'          => 'traa_ldactions_trigger_register_section',
		'type'        => 'group',
		//'description' => __( 'Define settings for the register trigger', 'automatic-actions-for-learndash' ),
		'repeatable'  => false,
		'options'     => array(
			'group_title'       => __( 'Registration Section', 'automatic-actions-for-learndash' ),
		),
		'classes' => 'traa-ldactions-trigger-register-section-row',
	) );

    $cmb_triggers->add_group_field( $registration_section, array(
		'name'    => __( 'Kind of registration', 'automatic-actions-for-learndash' ),
		'desc'    => __( '<strong>Any</strong>: any and all registration will trigger the action (you don\'t need a special registration URL)', 'automatic-actions-for-learndash' ) .
                    '<br>' . '<br>' .
                     __( '<strong>Special ones</strong>: give the special URL above to the visitors that you want to be affected by the action when registering', 'automatic-actions-for-learndash' ),
		'id'      => 'traa_ldactions_trigger_register_which',
		'type'    => 'radio_inline',
		'options' => array(
			'any' => __( 'Any', 'automatic-actions-for-learndash' ),
			'special' => __( 'Special ones', 'automatic-actions-for-learndash' )
		),
        'default' => 'any',
		'classes' => 'traa-ldactions-trigger-registration-which-row',
	) );

    $cmb_triggers->add_group_field( $registration_section, array(
		'name'    => __( 'Extend to Login', 'automatic-actions-for-learndash' ),
		'desc'    => __( 'Fire the trigger also when an already registered user logs into the site', 'automatic-actions-for-learndash' ),
		'id'      => 'traa_ldactions_trigger_register_login',
		'type'    => 'checkbox',
		'classes' => 'traa-ldactions-trigger-registration-login-row',
	) );

    $cmb_triggers->add_group_field( $registration_section, array(
        'name' => __( 'Trigger URL', 'automatic-actions-for-learndash' ),
        //'desc' => 'The URL for the registration that will trigger this action: <code>' . traaa_ldaction_generate_url($unique_code) . '</code>',
		'type' => 'title',
        'id'   => 'traa_ldactions_trigger_registration_url',
		'after' => 'traa_display_registration_url_for_the_action',
		'classes' => 'traa-ldactions-trigger-registration-url-row',
    ) );
    //REGISTRATION - REGISTRATION - REGISTRATION

    //COMPLETE COURSE - COMPLETE COURSE - COMPLETE COURSE
    $complete_course_section = $cmb_triggers->add_field( array(
		'id'          => 'traa_ldactions_trigger_complete_course_section',
		'type'        => 'group',
		//'description' => __( 'Define settings for the registration trigger', 'automatic-actions-for-learndash' ),
		'repeatable'  => false,
		'options'     => array(
			'group_title'       => __( 'Student Completes a Course', 'automatic-actions-for-learndash' ),
		),
		'classes' => 'traa-ldactions-trigger-complete-course-section-row',
	) );

    $cmb_triggers->add_group_field( $complete_course_section, array(
		'name'    => __( 'Which course:', 'automatic-actions-for-learndash' ),
		'id'      => 'traa_ldactions_trigger_complete_course_which',
		'type'    => 'radio_inline',
		'options' => array(
			'any' => __( 'Any', 'automatic-actions-for-learndash' ),
			'special' => __( 'Special ones', 'automatic-actions-for-learndash' )
		),
        'default' => 'any',
		'classes' => 'traa-ldactions-trigger-complete-course-which-row',
	) );

    $cmb_triggers->add_group_field( $complete_course_section, array(
		'name'    => __( 'Select course by', 'automatic-actions-for-learndash' ),
		'desc'    => __( 'Choose how to select course(s)', 'automatic-actions-for-learndash' ),
		'id'      => 'traa_ldactions_trigger_select_course',
		'type'    => 'multicheck_inline',
		'select_all_button' => false,
		'options' => array(
			'category' => __( 'LD Category', 'automatic-actions-for-learndash' ),
			'access' => __( 'LD Access Mode', 'automatic-actions-for-learndash' ),
			'title' => __( 'Title', 'automatic-actions-for-learndash' ),
		),
		'classes' => 'traa-ldactions-trigger-select-course-row',
	) );

	$cmb_triggers->add_group_field( $complete_course_section, array(
		'name'           => __( 'Course by LD Category','automatic-actions-for-learndash' ),
		'desc'           => __( 'Select one or more LearnDash Course Categories','automatic-actions-for-learndash' ),
		'id'             => 'traa_ldactions_trigger_course_by_category',
		'type'           => 'multicheck_inline',
		'select_all_button' => false,
		'options_cb'     => 'traa_ldactions_get_ld_course_categories',
		'classes' => 'traa-ldactions-trigger-course-by-category-row',
	) );

	$cmb_triggers->add_group_field( $complete_course_section, array(
		'name'           => __( 'Course by LD Access Mode','automatic-actions-for-learndash' ),
		'desc'           => __( 'Select one or more LearnDash Access Modes','automatic-actions-for-learndash' ),
		'id'             => 'traa_ldactions_trigger_course_by_access',
		'type'           => 'multicheck_inline',
		'select_all_button' => false,
		'options' => array(
			'free' => __(  'Free', 'automatic-actions-for-learndash' ),
			'paynow' => __(  'Buy Now', 'automatic-actions-for-learndash' ),
			'subscribe' => __(  'Recurring', 'automatic-actions-for-learndash' ),
			'closed' => __(  'Closed',  'automatic-actions-for-learndash' ),
		),
		'classes' => 'traa-ldactions-trigger-course-by-access-row',
	) );

	$cmb_triggers->add_group_field( $complete_course_section, array(
		'name'    => __( 'Course by Title', 'automatic-actions-for-learndash' ),
		'desc'    => __( 'Drag courses from the left column to the right column to attach them to this trigger.', 'automatic-actions-for-learndash' ),
		'id'      => 'traa_ldactions_trigger_course_by_title',
		'type'    => 'custom_attached_posts',
		'column'  => true, // Output in the admin post-listing as a custom column. https://github.com/CMB2/CMB2/wiki/Field-Parameters#column
		'options' => array(
			'show_thumbnails' => false, // Show thumbnails on the left
			'filter_boxes'    => true, // Show a text box for filtering the results
			'query_args'      => array(
				'posts_per_page' => 10,
				'post_type'      => 'sfwd-courses',
			), // override the get_posts args
		),
		'classes' => 'traa-ldactions-trigger-course-by-title-row',
	) );


    // //COMPLETE QUIZ - COMPLETE QUIZ - COMPLETE QUIZ
    // $complete_quiz_section = $cmb_triggers->add_field( array(
	// 	'id'          => 'traa_ldactions_trigger_complete_quiz_section',
	// 	'type'        => 'group',
	// 	'repeatable'  => false,
	// 	'options'     => array(
	// 		'group_title'       => __( 'Student Completes a Quiz', 'automatic-actions-for-learndash' ),
	// 	),
	// 	'classes' => 'traa-ldactions-trigger-complete-quiz-section-row',
	// ) );

    // $cmb_triggers->add_group_field( $complete_quiz_section, array(
	// 	'name'    => __( 'Which quiz:', 'automatic-actions-for-learndash' ),
	// 	'id'      => 'traa_ldactions_trigger_complete_quiz_which',
	// 	'type'    => 'radio_inline',
	// 	'options' => array(
	// 		'any' => __( 'Any', 'automatic-actions-for-learndash' ),
	// 		'special' => __( 'Special ones', 'automatic-actions-for-learndash' )
	// 	),
    //     'default' => 'any',
	// 	'classes' => 'traa-ldactions-trigger-complete-quiz-which-row',
	// ) );

    // $cmb_triggers->add_group_field( $complete_quiz_section, array(
	// 	'name'    => __( 'Select quiz by', 'automatic-actions-for-learndash' ),
	// 	'desc'    => __( 'Choose how to select quiz(zes)', 'automatic-actions-for-learndash' ),
	// 	'id'      => 'traa_ldactions_trigger_select_quiz',
	// 	'type'    => 'multicheck_inline',
	// 	'select_all_button' => false,
	// 	'options' => array(
	// 		'category' => __( 'Category', 'automatic-actions-for-learndash' ),
	// 		'title' => __( 'Title', 'automatic-actions-for-learndash' ),
	// 	),
	// 	'classes' => 'traa-ldactions-trigger-select-quiz-row',
	// ) );

	// $cmb_triggers->add_group_field( $complete_quiz_section, array(
	// 	'name'           => __( 'Quiz by Category','automatic-actions-for-learndash' ),
	// 	'desc'           => __( 'Select one or more categories','automatic-actions-for-learndash' ),
	// 	'id'             => 'traa_ldactions_trigger_quiz_by_category',
	// 	'type'           => 'multicheck_inline',
	// 	'select_all_button' => false,
	// 	'options_cb'     => 'traa_ldactions_get_ld_quiz_categories',
	// 	'classes' => 'traa-ldactions-trigger-quiz-by-category-row',
	// ) );

	// $cmb_triggers->add_group_field( $complete_quiz_section, array(
	// 	'name'    => __( 'Quiz by Title', 'automatic-actions-for-learndash' ),
	// 	'desc'    => __( 'Drag quizzes from the left column to the right column to attach them to this trigger.', 'automatic-actions-for-learndash' ),
	// 	'id'      => 'traa_ldactions_trigger_quiz_by_title',
	// 	'type'    => 'custom_attached_posts',
	// 	'column'  => true, // Output in the admin post-listing as a custom column. https://github.com/CMB2/CMB2/wiki/Field-Parameters#column
	// 	'options' => array(
	// 		'show_thumbnails' => false, // Show thumbnails on the left
	// 		'filter_boxes'    => true, // Show a text box for filtering the results
	// 		'query_args'      => array(
	// 			'posts_per_page' => 10,
	// 			'post_type'      => 'sfwd-quiz',
	// 		), // override the get_posts args
	// 	),
	// 	'classes' => 'traa-ldactions-trigger-quiz-by-title-row',
	// ) );
	// //COMPLETE QUIZ - COMPLETE QUIZ - COMPLETE QUIZ


	//UNIQUE CODE - UNIQUE CODE - UNIQUE CODE
	$cmb_triggers->add_field( array(
		'name'    => __( 'Unique Code for the action', 'automatic-actions-for-learndash' ),
        'description' => __( 'Automatically filled (don\'t touch this!)', 'automatic-actions-for-learndash' ),
		'id'      => 'traa_ldactions_unique_code',
		'type'    => 'hidden',
        'default' => $unique_code,
        // 'attributes' => array(
        //     'disabled' => 'disabled',
        // ),
	) );
	//UNIQUE CODE - UNIQUE CODE - UNIQUE CODE
    
}


//js validation for the actions
function traa_ldactions_after_form_do_js_validation_actions( $post_id, $cmb ) {
	static $added = false;

	// Only add this to the page once (not for every metabox)
	if ( $added ) {
		return;
	}

	$added = true;
	?>
	<script type="text/javascript">
	jQuery(document).ready(function($) {

		$form = $( document.getElementById( 'post' ) );
		$htmlbody = $( 'html, body' );
		
        //get all inputs checkboxes to check
        $courseEnrollSelectCheckboxes = $form.find( 'input[name*="traa_ldactions_select_course_enroll"]' );
        $groupAddSelectCheckboxes = $form.find( 'input[name*="traa_ldactions_select_group_add"]' );
		$roleChangeSelectCheckboxes = $form.find( 'input[name*="traa_ldactions_select_role_change"]' );
        
		function checkValidation( evt ) {
            $target = $('input[name="traa_ldactions_action_target"]:checked');
            if ( ! $target.length ) {
                return;
            }
            $target_value = $target.val();

            $checkboxesToValidate = '';
            if ( $target_value === 'course_enroll' ) {
                $checkboxesToValidate = $courseEnrollSelectCheckboxes;
            } else if ( $target_value === 'group_add' ) {
                $checkboxesToValidate = $groupAddSelectCheckboxes;
            } else if ( $target_value === 'role_change' ) {
                $checkboxesToValidate = $roleChangeSelectCheckboxes;
            }

            if ( ! $checkboxesToValidate.length ) {
                return;
            }

			var labels = [];
			var $first_error_row = null;
			var $row = null;

			function add_required( $row ) {
				$row.css({ 'background-color': 'rgb(255, 170, 170)' });
				$first_error_row = $first_error_row ? $first_error_row : $row;
				labels.push( $row.find( '.cmb-th label' ).text() );
			}

			function remove_required( $row ) {
				$row.css({ background: '' });
			}

            function at_least_one_checkbox_is_checked( $checkboxes ) {
                return $checkboxes.is(":checked");
            }

            if(at_least_one_checkbox_is_checked($checkboxesToValidate)) {
                remove_required( $checkboxesToValidate.closest( '.cmb-row' ) );

                //Validate nested checkboxes and inputs

                //get checkboxes checked
                $checkboxesChecked = $checkboxesToValidate.filter(':checked'); //category, access, title (and more to come)
                
                //loop for each checkbox checked
                $checkboxesChecked.each(function() {
                    $checkboxChecked = $(this);
                    //get its value
                    $checkboxCheckedValue = $checkboxChecked.val();
                    if($checkboxCheckedValue == 'title') {
                        //if title, check if input is empty (must have at least one ID)
                        $inputToVerify = $form.find( 'input[name*="traa_ldactions_' + $target_value + '_by_title"]' );
                        if($inputToVerify.length && $inputToVerify.val()) {
                            remove_required( $inputToVerify.closest( '.cmb-row' ) );
                        } else {
                            add_required( $inputToVerify.closest( '.cmb-row' ) );
                        }
                    } else {
                        //checkbox (category or access). At least one must be checked
                        $nestedCheckboxesToVerify = $form.find( 'input[type="checkbox"][name*="traa_ldactions_' + $target_value + '_by_' + $checkboxCheckedValue + '"]' );
						if($nestedCheckboxesToVerify.length) {
							if(at_least_one_checkbox_is_checked($nestedCheckboxesToVerify)) {
								remove_required( $nestedCheckboxesToVerify.closest( '.cmb-row' ) );
							} else {
								add_required( $nestedCheckboxesToVerify.closest( '.cmb-row' ) );
							} 
						}
                    }
                    
                });   
            } else {
                add_required( $checkboxesToValidate.closest( '.cmb-row' ) );
                
                // evt.preventDefault();
            } 

			if ( $first_error_row ) {
				evt.preventDefault();
				alert( '<?php _e( 'Check for some fields required and highlighted below!', 'automatic-actions-for-learndash' ); ?> ' );
				$htmlbody.animate({
					scrollTop: ( $first_error_row.offset().top - 200 )
				}, 1000);
			} 

		}

		$form.on( 'submit', checkValidation );
	});
	</script>
	<?php
}
add_action( 'cmb2_after_form', 'traa_ldactions_after_form_do_js_validation_actions', 10, 2 );


//js validation for the triggers
function traa_ldactions_after_form_do_js_validation_triggers( $post_id, $cmb ) {
	static $added = false;

	// Only add this to the page once (not for every metabox)
	if ( $added ) {
		return;
	}

	$added = true;
	?>
	<script type="text/javascript">
	jQuery(document).ready(function($) {

		$form = $( document.getElementById( 'post' ) );
		$htmlbody = $( 'html, body' );
		
        
		function checkValidation( evt ) {
			
			let labels = [];
			let $first_error_row = null;
			let $row = null;

            $target = $('select[name="traa_ldactions_trigger_target"]');
            if ( ! $target.length ) {
                return;
            }
            $target_value = $target.find(':selected').val(); //register, complete_course, complete_quiz

			traa_nested_checkboxes_to_check($target_value);

			function add_required( $row ) {
				$row.css({ 'background-color': 'rgb(255, 170, 170)' });
				$first_error_row = $first_error_row ? $first_error_row : $row;
				labels.push( $row.find( '.cmb-th label' ).text() );
			}

			function remove_required( $row ) {
				$row.css({ background: '' });
			}

			function traa_nested_checkboxes_to_check( $tgt ) {

				$item = '';
				if ( $tgt == 'complete_course' ) {
					$item = 'course';
				} else if ( $tgt == 'complete_quiz' ) {
					$item = 'quiz';
				}
				if($item == '') {
					return;
				}
				//radio val 'any' is default, so no need to check it
				//radio val 'special' demands at least one checkbox checked
				$checkToVal = $('.traa-ldactions-trigger-select-' + $item + '-row').find( 'input[type="checkbox"]' );
				if($checkToVal.is(":checked")) {
					remove_required( $checkToVal.closest( '.cmb-row' ) );

					//Validate nested checkboxes and inputs
					//get checkboxes checked
					$checkboxesChecked = $checkToVal.filter(':checked'); //category, access, title (and more to come)
					
					
					//loop for each checkbox checked
					$checkboxesChecked.each(function() {
						$checkboxChecked = $(this);
						//get its value
						$checkboxCheckedValue = $checkboxChecked.val();
						if($checkboxCheckedValue == 'title') {
							//if title, check if input is empty (must have at least one ID)
							$inputToVerify = $form.find( 'input[name*="traa_ldactions_trigger_' + $item + '_by_title"]' );
							if($inputToVerify.val()) {
								remove_required( $inputToVerify.closest( '.cmb-row' ) );
							} else {
								add_required( $inputToVerify.closest( '.cmb-row' ) );
							}
						} else {
							//checkbox (category, access or title). At least one must be checked
							$nestedCheckboxesToVerify = $form.find( 'input[type="checkbox"][name*="traa_ldactions_trigger_' + $item + '_by_' + $checkboxCheckedValue + '"]' );
							if(at_least_one_checkbox_is_checked($nestedCheckboxesToVerify)) {
								remove_required( $nestedCheckboxesToVerify.closest( '.cmb-row' ) );
							} else {
								add_required( $nestedCheckboxesToVerify.closest( '.cmb-row' ) );
							}
						}
					});  
				} else {
					if($('.traa-ldactions-trigger-complete-' + $item + '-which-row').find('input[type="radio"]:checked').val() != 'any') {
						add_required( $checkToVal.closest( '.cmb-row' ) ); 
					} else {
						remove_required( $checkToVal.closest( '.cmb-row' ) );
					}
				}
			}

            function at_least_one_checkbox_is_checked( $checkboxes ) {
                return $checkboxes.is(":checked");
            }
 

			if ( $first_error_row ) {
				evt.preventDefault();
				alert( '<?php _e( 'Check for some fields required and highlighted!', 'automatic-actions-for-learndash' ); ?> ' );
				$htmlbody.animate({
					scrollTop: ( $first_error_row.offset().top - 200 )
				}, 1000);
			} 

		}

		$form.on( 'submit', checkValidation );
	});
	</script>
	<?php
}
add_action( 'cmb2_after_form', 'traa_ldactions_after_form_do_js_validation_triggers', 10, 2 );



//Add custom CSS
function traa_ldactions_custom_css_for_metabox( $post_id, $cmb ) {
	?>
	<style type="text/css" media="screen">
		#traa_ldactions_action_settings .postbox-header,
		#traa_ldactions_trigger_settings .postbox-header,
		#traa_ldactions_activation_section .postbox-header,
		#traa_ldactions_execution_section .postbox-header {
			background-color: lightblue;
			height: 45px;
		}
		#traa_ldactions_action_settings .postbox-header h2,
		#traa_ldactions_trigger_settings .postbox-header h2,
		#traa_ldactions_activation_section .postbox-header h2,
		#traa_ldactions_execution_section .postbox-header h2 {
			font-size: 1.2em;
			color: #545d69
		}
	</style>
	<?php
}
add_action( "cmb2_after_form", 'traa_ldactions_custom_css_for_metabox', 10, 2 );