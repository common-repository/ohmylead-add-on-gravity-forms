<?php

GFForms::include_feed_addon_framework();

class GFOhmyleadAddOn extends GFFeedAddOn
{

    protected $_version = GF_SIMPLE_ADDON_VERSION;
    protected $_min_gravityforms_version = '1.9';
    protected $_slug = 'simpleaddon';
    protected $_path = 'simpleaddon/simpleaddon.php';
    protected $_full_path = __FILE__;
    protected $_title = 'Gravity Forms Ohmylead Add-On';
    protected $_short_title = 'Ohmylead';

    private static $_instance = null;

    public static function get_instance()
    {
        if (self::$_instance == null) {
            self::$_instance = new GFOhmyleadAddOn();
        }

        return self::$_instance;
    }

    public function init()
    {
        parent::init();
    }

    /**
     * Return the scripts which should be enqueued.
     *
     * @return array
     */
    public function scripts()
    {
        $scripts = array(
            array(
                'handle' => 'ohmylead_script_js',
                'src' => $this->get_base_url() . '/assets/js/ohmylead.js',
                'version' => $this->_version,
                'deps' => array('jquery'),
                'enqueue' => array(
                    array(
                        'admin_page' => array('form_settings'),
                        'tab' => 'simpleaddon'
                    )
                )
            ),
        );
        return array_merge(parent::scripts(), $scripts);
    }

    /**
     * Return the stylesheets which should be enqueued.
     *
     * @return array
     */
    public function styles()
    {
        $styles = array(
            array(
                'handle' => 'ohmylead_css',
                'src' => $this->get_base_url() . '/assets/css/ohmylead.css',
                'version' => $this->_version,
                'enqueue' => array(
                    array('admin_page' => array('form_settings'))
                )
            )
        );
        return array_merge(parent::styles(), $styles);
    }


    public function feed_settings_fields()
    {
        $current_form = $this->get_current_form();
        $form_fields = $current_form['fields'];

        if (rgpost('gform-settings-save')) {
           
            if (isset($_POST['_gaddon_setting_import']) && $_POST['_gaddon_setting_import'] == 1) {

                $response = $this->exportExistinggData($form_fields[0]['formId']);

                if ($response['countEntries'] == $response['exported']) {
                    GFCommon::add_message($response['exported'] . " Entry exported with success.");
                } else {
                    GFCommon::add_message($response['exported'] . " Entry exported with success.");
                    GFCommon::add_message(($response['countEntries'] - $response['exported']) . " Entry exported failed.", true);
                }
            }

        }

        return array(
            array(
                'fields' => array(
                    array(
                        'label' => "Source Name *",
                        'type' => 'text',
                        'name' => 'source_name',
                        'class' => 'medium',
                        'feedback_callback' => array($this, 'is_valid_setting'),
                    ),
                    array(
                        'label' => "Webhook URL *",
                        'type' => 'text',
                        'name' => 'webhook',
                        'class' => 'medium',
                        'feedback_callback' => array($this, 'is_valid_url'),
                    ),
                    array(
                        'label' => 'Active *',
                        'type' => 'radio',
                        'name' => 'is_active',
                        'horizontal' => true,
                        'default_value' => 'yes',
                        'choices' => array(
                            array(
                                'label' => 'Yes',
                                'value' => 'yes',
                            ),
                            array(
                                'label' => "No",
                                'value' => 'no',
                            ),
                        ),
                    )
                ),
            ),

            array(
                'title' => " Fields Mapping",
                'description' => "Match the fields between the system",
                'fields' => $this->data_select($form_fields)
            ),

        );
    }

    /**
     * Define the markup for the my_custom_field_type type field.
     *
     * @param array $field The field properties.
     */
    public function settings_my_custom_field_type($field)
    {
        $checkbox_field = $field['args']['checkbox'];
        $this->settings_checkbox($checkbox_field);
        $html = "<div class='custom-ohmylead-alert'><b>Warning!</b> If you ever export your data more than once, you will export all your existing entries (the ones you already exported and the new ones) resulting in having two of the same entry. To not encounter such activity, make sure you disable the box above.</div>";
        print $html;
    }

    public function data_select($fields)
    {

        $fields_form = array();

        if (is_array($fields) && count($fields) > 0) {
            foreach ($fields as $field) {
                $data = array();
                $data['type'] = "select";
                $data['label'] = $field['label'];
                $data['name'] = "map[".$field['id']."]";
                // $data['class'] = "ohmylead_map_field";
                $data['default_value'] = 'first - 3';
                $data['choices'] = $this->mapping_fields_ohmylead($data['label']);

                $fields_form[] = $data;
            }
            $source_url['type'] = "select";
            $source_url['label'] = "Source URL";
            $source_url['name'] = "source_url";
            // $data['class'] = "ohmylead_map_field";
            $source_url['default_value'] = 'first - 3';
            $source_url['choices'] = $this->mapping_fields_ohmylead($data['label']);
            $fields_form[] = $source_url;
            $fields_form[] = array(
                                'label' => 'Export existing Data',
                                'type' => 'my_custom_field_type',
                                'tooltip' => "Export Existing Data: When enabled, it will automatically export all your existing entries to your Ohmylead account.",
                                'args' => array(
                                    'checkbox' => array(
                                        'name' => 'is_import',
                                        'choices' => array(
                                            array(
                                                'label' => 'Yes',
                                                'value' => 'yes',
                                                'name' => 'import'
                                            ),
                                        )
                                    ),
                                ),
                            );

        }
        return $fields_form;
    }


    public function mapping_fields_ohmylead($field_name)
    {

        $data['label'] = "Map " . $field_name . " with : ";
        $data['value'] = 'null';
        $fields_form[] = $data;

        $fields = [
            'personal_name' => 'Full Name',
            'personal_first_name' => 'First Name',
            'personal_last_name' => 'Last Name',
            'added_at' => 'Genereted on',
            'email' => 'Email',
            'personal_phone' => 'Phone',
            'custom1' => 'Custom field #1',
            'custom2' => 'Custom field #2',
            'custom3' => 'Custom field #3',
            'custom4' => 'Custom field #4',
            'custom5' => 'Custom field #5',
            'custom6' => 'Custom field #6',
            'custom7' => 'Custom field #7',
            'custom8' => 'Custom field #8',
            'custom9' => 'Custom field #9',
            'custom10' => 'Custom field #10',
            'custom11' => 'Custom field #11',
            'custom12' => 'Custom field #12',
            'custom13' => 'Custom field #13',
            'custom14' => 'Custom field #14',
            'custom15' => 'Custom field #15',
            'custom16' => 'Custom field #16',
            'custom17' => 'Custom field #17',
            'custom18' => 'Custom field #18',
            'custom19' => 'Custom field #19',
            'custom20' => 'Custom field #20',
        ];

        if (is_array($fields) && count($fields) > 0) {
            foreach ($fields as $key => $field) {
                $data = array();
                $data['label'] = $field;
                $data['value'] = $key;
                $fields_form[] = $data;
            }

        }
        return $fields_form;
    }


    public function is_valid_url($value)
    {
        return filter_var($value, FILTER_VALIDATE_URL);
    }


    public function feed_list_columns()
    {
        return array(
            'source_name' => 'Name',
            'webhook' => 'Webhook URL'
        );
    }


    public function process_feed($feed, $entry, $form)
    {
        $fields_feed = $this->get_feed($feed['id']);

        if ($fields_feed['meta']['is_active']) {

            if (count($fields_feed['meta']['map']) > 0) {

                $json_body = array();

                foreach ($fields_feed['meta']['map'] as $field => $value) {

                    $json_body[$value] = rgar($entry, (string)$field);
                }
                $urlSourceMap = $fields_feed['meta']['source_url'];
                $json_body[$urlSourceMap] = $entry['source_url'];

                $form_data = array(
                    'sslverify' => false,
                    'ssl' => false,
                    'body' => $json_body,
                );
                $response = wp_remote_post($fields_feed['meta']['webhook'], $form_data);

            }

        }

        return true;
    }


    public function exportExistinggData($formId)
    {

        global $wpdb;

        $entriesTable = $wpdb->base_prefix . "gf_entry";
        $metaEntriesTable = $entriesTable . "_meta";

        $sql = "SELECT " . $entriesTable . ".* FROM " . $entriesTable . " WHERE " . $entriesTable . ".form_id =" . $formId;

        $fields_feed = $this->get_current_feed();

        $entriesList = $wpdb->get_results($sql);
        $countEntries = count($entriesList);
        if ($countEntries > 0) {
            $exported = 0;
            foreach ($entriesList as $key => $item) {
                $json_body = array();

                $sql = "SELECT meta_key,meta_value FROM " . $metaEntriesTable . " WHERE form_id = " . $formId . " AND entry_id = " . $item->id;
                $entriesMetaData = $wpdb->get_results($sql, ARRAY_A);

                $urlSourceMap = $fields_feed['meta']['source_url'];
                $json_body[$urlSourceMap] = $item->source_url;

                foreach ($fields_feed['meta']['map'] as $field => $value) {
                    $json_body[$value] = $this->getValueByMetaKey($entriesMetaData, $field);
                }

                $form_data = array(
                    'sslverify' => false,
                    'ssl' => false,
                    'body' => $json_body,
                );

                $response = wp_remote_post($fields_feed['meta']['webhook'], $form_data);

                if (!is_wp_error($response)) {
                    $exported++;
                }
            }
        }

        return array("countEntries" => $countEntries, "exported" => $exported);
    }

    public function getValueByMetaKey($entry, $key)
    {

        foreach ($entry as $index => $value) {
            if ($value['meta_key'] == $key) {
                return $value['meta_value'];
            }
        }
        return Null;
    }


}