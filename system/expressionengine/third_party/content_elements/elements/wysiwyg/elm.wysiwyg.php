<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

/**
 * Content_elements Class
 *
 * @package		Content_elements
 * @author		KREA SK s.r.o.
 * @copyright	Copyright (c) 2012, KREA SK s.r.o.
 * @link		http://www.krea.com
 */
class Wysiwyg_element {

	var $info = array(
		'name' => 'Wysiwyg',
	);
	var $settings = array();
	var $cache = array();
	var $toolbar_options = array(
		array(
			"Undo",
			"Redo",
		),
		array(
			"Cut",
			"Copy",
			"Paste",
			"PasteText",
			"PasteFromWord",
		),
		array(
			"Styles",
			"Format",
			"Font",
			"FontSize",
		),
		array(
			"Bold",
			"Italic",
			"Underline",
			"Strike",
		),
		array(
			"TextColor",
			"BGColor",
		),
		array(
			"JustifyLeft",
			"JustifyCenter",
			"JustifyRight",
			"JustifyBlock",
		),
		array(
			"NumberedList",
			"BulletedList",
		),
		array(
			"Subscript",
			"Superscript",
		),
		array(
			"Link",
			"Unlink",
			"Anchor",
		),
		array(
			"Maximize",
			"ShowBlocks",
			"Source",
		),
	);

	//CONSTRUCT

	function Wysiwyg_element() {
		$this->EE = &get_instance();
	}

	/**
	 * Define Wysiwyg editor URL and skin.
	 * 
	 */
	function _detect_ck_editor_theme() {

		// default sources
		$ck_editor_url = rtrim(CE_THEME_URL, '/') . '/elements/wysiwyg/ckeditor/ckeditor.js';
		$ck_editor_theme = "kama";

		// test field_types
		$more_ck_editors = $this->EE->db->query("SELECT field_type FROM " . $this->EE->db->dbprefix . "channel_fields WHERE field_type IN ('wygwam','wyvern','expresso')")->result_array();

		foreach ($more_ck_editors as $ck_editor) {
			$editor = $ck_editor["field_type"];

			switch ($editor) {
				case 'wygwam';

					//Note: 2.0.3+ (but 2.4.0 required)

					$wygwam_theme_path = rtrim(URL_THIRD_THEMES, '/') . '/wygwam/lib/ckeditor/ckeditor.js';

					if (file_exists($wygwam_theme_path)) {
						$ck_editor_url = $wygwam_theme_path;
						$ck_editor_theme = "wygwam2";
					}
					break;

				case 'wyvern';

					//working... its compatible

					break;

				case 'expresso';
					$expresso_theme_path = rtrim(URL_THIRD_THEMES, '/') . '/expresso/ckeditor/ckeditor.js';

					if (file_exists($expresso_theme_path)) {
						$ck_editor_url = $expresso_theme_path;
						$ck_editor_theme = "expresso,../skins/expresso/";
					}
					break;
			}
		}

		$this->ck_editor_url = $ck_editor_url;
		$this->ck_editor_theme = $ck_editor_theme;
	}

	/**
	 * Display element element in CP [ELEMENT HTML (BACKEND)]
	 * 
	 * @param mixed Saved data
	 * @return string HTML
	 */
	function display_element($data) {
		//first time... load css & js

		$assets = '';

		if (!isset($this->cache['assets_loaded'])) {
			$this->_detect_ck_editor_theme();

			$theme_url = rtrim(CE_THEME_URL, '/') . '/elements/wysiwyg/';

			$this->EE->elements->_include_css($theme_url . 'styles.css', $assets);
			$this->EE->elements->_include_js($this->ck_editor_url, $assets);
			$this->EE->elements->_include_js($theme_url . 'publish.js', $assets);

			// all loaded, remember it!
			$this->cache['assets_loaded'] = TRUE;
		}

		if ($data === FALSE) {
			$wysiwyg_id = '__element_wysiwyg_index__';
		} else {
			$wysiwyg_id = 'wysiwyg_' . md5(time() . rand(1, 9999999));
		}

		// optimize settings
		if ((int) $this->settings["height"] == 0)
			$this->settings["height"] = 200;

		if ((int) $this->settings["height"] < 100)
			$this->settings["height"] = 100;

		if ((int) $this->settings["height"] > 1000)
			$this->settings["height"] = 1000;

		// toolbar
		$toolbar = '';

		foreach ($this->toolbar_options as $toolgroup_id => $toolgroup) {
			$toolbar_part = '';

			foreach ($toolgroup as $tool_id => $tool) {
				if (@$this->settings['tool_' . $tool]) {
					$toolbar_part .= "\"" . $tool . "\",";
				}
			}

			if ($toolbar_part) {
				$toolbar .= "[" . rtrim($toolbar_part, ",") . "],";
			}
		}

		$toolbar = "[" . rtrim($toolbar, ",") . "]";

		$vars = array(
			"field_name" => $this->field_name,
			"value" => $data,
			"settings" => $this->settings,
			"toolbar" => $toolbar,
			"wysiwyg_id" => $wysiwyg_id,
			"ck_editor_theme" => $this->ck_editor_theme,
		);

		return $assets . $this->EE->load->view($this->EE->elements->get_element_view_path('elements/wysiwyg/views/wysiwyg'), $vars, TRUE);
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
		$settings = array(
			array(
				lang('wysiwyg_height'),
				form_input('height', is_numeric(@$data['height']) ? $data['height'] : '200', 'style="width:100px"'),
			),
		);

		$options = '';
		foreach ($this->toolbar_options as $toolgroup_id => $toolgroup) {
			foreach ($toolgroup as $tool_id => $tool) {
				if (!isset($data['tool_' . $tool])) {
					if (empty($data)) {
						$checked = ' checked="checked" ';
					} else {
						$checked = '';
					}
				} else {
					if ($data['tool_' . $tool]) {
						$checked = ' checked="checked" ';
					} else {
						$checked = '';
					}
				}

				$id = "id_chck_" . rand(0, 9999999);

				$options .= '<label class="ce_checkbox" for="' . $id . '">';
				$options .= '<input id="' . $id . '" name="tool_' . $tool . '" type="checkbox" ' . $checked . ' value="1" /> ' . $tool;
				$options .= '</label><br />';
			}

			if (($toolgroup_id + 1) < count($this->toolbar_options)) {
				$options .= '<br />';
			}
		}

		$settings[] = array(
			lang('wysiwyg_tools'),
			$options,
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

		$tagdata = file_get_contents(rtrim(PATH_THIRD, '/') . '/' . $this->EE->elements->addon_name . '/elements/wysiwyg/views/preview.php');

		return $this->replace_element_tag($data, $params, $tagdata);
	}

}
