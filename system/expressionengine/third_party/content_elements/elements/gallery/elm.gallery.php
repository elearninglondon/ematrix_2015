<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

/**
 * Gallery_element Class
 *
 * @package		Content_elements
 * @author		KREA SK s.r.o.
 * @copyright	Copyright (c) 2012, KREA SK s.r.o.
 * @link		http://www.krea.com
 */
class Gallery_element {

	var $info = array(
		'name' => 'Gallery',
	);
	var $settings = array();
	var $cache = array();
	var $only_cp = TRUE;

	/**
	 * Construct
	 * 
	 * @return void
	 */
	function Gallery_element() {
		$this->EE = &get_instance();
	}

	/**
	 * Save element(BACKEND)
	 *
	 * @param array element data
	 * @return string html
	 */
	function save_element($data = NULL) {
		$save_data = array(
			"gallery_id" => $this->element_id,
			"gallery_data" => $data,
		);

		return serialize($save_data);
	}

	/**
	 * Display element element in CP [ELEMENT HTML (BACKEND)]
	 * 
	 * @param mixed Saved data
	 * @return string HTML
	 */
	function display_element($data) {

		if (REQ != 'CP')
			return '';
		$assets = '';

		// first time... load css & js
		if (!isset($this->cache['assets_loaded'])) {
			$theme_url = rtrim(CE_THEME_URL, '/') . '/elements/gallery/';

			//fileuploader

			if (version_compare(APP_VER, '2.2.0', '>=')) {
				//$this->EE->cp->add_to_head('<script type="text/javascript">var ce_add_file_trigger_version=2;</script>');
				$this->EE->elements->_insert_js('var ce_add_file_trigger_version=2;', $assets);
			} else {
				//$this->EE->cp->add_to_head('<script type="text/javascript">var ce_add_file_trigger_version=1;</script>');
				$this->EE->elements->_insert_js('var ce_add_file_trigger_version=1;', $assets);
			}

			//add to head
			//$this->EE->cp->add_to_head('<script type="text/javascript">var ce_msg_1="' . $this->EE->lang->line('gallery_error_not_image') . '";</script>');
			$this->EE->elements->_insert_js('var ce_msg_1="' . $this->EE->lang->line('gallery_error_not_image') . '";', $assets);

			//$this->EE->cp->add_to_head('<link rel="stylesheet" href="' . $theme_url . 'styles.css" type="text/css" media="screen" />');
			$this->EE->elements->_include_css($theme_url . 'styles.css', $assets);

			//add to foot
			//$this->EE->cp->add_to_foot('<script type="text/javascript" src="' . $theme_url . 'gallery.js"></script>');
			$this->EE->elements->_include_js($theme_url . 'gallery.js', $assets);

			//$this->EE->cp->add_to_foot('<script type="text/javascript" src="' . $theme_url . 'publish.js"></script>');
			$this->EE->elements->_include_js($theme_url . 'publish.js', $assets);

			//all loaded, remember it!
			$this->cache['assets_loaded'] = TRUE;
		}

		if (!$data) {
			$gallery_id = '__element_gallery_index__';
			$images = array();
		} else {
			if (!is_array(@unserialize($data))) {
				$data = $this->save_element($data);
			}

			$data = unserialize($data);

			$gallery_id = $data["gallery_id"];
			$images = array();

			if (isset($data["gallery_data"]["dir"]))
				foreach ($data["gallery_data"]["dir"] as $image_id => $dir_id)
					if ($data["gallery_data"]["dir"][$image_id]) {
						//load thumb

						if (version_compare(APP_VER, '2.2.0', '<')) {
							$upload_directory_info = $this->EE->db->query("SELECT * FROM " . $this->EE->db->dbprefix . "upload_prefs WHERE id='" . (int) $data["gallery_data"]["dir"][$image_id] . "'");
							$upload_directory_server_path = $upload_directory_info->row('server_path');
							$upload_directory_url = $upload_directory_info->row('url');

							if (file_exists($upload_directory_server_path . '_thumbs/thumb_' . $data["gallery_data"]["name"][$image_id])) {
								$thumb = $upload_directory_url . '_thumbs/thumb_' . $data["gallery_data"]["name"][$image_id];
							} else {
								$thumb = PATH_CP_GBL_IMG . 'default.png';
							}
						} else {
							$this->EE->load->library('filemanager');
							$thumb_info = $this->EE->filemanager->get_thumb($data["gallery_data"]["name"][$image_id], $data["gallery_data"]["dir"][$image_id]);
							$thumb = $thumb_info['thumb'];
						}

						$images[] = array(
							"dir" => $data["gallery_data"]["dir"][$image_id],
							"name" => $data["gallery_data"]["name"][$image_id],
							"caption" => $data["gallery_data"]["caption"][$image_id],
							"url" => !empty($data["gallery_data"]["url"]) ? $data["gallery_data"]["url"][$image_id] : '',
							"thumb" => $thumb,
						);
					}
		}

		$vars = array(
			"images" => $images,
			"field_name" => $this->field_name,
			"gallery_id" => $gallery_id,
			"max_photos" => $this->settings["max_photos"],
		);

		return $assets . $this->EE->load->view($this->EE->elements->get_element_view_path('elements/gallery/views/gallery'), $vars, TRUE);
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
		$upload_preferences = $this->get_file_upload_preferences();
                
		if (!is_array($data)) {
                    $data = @unserialize($data);
		} 
                
                if(!is_array($data)) {
                    return false;
                }
		if (!isset($data["gallery_id"])) {
			$tmp["gallery_id"] = "x";
                        $tmp["gallery_data"] = $data;
                        $data = $tmp;
		}

		$images["images"] = array();

		//available sizes

		$sizes = array();
		preg_match_all('%{image:(.*?)}%', $tagdata, $matches);
		if (isset($matches[0])) {
			foreach ($matches[0] as $match_index => $match) {
				$sizes[] = array(
					"pattern" => $matches[0][$match_index],
					"replacement" => $matches[1][$match_index],
				);
			}
		}

		//each

		if (isset($data["gallery_data"]["dir"]))
			foreach ($data["gallery_data"]["dir"] as $image_id => $dir_id)
				if ($data["gallery_data"]["dir"][$image_id]) {
					$cell["name"] = $data["gallery_data"]["name"][$image_id];

					//get file_ext

					$ext_parts = (explode(".", $cell["name"]));
					$ext = (count($ext_parts) > 1) ? end($ext_parts) : '';

					$cell["extension"] = str_replace('jpeg', 'jpg', strtolower($ext));

					//fetch preferences				

					if (isset($upload_preferences[$data["gallery_data"]["dir"][$image_id]])) {
						$cell["dir"] = $upload_preferences[$data["gallery_data"]["dir"][$image_id]]["url"];
						$cell["server_path"] = $upload_preferences[$data["gallery_data"]["dir"][$image_id]]["server_path"];
						$cell["image"] = $cell["dir"] . $cell["name"];

						//get file size

						if (strpos($tagdata, '{size}') !== FALSE) {

							$cell["size"] = filesize($upload_preferences[$data["gallery_data"]["dir"][$image_id]]["server_path"] . $cell["name"]);

							if ($cell["size"] > 1024 * 1024 * 1024) {
								$cell["size"] = round($cell["size"] / (1024 * 1024 * 1024), 2) . 'GB';
							}
							if ($cell["size"] > 1024 * 1024) {
								$cell["size"] = round($cell["size"] / (1024 * 1024), 2) . 'MB';
							}
							if ($cell["size"] > 1024) {
								$cell["size"] = round($cell["size"] / 1024, 2) . 'kB';
							} else {
								$cell["size"] = $cell["size"] . 'B';
							}
						} else {
							$cell["size"] = "0B";
						}
					} else {
						$cell["dir"] = "";
						$cell["server_path"] = "";
						$cell["image"] = "";
						$cell["size"] = "0B";
					}

					$cell["caption"] = @$data["gallery_data"]["caption"][$image_id];
					$cell["url"] = @$data["gallery_data"]["url"][$image_id];

					//support for multisizes

					foreach ($sizes as $size) {
						$cell[trim($size["pattern"], '{ }')] = $cell["dir"] . "_" . $size["replacement"] . "/" . $cell["name"];
					}

					//thumb

					if (version_compare(APP_VER, '2.2.0', '<')) {
						if (file_exists($cell["server_path"] . '_thumbs/thumb_' . $cell["name"])) {
							$thumb = $cell["dir"] . '_thumbs/thumb_' . $cell["name"];
						} else {
							$thumb = PATH_CP_GBL_IMG . 'default.png';
						}
					} else {
						$this->EE->load->library('filemanager');
						$thumb_info = $this->EE->filemanager->get_thumb($cell["name"], $data["gallery_data"]["dir"][$image_id]);
						$thumb = $thumb_info['thumb'];
					}


					$cell["thumb"] = $thumb;

					//append

					$images["images"][] = $cell;
				}

		$images["element_name"] = $this->element_name;

		return $this->EE->elements->parse_variables($tagdata, array($images));
	}

	/**
	 * Display setting options, when element is created [DISPLAY SETTINGS]
	 * 
	 * @return mixed Saved params
	 */
	function display_element_settings($data) {
		$max_photos = array(1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 10 => 10, 15 => 15, 20 => 20, 25 => 25, 50 => 50, 100 => 100, 500 => 500, 1000 => 1000);

		if (!isset($data["max_photos"])) {
			$data["max_photos"] = 10;
		}

		$settings = array(
			array(
				lang('gallery_max_photos'),
				form_dropdown('max_photos', $max_photos, $data["max_photos"]),
			),
		);

		return $settings;
	}

	/**
	 * Get Upload Preferences
	 *
	 * @param	integer Preference ID
	 * @return	array
	 */
	function get_file_upload_preferences() {
		global $ce_upload_preferences;

		if (isset($ce_upload_preferences)) {
			return $ce_upload_preferences;
		}

		$ce_upload_preferences = array_filter((array) $this->EE->config->item('upload_preferences'));
		if (!empty($ce_upload_preferences)) {
			return $ce_upload_preferences;
		}

		$ce_upload_preferences = array();

		$this->EE->db->from('upload_prefs');
		$this->EE->db->order_by('name');

		foreach ($this->EE->db->get()->result_array() as $row) {
			$ce_upload_preferences[$row["id"]] = $row;
		}

		return $ce_upload_preferences;
	}

	/**
	 * Preview after publish
	 *
	 * @param array element data
	 * @return string html
	 */
	function preview_element($data) {
		$params = array();

		$tagdata = file_get_contents(rtrim(PATH_THIRD, '/') . '/' . $this->EE->elements->addon_name . '/elements/gallery/views/preview.php');

		return $this->replace_element_tag($data, $params, $tagdata);
	}

}
