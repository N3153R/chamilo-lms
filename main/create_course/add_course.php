<?php
/* For licensing terms, see /license.txt */

/**
 * This script allows professors and administrative staff to create course sites.
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University
 * @author Roan Embrechts, refactoring
 * @package chamilo.create_course
 * "Course validation" feature:
 * @author Jose Manuel Abuin Mosquera <chema@cesga.es>, Centro de Supercomputacion de Galicia
 * "Course validation" feature, technical adaptation for Chamilo 1.8.8:
 * @author Ivan Tcholakov <ivantcholakov@gmail.com>
 */
use \ChamiloSession as Session;

// Flag forcing the "current course" reset.
$cidReset = true;

// Including the global initialization file.
require_once '../inc/global.inc.php';

// Section for the tabs.
$this_section = SECTION_COURSES;

// "Course validation" feature. This value affects the way of a new course creation:
// true  - the new course is requested only and it is created after approval;
// false - the new course is created immediately, after filling this form.
$course_validation_feature = false;
if (api_get_setting('course_validation') == 'true' && !api_is_platform_admin()) {
    $course_validation_feature = true;
}

$htmlHeadXtra[] = '<script type="text/javascript">
    function setFocus(){
        $("#title").focus();
    }
    $(window).load(function () {
        setFocus();
    });
</script>';

$interbreadcrumb[] = array(
    'url' => api_get_path(WEB_PATH) . 'user_portal.php',
    'name' => get_lang('MyCourses')
);

// Displaying the header.
$tool_name = $course_validation_feature ? get_lang('CreateCourseRequest') : get_lang('CreateSite');

$tpl = new Template($tool_name);

if (
    api_get_setting('allow_users_to_create_courses') == 'false' &&
    !api_is_platform_admin()
) {
    api_not_allowed(true);
}

// Check access rights.
if (!api_is_allowed_to_create_course()) {
    api_not_allowed(true);
    exit;
}

// Build the form.
$form = new FormValidator('add_course');

// Form title
$form->addElement('header', $tool_name);

// Title
$form->addElement(
    'text',
    'title',
    array(
        get_lang('CourseName'),
        get_lang('Ex')
    ),
    array(
        'id' => 'title'
    )
);
$form->applyFilter('title', 'html_filter');
$form->addRule('title', get_lang('ThisFieldIsRequired'), 'required');

$form->addButtonAdvancedSettings('advanced_params');
$form->addElement(
    'html',
    '<div id="advanced_params_options" style="display:none">'
);

// Category category.
$url = api_get_path(WEB_AJAX_PATH) . 'course.ajax.php?a=search_category';

$form->addElement(
    'select_ajax',
    'category_code',
    get_lang('CourseFaculty'),
    null,
    array('url' => $url)
);

// Course code
$form->addText(
    'wanted_code',
    array(
        get_lang('Code'),
        get_lang('OnlyLettersAndNumbers')
    ),
    '',
    array(
        'maxlength' => CourseManager::MAX_COURSE_LENGTH_CODE,
        'pattern' => '[a-zA-Z0-9]+',
        'title' => get_lang('OnlyLettersAndNumbers')
    )
);
$form->applyFilter('wanted_code', 'html_filter');
$form->addRule(
    'wanted_code',
    get_lang('Max'),
    'maxlength',
    CourseManager::MAX_COURSE_LENGTH_CODE
);

// The teacher
//array(get_lang('Professor'), null), null, array('size' => '60', 'disabled' => 'disabled'));
$titular = & $form->addElement('hidden', 'tutor_name', '');
if ($course_validation_feature) {

    // Description of the requested course.
    $form->addElement(
        'textarea',
        'description',
        get_lang('Description'),
        array('rows' => '3')
    );

    // Objectives of the requested course.
    $form->addElement(
        'textarea',
        'objetives',
        get_lang('Objectives'),
        array('rows' => '3')
    );

    // Target audience of the requested course.
    $form->addElement(
        'textarea',
        'target_audience',
        get_lang('TargetAudience'),
        array('rows' => '3')
    );
}

// Course language.
$form->addElement(
    'select_language',
    'course_language',
    get_lang('Ln'),
    array(),
    array('style' => 'width:150px')
);
$form->applyFilter('select_language', 'html_filter');

// Exemplary content checkbox.
$form->addElement(
    'checkbox',
    'exemplary_content',
    null,
    get_lang('FillWithExemplaryContent')
);

if ($course_validation_feature) {

    // A special URL to terms and conditions that is set
    // in the platform settings page.
    $terms_and_conditions_url = trim(
        api_get_setting('course_validation_terms_and_conditions_url')
    );

    // If the special setting is empty,
    // then we may get the URL from Chamilo's module "Terms and conditions",
    // if it is activated.
    if (empty($terms_and_conditions_url)) {
        if (api_get_setting('allow_terms_conditions') == 'true') {
            $terms_and_conditions_url = api_get_path(WEB_CODE_PATH);
            $terms_and_conditions_url .= 'auth/inscription.php?legal';
        }
    }

    if (!empty($terms_and_conditions_url)) {
        // Terms and conditions to be accepted before sending a course request.
        $form->addElement(
            'checkbox',
            'legal',
            null,
            get_lang('IAcceptTermsAndConditions'),
            1
        );
        $form->addRule(
            'legal', get_lang('YouHaveToAcceptTermsAndConditions'),
            'required'
        );
        // Link to terms and conditions.
        $link_terms_and_conditions = '
            <script>
            function MM_openBrWindow(theURL, winName, features) { //v2.0
                window.open(theURL,winName,features);
            }
            </script>
        ';
        $link_terms_and_conditions .= Display::url(
            get_lang('ReadTermsAndConditions'),
            '#',
            ['onclick' => "javascript:MM_openBrWindow('$terms_and_conditions_url', 'Conditions', 'scrollbars=yes, width=800');"]
        );
        $form->addElement('label', null, $link_terms_and_conditions);
    }
}

$obj = new GradeModel();
$obj->fill_grade_model_select_in_form($form);

if (api_get_setting('teacher_can_select_course_template') === 'true') {
    $form->addElement(
        'select_ajax',
        'course_template',
        [
            get_lang('CourseTemplate'),
            get_lang('PickACourseAsATemplateForThisNewCourse'),
        ],
        null,
        ['url' => api_get_path(WEB_AJAX_PATH) . 'course.ajax.php?a=search_course']
    );
}

$form->addElement('html', '</div>');

// Submit button.
$form->addButtonCreate($course_validation_feature ? get_lang('CreateThisCourseRequest') : get_lang('CreateCourseArea'));

// The progress bar of this form.
$form->add_progress_bar();

// Set default values.
if (isset($_user['language']) && $_user['language'] != '') {
    $values['course_language'] = $_user['language'];
} else {
    $values['course_language'] = api_get_setting('platformLanguage');
}

$form->setDefaults($values);
$message = null;
$content = null;

// Validate the form.
if ($form->validate()) {
    $course_values = $form->exportValues();

    $wanted_code = $course_values['wanted_code'];
    $category_code = isset($course_values['category_code']) ? $course_values['category_code'] : '';
    $title = $course_values['title'];
    $course_language = $course_values['course_language'];
    $exemplary_content = !empty($course_values['exemplary_content']);

    if ($course_validation_feature) {
        $description = $course_values['description'];
        $objetives = $course_values['objetives'];
        $target_audience = $course_values['target_audience'];
    }

    if ($wanted_code == '') {
        $wanted_code = CourseManager::generate_course_code(api_substr($title, 0, CourseManager::MAX_COURSE_LENGTH_CODE));
    }

    // Check whether the requested course code has already been occupied.
    if (!$course_validation_feature) {
        $course_code_ok = !CourseManager::course_code_exists($wanted_code);
    } else {
        $course_code_ok = !CourseRequestManager::course_code_exists($wanted_code);
    }

    if ($course_code_ok) {
        if (!$course_validation_feature) {

            $params = array();
            $params['title'] = $title;
            $params['exemplary_content'] = $exemplary_content;
            $params['wanted_code'] = $wanted_code;
            $params['course_category'] = $category_code;
            $params['course_language'] = $course_language;
            $params['gradebook_model_id'] = isset($course_values['gradebook_model_id']) ? $course_values['gradebook_model_id'] : null;

            $course_info = CourseManager::create_course($params);

            if (!empty($course_info)) {
                /*
                $directory  = $course_info['directory'];
                $title      = $course_info['title'];

                // Preparing a confirmation message.
                $link = api_get_path(WEB_COURSE_PATH).$directory.'/';

                $tpl->assign('course_url', $link);
                $tpl->assign('course_title', Display::url($title, $link));
                $tpl->assign('course_id', $course_info['code']);

                $add_course_tpl = $tpl->get_template('create_course/add_course.tpl');
                $message = $tpl->fetch($add_course_tpl);*/

                $url = api_get_path(WEB_CODE_PATH);
                $url .= 'course_info/start.php?cidReq=';
                $url .= $course_info['code'];
                $url .= '&first=1';
                header('Location: ' . $url);
                exit;
            } else {
                $message = Display::return_message(
                    get_lang('CourseCreationFailed'),
                    'error',
                    false
                );
                // Display the form.
                $content = $form->returnForm();
            }
        } else {
            // Create a request for a new course.
            $request_id = CourseRequestManager::create_course_request(
                $wanted_code,
                $title,
                $description,
                $category_code,
                $course_language,
                $objetives,
                $target_audience,
                api_get_user_id(),
                $exemplary_content
            );

            if ($request_id) {
                $course_request_info = CourseRequestManager::get_course_request_info($request_id);
                $message = (is_array($course_request_info) ? '<strong>' . $course_request_info['code'] . '</strong> : ' : '') . get_lang('CourseRequestCreated');
                $message = Display::return_message(
                    $message,
                    'confirmation',
                    false
                );
                $message .= Display::tag(
                    'div',
                    Display::url(
                        get_lang('Enter'),
                        api_get_path(WEB_PATH) . 'user_portal.php',
                        ['class' => 'btn btn-default']
                    ),
                    ['style' => 'float: left; margin:0px; padding: 0px;']
                );
            } else {
                $message = Display::return_message(
                    get_lang('CourseRequestCreationFailed'),
                    'error',
                    false
                );
                // Display the form.
                $content = $form->return_form();
            }
        }
    } else {
        $message = Display::return_message(
            get_lang('CourseCodeAlreadyExists'),
            'error',
            false
        );
        // Display the form.
        $content = $form->return_form();
    }
} else {
    if (!$course_validation_feature) {
        $message = Display::return_message(get_lang('Explanation'));
    }
    // Display the form.
    $content = $form->returnForm();
}

$tpl->assign('message', $message);
$tpl->assign('content', $content);
$template = $tpl->get_template('layout/layout_1_col.tpl');
$tpl->display($template);
