<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

/**
 * Text_field_element Class
 *
 * @package		Content_elements
 * @author		KREA SK s.r.o.
 * @copyright	Copyright (c) 2012, KREA SK s.r.o.
 * @link		http://www.krea.com
 */
class Text_field_element {

	var $info = array(
		'name' => 'Text Field',
	);
	var $settings = array();
	var $cache = array();

	/**
	 * Constructor
	 *
	 * @return void
	 */
	function Text_field_element() {
		$this->EE = &get_instance();

		//translate name

		$this->info["name"] = $this->EE->lang->line('text_field_element_name');
	}

	/**
	 * Get tile html: ELEMENT HTML (BACKEND)
	 *
	 * @param mixed element value
	 * @return string html
	 */
	function display_element($data) {

		$assets = '';

		// first time... load css & js
		if (!isset($this->cache['assets_loaded'])) {
			$theme_url = rtrim(CE_THEME_URL, '/') . '/elements/text_field/';

			$this->EE->elements->_include_css($theme_url . 'styles.css', $assets);
			$this->EE->elements->_include_js($theme_url . 'publish.js', $assets);

			// all loaded, remember it!
			$this->cache['assets_loaded'] = TRUE;
		}

		//show field

		$value = $data;

		if ($this->settings['content_type'] == 'number')
			$value = (float) $value;
		if ($this->settings['content_type'] == 'integer')
			$value = (int) $value;

		$vars = array(
			"name" => $this->field_name,
			"value" => $value,
		);

		return $assets . $this->EE->load->view($this->EE->elements->get_element_view_path('elements/text_field/views/text_field'), $vars, TRUE);
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

		// {char_limit}
		if ((int) @$params['char_limit']) {
			if (mb_strlen($data, 'UTF-8') > (int) $params['char_limit']) {
				$data = trim(mb_strcut($data, 0, (int) @$params['char_limit'], 'UTF-8'), '. ') . '...';
			}
		}

		// Settings: content type
		switch ($this->settings["content_type"]) {
			case 'number':
				$data = (float) $data;
				break;
			case 'integer':
				$data = (int) $data;
				break;
		}

		// Settings: content type
		if (!defined("BASE")) {//dump_var($this->row, 1);
			$data = $this->EE->typography->parse_type($data, array(
				'text_format' => $this->settings["content_format"],
				'html_format' => !empty($this->row['channel_html_formatting']) ? $this->row['channel_html_formatting'] : 'safe',
				'auto_links' => (@$this->row['channel_auto_link_urls'] == 'y') ? 'y' : 'n',
				'allow_img_url' => (@$this->row['channel_allow_img_urls'] == 'y') ? 'y' : 'n'
					)
			);
		}

		// Replace EE entities
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
		$content_types = array(
			'' => $this->EE->lang->line('text_field_all'),
			'number' => $this->EE->lang->line('text_field_number'),
			'integer' => $this->EE->lang->line('text_field_integer'),
		);

		$content_formats = array(
			'none' => $this->EE->lang->line('text_field_none'),
			'xhtml' => $this->EE->lang->line('text_field_xhtml'),
		);

		$settings = array(
			array(
				lang('text_field_content_type'),
				form_dropdown('content_type', $content_types, @$data["content_type"]),
			),
			array(
				lang('text_field_content_format'),
				form_dropdown('content_format', $content_formats, @$data["content_format"]),
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

		$tagdata = file_get_contents(rtrim(PATH_THIRD, '/') . '/' . $this->EE->elements->addon_name . '/elements/text_field/views/preview.php');

		return $this->replace_element_tag($data, $params, $tagdata);
	}

}
