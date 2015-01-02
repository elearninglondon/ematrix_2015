<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

/**
 * Elements Class - by KREA SK s.r.o.
 *
 * @package		Content_elements
 * @author		KREA SK s.r.o.
 * @copyright	Copyright (c) 2012, KREA SK s.r.o.
 * @link		http://www.krea.com/docs/content-elements
 */
if (!class_exists('Elements')) {

	class Elements {

		public $addon_name = 'content_elements';
		static $cache = array(
			"includes" => array()
		);

		/**
		 * Constructor
		 *
		 * @return void
		 */
		function Elements() {
			$this->EE = &get_instance();
		}

		/**
		 * Constructor
		 *
		 * @return void
		 */
		function fetch_avaiable_elements($vars2export = array()) {
			if (isset($this->avaiable_elements)) {
				return $this->avaiable_elements;
			}

			$this->avaiable_elements = array();

			//list of all installed elements...

			$inst_elms = $this->EE->db->select("fieldtype_id, name, version, settings")
					->from($this->EE->db->dbprefix . "fieldtypes")
					->order_by("fieldtype_id")
					->get()
					->result();

			//** --------------------------------------------
			//** LOAD INCLUDED ELEMENTS
			//** --------------------------------------------
			foreach (glob(PATH_THIRD . 'content_elements/elements/*') as $element_dir) {
				//loop only direcotries

				if (!is_dir($element_dir))
					continue;

				//get element name

				preg_match('%elements/([^/]*)$%', $element_dir, $matches);

				if (isset($matches[1])) {
					$element_name = $matches[1];
				} else {
					continue;
				}

				$element_class = ucfirst($element_name) . '_element';

				if (!class_exists($element_class)) {
					$this->load_langfile($element_name);
					require(PATH_THIRD . 'content_elements/elements/' . $element_name . '/elm.' . $element_name . '.php');
				}

				$incl_elm = new $element_class;

				$this->{$element_name} = new stdClass;
				$this->{$element_name}->settings = $incl_elm->settings;
				$this->{$element_name}->handler = $incl_elm;
				$this->{$element_name}->name = $incl_elm->info["name"];

				if (isset($incl_elm->ee_requirements)) {
					if (version_compare(APP_VER, $incl_elm->ee_requirements, '<')) {
						continue;
					}
				}

				$this->avaiable_elements[] = $element_name;
			}

			//** --------------------------------------------
			//** LOAD INSTALLED ELEMENTS
			//** --------------------------------------------
			foreach ($inst_elms as $inst_elm) {
				$this->EE->load->library('api');
				$this->EE->load->library('api/api_channel_fields');

				$inst_elm_handler = $this->EE->api_channel_fields->include_handler($inst_elm->name);

				if (method_exists($inst_elm_handler, 'display_element')) {
					$inst_elm_handler = new $inst_elm_handler;

					//fetch infomations about available elements

					$this->{$inst_elm->name} = new stdClass;
					$this->{$inst_elm->name}->settings = unserialize(base64_decode($inst_elm->settings));
					$this->{$inst_elm->name}->handler = $inst_elm_handler;
					$this->{$inst_elm->name}->name = $inst_elm_handler->info["name"];

					$this->avaiable_elements[] = $inst_elm->name;
				}
			}

			foreach ($this->avaiable_elements as $element_name) {
				foreach ((array) $vars2export as $var2export_k => $var2export_v) {
					$this->{$element_name}->handler->{$var2export_k} = $var2export_v;
				}
			}

			return $this->avaiable_elements;
		}

		/**
		 * Parse variables (easier template generator)
		 *
		 * @return void
		 */
		function parse_variables($_tagdata, $vars) { //dump_var($vars, 1);
			$output = ''; //output	
			$count = 0; //counter
			foreach ($vars as $list) {
				$count++;

				$tagdata = $_tagdata;

				/** ----------------------------------------
				  /**  parse {switch} variable
				  /** ---------------------------------------- */
				if (preg_match('#{(switch(.*?))}#s', $tagdata, $_match) == TRUE) {
					$sparam = $this->EE->functions->assign_parameters($_match[1]);

					$sw = '';

					if (isset($sparam['switch'])) {
						$sopt = @explode("|", $sparam['switch']);

						$sw = $sopt[($count + count($sopt) - 1) % count($sopt)];
					}

					$tagdata = $this->EE->TMPL->swap_var_single($_match[1], $sw, $tagdata);
				}

				/** ----------------------------------------
				  /**  Others tag
				  /** ---------------------------------------- */
				if (is_array($list))
					foreach ($list as $tag => $value) {
						//if array ...

						if (is_array($value)) {
							preg_match_all('~{' . $tag . '([^}]*?)}(.*?){/' . $tag . '}~s', $tagdata, $matches);

							foreach ($matches[0] as $i => $match) {
								//fetch params for variable_pairs tag

								$paramsString = str_replace('"', "'", $matches[1][$i]);

								$params = array();
								preg_match_all("/([^']*?)='([^']*?)'/", $paramsString, $paramsMatches);

								if (isset($paramsMatches[0]))
									foreach ($paramsMatches[0] as $pm_index => $pm) {
										if (trim($paramsMatches[1][$pm_index])) {
											$params[trim($paramsMatches[1][$pm_index])] = trim($paramsMatches[2][$pm_index]);
										}
									}

								//offset (rebuild array)

								if ((int) @$params["offset"]) {
									//rebuild array

									$new_value = array();
									$skipped = 0;

									foreach ($value as $k => $v) {
										$skipped++;

										if ($skipped >= (int) @$params["offset"]) {
											$new_value[] = $v;
										}
									}

									$value = $new_value;
								}

								//limit (rebuild array)

								if ((int) @$params["limit"]) {
									//rebuild array

									$new_value = array();
									$printed = 0;

									foreach ($value as $k => $v) {
										$printed++;

										if ($printed <= (int) @$params["limit"]) {
											$new_value[] = $v;
										}
									}

									$value = $new_value;
								}

								//recursive call	...		
								$pattern = $this->parse_variables($matches[2][$i], $value);

								//... apply recursive data
								$tagdata = str_replace($matches[0][$i], $pattern, $tagdata);
							}
						}

						//... or single variable
						else {
							$tagdata = str_replace('{' . $tag . '}', $value, $tagdata);
						}
					}

				//count
				$tagdata = str_replace('{count}', $count, $tagdata);
				$tagdata = str_replace('{cnt}', $count, $tagdata);
				$tagdata = str_replace('{total_count}', count($vars), $tagdata);

				//conds

				$conds = array();

				if (is_array($list))
					foreach ($list as $tag => $value) {
						if (is_array($value)) {
							$conds[$tag] = count($value) ? 1 : 0;
						} else {
							$conds[$tag] = ($value) ? $value : 0;
						}
						$conds['count'] = $count;
						$conds['cnt'] = $count;
						$conds['total_count'] = count($vars);
						$conds['first'] = (int) $count === 1 ? 1 : 0;
						$conds['last'] = (int) $count === (int) count($vars) ? 1 : 0;
						$conds['odd'] = ((int) $count % 2 == 0) ? 1 : 0;
					}

				$output .= $this->EE->functions->prep_conditionals($tagdata, $conds);
			}

			//cleanup

			/*
			  $output = preg_replace('~<ul([^>])*'.'>\s*</ul>~s', '', $output);
			 */
			return $output;
		}

		/**
		 * Load ELEMENT language file
		 *
		 * @param string element type
		 * @return void
		 */
		function load_langfile($element_name) {
			//allready loaded

			if (isset($this->EE->lang->language[$element_name . '_element_name'])) {
				return '';
			}

			//load from directories

			$element_lang_custom = PATH_THIRD . 'content_elements/elements/' . $element_name . '/language/' . $this->EE->session->userdata["language"] . '/lang.' . $element_name . '.php';
			$element_lang_default = PATH_THIRD . 'content_elements/elements/' . $element_name . '/language/english/lang.' . $element_name . '.php';

			if (is_file($element_lang_custom)) {
				require($element_lang_custom);
			} elseif (is_file($element_lang_default)) {
				require($element_lang_default);
			} else {
				//no language?

				die('File ' . basename($element_lang_custom) . ' not found');
			}

			//merge to EE

			$this->EE->lang->language = array_merge($this->EE->lang->language, $lang);
		}

		/**
		 * Return view path.
		 */
		public function _get_view_path($name = '') {

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
		 * Return view path.
		 */
		public function get_element_view_path($name = '') {

			return '../../' . $this->addon_name . '/' . $name;
		}

		/**
		 * Tool: create upload field for older EE
		 *  
		 * @access public
		 */
		public function create_ee_upload_field($field_name, $filename, $allowed_file_dirs, $content_type = 'image') {

//return;

			if (version_compare(APP_VER, '2.1.0', '<=')) {
				$endpoint_url = 'C=content_publish&M=filemanager_endpoint';
			} else {
				$endpoint_url = 'C=content_publish&M=filemanager_actions';
			}

			$this->EE->lang->loadfile('content');

			if (!empty($this->EE->javascript))
				$this->EE->javascript->set_global(array(
					'filebrowser' => array(
						'publish' => TRUE
					)
				));

			if (!empty($this->EE->cp)) {
				$this->EE->cp->add_js_script('plugin', array('tmpl', 'ee_table'));

				// Include dependencies
				$this->EE->cp->add_js_script(array(
					'file' => array(
						'underscore',
						'files/publish_fields'
					),
					'plugin' => array(
						'scrollable',
						'scrollable.navigator',
						'ee_filebrowser',
						'ee_fileuploader',
						'tmpl'
					)
				));
			}

			$this->EE->load->helper('html');

			if (!empty($this->EE->javascript))
				$this->EE->javascript->set_global(array(
					'lang' => array(
						'resize_image' => lang('resize_image'),
						'or' => lang('or'),
						'return_to_publish' => lang('return_to_publish')
					),
					'filebrowser' => array(
						'endpoint_url' => $endpoint_url,
						'window_title' => lang('file_manager'),
						'next' => anchor(
								'#', img(
										$this->EE->cp->cp_theme_url . 'images/pagination_next_button.gif', array(
									'alt' => lang('next'),
									'width' => 13,
									'height' => 13
										)
								), array(
							'class' => 'next'
								)
						),
						'previous' => anchor(
								'#', img(
										$this->EE->cp->cp_theme_url . 'images/pagination_prev_button.gif', array(
									'alt' => lang('previous'),
									'width' => 13,
									'height' => 13
										)
								), array(
							'class' => 'previous'
								)
						)
					),
					'fileuploader' => array(
						'window_title' => lang('file_upload'),
						'delete_url' => 'C=content_files&M=delete_files'
					)
				));
			/**/

			if (version_compare(APP_VER, '2.2.0', '<')) {
				$this->EE->load->library('filemanager');
				$this->EE->filemanager->filebrowser($endpoint_url);
			} else {
				if (!empty($this->EE->cp))
					$this->EE->cp->add_to_head($this->EE->view->head_link('css/file_browser.css'));
			}
		}

		public function define_theme_url($addon_name = 'content_elements') {

			if (defined('CE_THEME_URL'))
				return CE_THEME_URL;

			if (defined('URL_THIRD_THEMES') === TRUE) {
				$theme_url = URL_THIRD_THEMES;
			} else {
				$theme_url = $this->EE->config->item('theme_folder_url') . 'third_party/';
			}

			// Are we working on SSL?
			if (isset($_SERVER['HTTP_REFERER']) == TRUE AND strpos($_SERVER['HTTP_REFERER'], 'https://') !== FALSE) {
				$theme_url = str_replace('http://', 'https://', $theme_url);
			} elseif (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) != 'off') {
				$theme_url = str_replace('http://', 'https://', $theme_url);
			}

			$theme_url = str_replace(array('https://', 'http://'), '//', $theme_url);

			define('CE_THEME_URL', $theme_url . $addon_name . '/');

			return CE_THEME_URL;
		}

		/**
		 * Tool: get theme url
		 *  
		 * @access public
		 */
		public function _theme_url() {
			$this->cache['theme_url'] = $this->EE->elements->define_theme_url($this->addon_name);
			return $this->cache['theme_url'];
		}

		/**
		 * Include CSS theme to CP header
		 *
		 * @param string CSS file naname
		 * @return string
		 */
		public function _include_theme_css($file, &$r = FALSE) {
			if (!in_array($file, self::$cache['includes'])) {
				self::$cache['includes'][] = $file;

				$to_add = '<link rel="stylesheet" type="text/css" href="' . $this->_theme_url() . 'styles/' . $file . '" />';

				if (REQ == 'CP')
					$this->EE->cp->add_to_head($to_add);
				else {
					$r .= $to_add;
				}
			}
		}

		/**
		 * Include CSS file directly to CP header
		 *
		 * @param string CSS file naname
		 * @return string
		 */
		public function _include_css($file, &$r = FALSE) {
			if (!in_array($file, self::$cache['includes'])) {
				self::$cache['includes'][] = $file;

				$to_add = '<link rel="stylesheet" type="text/css" href="' . $file . '" />';

				if (REQ == 'CP')
					$this->EE->cp->add_to_head($to_add);
				else {
					$r .= $to_add;
				}
			}
		}

		/**
		 * Include JS theme to CP header
		 *
		 * @param string JS file naname
		 * @return string
		 */
		public function _include_theme_js($file, &$r = FALSE) {
			if (!in_array($file, self::$cache['includes'])) {
				self::$cache['includes'][] = $file;

				$to_add = '<script type="text/javascript" src="' . $this->_theme_url() . 'scripts/' . $file . '"></script>';

				if (REQ == 'CP')
					$this->EE->cp->add_to_foot($to_add);
				else {
					$r .= $to_add;
				}
			}
		}

		/**
		 * Include JS theme to CP header
		 *
		 * @param string JS file naname
		 * @return string
		 */
		public function _include_js($file, &$r = FALSE) {
			if (!in_array($file, self::$cache['includes'])) {
				self::$cache['includes'][] = $file;

				$to_add = '<script type="text/javascript" src="' . $file . '"></script>';
				
				if (REQ == 'CP')
					$this->EE->cp->add_to_foot($to_add);
				else {
					$r .= $to_add;
				}
			}
		}

		/**
		 * Include JS stream directly to CP header
		 *
		 * @param string JS file naname
		 * @return string
		 */
		public function _insert_js($js, &$r = FALSE) {
			if (!in_array($js, self::$cache['includes'])) {
				self::$cache['includes'][] = $js;

				$to_add = '<script type="text/javascript">' . $js . '</script>';

				if (REQ == 'CP')
					$this->EE->cp->add_to_foot($to_add);
				else {
					$r .= $to_add;
				}
			}
		}

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