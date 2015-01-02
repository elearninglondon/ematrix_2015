<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

/**
 * Table_element Class
 *
 * @package		Content_elements
 * @author		KREA SK s.r.o.
 * @copyright	Copyright (c) 2012, KREA SK s.r.o.
 * @link		http://www.krea.com
 */
class Table_element {

	var $info = array(
		'name' => 'Table',
	);
	var $settings = array();
	var $cache = array();

	//CONSTRUCT	

	function Table_element() {
		$this->EE = &get_instance();
	}

	/**
	 * Get tile html: ELEMENT HTML (BACKEND)
	 *
	 * @param int field_id
	 * @param mixed element value
	 * @param array settings
	 * @param array all saved data together
	 * @return string html
	 */
	function display_element($data) { 
		$assets = '';
		
		// first time... load css & js
		if (!isset($this->cache['assets_loaded'])) {
			$theme_url = rtrim(CE_THEME_URL, '/') . '/elements/table/';

			/*
			  $this->EE->cp->add_to_head('<link rel="stylesheet" href="' . $theme_url . 'styles.css" type="text/css" media="screen" />');
			  $this->EE->cp->add_to_foot('<script type="text/javascript" src="' . $theme_url . 'table_operations.js"></script>');
			  $this->EE->cp->add_to_foot('<script type="text/javascript" src="' . $theme_url . 'publish.js"></script>');
			 */

			$this->EE->elements->_include_css($theme_url . 'styles.css', $assets);
			$this->EE->elements->_include_js($theme_url . 'table_operations.js', $assets);
			$this->EE->elements->_include_js($theme_url . 'publish.js', $assets);

			// all loaded, remember it!

			$this->cache['assets_loaded'] = TRUE;
		}

		if (!$data) {
			$table_id = '__element_table_index__';

			$cols = (int) $this->settings["cols"];
			$rows = (int) $this->settings["rows"];
			$header = (int) $this->settings["header"];

			$cell = array();
			for ($i = 1; $i <= ($rows * $cols); $i++) {
				$cell[] = "";
			}

			$set_focus = true;
		} else {
			if (!is_array(@unserialize($data))) {
				$data = $this->save_element($data);
			}

			$data = unserialize($data);

			$table_id = $data["table_id"];
			$cell = $data["table_data"]["cell"];
			$cols = $data["table_data"]["cols"];
			$rows = $data["table_data"]["rows"];
			$header = (int) $this->settings["header"];
			$set_focus = false;
		}

		$vars = array(
			"table_id" => $table_id,
			"cell" => $cell,
			"cols" => $cols,
			"rows" => $rows,
			"header" => $header,
			"field_name" => $this->field_name,
		);

		return $assets.$this->EE->load->view($this->EE->elements->get_element_view_path('elements/table/views/table'), $vars, TRUE);
	}

	/**
	 * Parse Template (FRONTEND)
	 *
	 * @param string html
	 * @param mixed element data
	 * @param array fetched params
	 * @param array element settings
	 * @return string html
	 */
	function replace_element_tag($data, $params = array(), $tagdata) {
		if (!is_array(@unserialize($data))) {
			return false;
		} else {
			$data = unserialize($data);
		}

		$rows = $data["table_data"]["rows"];
		$cols = $data["table_data"]["cols"];
		$cell = $data["table_data"]["cell"];

		$header = (int) $this->settings["header"];
		if ($header)
			$rows = $rows + 1;

		//create pattern

		$table_pattern = array(
			"rows" => array(),
			"thead" => array(),
			"tbody" => array(),
		);

		//rows

		$cell_index = 0;
		for ($i = 0; $i < $rows; $i++) {
			$table_pattern["rows"][$i] = array(
				"cells" => array(),
			);

			for ($j = 0; $j < $cols; $j++) {
				$table_pattern["rows"][$i]["cells"][$j]["value"] = !empty($cell[$cell_index]) ? $cell[$cell_index] : '';
				$cell_index++;


				//** -------------------------------
				//** replace EE entities
				//** -------------------------------	

				$table_pattern["rows"][$i]["cells"][$j]["value"] = preg_replace("/{([_a-zA-Z]*)}/u", "&#123;$1&#125;", $table_pattern["rows"][$i]["cells"][$j]["value"]);
			}
		}

		//thead & tbody

		foreach ($table_pattern["rows"] as $k => $row) {
			if ($k == 0 && $header) {
				$table_pattern["thead"][] = $row;
			} else {
				$table_pattern["tbody"][] = $row;
			}
		}

		$table_pattern["element_name"] = $this->element_name;

		return $this->EE->elements->parse_variables($tagdata, array($table_pattern));
	}

	/**
	 * Save element(BACKEND)
	 *
	 * @param array element data
	 * @return string html
	 */
	function save_element($data) {
		$save_data = array(
			"table_id" => $this->element_id,
			"table_data" => $data
		);

		return serialize($save_data);
	}

	/**
	 * Display settings (BACKEND)
	 *
	 * @param array element settings
	 * @return string html
	 */
	function display_element_settings($data) {
		$row_options = array(
			1 => 1,
			2 => 2,
			3 => 3,
			4 => 4,
			5 => 5,
			10 => 10,
			15 => 15,
		);
		$col_options = array(
			1 => 1,
			2 => 2,
			3 => 3,
			4 => 4,
			5 => 5,
		);

		$settings = array(
			array(
				lang('table_header'),
				form_dropdown('header', array(1 => $this->EE->lang->line('yes'), 0 => $this->EE->lang->line('no')), @$data['header'], 'style="width:50px"'),
			),
			array(
				lang('table_init_rows'),
				form_dropdown('rows', $row_options, is_numeric(@$data['rows']) ? $data['rows'] : '3', 'style="width:50px"'),
			),
			array(
				lang('table_init_cols'),
				form_dropdown('cols', $col_options, is_numeric(@$data['cols']) ? $data['cols'] : '3', 'style="width:50px"'),
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

		$tagdata = file_get_contents(rtrim(PATH_THIRD, '/') . '/' . $this->EE->elements->addon_name . '/elements/table/views/preview.php');

		return $this->replace_element_tag($data, $params, $tagdata);
	}

}
