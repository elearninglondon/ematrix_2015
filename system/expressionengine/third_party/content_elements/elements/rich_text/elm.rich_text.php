<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

/**
 * Rich_text_element Class
 *
 * @package		Content_elements
 * @author		KREA SK s.r.o.
 * @copyright	Copyright (c) 2012, KREA SK s.r.o.
 * @link		http://www.krea.com
 */
class Rich_text_element {

	var $info = array(
		'name' => 'Rich_text',
	);
	var $settings = array();
	var $cache = array();
	var $ee_requirements = '2.5.0';

	/**
	 * Constructor
	 *
	 * @return void
	 */
	function Rich_text_element() {
		$this->EE = &get_instance();

		// Translate name
		$this->info["name"] = $this->EE->lang->line('rich_text_element_name');
	}

	/**
	 * Load js: You have chance embed js settings into header of footer once
	 *
	 * @return void
	 */
	function fetch_assets($field_id) {
		$theme_url = rtrim(CE_THEME_URL, '/') . '/elements/rich_text/';

		if (!isset($this->js_loaded)) {
			$this->EE->cp->add_to_head('<link rel="stylesheet" href="' . $theme_url . 'styles.css" type="text/css" media="screen" />');
			$this->js_loaded = true;
		}
	}

	/**
	 * Get tile html: ELEMENT HTML (BACKEND)
	 *
	 * @param mixed element value
	 * @return string html
	 */
	function display_element($data) {

		/*if(REQ != 'CP')
			return 'Element not allowed outside Control Panel!';*/
		
		$assets = '';
		
		// First time... load css & js
		$this->_load_rte_libraries();

		$this->settings['field_ta_rows'] = $this->settings['rows'];
		$this->settings['field_text_direction'] = 'lft';
		$this->settings['field_fmt'] = '';

		if (!isset($this->cache['assets_loaded'])) {
			$theme_url = rtrim(CE_THEME_URL, '/') . '/elements/rich_text/';
			
			$this->EE->elements->_include_css($theme_url . 'styles.css', $assets);
			
			if(REQ == 'CP')
				$this->EE->elements->_include_js($theme_url . 'publish.js', $assets);

			// If called inside Low variables			
			//if (!empty($this->var_id)) {
						
			if (!empty($this->var_id)) {
				$this->EE->load->add_package_path(PATH_MOD . 'rte/');
				$this->EE->elements->_insert_js($this->EE->rte_lib->build_js($_POST ? -1 : 0, '.ce_rich_text', NULL, TRUE), $assets);
			}
			else if (version_compare(APP_VER, '2.7.0', '>=')) {
				$this->EE->load->add_package_path(PATH_MOD . 'rte/');
				
				$this->EE->elements->_insert_js(
					'var ce_rich_text_init_270 = function(){'.
					$this->EE->rte_lib->build_js(0, '.ce_rich_text', NULL, TRUE).'}',
					$assets
				);
			}

			// All loaded, remember it!			
			$this->cache['assets_loaded'] = TRUE;
		}

		if ((int) $this->settings["rows"] < 2)
			$this->settings["rows"] = 2;

		if ((int) $this->settings["rows"] > 20)
			$this->settings["rows"] = 20;

		$vars = array(
			"name" => $this->field_name,
			"value" => $data,
			"settings" => $this->settings
		);

		return $assets.$this->EE->load->view($this->EE->elements->get_element_view_path('elements/rich_text/views/rich_text'), $vars, TRUE);
	}

	/**
	 * Parse Template (FRONTEND)
	 *
	 * @param data mixed
	 * @param params tag params
	 * @param taggdata html markup
	 * @return string html
	 */
	function replace_element_tag($data, $params = array(), $tagdata) {
		//** -------------------------------
		//** {strip_tags}
		//** -------------------------------		 
		if (@$params['strip_tags'] == "yes") {
			$data = strip_tags($data);

			//** -------------------------------
			//** {char_limit}
			//** -------------------------------	

			if ((int) @$params['char_limit']) {
				if (mb_strlen($data, 'UTF-8') > (int) $params['char_limit']) {
					$data = trim(mb_strcut($data, 0, (int) @$params['char_limit'], 'UTF-8'), '. ') . '...';
				}
			}
		}

		//** -------------------------------
		//** replace EE entities
		//** -------------------------------	

		$data = preg_replace("/{([_a-zA-Z]*)}/u", "&#123;$1&#125;", $data);

		return $this->EE->elements->parse_variables($tagdata, array(array(
						"value" => $data,
						"element_name" => $this->element_name,
						)));
	}

	/**
	 * Display settings (BACKEND)
	 *
	 * @param array element settings
	 * @return string html
	 */
	function display_element_settings($data) {
		$content_formats = array(
			'br' => $this->EE->lang->line('rich_text_input_br'),
			'none' => $this->EE->lang->line('rich_text_input_none'),
			'xhtml' => $this->EE->lang->line('rich_text_input_xhtml'),
		);

		$settings = array(
			array(
				lang('rich_text_rows'),
				form_input('rows', is_numeric(@$data['rows']) ? $data['rows'] : '6', 'style="width:100px"'),
			),
		);

		return $settings;
	}

	/**
	 * Preview after publish
	 *
	 * @param mixed element data
	 * @return string html
	 */
	function preview_element($data) {
		$params = array();

		$tagdata = file_get_contents(rtrim(PATH_THIRD, '/') . '/' . $this->EE->elements->addon_name . '/elements/rich_text/views/preview.php');

		return $this->replace_element_tag($data, $params, $tagdata);
	}

	/**
	 * Load Rich text libraries. //rte_lib
	 */
	private function _load_rte_libraries() {

		if (!empty($this->EE->rte_lib))
			return FALSE;

		$path = PATH_MOD . 'rte/libraries/Rte_lib.php';

		if (!is_file($path))
			return FALSE;

		require_once $path;

		$this->EE->rte_lib = new Rte_lib();
	}

}
