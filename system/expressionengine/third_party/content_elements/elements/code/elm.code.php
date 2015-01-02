<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

/**
 * Code_element Class
 *
 * @package		Content_elements
 * @author		KREA SK s.r.o.
 * @copyright	Copyright (c) 2012, KREA SK s.r.o.
 * @link		http://www.krea.com
 */
class Code_element {

	var $info = array(
		'name' => 'Code',
	);
	var $settings = array();
	var $cache = array();

	/**
	 * Constructor
	 *
	 * @return void
	 */
	function Code_element() {
		$this->EE = &get_instance();
	}

	/**
	 * Get tile html: ELEMENT HTML (BACKEND)
	 *
	 * @param mixed element value
	 * @return string html
	 */
	function display_element($data) {
		// first time... load css & js
		$assets = '';
		
		if (!isset($this->cache['assets_loaded'])) {
			$theme_url = rtrim(CE_THEME_URL, '/') . '/elements/code/';

			$this->EE->elements->_include_css($theme_url . 'jquery-linedtextarea.css', $assets);
			$this->EE->elements->_include_js($theme_url . 'jquery-linedtextarea.js', $assets);
			$this->EE->elements->_include_js($theme_url . 'publish.js', $assets);

			// all loaded, remember it!
			$this->cache['assets_loaded'] = TRUE;
		}

		if ((int) $this->settings["rows"] < 2)
			$this->settings["rows"] = 2;
		
		if ((int) $this->settings["rows"] > 20)
			$this->settings["rows"] = 20;

		$vars = array(
			"value" => $data,
			"field_name" => $this->field_name,
			"settings" => $this->settings,
		);

		return $assets.$this->EE->load->view($this->EE->elements->get_element_view_path('elements/code/views/code'), $vars, TRUE);
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
		$raw_data = $data;
		// {char_limit}
		if ((int) @$params['char_limit']) {
			if (mb_strlen($data, 'UTF-8') > (int) $params['char_limit']) {
				$data = trim(mb_strcut($data, 0, (int) @$params['char_limit'], 'UTF-8'), '. ') . '...';
			}
		}

		// Settings: content type
		if (@$this->settings["content_format"] == "pre") {
			$data = "<pre>" . htmlspecialchars($data) . "</pre>";
		} else {
			$data = $this->EE->typography->parse_type(
					$data, array(
				'text_format' => $this->settings["content_format"],
				'html_format' => !empty($this->row['channel_html_formatting']) ? $this->row['channel_html_formatting'] : 'safe',
				'auto_links' => (@$this->row['channel_auto_link_urls'] == 'y') ? 'y' : 'n',
				'allow_img_url' => (@$this->row['channel_allow_img_urls'] == 'y') ? 'y' : 'n'
					)
			);
		}

		// Replace EE entities
		$data = str_replace("{", "&#123;", $data);
		$data = str_replace("}", "&#125;", $data);

		return $this->EE->elements->parse_variables($tagdata, array(array(
						"value" => $data,
						"raw" => $raw_data,
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
			'pre' => $this->EE->lang->line('code_input_pre'),
			'br' => $this->EE->lang->line('code_input_br'),
			'none' => $this->EE->lang->line('code_input_none'),
		);

		$settings = array(
			array(
				lang('code_rows'),
				form_input('rows', is_numeric(@$data['rows']) ? $data['rows'] : '12', 'style="width:100px"'),
			),
			array(
				lang('code_input_content_format'),
				form_dropdown('content_format', $content_formats, @$data['content_format']),
			),
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

		$tagdata = file_get_contents(rtrim(PATH_THIRD, '/') . '/' . $this->EE->elements->addon_name .'/elements/code/views/preview.php');

		return $this->replace_element_tag($data, $params, $tagdata);
	}

}
