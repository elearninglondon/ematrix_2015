<?php

namespace EEBlocks\Controller;

use \Exception;
use \EEBlocks\Controller\FieldTypeWrapper;

class FieldTypeManager
{
	private $EE;
	private $_prefix;

	function __construct($ee) {
		$this->EE = $ee;
		$this->_prefix = 'blocks';
	}

	public function loadFieldTypePackage($fieldType)
	{
		$fieldtypeApi = $this->EE->api_channel_fields;

		// Add fieldtype package path
		$_ft_path = $fieldtypeApi->ft_paths[$fieldType];
		$this->EE->load->add_package_path($_ft_path, FALSE);
	}

	public function unloadFieldTypePackage($fieldType)
	{
		$fieldtypeApi = $this->EE->api_channel_fields;

		// Add fieldtype package path
		$_ft_path = $fieldtypeApi->ft_paths[$fieldType];
		$this->EE->load->remove_package_path($_ft_path);
	}

	public function instantiateFieldtype(
		$atomDefinition,
		$rowName = NULL,
		$fieldId = 0,
		$entryId = 0)
	{
		if ( ! isset($this->EE->api_channel_fields->field_types[$atomDefinition->type]))
		{
			$this->EE->load->library('api');
			$this->EE->api->instantiate('channel_fields');
			$this->EE->api_channel_fields->fetch_installed_fieldtypes();
		}

		// Instantiate fieldtype
		$fieldtype = $this->EE->api_channel_fields->setup_handler($atomDefinition->type, TRUE);

		if ( ! $fieldtype)
		{
			return NULL;
		}

		$colId = $this->_prefix . '_' . $atomDefinition->id;

		// Assign settings to fieldtype manually so they're available like
		// normal field settings
		$fieldtype->_init(
			array(
				'field_id'      => $colId,
				'field_name'    => 'col_id_' . $colId,
				'content_id'    => $entryId,
				'content_type'  => 'grid')); // TODO: Should this be 'blocks'?

		// Assign fieldtype column settings and any other information that
		// will be helpful to be accessible by fieldtypes
		$fieldtype->settings = array_merge(
			$atomDefinition->settings,
			array(
				'field_label'     => $atomDefinition->name,
				'field_required'  => 'n', // TODO: required field?
				'col_id'          => $colId,
				'col_name'        => $atomDefinition->shortname,
				'col_required'    => 'n', // TODO: required field?
				'entry_id'        => $entryId,
				'grid_field_id'   => $fieldId,
				'grid_row_name'   => $rowName)
		);

		$ftw = new FieldTypeWrapper($fieldtype);

		if ($ftw->getContentType() === 'none')
		{
			throw new Exception("Specified fieldtype '{$atomDefinition->type}' does not support blocks");
		}

		return $ftw;
	}

	public function getFieldTypes($filter = null)
	{
		$this->EE->load->library('api');
		$this->EE->api->instantiate('channel_fields');

		$fieldtypeApi = $this->EE->api_channel_fields;

		$fieldtypes = $fieldtypeApi->fetch_installed_fieldtypes();

		unset($fieldtypes['grid']);
		// For some reason, calling setup_handler on blocks makes it so that
		// the module can't load any views. So, don't let setup_handler be
		// called on blocks.
		unset($fieldtypes['blocks']);

		foreach ($fieldtypes as $field_name => $data)
		{
			$fieldtype = $fieldtypeApi->setup_handler($field_name, TRUE);
			$ftw = new FieldTypeWrapper($fieldtype);

			if (!$ftw->supportsGrid())
			{
				unset($fieldtypes[$field_name]);
				continue;
			}

			if (!$ftw->supportsBlocks())
			{
				// It doesn't support Blocks. But don't be too hasty; maybe
				// it's in the whitelist.

				if (is_null($filter) || !$filter->filter($field_name, $fieldtypes[$field_name]['version']))
				{
					// OK, the whitelist didn't like it, either.
					unset($fieldtypes[$field_name]);
				}
			}
		}

		asort($fieldtypes);

		return $fieldtypes;
	}
}