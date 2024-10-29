jQuery(document).ready(function ($) {

    //actions
    const action_target = $('input[name="traa_ldactions_action_target"]');

    const course_enroll_section = $('.traa-ldactions-action-course-enroll-section-row');
    const course_enroll_select_row = $('.traa-ldactions-select-course-enroll-row');
    const course_enroll_checkboxes = course_enroll_select_row.find('input[type="checkbox"]');

    const group_add_section = $('.traa-ldactions-action-group-add-section-row');
    const group_add_select_row = $('.traa-ldactions-select-group-add-row');
    const group_add_checkboxes = group_add_select_row.find('input[type="checkbox"]');

    const role_change_section = $('.traa-ldactions-action-role-change-section-row');
    const role_change_select_row = $('.traa-ldactions-role-change-select-row');


    //triggers
    const complete_course_section = $('.traa-ldactions-trigger-complete-course-section-row');
    const registration_section = $('.traa-ldactions-trigger-register-section-row');
    const trigger_target = $('select[name="traa_ldactions_trigger_target"]');
    // const complete_quiz_section = $('.traa-ldactions-trigger-complete-quiz-section-row');

    //SHOW-HIDE TABS CONTENT
    $('#traa-automatic-tabs a.button').click(function () {
        var target = $(this).data('target-content');

        $('.traa-automatic-tab').hide();
        $('.traa-automatic-tab#' + target).show();

        $('#traa-automatic-tabs a.button').removeClass('active');
        $(this).addClass('active');
    });
    //END SHOW-HIDE TABS CONTENT

    //no use?
    function traa_show_hide_fields(checkbox) {
        let val = checkbox.val();
        let checked = checkbox.is(':checked');
        let target_class = '';
        
        if(val == 'category' ) {
            target_class = 'traa-ldactions-' + item + '-by-category-row';
        } else if(val == 'access') {
            target_class = 'traa-ldactions-' + item + '-by-access-row';
        } else if(val == 'title') { 
            target_class = 'traa-ldactions-' + item + '-by-title-row';
        }

        if(!target_class) {
            return;
        }
        
        if(checked) {
            $('.' + target_class).show();
        } else {
            $('.' + target_class).hide();
        }    

    }


    //ACTIONS - ACTIONS - ACTIONS - ACTIONS
    //On the LDAction CPT (ld-actions) edit page, show fields conditionally
    function traa_show_hide_section_actions() {
        let target = $('input[name="traa_ldactions_action_target"]:checked').val();
        if (target == 'group_add') {
            course_enroll_section.closest('.cmb-row').hide();
            role_change_section.closest('.cmb-row').hide();
            group_add_section.closest('.cmb-row').show();
        } else if (target == 'course_enroll') {
            course_enroll_section.closest('.cmb-row').show();
            group_add_section.closest('.cmb-row').hide();
            role_change_section.closest('.cmb-row').hide();
        } else {
            course_enroll_section.closest('.cmb-row').hide();
            group_add_section.closest('.cmb-row').hide();
            role_change_section.closest('.cmb-row').show();
        }
    }
    //On page load
    traa_show_hide_section_actions();
    //On change
    action_target.change(function () {
        traa_show_hide_section_actions();
    });

    //monitor changes on checkboxes (course_enroll and group_add). For future actions, add to array below
    course_group_array = ['course-enroll', 'group-add'];
    $( course_group_array).each(function( index ) {
        let item = this;
        $('.traa-ldactions-' + item + '-by-category-row').hide();
        $('.traa-ldactions-' + item + '-by-access-row').hide();
        $('.traa-ldactions-' + item + '-by-title-row').hide();
        
        let sel_checked = $('.traa-ldactions-select-' + item + '-row').find('input[type="checkbox"]:checked');
        
        sel_checked.each(function( index ) {
            let val = $(this).val(); 
            if(val == 'category') {
                $('.traa-ldactions-' + item + '-by-category-row').show(); 
            } else if(val == 'access') {
                $('.traa-ldactions-' + item + '-by-access-row').show();
            } else if(val == 'title') {
                $('.traa-ldactions-' + item + '-by-title-row').show();
            }
        });
        
        chbx = (this == 'course-enroll') ? course_enroll_checkboxes : group_add_checkboxes;
        chbx.change(function () {
            let val = $(this).val();
            let checked = $(this).is(':checked');
            let target_class = '';
            
            if(val == 'category' ) {
                target_class = 'traa-ldactions-' + item + '-by-category-row';
            } else if(val == 'access') {
                target_class = 'traa-ldactions-' + item + '-by-access-row';
            } else if(val == 'title') { 
                target_class = 'traa-ldactions-' + item + '-by-title-row';
            }

            if(!target_class) {
                return;
            }
            
            if(checked) {
                $('.' + target_class).show();
            } else {
                $('.' + target_class).hide();
            }    

        });     
    });
    //ACTIONS - ACTIONS - ACTIONS - ACTIONS



    //TRIGGERS - TRIGGERS - TRIGGERS - TRIGGERS
    //On the LDAction CPT (ld-actions) edit page, show fields conditionally
    function traa_show_hide_section_triggers() {
        let target = $('select[name="traa_ldactions_trigger_target"]').find(':selected').val();
        if (target == 'register') {
            complete_course_section.closest('.cmb-row').hide();
            // complete_quiz_section.closest('.cmb-row').hide();
            registration_section.closest('.cmb-row').show();
        } else if (target == 'complete_course') {
            complete_course_section.closest('.cmb-row').show();
            // complete_quiz_section.closest('.cmb-row').hide();
            registration_section.closest('.cmb-row').hide();
        } else if (target == 'complete_quiz') {
            complete_course_section.closest('.cmb-row').hide();
            // complete_quiz_section.closest('.cmb-row').show();
            registration_section.closest('.cmb-row').hide();
        } 
    }    

    function traa_any_or_special(item) {
        //check if radio is "any" (hide everything) or "special" (show select by checkboxes)
        let which = $('.traa-ldactions-trigger-complete-' + item + '-which-row').find('input[type="radio"]');
        //get which element that is checked
        let whichChecked = which.filter(':checked');
        $('.traa-ldactions-trigger-select-' + item + '-row').hide(); //select by checkbox
        $('.traa-ldactions-trigger-' + item + '-by-category-row').hide(); //select by category checkbox
        $('.traa-ldactions-trigger-' + item + '-by-title-row').hide(); //select by title field
        if(item == 'course') {
            $('.traa-ldactions-trigger-' + item + '-by-access-row').hide(); //select by access checkbox (only course)
        }
        if(whichChecked.val() == 'special') {
            traa_trigger_select_by(item);
        }
    }

    function traa_trigger_select_by(item) {
        $('.traa-ldactions-trigger-' + item + '-by-category-row').hide(); //select by category checkbox
        $('.traa-ldactions-trigger-' + item + '-by-title-row').hide(); //select by title field
        if(item == 'course') {
            $('.traa-ldactions-trigger-' + item + '-by-access-row').hide(); //select by access checkbox (only course)
        }
        $('.traa-ldactions-trigger-select-' + item + '-row').show(); //select by checkbox
        let sel_checked = $('.traa-ldactions-trigger-select-' + item + '-row').find('input[type="checkbox"]:checked');
        sel_checked.each(function( index ) {
            let val = $(this).val(); 
            if(!val) {
                return;
            }
            $('.traa-ldactions-trigger-' + item + '-by-' + val + '-row').show();
        });
    }

    //show / hide sections
    
    //on page load
    traa_show_hide_section_triggers();
    //on change
    trigger_target.change(function () {
        traa_show_hide_section_triggers();
    });

    
    course_quiz_array = ['course', 'quiz'];
    $(course_quiz_array).each(function( index ) {
        let item = this;

        //on page load any/special and checkboxes
        traa_any_or_special(item);

        //on change radio any/special
        $('.traa-ldactions-trigger-complete-' + item + '-which-row').find('input[type="radio"]').change(function () {
            traa_any_or_special(item);
        });

        //on change checkbox select by
        $('.traa-ldactions-trigger-select-' + item + '-row').find('input[type="checkbox"]').change(function () {
            traa_trigger_select_by(item);
        });
    });


/*
<div class="cmb-td">
    <h5 class="cmb2-metabox-title" id="traa-ldactions-trigger-register-section-0-traa-ldactions-trigger-registration-url" data-hash="4em7l2pvst30">
        Special Registration URL
    </h5>
    <p>
        Save the action to generate the registration URL for it.
    </p>
</div>

<ul class="cmb2-radio-list cmb2-list">	
    <li>
        <input type="radio" class="cmb2-option" name="traa_ldactions_trigger_register_section[0][traa_ldactions_trigger_register_which]" id="traa_ldactions_trigger_register_section_0_traa_ldactions_trigger_register_which1" value="any" data-hash="7iejmrh7cj10"> 
        <label for="traa_ldactions_trigger_register_section_0_traa_ldactions_trigger_register_which1">Any</label>
    </li>
	<li><input type="radio" class="cmb2-option" name="traa_ldactions_trigger_register_section[0][traa_ldactions_trigger_register_which]" id="traa_ldactions_trigger_register_section_0_traa_ldactions_trigger_register_which2" value="special" data-hash="7iejmrh7cj10">
        <label for="traa_ldactions_trigger_register_section_0_traa_ldactions_trigger_register_which2">Special ones</label>
    </li>
</ul>
*/

    //given the elements above, only show the p element if "special" value is selected on radio
    
    function traa_show_hide_special_url() {
        let login_descr = $('#cmb-group-traa_ldactions_trigger_register_section-0 > div.inside.cmb-td.cmb-nested.cmb-field-list > div.cmb-row.cmb-type-checkbox.cmb2-id-traa-ldactions-trigger-register-section-0-traa-ldactions-trigger-register-login.cmb-repeat-group-field.traa-ldactions-trigger-registration-login-row > div.cmb-td > label > span');
        let target = $('input[name="traa_ldactions_trigger_register_section[0][traa_ldactions_trigger_register_which]"]:checked').val();
        if (target == 'special') {
            $('.traa-ldactions-trigger-registration-url-row').show();
            login_descr.text(login_descr.text() + ' through a special login URL');
            if($('input[name="traa_ldactions_trigger_register_section[0][traa_ldactions_trigger_register_login]"]').is(':checked')) { 
                $('#traa-login-url-paragraph').show();
            } else {
                $('#traa-login-url-paragraph').hide(); 
            }
        } else {
            $('.traa-ldactions-trigger-registration-url-row').hide();
            login_descr.text(login_descr.text().replace(/ through a special login URL/g, ''));
            $('#traa-login-url-paragraph').hide();
        }
    }
    //On page load
    traa_show_hide_special_url();
    //On change
    $('input[name="traa_ldactions_trigger_register_section[0][traa_ldactions_trigger_register_which]"]').change(function () {
        traa_show_hide_special_url();
    });
    $('input[name="traa_ldactions_trigger_register_section[0][traa_ldactions_trigger_register_login]"]').change(function () {
        let checked = $(this).is(':checked');
        let login_descr = $('#cmb-group-traa_ldactions_trigger_register_section-0 > div.inside.cmb-td.cmb-nested.cmb-field-list > div.cmb-row.cmb-type-checkbox.cmb2-id-traa-ldactions-trigger-register-section-0-traa-ldactions-trigger-register-login.cmb-repeat-group-field.traa-ldactions-trigger-registration-login-row > div.cmb-td > label > span');
        let target = $('input[name="traa_ldactions_trigger_register_section[0][traa_ldactions_trigger_register_which]"]:checked').val();
        if(checked && target == 'special') {
            $('#traa-login-url-paragraph').show();
            // login_descr.text(login_descr.text() + ' through a special login URL');
        } else {
            $('#traa-login-url-paragraph').hide();
            if(target != 'special') {
                // login_descr.text(login_descr.text().replace(/ through a special login URL/g, '')); 
            }
        }
    });
    //TRIGGERS - TRIGGERS - TRIGGERS - TRIGGERS
}); //end jQuery