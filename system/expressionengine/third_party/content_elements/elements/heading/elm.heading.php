<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

/**
 * Heading_element Class
 *
 * @package		Content_elements
 * @author		KREA SK s.r.o.
 * @copyright	Copyright (c) 2012, KREA SK s.r.o.
 * @link		http://www.krea.com
 */
class Heading_element {

	var $info = array(
		'name' => 'Heading',
	);
	var $settings = array();
	var $cache = array();

	/**
	 * Constructor
	 *
	 * @return void
	 */
	function Heading_element() {
		$this->EE = &get_instance();
		$this->EE->elements->define_theme_url();
	}

	/**
	 * Save element(BACKEND)
	 *
	 * @param array element data
	 * @return string html
	 */
	function save_element($data = NULL) {

		$save_data = array(
			"heading_id" => $this->element_id,
			"heading_data" => $data
		);
		
		return serialize($save_data);
	}

	/**
	 * Display element element in CP [ELEMENT HTML (BACKEND)]
	 * 
	 * @param integer Field ID
	 * @param mixed Saved data
	 * @param array Settings/Config 
	 * @param array All saved data   
	 * @return string HTML
	 */
	function display_element($data) {
		
		$assets = '';
		
		if (!isset($this->cache['assets_loaded'])) {
			
			//theme
			$theme_url = rtrim(CE_THEME_URL, '/') . '/elements/heading/';

			$this->EE->elements->_include_css($theme_url . 'styles.css', $assets);
			$this->EE->elements->_include_js($theme_url . 'publish.js', $assets);

			//all loaded, remember it!			
			$this->cache['assets_loaded'] = TRUE;
		}

		if (!$data) {
			$heading_id = '__element_heading_index__';
			$heading = '';
			$content = '';
		} else {
			if (!is_array(@unserialize($data))) {
				$data = $this->save_element($data);
			}

			$data = unserialize($data);

			$heading_id = $data["heading_id"];
			$heading = $data["heading_data"]["heading"];
			$content = $data["heading_data"]["content"];
		}

		$headings = array();

		for ($i = 1; $i <= 6; $i++) {
			if (@$this->settings['h' . $i])
				$headings['h' . $i] = $this->EE->lang->line('heading_h' . $i);
		}

		$vars = array(
			"field_name" => $this->field_name,
			"heading_id" => $heading_id,
			"heading" => $heading,
			"content" => $content,
			"headings" => $headings,
		);

		return $assets.$this->EE->load->view($this->EE->elements->get_element_view_path('elements/heading/views/heading'), $vars, TRUE);
	}

	/**
	 * Display element element in FrontEnd [PARSE TEMPLATE (FRONTEND)	]
	 * 
	 * @param mixed Saved data
	 * @param array Params 
	 * @param string Tagdata 
	 * @return string Template
	 */
	function replace_element_tag($data, $params = array(), $tagdata) {

		if (!is_array(@unserialize($data))) {
			return false;
		} else {
			$data = unserialize($data);
		}

		$heading_id = $data["heading_id"];
		$heading = $data["heading_data"]["heading"];
		$content = $data["heading_data"]["content"];

		//** -------------------------------
		//** {char_limit}
		//** -------------------------------		

		if ((int) @$params['char_limit']) {
			if (mb_strlen($content, 'UTF-8') > (int) $params['char_limit']) {
				$content = trim(mb_strcut($content, 0, (int) @$params['char_limit'], 'UTF-8'), '. ') . '...';
			}
		}

		$value = $content;

		//Wrap with <h1>, <h2>, ...

		if ($heading) {
			$value = '<' . $heading . '>' . htmlspecialchars($content) . '</' . $heading . '>';
		}

		//** -------------------------------
		//** replace EE entities
		//** -------------------------------	

		$value = preg_replace("/{([_a-zA-Z]*)}/u", "&#123;$1&#125;", $value);
		$content = preg_replace("/{([_a-zA-Z]*)}/u", "&#123;$1&#125;", $content);

		return $this->EE->elements->parse_variables($tagdata, array(array(
						"value" => $value,
						"content" => $content,
						"heading" => $heading,
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
		$options = '';

		for ($i = 1; $i <= 6; $i++) {
			//default value (fisttime, all checked)

			if (empty($data)) {
				$checked = 1;
			} else {
				$checked = 0;
			}

			//standard: load if setting exists

			if (isset($data["h" . $i]) && $data["h" . $i]) {
				$checked = $data["h" . $i];
			}

			if ($checked) {
				$checked_string = ' checked="checked" ';
			} else {
				$checked_string = '';
			}

			$id = "hd_chck_" . rand(0, 9999999);

			$onclick = "if ($(this).parent().parent().parent().find('input.chck_on:checked').size() == 0) { $(this).attr('checked','checked'); $(this).parent().find('input[type=text]').val(1); }";

			$options .= '<label class="ce_checkbox" for="' . $id . '">';
			$options .= '<input id="' . $id . '" name="h' . $i . '" class="chck_on" type="checkbox" onclick="' . $onclick . '" ' . $checked_string . ' value="1" /> ' . lang('heading_h' . $i);
			$options .= '</label><br />';
		}

		$settings = array(
			array(
				lang('heading_options'),
				$options,
			)
		);

		return $settings;
	}

	/**
	 * Preview after publish
	 *
	 * @param array element data
	 * @return string html
	 */
	function preview_element($data) {
		$params = array();

		$tagdata = file_get_contents(rtrim(PATH_THIRD, '/') . '/' . $this->EE->elements->addon_name . '/elements/heading/views/preview.php');

		return $this->replace_element_tag($data, $params, $tagdata);
	}

}
