<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

/**
 * Content_elements_ft fieldtype Class - by KREA SK s.r.o.
 *
 * @package		Content_elements
 * @author		KREA SK s.r.o.
 * @copyright	Copyright (c) 2012, KREA SK s.r.o.
 * @link		http://www.krea.com/docs/content-elements
 */
class Content_Elements_ft extends EE_Fieldtype {

	public $info = array(
		'name' => 'Content Elements',
		'version' => '1.6.10'
	);
	public $has_array_data = TRUE; // Parser Flag (preparse pairs?)
	static $cache = array(
		"includes" => array()
	);

	/**
	 * Constructor (inicialize session & language)
	 *
	 * @return void
	 */
	function __construct() {
		return $this->Content_Elements_ft();
	}

	function Content_Elements_ft() {
		parent::__construct();

		if (session_id() == '') {
			session_start();
		}

		// Create addon_name from class name
		$this->addon_name = strtolower(substr(__CLASS__, 0, -3));

		// Fetch language
		$this->EE->lang->loadfile('content_elements');

		// Load elements library
		$this->_load_elements_lib();

		// Define themes URL
		$this->_theme_url();
	}

	/**
	 * Backward compatiblity mode
	 *
	 * @return string
	 */
	public function _compatibilty_mode($data) {

		if (($old_data = @unserialize($data)) && is_array($old_data) && isset($old_data["element_name"])) {


			$new_data = array();

			foreach ($old_data["element_name"] as $element_key => $element_type) {
				if (!isset($old_data["element_settings"][$element_key])) {
					continue;
				}

				switch ($element_type) {
					default:
						$element_data = $old_data["tile"][$element_key];
						break;
					case "heading":
						$data_index = $old_data["tile"][$element_key];
						$element_data = serialize(array(
							"heading_id" => $data_index,
							"heading_data" => $old_data[$data_index],
								));
						break;
					case "table":
						$data_index = $old_data["tile"][$element_key];
						$element_data = serialize(array(
							"table_id" => $data_index,
							"table_data" => $old_data[$data_index],
								));
						break;
					case "gallery":
						$data_index = $old_data["tile"][$element_key];

						$element_data = serialize(array(
							"gallery_id" => $data_index,
							"gallery_data" => $old_data[$data_index],
								));
						break;
				}

				$new_data[md5(uniqid() . rand(1, 99999))] = array(
					"element_type" => $element_type,
					"element_settings" => $old_data["element_settings"][$element_key],
					"data" => $element_data,
				);
			}

			return serialize($new_data);
		}

		return $data;
	}

	/**
	 * Tool: get theme url
	 *  
	 * @access public
	 */
	public function _theme_url() {
		return $this->EE->elements->_theme_url();
	}

	/**
	 * Include CSS theme to CP header
	 *
	 * @param string CSS file naname
	 * @return string
	 */
	private function _include_theme_css($file, &$r = FALSE) {
		return $this->EE->elements->_include_theme_css($file, $r);
	}

	/**
	 * Include CSS file directly to CP header
	 *
	 * @param string CSS file naname
	 * @return string
	 */
	private function _include_css($file) {
		return $this->EE->elements->_include_css($file);
	}

	/**
	 * Include JS theme to CP header
	 *
	 * @param string JS file naname
	 * @return string
	 */
	private function _include_theme_js($file, &$r = FALSE) {
		return $this->EE->elements->_include_theme_js($file, $r);
	}

	/**
	 * Include JS theme to CP header
	 *
	 * @param string JS file naname
	 * @return string
	 */
	private function _include_js($file, &$r = FALSE) {
		return $this->EE->elements->_include_js($file, $r);
	}

	/**
	 * Include JS stream directly to CP header
	 *
	 * @param string JS file naname
	 * @return string
	 */
	private function _insert_js($js, &$r = FALSE) {
		return $this->EE->elements->_insert_js($js, $r);
	}

	/**
	 * ================================
	 * Display ELEMENT settings wrapper
	 * ================================
	 * 
	 * @param string ELEMENT name
	 * @param array ELEMENT settings/data 
	 * @return string
	 */
	private function _display_content_element_settings($element, $settings = array()) {

		if (!empty($this->EE->elements->$element->handler) and method_exists($this->EE->elements->$element->handler, 'display_element_settings')) {
			$data = $this->EE->elements->$element->handler->display_element_settings($this->_exclude_setting_system_fields($settings));

			if (is_array($data)) {
				$data = $this->EE->load->view($this->_get_view_path('layout/settings_table'), array("settings" => $data, 'element' => $element), TRUE);
			}
		} else {
			$data = '';
		}

		// Replace name="xxx" -> name="content_elements[element][__index__][var_name]"
		preg_match_all('/name\s*=\s*["|\']([^"\']*?)["|\']/', $data, $matches);
		if (isset($matches[0]) && is_array($matches[0]))
			foreach ($matches[0] as $k => $pattern) {
				$replace_with = rtrim(preg_replace('/(?<!\])\[/', ']\\0', $matches[1][$k]), ']');
				if (isset($settings["eid"])) {
					$replacement = str_replace($matches[1][$k], 'content_element[' . $element . '][' . $settings["eid"] . '][' . $replace_with . ']', $matches[0][$k]);
				} else {
					$replacement = str_replace($matches[1][$k], 'content_element[' . $element . '][__index__][' . $replace_with . ']', $matches[0][$k]);
				}
				$data = str_replace($pattern, $replacement, $data);
			}

		// Display
		$vars = array(
			"title" => @$settings['title'], // element title
			"eid" => (@$settings['eid']) ? $settings['eid'] : '__index__',
			"element" => $element, // element type
			"data" => $data,
		);

		return $this->EE->load->view($this->_get_view_path('layout/settings_wrapper'), $vars, TRUE);
	}

	/**
	 * Exclude ELEMENT system fields
	 *
	 * @param string ELEMENT name
	 * @param array ELEMENT settings/data 
	 * @return string
	 */
	private function _exclude_setting_system_fields($settings) {
		if (isset($settings["title"])) {
			unset($settings["title"]);
		}

		if (isset($settings["eid"])) {
			unset($settings["eid"]);
		}

		return $settings;
	}

	/**
	 * Load elements lib
	 *
	 * @return string
	 */
	private function _load_elements_lib() {
		if (!isset($this->EE->elements)) {
			require_once(dirname(__FILE__) . '/libraries/elements.php');
			$this->EE->elements = new Elements();
		}
	}

	/**
	 * Get elements field_name
	 *
	 * @return string
	 */
	private function _element_field_name() {

		// Matrix
		if (isset($this->cell_name)) {
			return $this->cell_name;
		}

		//grid
		if (isset($this->settings['grid_row_id'])) {
			//return 'ddd';
			//return "field_id_" . $this->settings['grid_field_id'] . "[rows][row_id_" . $this->settings['grid_row_id'] . "]" . "[col_id_" . $this->settings['col_id'] . "]";
		}

		return $this->field_name;
	}

	/**
	 * ==============================
	 * Save fieldtype on publish page
	 * ==============================
	 * 
	 * @param array data
	 * @param bool debug
	 * @param mixed action
	 * @return string
	 */
	public function save($data, $debug = FALSE, $action = FALSE) {
		// Fetch elements
		if (!isset($data))
			$data = array();

		// If filedtype is required, one element must be attached
		if (!empty($this->settings['field_required']) and $this->settings['field_required'] == 'y' && !count($data)) {
			return $this->EE->lang->line('required');
		}

		// Fetch elements
		$content_elements = $this->EE->elements->fetch_avaiable_elements($this->get_vars2export());

		// Loop all data	
		$save_data = array();

		foreach ((array) $data as $eid => $element) {

			if (empty($element["element_settings"])) {
				continue;
			}

			$element_settings = unserialize(base64_decode($element["element_settings"]));
			$element_type = $element_settings["type"];

			if (!empty($this->EE->elements->$element_type) and method_exists($this->EE->elements->$element_type->handler, 'save_element')) {
				// Attach element settings

				foreach ($this->_exclude_setting_system_fields($element_settings["settings"]) as $setting_var => $setting_value) {
					$this->EE->elements->$element_type->handler->settings[$setting_var] = $setting_value;
				}

				// Attach element field name
				$this->EE->elements->$element_type->handler->field_name = $this->_element_field_name() . '[' . $eid . '][data]';

				// Attach title					
				$this->EE->elements->$element_type->handler->element_title = $element_settings["settings"]["title"];

				// Set element id
				$this->EE->elements->$element_type->handler->element_id = $eid;

				// Set field id	
				if (isset($this->field_id) && $this->field_id) {
					$this->EE->elements->$element_type->handler->field_id = $this->field_id;
				}

				// Save element data
				if (!empty($action)) {

					switch ($action) {
						case 'draft':
							// If save_element_draft exists
							if (method_exists($this->EE->elements->$element_type->handler, 'save_element_draft')) {
								$data[$eid]["data"] = $this->EE->elements->$element_type->handler->save_element_draft($data[$eid]["data"]);
								$save_action_done = TRUE;
							}
							break;
						case 'draft_publish':
							// If publish_element_draft exists
							if (method_exists($this->EE->elements->$element_type->handler, 'publish_element_draft')) {
								$data[$eid]["data"] = $this->EE->elements->$element_type->handler->publish_element_draft($data[$eid]["data"]);
								$save_action_done = TRUE;
							}
							break;
						case 'draft_discard':
							// If discard_element_draft exists
							if (method_exists($this->EE->elements->$element_type->handler, 'discard_element_draft')) {
								$data[$eid]["data"] = $this->EE->elements->$element_type->handler->discard_element_draft($data[$eid]["data"]);
								$save_action_done = TRUE;
							}
							break;
					}
				}

				// No action done, call save_element
				if (empty($save_action_done)) {
					$data[$eid]["data"] = $this->EE->elements->$element_type->handler->save_element($data[$eid]["data"]);
				}
			} else {
				//$data[$eid]["data"] = $data[$eid]["data"];
			}
		}

		// Serialize & base64_decode(string encoded_data)
		if ($data) {
			$data = serialize($data);
			//$data = base64_encode($data);
		}

		return $data;
	}

	/**
	 * ===================================
	 * Post Save fieldtype on publish page
	 * ===================================
	 *
	 * @param array data 
	 * @return string
	 */
	public function post_save($data) {

		// Empty save result
		if (!$data) {
			return $data;
		}

		// Fetch elements
		$this->_load_elements_lib();
		$content_elements = $this->EE->elements->fetch_avaiable_elements($this->get_vars2export());

		// Current settings
		if (!is_array($data))
			$data = unserialize($data);

		foreach ($data as $eid => $element) {

			if (empty($element["element_type"]))
				continue;

			$element_data = $element["data"];
			$element_settings = unserialize(base64_decode($element["element_settings"]));
			$element_type = $element_settings["type"];

			if (!empty($this->EE->elements->$element_type) and method_exists($this->EE->elements->$element_type->handler, 'post_save_element')) {
				// Attach element settings
				foreach ($this->_exclude_setting_system_fields($element_settings["settings"]) as $setting_var => $setting_value) {
					$this->EE->elements->$element_type->handler->settings[$setting_var] = $setting_value;
				}

				// Attach element field name
				$this->EE->elements->$element_type->handler->field_name = $this->_element_field_name() . '[' . $eid . '][data]';

				// Attach title					
				$this->EE->elements->$element_type->handler->element_title = $element_settings["settings"]["title"];

				// Set element id
				$this->EE->elements->$element_type->handler->element_id = $eid;

				// Set field id	
				if (isset($this->field_id) && $this->field_id) {
					$this->EE->elements->$element_type->handler->field_id = $this->field_id;
				}

				// Send data & call event
				$this->EE->elements->$element_type->handler->post_save_element($data[$eid]["data"]);
			}
		}

		## fix saved revision
		
		// get channel settings
		$query = ee()->api_channel_structure->get_channel_info($_POST['channel_id']);
		foreach(array('channel_url', 'rss_url', 'deft_status', 'comment_url', 'comment_system_enabled', 'enable_versioning', 'max_revisions') as $key)
		{
			$c_prefs[$key] = $query->row($key);
		}
		
		// get entry titles
		ee()->db->select('versioning_enabled');
		$query_v = ee()->db->get_where('channel_titles', array('entry_id' => $this->settings['entry_id']));
		
		// if entry version is disable then disable versioning
		if ($query_v->row('versioning_enabled') == 'n')
		{
			$c_prefs['enable_versioning'] = 'n';
		}
		
		// if is versioning enabled then fix saved data
		if ($c_prefs['enable_versioning'] == 'y')
		{
			// get last saved revision
			ee()->db->select('*');
			ee()->db->from('entry_versioning');
			ee()->db->where('entry_id', $this->settings['entry_id']);
			ee()->db->order_by('version_id', 'desc');
			ee()->db->limit(1);
				
			$query = ee()->db->get();
				
			// if revision exist
			if ($query->num_rows == 1) {
		
				$temp = $query->result_array();
		
				// replace wrong data
				$field = unserialize($temp[0]['version_data']);
				$field[$this->settings['field_name']] = $data;
				$field = serialize($field);
		
				// update last revision with correct data
				ee()->db->update('entry_versioning', array('version_data'	=> $field), array('version_id' => $temp[0]['version_id']));
		
			}
				
		}

		return $data;
	}

	/**
	 * ==================================
	 * Validate fieldtype on publish page
	 * ==================================
	 *
	 * @param array data
	 * @return string
	 */
	public function validate($data) {
		// Fetch elements
		if (!isset($data) || !$data)
			$data = array();

		// if filedtype is required, one element must be attached
		if ($this->settings['field_required'] == 'y' && !count($data)) {
			return $this->EE->lang->line('required');
		}

		// Fetch elements
		$this->_load_elements_lib();
		$content_elements = $this->EE->elements->fetch_avaiable_elements($this->get_vars2export());

		// Loop all data
		foreach ($data as $eid => $element) {
			$element_settings = unserialize(base64_decode($element["element_settings"]));
			$element_type = $element["element_type"];

			$this->EE->elements->$element_type->handler->element_name
					= $element_settings["settings"]["title"];

			$this->EE->elements->$element_type->handler->element_title
					= $element_settings["settings"]["title"];

			$this->EE->elements->$element_type->handler->element_id
					= $element_settings["settings"]["eid"];

			if (!empty($this->EE->elements->$element_type->handler) and method_exists($this->EE->elements->$element_type->handler, 'validate_element')) {
				// Attach element settings
				foreach ($this->_exclude_setting_system_fields($element_settings["settings"]) as $setting_var => $setting_value) {
					$this->EE->elements->$element_type->handler->settings[$setting_var] = $setting_value;
				}

				if (empty($data[$eid]["data"])) {
					return FALSE;
				}

				// Validate data
				$validate = $this->EE->elements->$element_type->handler->validate_element($data[$eid]["data"]);

				if ($validate && $validate !== TRUE) {
					return $validate;
				}
			}
		}

		return TRUE;
	}

	/**
	 * ==============================
	 * Show fieldtype on PUBLISH page
	 * ==============================
	 *
	 * @return string
	 */
	public function display_field($data) {
		$r = '';
		$this->_include_js($this->_theme_url() . 'scripts/jquery.ui.sortable.js', $r);
		$this->_include_js($this->_theme_url() . 'scripts/display_field.js', $r);
		//$this->_include_js($this->_theme_url() . 'scripts/ee_filebrowser.js', $r);
		// CSS
		$this->_include_theme_css('display_field.css', $r);

		// Check to see if we are loading a draft into the publish view
		/*
		  #0005060
		  if (isset($this->EE->session->cache['ep_better_workflow']['is_draft']) && $this->EE->session->cache['ep_better_workflow']['is_draft']) {

		  if (is_array($data))
		  $data = implode(current($data), ',');
		  } */

		// Try unserialize data field
		if (!is_array($data)) {
			// If member provides UPDATE fix EE security: slashed variables recursively
			$data = html_entity_decode($data, ENT_QUOTES);

			// Unserialize
			$data = $this->_compatibilty_mode($data);

			$data = @unserialize($data);
		} else {

			// If member provides SAVE & VALIDATION failed
			if (!function_exists('_ce_remove_html_entities')) {

				function _ce_remove_html_entities($input) {
					if (is_array($input)) {
						foreach ($input as $k => $v) {
							$input[$k] = _ce_remove_html_entities($v);
						}
					} else {
						$input = html_entity_decode($input, ENT_QUOTES);
					}
					return $input;
				}

			}

			// Fix EE security: slashed variables recursively			
			$data = _ce_remove_html_entities($data);
		}

		// Keep array format (if unserialize failed)
		if (!is_array($data)) {
			$data = array();
		}

		// Load Elements
		$this->_load_elements_lib();
		$content_elements = $this->EE->elements->fetch_avaiable_elements($this->get_vars2export());
		$settings = unserialize($this->settings['content_elements']);

		// Prepare 
		global $content_elements_initialized;
		if (!isset($content_elements_eid_defined)) {

			// Dialogs
			$r .= '<script type="text/javascript">
				var content_elements_remove_dialog_btn_yes = "' . $this->EE->lang->line('content_elements_remove_dialog_btn_yes') . '";
				var content_elements_remove_dialog_btn_no = "' . $this->EE->lang->line('content_elements_remove_dialog_btn_no') . '";
			</script>';
			$r .= '<div style="display:none" id="ce_delete_alert_body" title="' . $this->EE->lang->line('content_elements_remove_dialog_head') . '"><div class="ui-dialog-content ui-widget-content" style="color: red">' . $this->EE->lang->line('content_elements_remove_dialog_body') . '</div></div>';

			// Elements empty settings list
			$r .= '<script type="text/javascript">if (typeof content_elements_eid == "undefined" || !(content_elements_eid instanceof Array)) { var content_elements_eid = [];}</script>';
		}

		// Fetch each element settings
		$r .= '<script type="text/javascript">';
		foreach ($settings as $k => $element) {
			$eid = $element["settings"]["eid"];
			$eid_settings = base64_encode(serialize($element));
			$r .= 'content_elements_eid["' . $eid . '"] = "' . $eid_settings . '";';
		}
		$r .= '</script>';

		// Display prototypes of All available elements
		$elements_prototypes = '';

		foreach ($settings as $k => $element) {
			$element_type = $element["type"];
			$element_settings = $this->_exclude_setting_system_fields($element["settings"]);

			if (!empty($this->EE->elements->$element_type->handler)
					and method_exists($this->EE->elements->$element_type->handler, 'display_element')) {

				if (REQ == 'PAGE' and !empty($this->EE->elements->$element_type->handler->only_cp)) {
					continue;
				}

				foreach ($element_settings as $setting_var => $setting_value) {
					$this->EE->elements->$element_type->handler->settings[$setting_var] = $setting_value;
				}

				$this->EE->elements->$element_type->handler->field_name = '__element_name__[__index__][data]';

				// Set element id
				$this->EE->elements->$element_type->handler->element_id = $element["settings"]["eid"];

				$elements_prototypes[$element["settings"]["eid"]] = array(
					"type" => $element_type,
					"content" => $this->EE->elements->$element_type->handler->display_element(''),
					"settings" => base64_encode(serialize($element)),
					"eid" => $element["settings"]["eid"],
					"title" => $element["settings"]["title"],
					"element_field_name" => $this->EE->elements->$element_type->handler->field_name,
					"element_field_id" => !empty($this->settings["field_id"]) ? $this->settings["field_id"] : NULL,
				);
			}
		}

		foreach ($data as $eid => $element) {

			if (empty($element["element_type"]))
				continue;

			$element_type = $element["element_type"];

			if (empty($this->EE->elements->$element_type->handler))
				continue;

			if (REQ == 'PAGE' and !empty($this->EE->elements->$element_type->handler->only_cp)) {
				continue;
			}

			$element_settings = unserialize(base64_decode($element["element_settings"]));

			if (!isset($elements_prototypes[$element_settings["settings"]["eid"]])) {
				foreach ($this->_exclude_setting_system_fields($element_settings["settings"]) as $setting_var => $setting_value) {
					$this->EE->elements->$element_type->handler->settings[$setting_var] = $setting_value;
				}
				$this->EE->elements->$element_type->handler->field_name = '__element_name__[__index__][data]';

				$elements_prototypes[$element_settings["settings"]["eid"]] = array(
					"type" => $element_type,
					"content" => $this->EE->elements->$element_type->handler->display_element(FALSE),
					"settings" => base64_encode(serialize($element_settings)),
					"eid" => $element_settings["settings"]["eid"],
					"title" => $element_settings["settings"]["title"],
					"element_field_name" => $this->EE->elements->$element_type->handler->field_name,
					"element_field_id" => !empty($this->settings["field_id"]) ? $this->settings["field_id"] : NULL,
				);
			}
		}

		// Compose toolbar icons
		$buttons = array();
		foreach ($settings as $k => $element) {

			$element_type = $element["type"];
			$element_settings = $this->_exclude_setting_system_fields($element["settings"]);

			if (REQ == 'PAGE' and !empty($this->EE->elements->$element_type->handler->only_cp)) {
				continue;
			}

			$buttons[] = array(
				"type" => $element['type'],
				"title" => $element['settings']['title'],
				"eid" => $element['settings']['eid'],
				"settings" => $element['settings']
			);
		}

		// Load tiles
		$tiles = array();

		foreach ($data as $eid => $element) {

			if (empty($element["element_type"]))
				continue;

			$element_type = $element["element_type"];
			$element_settings = unserialize(base64_decode($element["element_settings"]));

			if (empty($this->EE->elements->$element_type->handler))
				continue;

			$this->EE->elements->$element_type->handler->element_id = $element_settings["settings"]["eid"];

			// Load actual settings
			foreach ($settings as $k => $basic_settings) {
				if ($settings[$k]["settings"]["eid"] == $element_settings["settings"]["eid"]) {
					$element_settings = $settings[$k];
				}
			}

			// Fetch handler
			foreach ($this->_exclude_setting_system_fields($element_settings["settings"]) as $setting_var => $setting_value) {
				$this->EE->elements->$element_type->handler->settings[$setting_var] = $setting_value;
			}

			$this->EE->elements->$element_type->handler->field_name = $this->_element_field_name() . '[' . $eid . '][data]';
			$this->EE->elements->$element_type->handler->element_field_id = !empty($this->settings["field_id"]) ? $this->settings["field_id"] : NULL;

			// Parse data & show field
			$tiles[] =
					array(
						"type" => $element_type,
						"content" => $this->EE->elements->$element_type->handler->display_element($element["data"]),
						"settings" => base64_encode(serialize($element_settings)),
						"eid" => $eid,
						"title" => $element_settings["settings"]["title"],
						"element_field_name" => $this->_element_field_name() . '[' . $eid . ']',
						"element_field_id" => !empty($this->settings["field_id"]) ? $this->settings["field_id"] : NULL,
			);
		}

		$protypes = $this->EE->load->view($this->_get_view_path('publish_prototypes'), array(
			"field_id" => $this->settings["field_id"],
			"tiles" => $elements_prototypes,
				), TRUE
		);

		if (REQ == 'CP') {
			$this->EE->cp->add_to_foot($protypes);
		} else {
			$r .= $protypes;
		}

		// Compose vars	[print_r($elements_prototypes); exit;]
		$vars = array(
			"field_id" => $this->settings["field_id"],
			"field_name" => $this->_element_field_name(),
			"buttons" => $buttons,
			"tiles" => $tiles,
			"theme_url" => $this->_theme_url(),
		);

		$r .= $this->EE->load->view($this->_get_view_path('publish'), $vars, TRUE);

		return $r;
	}

	/**
	 * =============
	 * Save settings
	 * =============
	 *
	 * @param array submit data
	 * @return array save data 
	 */
	public function save_settings($data) {
		//dump_var($_POST['content_element_item']);
		//dump_var($data, 1);
		// Not set? set!
		if (!isset($data['content_element_item']) and isset($_POST['content_element_item'])) {
			$data['content_element_item'] = (array) $_POST['content_element_item'];
			$data['content_element'] = (array) $_POST['content_element'];
		}

		// Not array? array!
		if (empty($data['content_element_item']) or !is_array($data['content_element_item'])) {
			$data['content_element_item'] = array();
			$data['content_element'] = array();
		}

		// Get lists of elements
		$this->_load_elements_lib();
		$content_elements = $this->EE->elements->fetch_avaiable_elements($this->get_vars2export());

		$save_settings = array();

		// Loop element types
		//foreach ($_POST['content_element_item'] as $element_id => $element_type) {
		foreach ($data['content_element_item'] as $element_id => $element_type) {

			//$settings = @$_POST['content_element'][$element_type][$element_id];
			$settings = @$data['content_element'][$element_type][$element_id];
			if (is_array($settings)) {

				// Separate systems settings (title & eid) // the reason is compatibility with CE 1.4.0.3
				$title = '';
				$eid = $element_id;

				if (isset($settings["title"])) {
					$title = $settings["title"];
				}

				if (isset($settings["eid"])) {
					$eid = $settings["eid"];
				}

				// Loop params
				if (!empty($this->EE->elements->$element_type->handler) and method_exists($this->EE->elements->$element_type->handler, 'save_element_settings')) {
					$settings = $this->EE->elements->$element_type->handler->save_element_settings($this->_exclude_setting_system_fields($settings));
				}

				$settings["eid"] = $element_id;
				$settings["title"] = $title;
			}

			$save_settings[] = array(
				'type' => $element_type,
				'settings' => $settings,
			);
		}

		//dump_var($save_settings, 1);

		return array(
			'content_elements' => serialize($save_settings)
		);
	}

	/**
	 * Post save settings
	 *
	 * @param array field data
	 * @return array field data 
	 */
	public function post_save_settings($data) {
		// Get lists of elements
		$this->_load_elements_lib();
		$content_elements = $this->EE->elements->fetch_avaiable_elements($this->get_vars2export());

		// Get current settings
		$field_settings = $this->EE->db->
						select("field_settings")->
						from($this->EE->db->dbprefix . "channel_fields")->
						where("field_id", $data["field_id"])->
						get()->row('field_settings');

		if (is_array($field_settings))
			return $data;

		$field_settings = unserialize(base64_decode($field_settings));

		$saved_settings = unserialize($field_settings["content_elements"]);

		// Loop & resave
		foreach ($saved_settings as $index => $element) {
			$element_type = $element["type"];
			$element_settings = $element["settings"];

			// POST_SAVE_ELEMENT_SETTINGS
			if (!empty($this->EE->elements->$element_type->handler) and method_exists($this->EE->elements->$element_type->handler, 'post_save_element_settings')) {
				$element_eid = $element_settings["eid"];
				$element_title = $element_settings["title"];

				// Attach element settings identifier
				$this->EE->elements->$element_type->handler->element_settings_id = $element_eid;

				// Attach element title
				$this->EE->elements->$element_type->handler->element_title = $element_title;

				// Attach field id	
				if (isset($this->field_id) && $this->field_id) {
					$this->EE->elements->$element_type->handler->field_id = $this->field_id;
				}

				// Call event
				$this->EE->elements->$element_type->handler->post_save_element_settings($this->_exclude_setting_system_fields($element_settings));
			}
		}

		return $data;
	}

	/**
	 * Display settings
	 *
	 * @param array settings data
	 * @return array settings data 
	 */
	public function display_settings($data) {

		// Load CSS & JS
		$this->_include_theme_css('settings.css');
		$this->_include_theme_js('jquery.ui.sortable.js');

		// Not set? Set! Not array? Array!
		if (isset($data['content_elements']) && is_array(@unserialize($data['content_elements']))) {
			$data['content_elements'] = @unserialize($data['content_elements']);
		}

		if (!isset($data['content_elements'])) {
			$data['content_elements'] = array();
		}

		// Get lists of elements
		$this->_load_elements_lib();

		$content_elements = $this->EE->elements->fetch_avaiable_elements($this->get_vars2export());

		$content_elements_type_options = array('' => $this->EE->lang->line('settings_choose_content_element_type'));

		$content_elements_settings = array();

		// Load elements & show config
		foreach ($content_elements as $element) {
			// Fill available elements to dropdown
			$content_elements_type_options[$element] = $this->EE->elements->{$element}->name;

			// Fill available elements to hidden settings element
			$content_elements_settings[$element] = $this->_display_content_element_settings($element);
		}

		// Load current settings
		$current_configuration_data = '';

		foreach ($data['content_elements'] as $current_configuration) {
			// Show settings as visbible settings element
			$current_configuration_data .= $this->_display_content_element_settings($current_configuration["type"], $current_configuration["settings"]);
		}

		// Sumarize template data
		$vars = array(
			'elements' => $content_elements,
			'content_elements_type_options' => $content_elements_type_options,
			'content_elements_settings' => $content_elements_settings,
			'current_configuration' => $current_configuration_data,
		);

		// Display settings
		$this->EE->cp->add_to_foot($this->EE->load->view($this->_get_view_path('layout/settings_hidden_box'), $vars, TRUE));
		return $this->EE->load->view($this->_get_view_path('settings/settings'), $vars, TRUE);
	}

	/**
	 * Replace frontend tag
	 *
	 * @param array data for content
	 * @param array fetch params 
	 * @param string html
	 * @return string
	 */
	public function replace_tag($data, $params = array(), $tagdata = FALSE) {
		// Compatibilty mode
		$data = $this->_compatibilty_mode($data);

		global $element_counter_global;

		// Global counter
		if (isset($element_counter_global)) {
			$element_counter_global++;
		} else {
			$element_counter_global = 1;
		}

		$output = '';

		if (!is_array($data)) {
			$data = @unserialize($data);
		}

		if (!is_array($data)) {
			$data = array();
		}

		$this->_load_elements_lib();
		$this->EE->elements->fetch_avaiable_elements($this->get_vars2export());

		// Parse count of all elements in entry
		$tagdata = str_replace('{ce_count}', count($data), $tagdata);

		// Initialization of first and last tag
		$ce_first = TRUE;
		$ce_last = FALSE;

		foreach ($data as $index => $element) {
			if (!isset($element["element_type"]) || !isset($element["element_settings"])) {
				continue;
			}

			$element_type = $element["element_type"];
			$element_settings = unserialize(base64_decode($element["element_settings"]));

			preg_match_all('#{' . $element_type . '([^}]*?)}(.*?){/' . $element_type . '}#su', $tagdata, $matches);

			if (isset($matches[0]) && is_array($matches[0])) {
				foreach ($matches[2] as $k => $tagdata_chunk) {

					// Fetch params
					$param_string = trim($matches[1][$k]);
					$params = array();

					preg_match_all('#(.*?)="(.*?)"#su', $param_string, $param_matches);

					if (isset($param_matches[0]) && is_array($param_matches[0])) {
						foreach ($param_matches[0] as $param_key => $param_chunk) {
							if (trim($param_matches[1][$param_key]) && trim($param_matches[2][$param_key])) {
								$params[trim($param_matches[1][$param_key])] = trim($param_matches[2][$param_key]);
							}
						}
					}

					// REPLACE_ELEMENT_TAG
					if (!empty($this->EE->elements->$element_type->handler) and method_exists($this->EE->elements->$element_type->handler, 'replace_element_tag')) {
						// Attach element settings
						foreach ($this->_exclude_setting_system_fields($element_settings["settings"]) as $setting_var => $setting_value) {
							$this->EE->elements->$element_type->handler->settings[$setting_var] = $setting_value;
						}

						// Deprecated since CE 1.0
						$this->EE->elements->$element_type->handler->element_name
								= $element_settings["settings"]["title"];

						$this->EE->elements->$element_type->handler->element_title
								= $element_settings["settings"]["title"];

						$this->EE->elements->$element_type->handler->element_id
								= $index; //$element_settings["settings"]["eid"];dump_var($data, 1);

						$this->EE->elements->$element_type->handler->row
								= $this->row; //$element_settings["settings"]["eid"];dump_var($data, 1);
						// Start & end tag
						$tagdata_parsed = $this->EE->elements->$element_type->handler->replace_element_tag($element["data"], $params, $tagdata_chunk);
					} else {
						$tagdata_parsed = $this->EE->elements->$element_type->handler->replace_tag($element["data"], $params, $tagdata_chunk);
					}


					// Parse CE_First variable
					$tagdata_parsed = str_replace('{ce_first}', $ce_first, $tagdata_parsed);

					// Parse CE_Last variable
					if ($index == end(array_keys($data)))
						$ce_last = TRUE;

					// Parse, if element is the last element
					$tagdata_parsed = str_replace('{ce_last}', $ce_last, $tagdata_parsed);

					// Counter
					if (isset($element_counter[$element_type])) {
						$element_counter[$element_type]++;
					} else {
						$element_counter[$element_type] = 1;
					}

					// Parse element ID
					$tagdata_parsed = str_replace("{element_id}", $element_type . '_' . $element_counter_global . '_' . $element_counter[$element_type], $tagdata_parsed);

					// Parse varibles in conditions
					$conds = array(
						'ce_first' => (bool) $ce_first,
						'ce_last' => (bool) $ce_last,
					);

					$tagdata_parsed = $this->EE->functions->prep_conditionals($tagdata_parsed, $conds);

					// If element is first, set next elements not to be first
					if ($ce_first)
						$ce_first = FALSE;

					$output .= $tagdata_parsed;
				}
			}
		}

		return $output;
	}

	/**
	 * Return view path.
	 */
	private function _get_view_path($name = '') {

		if (version_compare(APP_VER, '2.2.0', '>=')) {

			// Load package path for third party fieldtype loaders
			$this->EE->load->add_package_path(dirname(__FILE__));

			// Field type library
			return $name;
		} else {
			return '../../' . $this->addon_name . '/views/' . $name;
		}
	}

	/**
	 * Return array of variables added to each element 
	 * 
	 * @return array
	 */
	private function get_vars2export() {
		return array(
			'var_id' => !empty($this->var_id) ? $this->var_id : FALSE
		);
	}

	/**
	 * --------------------
	 * Matrix compatibility
	 * --------------------
	 */
	public function display_cell($data) {
		$this->_include_theme_js('display_field_matrix.js');
		return $this->display_field($data);
	}

	/**
	 * Display CELL settings
	 *
	 * @param array settings data
	 * @return array settings data 
	 */
	public function display_cell_settings($data) {
		return $this->display_settings($data);
	}

	/**
	 * Save CELL settings
	 *
	 * @param array settings data
	 * @return array settings data 
	 */
	public function save_cell_settings($data) {
		$_POST_BACKUP = $_POST;

		$_POST = array(
			'content_element_item' => isset($data["content_element_item"]) ? $data["content_element_item"] : array(),
			'content_element' => isset($data["content_element"]) ? $data["content_element"] : array(),
		);

		$data = $this->save_settings($data);

		global $_POST;
		$_POST = $_POST_BACKUP;

		return $data;
	}

	/**
	 * Validate CELL
	 *
	 * @param array settings data
	 * @return array settings data 
	 */
	public function validate_cell($data) {
		return $this->validate($data);
	}

	/**
	 * Save CELL
	 *
	 * @param array settings data
	 * @return array settings data 
	 */
	public function save_cell($data) {
		return $this->save($data);
	}

	/**
	 * Save CELL
	 *
	 * @param array settings data
	 * @return array settings data 
	 */
	public function post_save_cell($data) {
		return $this->post_save($data);
	}

	/**
	 * -----------------------------
	 * Better Workflow compatibility
	 * -----------------------------
	 */

	/**
	 * Delete draft data
	 *
	 * @return string
	 */
	public function draft_discard() {

		// Delete all the current draft content
		return $this->save($this->_get_current_data(), 0, 'draft_discard');
	}

	/**
	 * Publish draft data
	 *
	 * @return string
	 */
	public function draft_publish() {

		// Delete all the current draft content
		return $this->save($this->_get_current_data(), 0, 'draft_publish');
	}

	/**
	 * Return current data
	 *
	 * @return array
	 */
	private function _get_current_data() {

		// Init data
		$data = array();

		if (!empty($_GET['entry_id']) and !empty($this->settings['field_name'])) {
			$data_serialized = $this->EE->db
					->select($this->settings['field_name'])
					->from($this->EE->db->dbprefix . "channel_data")
					->where("entry_id", (int) $_GET['entry_id'])
					->get()
					->row($this->field_name);

			if (is_array($data_serialized->{$this->settings['field_name']}))
				$data = $data_serialized;
			else
				$data = unserialize($data_serialized->{$this->settings['field_name']});
		}

		return $data;
	}

	/**
	 * Save draft data
	 *
	 * @return string
	 */
	public function draft_save($data, $draft_action = "") {
		return $this->save($data, 0, 'draft');
	}

	/**
	 * ---------------------------
	 * Low variables compatibility
	 * ---------------------------
	 */

	/**
	 * Show fieldtype on PUBLISH page
	 *
	 * @return string
	 */
	public function display_var_field($data) {

		// Filemanager fix
		$this->EE->elements->create_ee_upload_field('image', '', 0, 'all');

		$this->settings["field_id"] = $this->var_id;
		return $this->display_field($data);
	}

	/**
	 * Display var settings
	 *
	 * @param array settings
	 * @return array
	 */
	public function display_var_settings($settings) {

		return array(
			array(
				lang('label_settings_link_label'),
				$this->display_settings($settings)
			)
		);
	}

	/**
	 * Method to catch the settings values before saving them to the database.
	 *
	 * @param array settings
	 * @return array
	 */
	public function save_var_settings($settings) {
		return $this->save_settings($settings);
	}

	/**
	 * Save
	 *
	 * @param array data
	 * @return string
	 */
	public function save_var_field($data) {
		return $this->save($data);
	}

	/**
	 * Replace Low Variables tag
	 *
	 * @param array data for content
	 * @param array fetch params 
	 * @param string html
	 * @return string
	 */
	public function display_var_tag($data, $params = '', $tagdata = '') {
		return $this->replace_tag($data, $params, $tagdata);
	}

	/**
	 * ---------------------------
	 * Grid compatibility
	 * ---------------------------
	 */

	/**
	 * Grid compatibility check
	 *
	 * @param string
	 * @return boolean
	 */
	public function accepts_content_type($name = '') {
		return ($name == 'channel' || $name == 'grid');
	}

	/**
	 * Grid settings
	 *
	 * @param array
	 * @return array - Array of settings for the column
	 */
	public function grid_display_settings($data = array()) {

		$settings = $this->display_settings($data);
		$this->_include_theme_js('display_field_grid.js');

		return array($settings);
	}

	/**
	 * Grid settings
	 *
	 * @param array
	 * @return array - Array of settings for the column
	 */
	public function grid_validate_settings($data = array()) {

		//$settings = $this->display_settings($data);
		//$this->_include_theme_js('display_field_grid.js');

		$this->_include_theme_js('validate_field_grid.js');

		$settings = '<script></script>';

		//return array($settings);
	}

	/**
	 * Only called when being rendered in a Grid field cell:
	 *
	 * @param array
	 * @return array - Array of settings for the column
	 */
	public function grid_display_field($data = array()) {

		$this->_include_theme_js('display_field_grid.js');

		$this->settings['field_id'] = $this->settings['grid_field_id'];

		return $this->display_field($data);
	}

	/**
	 * Only called when being saved in a Grid field cell:
	 *
	 * @param array
	 * @return array - Array of data for the column
	 */
	public function grid_save($data = array()) {
		$data = (array) $data;
		foreach ($data as $key => &$value) {
			if (is_scalar($value))
				$value = !empty($_POST['content_elements'][$key]) ? $_POST['content_elements'][$key] : array();
		}

		return $this->save($data);
	}

}

if (!function_exists('dump_var')) {

	function dump_var($var, $exit = FALSE) {
		echo '<pre>';
		print_r($var);
		echo '</pre>';

		if ($exit)
			exit;
	}

}

// END Password_Ft class

/* End of file ft.text.php */
/* Location: ./system/expressionengine/third_party/content_elements/ft.content_elements.php */
