<?php
/* For licensing terms, see /license.txt */
/**
 * SessionsBlockSliderPlugin class
 * Plugin to add a sessions slider in homepage
 * @package chamilo.plugin.sessions_slider_block
 * @author Angel Fernando Quiroz Campos <angel.quiroz@beeznest.com>
 */
class SessionsSliderBlockPlugin extends Plugin
{
    const CONFIG_SHOW_SLIDER = 'show_slider';
    const FIELD_VARIABLE_SHOW_IN_SLIDER = 'show_in_slider';
    const FIELD_VARIABLE_URL = 'url_in_slider';
    const FIELD_VARIABLE_IMAGE = 'image_in_slider';
    const FIELD_VARIABLE_COURSE_LEVEL = 'course_level';

    /**
     * Class constructor
     */
    protected function __construct()
    {
        parent::__construct(
            '1.0',
            'Angel Fernando Quiroz Campos',
            [self::CONFIG_SHOW_SLIDER => 'boolean']
        );
    }

    /**
     * Instance the plugin
     * @staticvar SessionsBlockSliderPlugin $result
     * @return Tour
     */
    public static function create(){
        static $result = null;

        return $result ? $result : $result = new self();
    }

    /**
     * Returns the "system" name of the plugin in lowercase letters
     * @return string
     */
    public function get_name()
    {
        return 'sessions_slider_block';
    }

    /**
     * Install the plugin
     */
    public function install()
    {
        $this->createExtraFields();
    }

    /**
     * Create the new extra fields
     */
    private function createExtraFields()
    {
        $sessionExtraField = new ExtraField('session');

        $sessionExtraField->save([
            'field_type' => ExtraField::FIELD_TYPE_CHECKBOX,
            'variable' => self::FIELD_VARIABLE_SHOW_IN_SLIDER,
            'display_text' => $this->get_lang('ShowInSliderBlock'),
            'default_value' => null,
            'field_order' => null,
            'visible' => true,
            'changeable' => true,
            'filter' => null
        ]);

        $sessionExtraField->save([
            'field_type' => ExtraField::FIELD_TYPE_TEXT,
            'variable' => self::FIELD_VARIABLE_URL,
            'display_text' => $this->get_lang('UrlForSliderBlock'),
            'default_value' => null,
            'field_order' => null,
            'visible' => true,
            'changeable' => true,
            'filter' => null
        ]);

        $sessionExtraField->save([
            'field_type' => ExtraField::FIELD_TYPE_FILE_IMAGE,
            'variable' => self::FIELD_VARIABLE_IMAGE,
            'display_text' => $this->get_lang('ImageForSliderBlock'),
            'default_value' => null,
            'field_order' => null,
            'visible' => true,
            'changeable' => true,
            'filter' => null
        ]);

        $levelOptions = array(
            get_lang('Beginner')
        );

        $courseExtraField = new ExtraField('course');
        $courseExtraField->save([
            'field_type' => ExtraField::FIELD_TYPE_SELECT,
            'variable' => self::FIELD_VARIABLE_COURSE_LEVEL,
            'display_text' => $this->get_lang('Level'),
            'default_value' => null,
            'field_order' => null,
            'visible' => true,
            'changeable' => true,
            'field_options' => implode('; ', $levelOptions)
        ]);
    }

    /**
     * Uninstall the plugin
     */
    public function uninstall()
    {
        $this->deleteExtraFields();
    }

    /**
     * Get the extra field information by its variable
     * @param sstring $fieldVariable The field variable
     * @return array The info
     */
    private function getExtraFieldInfo($fieldVariable)
    {
        $extraField = new ExtraField('session');
        $extraFieldHandler = $extraField->get_handler_field_info_by_field_variable($fieldVariable);

        return $extraFieldHandler;
    }

    /**
     * Get the created extrafields variables for session by this plugin
     * @return array The variables
     */
    public function getSessionExtrafields(){
        return [
            self::FIELD_VARIABLE_SHOW_IN_SLIDER,
            self::FIELD_VARIABLE_IMAGE,
            self::FIELD_VARIABLE_URL
        ];
    }

    /**
     * Get the created extrafields variables for courses by this plugin
     * @return array The variables
     */
    public function getCourseExtrafields(){
        return [
            self::FIELD_VARIABLE_COURSE_LEVEL
        ];
    }

    /**
     * Delete extra field and their values
     */
    private function deleteExtraFields()
    {
        $sessionVariables = $this->getSessionExtrafields();

        foreach ($sessionVariables as $variable) {
            $fieldInfo = $this->getExtraFieldInfo($variable);
            $fieldExists = $fieldInfo !== false;

            if (!$fieldExists) {
                continue;
            }

            $extraField = new ExtraField('session');
            $extraField->delete($fieldInfo['id']);
        }

        $courseVariables = $this->getSessionExtrafields();

        foreach ($courseVariables as $variable) {
            $fieldInfo = $this->getExtraFieldInfo($variable);
            $fieldExists = $fieldInfo !== false;

            if (!$fieldExists) {
                continue;
            }

            $extraField = new ExtraField('course');
            $extraField->delete($fieldInfo['id']);
        }
    }

    /**
     * Get the session to show in slider
     * @return array The session list
     */
    public function getSessionList()
    {
        $showInSliderFieldInfo = $this->getExtraFieldInfo(self::FIELD_VARIABLE_SHOW_IN_SLIDER);

        $fieldValueInfo = new ExtraFieldValue('session');
        $values = $fieldValueInfo->getValuesByFieldId($showInSliderFieldInfo['id']);

        if (!is_array($values)) {
            return [];
        }

        $sessions = [];

        foreach ($values as $valueInfo) {
            $sessions[] = api_get_session_info($valueInfo['item_id']);
        }

        return $sessions;
    }

}
