<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once PATH_THIRD.'blocks/config.php';
require_once __DIR__ . '/libraries/autoload.php';

class Blocks_ft extends EE_Fieldtype
{

	var $info = array(
		'name' => BLOCKS_NAME,
		'version' => BLOCKS_VERSION
	);

	var $has_array_data = true;
	var $cache = null;
	private $_ftManager;

	function __construct()
	{
		$this->EE = ee();
		$this->_ftManager = new \EEBlocks\Controller\FieldTypeManager($this->EE);

		if (! isset($this->EE->session->cache[__CLASS__]))
		{
			$this->EE->session->cache[__CLASS__] = array();
		}
		$this->cache =& $this->EE->session->cache[__CLASS__];

		if (!isset($this->cache['includes'])) {
			$this->cache['includes'] = array();
		}
		if (!isset($this->cache['validation'])) {
			$this->cache['validation'] = array();
		}
	}

	protected function includeThemeJS($file)
	{
		if (! in_array($file, $this->cache['includes']))
		{
			$this->cache['includes'][] = $file;
			$this->EE->cp->add_to_foot('<script type="text/javascript" src="'.$this->getThemeURL().$file.'?version='.BLOCKS_VERSION.'"></script>');
		}
	}

	protected function includeThemeCSS($file)
	{
		if (! in_array($file, $this->cache['includes']))
		{
			$this->cache['includes'][] = $file;
			$this->EE->cp->add_to_head('<link rel="stylesheet" href="'.$this->getThemeURL().$file.'?version='.BLOCKS_VERSION.'">');
		}
	}

	protected function getThemeURL()
	{
		if (! isset($this->cache['theme_url']))
		{
			$theme_folder_url = defined('URL_THIRD_THEMES') ? URL_THIRD_THEMES : $this->EE->config->slash_item('theme_folder_url').'third_party/';
			$this->cache['theme_url'] = $theme_folder_url.'blocks/';
		}

		return $this->cache['theme_url'];
	}

	protected function includeGridAssets()
	{
		if ( ! ee()->session->cache(__CLASS__, 'grid_assets_loaded'))
		{
			if (REQ == 'CP')
			{
				$css_link = ee()->view->head_link('css/grid.css');
			}
			// Channel Form
			else
			{
				$css_link = '<link rel="stylesheet" href="'.ee()->config->slash_item('theme_folder_url').'cp_themes/default/css/grid.css" type="text/css" media="screen" />'.PHP_EOL;
			}

			ee()->cp->add_to_head($css_link);

			ee()->cp->add_js_script('ui', 'sortable');
			ee()->cp->add_js_script('file', 'cp/sort_helper');
			ee()->cp->add_js_script('file', 'cp/grid');

			ee()->session->set_cache(__CLASS__, 'grid_assets_loaded', TRUE);
		}
	}

	public function display_field($data)
	{
		$this->includeGridAssets();
		$this->includeThemeCSS('css/cp.css');
		$this->includeThemeJS('javascript/html.sortable.js');
		$this->includeThemeJS('javascript/cp.js');

		$adapter = new \EEBlocks\Database\Adapter($this->EE);
		$entry_id = isset($this->settings['entry_id']) ? $this->settings['entry_id'] : $this->EE->input->get_post('entry_id');
		$site_id = $this->settings['site_id'];
		$blockDefinitions = $adapter->getBlockDefinitionsForField($this->field_id);

		$controller = new \EEBlocks\Controller\PublishController(
			$this->EE,
			$this->id(),
			$this->name(),
			$adapter,
			$this->_ftManager
			);


		if (!is_array($data))
		{
			// We're displaying the field for the first time. So, get the data
			// from the database.

			$blocks = $adapter->getBlocks($site_id, $entry_id, $this->field_id);

			$viewdata = $controller->displayField(
				$entry_id,
				$blockDefinitions,
				$blocks);
		}
		else
		{
			if (isset($this->cache['validation'][$this->id()]))
			{
				$data = $this->cache['validation'][$this->id()]['value'];
			}

			// Validation failed. Either our validation or another validation,
			// we don't know, but now we need to output the data that was
			// entered instead of getting it from the database.
			$viewdata = $controller->displayValidatedField(
				$entry_id,
				$blockDefinitions,
				$data);
		}

		return $this->EE->load->view('editor', $viewdata, TRUE);
	}

	public function validate($data)
	{
		$field_id = $this->id();
		if (isset($this->cache['validation'][$field_id]))
		{
			return $this->cache['validation'][$field_id];
		}

		$this->EE->lang->loadfile('blocks');

		$adapter = new \EEBlocks\Database\Adapter($this->EE);
		$entry_id = isset($this->settings['entry_id']) ? $this->settings['entry_id'] : $this->EE->input->get_post('entry_id');
		$site_id = $this->settings['site_id'];

		$controller = new \EEBlocks\Controller\PublishController(
			$this->EE,
			$this->id(),
			$this->name(),
			$adapter,
			$this->_ftManager);
		$validated = $controller->validate(
			$data,
			$site_id,
			$entry_id);

		$this->cache['validation'][$field_id] = $validated;

		return $validated;
	}

	public function save($data)
	{
		$this->EE->session->set_cache(__CLASS__, $this->name(), $data);

		return ' ';
	}

	public function post_save($data)
	{
		// Prevent saving if save() was never called, happens in Channel Form
		// if the field is missing from the form
		if (($data = $this->EE->session->cache(__CLASS__, $this->name(), FALSE)) !== FALSE)
		{
			$adapter = new \EEBlocks\Database\Adapter($this->EE);
			$entry_id = isset($this->settings['entry_id']) ? $this->settings['entry_id'] : $this->EE->input->get_post('entry_id');
			$site_id = $this->settings['site_id'];

			$controller = new \EEBlocks\Controller\PublishController(
				$this->EE,
				$this->id(),
				$this->name(),
				$adapter,
				$this->_ftManager);
			$controller->save(
				$data,
				$site_id,
				$entry_id);
		}
	}

	public function replace_tag($data, $params = array(), $tagdata = FALSE)
	{
		if (!$tagdata) return;

		$entry_id = $this->row['entry_id'];
		$site_id = $this->row['entry_site_id'];

		$adapter = new \EEBlocks\Database\Adapter($this->EE);
		$blocks = $adapter->getBlocks(
			$site_id,
			$entry_id,
			$this->field_id);

		$controller = new \EEBlocks\Controller\TagController(
			$this->EE,
			$this->field_id,
			$this->_ftManager);
		return $controller->replace($tagdata, $blocks, $this->row);
	}

	public function display_settings($data)
	{
		$this->includeThemeCSS('css/edit-field.css');
		$this->includeThemeJS('javascript/html.sortable.js');
		$this->includeThemeJS('javascript/edit-field.js');

		$blockDefinitionMaintenanceUrl = BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=blocks';

		$this->EE->lang->loadfile('blocks');

		$adapter = new \EEBlocks\Database\Adapter($this->EE);
		$selectedBlockDefinitions = $adapter->getBlockDefinitionsForField(
			$this->field_id);
		$allBlockDefinitions = $adapter->getBlockDefinitions();

		$blockDefinitions = $this->sortBlockDefinitions(
			$selectedBlockDefinitions,
			$allBlockDefinitions);

		$output = '';

		if (count($blockDefinitions) > 0)
		{
			$output .= "<div class='blockselectors'>";
			$i = 1;

			foreach ($blockDefinitions as $blockDefinition)
			{
				$prefix = 'blockdefinitions[' . $blockDefinition->id . ']';
				$checked = '';
				if ($blockDefinition->selected)
				{
					$checked = 'checked';
				}

				$output .= "<div class='blockselector'>";
				$output .= "<input type='hidden' name='{$prefix}[order]' value='$i' js-order>";
				$output .= "<input type='hidden' name='{$prefix}[selected]' value='0'>";
				$output .= "<span class='blockselector-handle'>::</span>";
				$output .= "<label><input type='checkbox' name='{$prefix}[selected]' value='1' $checked js-checkbox> <span>{$blockDefinition->name}</span></label>";
				$output .= "</div>\n";
				$i++;
			}

			$output .= "</div>"; // .blockselectors
		}
		else
		{
			$output .= '<p class="notice">' . lang('blocks_fieldsettings_noblocksdefined') . '</p>';
		}

		$output .= "<p><a href='{$blockDefinitionMaintenanceUrl}'>" . lang('blocks_fieldsettings_manageblockdefinitions') . "</a></p>";

		$this->EE->table->add_row(
			'<strong>'.lang('blocks_fieldsettings_associateblocks').'</strong>'.'<br>'.
			lang('blocks_fieldsettings_associateblocks_info'),
			$output
			);
	}

	protected function sortBlockDefinitions($selected, $all)
	{
		$return = array();
		$selectedIds = array();

		foreach ($selected as $blockDefinition)
		{
			$selectedIds[] = $blockDefinition->id;
			$blockDefinition->selected = true;
			$return[] = $blockDefinition;
		}

		foreach ($all as $blockDefinition)
		{
			if (in_array($blockDefinition->id, $selectedIds)) {
				continue;
			}

			$blockDefinition->selected = false;
			$return[] = $blockDefinition;
		}

		return $return;
	}

	public function post_save_settings($data)
	{
		$fieldId = $data['field_id'];

		$blockDefinitions = $this->EE->input->post('blockdefinitions');

		$adapter = new \EEBlocks\Database\Adapter($this->EE);

		if ($blockDefinitions)
		{
			foreach ($blockDefinitions as $blockDefinitionId => $values)
			{
				if ($values['selected'] == '0')
				{
					$adapter->disassociateBlockDefinitionWithField(
						$fieldId,
						$blockDefinitionId);
				}
				else if ($values['selected'] == '1')
				{
					$order = intval($values['order']);
					$adapter->associateBlockDefinitionWithField(
						$fieldId,
						$blockDefinitionId,
						$order);
				}
			}
		}
	}
}
