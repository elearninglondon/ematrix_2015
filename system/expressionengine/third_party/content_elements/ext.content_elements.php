<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * Content_elements extension Class - by KREA SK s.r.o.
 *
 * @package		Content_elements
 * @author		KREA SK s.r.o.
 * @copyright	Copyright (c) 2012, KREA SK s.r.o.
 * @link		http://www.krea.com/docs/content-elements
 */
class Content_elements_ext
{

    /**
     * Settings
     *  
     * @access public
     * @var array
     */
    var $settings = array();

    /**
     * Module name
     *  
     * @access public
     * @var string
     */
    var $name = '';

    /**
     * Module description
     *  
     * @access public
     * @var string
     */
    var $description = '';

    /**
     * Settings not exists
     *  
     * @access public
     * @var string
     */
    var $settings_exist = 'n';

    /**
     * Version
     *  
     * @access public
     * @var string
     */
    var $version = '1.6.10';

    /**
     * Docs
     *  
     * @access public
     * @var string
     */
    var $docs_url = 'http://www.krea.com';

    /**
     * Preview start tag
     *  
     * @access public
     * @var string
     */
    var $preview_start_tag = "<div class=\"ce_preview\">";

    /**
     * Preview end tag
     *  
     * @access public
     * @var string
     */
    var $preview_end_tag = "</div>";

    /*     * ********************************** FUNCTIONS LIST ***************************************** */

    /**
     * Constructor
     *  
     * @access public
     */
    function Content_elements_ext($settings = '')
    {
        $this->EE = & get_instance();

        if (isset($this->EE->session->userdata["language"]))
        {
            $this->EE->lang->loadfile('content_elements');
            $this->name = $this->EE->lang->line('content_elements_module_name');
        }
    }

    /**
     * Activate extensions
     *  
     * @access public
     */
    function activate_extension()
    {
        $this->EE->load->dbforge();

        $data = array(
            'class' => __class__,
            'method' => 'typography_parse_type_start',
            'hook' => 'typography_parse_type_start',
            'settings' => '',
            'priority' => 10,
            'version' => $this->version,
            'enabled' => 'y'
        );

        $this->EE->db->insert('extensions', $data);
    }

    /**
     * Update extension
     * 
     * @access public
     * @param string $current
     * @return boolean
     */
    function update_extension($current = '')
    {
        if ($current == '' OR $current == $this->version)
        {
            return FALSE;
        }

        ee()->db->where('class', __CLASS__);
        ee()->db->update(
                'extensions', array('version' => $this->version)
        );
    }

    /**
     * Deactivate extensions
     *  
     * @access public
     */
    function disable_extension()
    {
        $this->EE->load->dbforge();

        $this->EE->db->where('class', __class__);
        $this->EE->db->delete('extensions');
    }

    /**
     * Exclude ELEMENT system fields
     *
     * @param string ELEMENT name
     * @param array ELEMENT settings/data 
     * @return string
     */
    private function _exclude_setting_system_fields($settings)
    {
        if (isset($settings["title"]))
        {
            unset($settings["title"]);
        }
        if (isset($settings["eid"]))
        {
            unset($settings["eid"]);
        }
        return $settings;
    }

    /** ----------------------------------------------------------------------------------------------------
      /**
      /** 	HOOKS
      /**
      /** ---------------------------------------------------------------------------------------------------- */
    function typography_parse_type_start($str, $typography, $prefs)
    {
        global $CE_PATTERNS;

        //fetch elements
        $this->EE->load->library('elements');
        $this->EE->elements->fetch_avaiable_elements();

        if (defined("BASE") && isset($_GET["C"]) && $_GET["C"] == "content_publish")
        {
            $element_data = @unserialize($str);

            if (is_array($element_data))
            {
                $first_element = (end(array_reverse($element_data)));
            }

            if (is_array($element_data) && is_array($first_element) && isset($first_element["element_type"]) && isset($first_element["element_settings"]))
            {
                $this->EE->load->library('elements');

                $r = '<link rel="stylesheet" href="' . rtrim(CE_THEME_URL, '/') . '/styles/preview.css" type="text/css" media="screen" />
					<div class="ce_preview_wrapper"><div class="ce_preview_wrapper_inner">
				';

                foreach ($element_data as $index => $element)
                {
                    $element_settings = unserialize(base64_decode($element["element_settings"]));
                    $element_type = @$element_settings["type"];
                    $preview = '';

                    if ($element_type && method_exists($this->EE->elements->$element_type->handler, 'preview_element'))
                    {
                        //attach element settings

                        foreach ($this->_exclude_setting_system_fields($element_settings["settings"]) as $setting_var => $setting_value)
                        {
                            $this->EE->elements->$element_type->handler->settings[$setting_var] = $setting_value;
                        }

                        //start & end tag

                        $this->EE->elements->$element_type->handler->preview_start_tag = $this->preview_start_tag;
                        $this->EE->elements->$element_type->handler->preview_end_tag = $this->preview_end_tag;

                        //title & eid

                        $this->EE->elements->$element_type->handler->element_name = $element_settings["settings"]["title"];

                        $this->EE->elements->$element_type->handler->element_title = $element_settings["settings"]["title"];

                        $this->EE->elements->$element_type->handler->element_id = $index;

                        //validate data

                        $preview_data = $this->EE->elements->$element_type->handler->preview_element($element["data"]);

                        $preview = $this->EE->elements->$element_type->handler->preview_start_tag .
                                $preview_data .
                                $this->EE->elements->$element_type->handler->preview_end_tag;
                    }
                    else
                    {
                        $preview = $this->preview_start_tag . $element["data"] . $this->preview_end_tag;
                    }

                    $r .= $preview;
                }

                $pattern_id = "{" . md5('ce_' . uniqid() . rand(1, 100000)) . "}";

                $r .= '</div><script>
					$(".ce_preview_wrapper_inner").find(".ce_preview:last").css("border-bottom","none");
					$(".ce_preview_wrapper_inner").find(".ce_preview:last").css("padding-bottom","10px");
				</script></div>';

                $CE_PATTERNS[$pattern_id] = $r;

                ob_start();

                if (!function_exists('content_elements_preview_replacement'))
                {

                    function content_elements_preview_replacement()
                    {
                        global $CE_PATTERNS;
                        $r = ob_get_clean();

                        foreach ($CE_PATTERNS as $pattern => $replacement)
                        {
                            $r = str_replace("<p>" . $pattern . "</p>", $replacement, $r);
                            $r = str_replace($pattern, $replacement, $r);
                        }

                        echo $r;
                    }

                }

                register_shutdown_function('content_elements_preview_replacement');

                return $pattern_id;
            }
        }


        return $str;
    }

}

//END Class
