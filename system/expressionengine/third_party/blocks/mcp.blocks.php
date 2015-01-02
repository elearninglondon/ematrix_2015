<?php if (! defined('BASEPATH')) exit('Invalid file request');

require_once PATH_THIRD.'blocks/config.php';
require_once __DIR__ . '/libraries/autoload.php';

class Blocks_mcp
{
	private $_ftManager;
	private $_ftFilter;

	function __construct()
	{
		$this->EE =& ee();
		$this->_ftManager = new \EEBlocks\Controller\FieldTypeManager($this->EE);

		$filter = new \EEBlocks\Controller\FieldTypeFilter();
		$filter->load(PATH_THIRD.'blocks/fieldtypes.xml');
		$this->_ftFilter = $filter;

		$this->_base = 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=blocks';
	}

	function index()
	{
		$this->EE->view->cp_page_title = lang('blocks_blockdefinitions_title');

		$this->EE->load->library('table');

		$adapter = new \EEBlocks\Database\Adapter($this->EE);
		$blockDefinitions = $adapter->getBlockDefinitions();

		$vars['blockDefinitions'] = $blockDefinitions;
		$vars['base'] = $this->_base;

		return $this->EE->load->view('cp-blockdefinitions', $vars, TRUE);
	}

	function confirmdelete()
	{
		$this->EE->load->helper('form');
		$this->EE->load->library('table');
		$this->EE->load->library('form_validation');
		$this->EE->form_validation->set_error_delimiters('<p class="notice">', '</p>');

		$adapter = new \EEBlocks\Database\Adapter($this->EE);

		$blockDefinitionId = $this->EE->input->get_post('blockdefinition');
		$blockDefinitionId = intval($blockDefinitionId);
		$blockDefinition = $adapter->getBlockDefinitionById($blockDefinitionId);

		if ($_SERVER['REQUEST_METHOD'] == 'POST' && !is_null($blockDefinition)) {
			$adapter->deleteBlockDefinition($blockDefinitionId);
			$this->EE->functions->redirect(BASE.AMP.$this->_base, false, 302);
		}

		$this->EE->cp->set_breadcrumb(BASE.AMP.$this->_base, lang('blocks_module_name'));
		$this->EE->view->cp_page_title = lang('blocks_confirmdelete_title');

		$postUrl = $this->_base.AMP.'method=confirmdelete'.AMP.'blockdefinition='.$blockDefinition->id;

		$vars['blockDefinition'] = $blockDefinition;
		$vars['hiddenValues'] = array('blockdefinition' => is_null($blockDefinitionId) ? 'new' : $blockDefinitionId);
		$vars['postUrl'] = $postUrl;

		// And finally, output.
		return $this->EE->load->view('confirmdelete', $vars, TRUE);
	}

	function blockdefinition()
	{
		// The built-in fields need some help loading their language content.
		$this->EE->lang->loadfile('fieldtypes');
		$this->EE->lang->loadfile('admin_content');

		$this->EE->load->helper('form');
		$this->EE->load->library('table');
		$this->EE->load->library('form_validation');
		$this->EE->form_validation->set_error_delimiters('<p class="notice">', '</p>');

		$adapter = new \EEBlocks\Database\Adapter($this->EE);

		$blockDefinitionId = $this->EE->input->get_post('blockdefinition');
		if ($blockDefinitionId == 'new') {
			$blockDefinitionId = null;
			$blockDefinition = new \EEBlocks\Model\BlockDefinition(
				null, // id
				'',   // shortname
				'',   // name
				'');  // instructions
		}
		else {
			$blockDefinitionId = intval($blockDefinitionId);
			$blockDefinition = $adapter->getBlockDefinitionById($blockDefinitionId);
		}

		$errors = array();

		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			$this->EE->form_validation->set_rules('blockdefinition_name', 'Name', 'trim|required');
			$this->EE->form_validation->set_rules('blockdefinition_shortname', 'Short Name', 'trim|required|callback_validateShortname[' . $blockDefinitionId . ']');
			$succeeded = $this->EE->form_validation->run();

			if ($succeeded)
			{
				$name = $this->EE->input->post('blockdefinition_name');
				$shortname = $this->EE->input->post('blockdefinition_shortname');
				$instructions = $this->EE->input->post('blockdefinition_instructions');

				$settings = $this->EE->input->post('grid');
				$errors = array_merge($errors, $this->validateAtomSettings($settings));

				if (empty($errors))
				{
					$blockDefinition->name = $name;
					$blockDefinition->shortname = $shortname;
					$blockDefinition->instructions = $instructions;

					if ($blockDefinitionId == null) {
						$adapter->createBlockDefinition($blockDefinition);
					}
					else {
						$adapter->updateBlockDefinition($blockDefinition);
					}

					$this->applyAtomSettings($blockDefinition, $settings, $adapter);

					$this->EE->functions->redirect(BASE.AMP.$this->_base, false, 302);
					return;
				}
			}
		}

		$this->EE->cp->set_breadcrumb(BASE.AMP.$this->_base, lang('blocks_blockdefinitions_title'));


		if ($blockDefinition->name != '') {
			$this->EE->view->cp_page_title = $blockDefinition->name;
		}
		else {
			$this->EE->view->cp_page_title = 'New Block';
		}

		$atomDefinitionsView = $this->getAtomDefinitionsView($blockDefinition);

		$errors = $this->prepareErrors($errors);

		$postUrl = $this->_base.AMP.'method=blockdefinition'.AMP.'blockdefinition='.$blockDefinition->id;

		$vars['blockDefinition'] = $blockDefinition;
		$vars['hiddenValues'] = array('blockdefinition' => is_null($blockDefinitionId) ? 'new' : $blockDefinitionId);
		$vars['atomDefinitionsView'] = $atomDefinitionsView;
		$vars['errors'] = $errors['error_string'];
		$vars['postUrl'] = $postUrl;


		// Let grid do it's thing.
		ee()->cp->add_to_head(ee()->view->head_link('css/grid.css'));

		ee()->cp->add_js_script('plugin', 'ee_url_title');
		ee()->cp->add_js_script('ui', 'sortable');
		ee()->cp->add_js_script('file', 'cp/sort_helper');
		ee()->cp->add_js_script('file', 'cp/grid');

		$settings = array('error_fields' => $errors['field_names']);

		ee()->javascript->output('EE.grid_settings('.json_encode($settings).');');

		// If this is a new block definition, turn on the EE feature where the
		// shortname gets autopopulated when the name gets entered.
		if ($blockDefinition->name == '')
		{
			ee()->javascript->output('$("#blockdefinition_name").bind("keyup keydown", function() {
				$(this).ee_url_title("#blockdefinition_shortname", true);
			});');
		}

		// And finally, output.
		return $this->EE->load->view('cp-blockdefinition', $vars, TRUE);
	}

	// The form validator requires this to be public.
	public function validateShortname($shortname, $blockDefinitionId)
	{
		if (preg_match('/[^a-z0-9\-\_]/i', $shortname))
		{
			$this->EE->form_validation->set_message(
				'validateShortname',
				lang('blocks_blockdefinition_shortname_invalid'));
			return false;
		}

		$blockDefinitionId = intval($blockDefinitionId);
		$adapter = new \EEBlocks\Database\Adapter($this->EE);
		$blockDefinition = $adapter->getBlockDefinitionByShortname($shortname);
		if (!is_null($blockDefinition) && $blockDefinition->id !== $blockDefinitionId)
		{
			$this->EE->form_validation->set_message(
				'validateShortname',
				lang('blocks_blockdefinition_shortname_inuse'));
			return false;
		}

		return true;
	}

	private function validateAtomSettings($settings)
	{
		$errors = array();
		$col_names = array();

		// Create an array of column names for counting to see if there are
		// duplicate column names; they should be unique
		foreach ($settings['cols'] as $col_field => $column)
		{
			$col_names[] = $column['col_name'];
		}

		$col_name_count = array_count_values($col_names);

		foreach ($settings['cols'] as $col_field => $column)
		{
			// Column labels are required
			if (empty($column['col_label']))
			{
				$errors[$col_field]['col_label'] = 'grid_col_label_required';
			}

			// Column names are required
			if (empty($column['col_name']))
			{
				$errors[$col_field]['col_name'] = 'grid_col_name_required';
			}
			// Columns cannot be the same name as our protected modifiers
			/*
			elseif (in_array($column['col_name'], ee()->grid_parser->reserved_names))
			{
				$errors[$col_field]['col_name'] = 'grid_col_name_reserved';
			}
			*/
			// There cannot be duplicate column names
			elseif ($col_name_count[$column['col_name']] > 1)
			{
				$errors[$col_field]['col_name'] = 'grid_duplicate_col_name';
			}

			// Column names must contain only alpha-numeric characters and no spaces
			if (preg_match('/[^a-z0-9\-\_]/i', $column['col_name']))
			{
				$errors[$col_field]['col_name'] = 'grid_invalid_column_name';
			}

			$column['col_id'] = (strpos($col_field, 'new_') === FALSE)
				? str_replace('col_id_', '', $col_field) : FALSE;
			$column['col_required'] = isset($column['col_required']) ? 'y' : 'n';
			$column['col_settings']['col_required'] = $column['col_required'];

			$atomDefinition = new \EEBlocks\Model\AtomDefinition(
				intval($column['col_id']),
				$column['col_name'],
				$column['col_label'],
				$column['col_instructions'],
				1, // order
				$column['col_type'],
				$column['col_settings']);

			$fieldtype = $this->_ftManager->instantiateFieldtype($atomDefinition, NULL, 0, 0);

			// Let fieldtypes validate their Grid column settings; we'll
			// specifically call grid_validate_settings() because validate_settings
			// works differently and we don't want to call that on accident
			$ft_validate = $fieldtype->validateSettings($column['col_settings']);

			if (is_string($ft_validate))
			{
				$errors[$col_field]['custom'] = $ft_validate;
			}
		}

		return $errors;
	}

	private function applyAtomSettings($blockDefinition, $settings, $adapter)
	{
		//$new_field = ee()->grid_model->create_field($settings['field_id'], $this->content_type);

		// Keep track of column IDs that exist so we can compare it against
		// other columns in the DB to see which we should delete
		$col_ids = array();

		// Determine the order of each atom definition.
		$order = 0;

		// Go through ALL posted columns for this field
		foreach ($settings['cols'] as $col_field => $column)
		{
			$order++;
			// Attempt to get the column ID; if the field name contains 'new_',
			// it's a new field, otherwise extract column ID
			$column['col_id'] = (strpos($col_field, 'new_') === FALSE)
				? str_replace('col_id_', '', $col_field) : FALSE;

			$id = $column['col_id'] ? intval($column['col_id']) : null;

			$column['col_required'] = isset($column['col_required']) ? 'y' : 'n';
			$column['col_settings']['col_required'] = $column['col_required'];

			// We could find the correct atom definition in the block
			// definition, but we'd end up overwriting all of it's properties
			// anyway, so we may as well make a new model object that
			// represents the same atom definition.
			$atomDefinition = new \EEBlocks\Model\AtomDefinition(
				$id,
				$column['col_name'],
				$column['col_label'],
				$column['col_instructions'],
				$order,
				$column['col_type'],
				$column['col_settings']);

			$atomDefinition->settings = $this->_save_settings($atomDefinition);
			$atomDefinition->settings['col_required'] = $column['col_required'];

			if (is_null($atomDefinition->id))
			{
				$adapter->createAtomDefinition($blockDefinition->id, $atomDefinition);
			}
			else
			{
				$adapter->updateAtomDefinition($atomDefinition);
			}

			$col_ids[] = $atomDefinition->id;
		}

		// Delete existing atoms that were not included.
		foreach ($blockDefinition->atoms as $atomDefinition)
		{
			if (!in_array($atomDefinition->id, $col_ids)) {
				$adapter->deleteAtomDefinition($atomDefinition->id);
			}
		}
	}

	private function getAtomDefinitionsView($blockDefinition)
	{
		$vars = array();
		$vars['columns'] = array();

		foreach ($blockDefinition->atoms as $atomDefinition)
		{
			$atomDefinitionView = $this->getAtomDefinitionView($atomDefinition);
			$vars['columns'][] = $atomDefinitionView;
		}

		// Fresh settings forms ready to be used for added columns
		$vars['settings_forms'] = array();
		foreach ($this->_ftManager->getFieldTypes($this->_ftFilter) as $field_name => $data)
		{
			$vars['settings_forms'][$field_name] = $this->getAtomDefinitionSettingsForm(null, $field_name);
		}


		// Will be our template for newly-created columns
		$vars['blank_col'] = $this->getAtomDefinitionView(null);

		if (empty($vars['columns']))
		{
			$vars['columns'][] = $vars['blank_col'];
		}

		return $this->EE->load->view('cp-atomdefinitions', $vars, TRUE);
	}

		/**
	 * Returns rendered HTML for a column on the field settings page
	 *
	 * @param	array	Array of single column settings from the grid_columns table
	 * @return	string	Rendered column view for settings page
	 */
	public function getAtomDefinitionView($atomDefinition, $column = NULL)
	{
		$fieldtypes = $this->_ftManager->getFieldTypes($this->_ftFilter);

		// Create a dropdown-frieldly array of available fieldtypes
		$fieldtypesLookup = array();
		foreach ($fieldtypes as $key => $value)
		{
			$fieldtypesLookup[$key] = $value['name'];
		}

		$field_name = (is_null($atomDefinition)) ? 'new_0' : 'col_id_'.$atomDefinition->id;

		$settingsForm = (is_null($atomDefinition))
			? $this->getAtomDefinitionSettingsForm(null, 'text')
			: $this->getAtomDefinitionSettingsForm($atomDefinition, $atomDefinition->type, $column);

		return $this->EE->load->view(
			'cp-atomdefinition',
			array(
				'atomDefinition'  => $atomDefinition,
				'field_name'      => $field_name,
				'settingsForm'    => $settingsForm,
				'fieldtypes'      => $fieldtypesLookup
			),
			TRUE
		);
	}

	// ------------------------------------------------------------------------

	/**
	 * Returns rendered HTML for the custom settings form of a grid column type
	 *
	 * @param	string	Name of fieldtype to get settings form for
	 * @param	array	Column data from database to populate settings form
	 * @return	array	Rendered HTML settings form for given fieldtype and
	 * 					column data
	 */
	public function getAtomDefinitionSettingsForm($atomDefinition, $type)
	{
		$ft_api = ee()->api_channel_fields;
		$settings = NULL;

		// Returns blank settings form for a specific fieldtype
		if (is_null($atomDefinition))
		{
			$ft_api->setup_handler($type);

			if ($ft_api->check_method_exists('grid_display_settings'))
			{
				$settings = $ft_api->apply('grid_display_settings', array(array()));
			}

			return $this->_view_for_col_settings($atomDefinition, $type, $settings);
		}

		$fieldtype = $this->_ftManager->instantiateFieldtype(
			$atomDefinition,
			NULL,
			0, // Field ID? At this point, we don't have one.
			0);

		$settings = $fieldtype->displaySettings($atomDefinition->settings);

		// Otherwise, return the prepopulated settings form based on column settings
		return $this->_view_for_col_settings($atomDefinition, $type, $settings);
	}

	/**
	 * Returns rendered HTML for the custom settings form of a grid column type,
	 * helper method for Grid_lib::get_settings_form
	 *
	 * @param	string	Name of fieldtype to get settings form for
	 * @param	array	Column data from database to populate settings form
	 * @param	int		Column ID for field naming
	 * @return	array	Rendered HTML settings form for given fieldtype and
	 * 					column data
	 */
	protected function _view_for_col_settings($atomDefinition, $type, $settings)
	{
		$settings_view = $this->EE->load->view(
			'cp-atomdefinitionsettings',
			array(
				'atomDefinition' => $atomDefinition,
				'col_type'       => $type,
				'col_settings'   => (empty($settings)) ? array() : $settings
			),
			TRUE
		);

		$col_id = (is_null($atomDefinition)) ? 'new_0' : 'col_id_'.$atomDefinition->id;

		// Namespace form field names
		return $this->_namespace_inputs(
			$settings_view,
			'$1name="grid[cols]['.$col_id.'][col_settings][$2]$3"'
		);
	}

	/**
	 * Performes find and replace for input names in order to namespace them
	 * for a POST array
	 *
	 * @param	string	String to search
	 * @param	string	String to use for replacement
	 * @return	string	String with namespaced inputs
	 */
	protected function _namespace_inputs($search, $replace)
	{
		return preg_replace(
			'/(<[input|select|textarea][^>]*)name=["\']([^"\'\[\]]+)([^"\']*)["\']/',
			$replace,
			$search
		);
	}

	protected function prepareErrors($validate)
	{
		$errors = array();
		$field_names = array();

		// Gather error messages and fields with errors so that we can
		// display the error messages and highlight the fields that
		// have errors
		foreach ($validate as $column => $fields)
		{
			foreach ($fields as $field => $error)
			{
				$errors[] = $error;
				$field_names[] = 'grid[cols]['.$column.']['.$field.']';
			}
		}

		// Make error messages unique and convert to a string to pass
		// to form validaiton library
		$errors = array_unique($errors);
		$error_string = '';
		foreach ($errors as $error)
		{
			$error_string .= lang($error).'<br>';
		}

		return array(
			'field_names' => $field_names,
			'error_string' => $error_string
		);
	}

	protected function _save_settings($atomDefinition)
	{
		if (!isset($atomDefinition->settings))
		{
			$atomDefinition->settings = array();
		}

		$fieldtype = $this->_ftManager->instantiateFieldtype(
			$atomDefinition,
			null,
			0,
			0);

		if ( ! ($settings = $fieldtype->saveSettings($atomDefinition->settings)))
		{
			return $atomDefinition->settings;
		}

		return $settings;
	}
}
