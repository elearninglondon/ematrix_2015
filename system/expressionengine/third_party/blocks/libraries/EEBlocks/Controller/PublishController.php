<?php

namespace EEBlocks\Controller;

use \stdClass as stdClass;
use \Exception as Exception;

class PublishController {

	private $EE;
	private $_fieldId;
	private $_fieldName;
	private $_adapter;
	private $_ftManager;
	private $_prefix;

	/**
	 * Create the controller
	 *
	 * @param object $ee The ExpressionEngine instance.
	 * @param int $fieldId The database ID for the EE field itself.
	 * @param string $fieldName The ExpressionEngine name for the field.
	 * @param \EEBlocks\Database\Adapter $adapter The database adapter used
	 *        for querying from and saving to the database.
	 * @param \EEBlocks\Controller\FieldTypeManager $fieldTypeManager The
	 *        object responsible for creating and loading field types.
	 */
	public function __construct($ee, $fieldId, $fieldName, $adapter, $fieldTypeManager)
	{
		$this->EE = $ee;
		$this->_fieldId = $fieldId;
		$this->_fieldName = $fieldName;
		$this->_adapter = $adapter;
		$this->_ftManager = $fieldTypeManager;
		$this->_prefix = 'blocks';
	}

	/**
	 * Generate publish field HTML
	 *
	 * @param int $entryId The Entry ID.
	 * @param object $blockDefinitions The definitions for blocks associated
	 *        with the field type.
	 * @param object $blocks The blocks for this specific entry/field.
	 */
	public function displayField($entryId, $blockDefinitions, $blocks)
	{
		$vars = array();
		$vars['blocks'] = array();

		foreach ($blocks as $block)
		{
			$names = new stdClass();
			$names->id           = 'field_id_' . $this->_fieldId . '[blocks_block_id_' . $block->id . '][id]';
			$names->definitionId = 'field_id_' . $this->_fieldId . '[blocks_block_id_' . $block->id . '][blockdefinitionid]';
			$names->order        = 'field_id_' . $this->_fieldId . '[blocks_block_id_' . $block->id . '][order]';
			$names->deleted      = 'field_id_' . $this->_fieldId . '[blocks_block_id_' . $block->id . '][deleted]';

			$block->deleted = "false";

			$blocktmp = array(
				'fieldnames' => $names,
				'block' => $block,
				'controls' => array()
				);

			foreach ($block->atoms as $shortname => $atom)
			{
				$control = array(
					'atom' => $atom
					);

				$data = $block->atoms[$atom->definition->shortname]->value;

				$control['html'] = $this->_publishAtom(
					$atom->definition,
					$entryId,
					'blocks_block_id_' . $block->id,
					$data);
				$blocktmp['controls'][] = $control;
			}

			$vars['blocks'][] = $blocktmp;
		}

		$vars['blockdefinitions'] = $this->createBlockDefinitionsVars(
			$entryId,
			$blockDefinitions);

		$vars['fieldid'] = $this->_fieldId;

		return $vars;
	}

	public function displayValidatedField($entryId, $blockDefinitions, $data)
	{
		$vars = array();
		$vars['blocks'] = array();

		foreach ($data as $id => $blockData)
		{
			if ($id === 'blocks_new_row_0')
			{
				continue;
			}

			$block = new stdClass();
			$block->order = isset($blockData['order']) ? $blockData['order'] : "-1";

			if (substr($id, 0, 15) === 'blocks_new_row_')
			{
				$prefix = 'blocks_new_row_';
				$block->id = md5(rand());
				$block->deleted = 'false';
			}
			else {
				$prefix = 'blocks_block_id_';
				$block->id = $blockData['id'];
				$block->deleted = $blockData['deleted'];
			}

			$names = new stdClass();
			$names->id           = 'field_id_' . $this->_fieldId . '[' . $prefix . $block->id . '][id]';
			$names->definitionId = 'field_id_' . $this->_fieldId . '[' . $prefix . $block->id . '][blockdefinitionid]';
			$names->order        = 'field_id_' . $this->_fieldId . '[' . $prefix . $block->id . '][order]';
			$names->deleted      = 'field_id_' . $this->_fieldId . '[' . $prefix . $block->id . '][deleted]';

			$block->definition = $this->findBlockDefinition($blockDefinitions, intval($blockData['blockdefinitionid']));

			$blocktmp = array(
				'fieldnames' => $names,
				'block' => $block,
				'controls' => array()
				);

			// For some unknown reason, $blockData['values'] can come in out
			// of order. So, we need to get the values, yes, but then we need
			// to put them in the right order. So, let $atomcontrols hold the
			// values, and later we'll put them into $blocktmp['controls']
			// based on the proper order.
			$atomcontrols = array();

			foreach ($blockData['values'] as $valueId => $valueData)
			{
				if (substr($valueId, -6) === '_error')
				{
					continue;
				}

				$atomDefinitionId = str_replace('col_id_blocks_', '', $valueId);

				// If the value ID is like col_id_blocks_10_something, we can
				// ignore this.
				if (strpos($atomDefinitionId, '_'))
				{
					continue;
				}

				$atomDefinition = $this->findAtomDefinition(
					$block->definition,
					intval($atomDefinitionId));

				$atom = new stdClass();
				$atom->definition = $atomDefinition;

				if (isset($blockData['values'][$valueId . '_error']))
				{
					$atom->error = $blockData['values'][$valueId . '_error'];
				}

				$control = array(
					'atom' => $atom
					);

				$control['html'] = $this->_publishAtom(
					$atom->definition,
					$entryId,
					$prefix . $block->id,
					$valueData);
				$atomcontrols[$atomDefinitionId] = $control;
			}

			// Because the values can come back out-of-order, we now need to
			// go through the atom definitions for the current block
			// definition, and add the controls in the correct order.
			foreach ($block->definition->atoms as $atomDefinition)
			{
				if (isset($atomcontrols[$atomDefinition->id]))
				{
					$blocktmp['controls'][] = $atomcontrols[$atomDefinition->id];
				}
				else
				{
					// Uh oh, this is highly unorthodox.
				}
			}

			$vars['blocks'][] = $blocktmp;
		}

		$vars['blockdefinitions'] = $this->createBlockDefinitionsVars(
			$entryId,
			$blockDefinitions);

		$vars['fieldid'] = $this->_fieldId;

		return $vars;
	}

	protected function createBlockDefinitionsVars($entryId, $blockDefinitions)
	{
		$blockdefinitions = array();

		foreach ($blockDefinitions as $blockDefinition)
		{
			$templateid = 'blocks-template-' . $this->_fieldId . '-' . $blockDefinition->shortname;

			$names = new stdClass();
			$names->order             = 'field_id_' . $this->_fieldId . '[blocks_new_row_0][order]';
			$names->blockdefinitionid = 'field_id_' . $this->_fieldId . '[blocks_new_row_0][blockdefinitionid]';

			$blocktmp = array(
				'fieldnames' => $names,
				'templateid' => $templateid,
				'name' => $blockDefinition->name,
				'shortname' => $blockDefinition->shortname,
				'instructions' => $blockDefinition->instructions,
				'blockdefinitionid' => $blockDefinition->id,
				'controls' => array()
				);

			foreach ($blockDefinition->atoms as $shortname => $atom)
			{
				$control = array(
					'atom' => $atom
					);
				$control['html'] = $this->_publishAtom($atom, $entryId, NULL, NULL);
				$blocktmp['controls'][] = $control;
			}

			$blockdefinitions[] = $blocktmp;
		}
		return $blockdefinitions;
	}

	/**
	 * Returns publish field HTML for a given atoms
	 *
	 * @param object $atomDefinition Atom Definition.
	 * @param int $entryId Entry ID.
	 * @param object $block The current value of the block.
	 */
	protected function _publishAtom($atomDefinition, $entryId, $rowId, $data)
	{
		$fieldtype = $this->_ftManager->instantiateFieldtype(
			$atomDefinition,
			NULL,
			$this->_fieldId,
			$entryId
			);

		if (is_null($data))
		{
			$data = '';
		}

		// $row doesn't even exist! Does this ever get run?
		if (isset($row['row_id']))
		{
			$fieldtype->setSetting('grid_row_id', 'blocks_' . $blockId);
		}

		// Call the fieldtype's field display method and capture the output
		$this->_ftManager->loadFieldTypePackage($atomDefinition->type);
		$display_field = $fieldtype->displayField($data);
		$this->_ftManager->unloadFieldTypePackage($atomDefinition->type);

		if (is_null($rowId))
		{
			$rowId = 'blocks_new_row_0';
		}

		// Return the publish field HTML with namespaced form field names
		return $this->_namespaceInputs(
			$display_field,
			'$1name="'.$this->_fieldName.'['.$rowId.'][values][$2]$3"'
		);
	}

	protected function _namespaceInputs($search, $replace)
	{
		return preg_replace(
			'/(<[input|select|textarea][^>]*)name=["\']([^"\'\[\]]+)([^"\']*)["\']/',
			$replace,
			$search
		);
	}

	public function validate($data, $siteId, $entryId)
	{
		return $this->_processFieldData('validate', $data, $siteId, $entryId);
	}

	public function save($data, $siteId, $entryId)
	{
		$data = $this->_processFieldData('save', $data, $siteId, $entryId);

		foreach ($data['value'] as $col_id => $blockdata)
		{
			if (substr($col_id, 0, 16) === 'blocks_block_id_')
			{
				$block_id = intval(substr($col_id, 16));

				if ($blockdata['deleted'] == 'true')
				{
					$this->_adapter->deleteBlock($block_id);
					continue;
				}

				$this->_adapter->setBlockOrder($block_id, $blockdata['order']);
			}
			else
			{
				$blockDefinitionId = intval($blockdata['blockdefinitionid']);

				$block_id = $this->_adapter->createBlock(
					$blockDefinitionId,
					$siteId,
					$entryId,
					$this->_fieldId,
					$blockdata['order']);
			}

			foreach ($blockdata['values'] as $atomId => $data)
			{
				$this->_adapter->setAtomData($block_id, $atomId, $data);
			}
		}
	}

	protected function findBlock($blocks, $blockId)
	{
		// The could be a more efficient way to do this than a for loop. But
		// at this point, I don't think it's necessary. We'll have maybe 10
		// blocks max?
		foreach ($blocks as $block)
		{
			if ($block->id === $blockId)
			{
				return $block;
			}
		}
		return NULL;
	}

	protected function findBlockDefinition($blockDefinitions, $blockDefinitionId)
	{
		// Ditto my comment in findBlock about efficiency.
		foreach ($blockDefinitions as $blockDefinition)
		{
			if ($blockDefinition->id === $blockDefinitionId)
			{
				return $blockDefinition;
			}
		}
		return NULL;
	}

	protected function findAtomDefinition($blockDefinition, $atomDefinitionId)
	{
		foreach ($blockDefinition->atoms as $shortname => $atomDefinition)
		{
			if ($atomDefinition->id === $atomDefinitionId)
			{
				return $atomDefinition;
			}
		}
		return NULL;
	}

	/**
	 * Processes a POSTed Grid field for validation for saving
	 *
	 * The main point of the validation method is, of course, to validate the
	 * data in each cell and collect any errors. But it also reconstructs
	 * the post data in a way that display_field can take it if there is a
	 * validation error. The validation routine also keeps track of any other
	 * input fields and carries them through to the save method so that those
	 * values are available to fieldtypes while they run their save methods.
	 *
	 * The save method takes the validated data and gives it to the fieldtype's
	 * save method for further processing, in which the fieldtype can specify
	 * other columns that need to be filled.
	 *
	 * @param	string	Method to process, 'save' or 'validate'
	 * @param	array	Grid publish form data
	 * @param	int		Field ID of field being saved
	 * @param	int		Entry ID of entry being saved
	 * @return	boolean
	 */
	protected function _processFieldData($method, $data, $siteId, $entryId)
	{
		$this->EE->load->helper('custom_field_helper');

		// Get column data for the current field
		$blockDefinitions = $this->_adapter->getBlockDefinitionsForField($this->_fieldId);
		$blocks = $this->_adapter->getBlocks($siteId, $entryId, $this->_fieldId);

		// We'll store our final values and errors here
		$finalValues = array();
		$errors = FALSE;

		#if ( ! is_array($data))
		#{
		#	$data = array();
		#}
		// Rows key may not be set if we're at the saving stage
		#elseif (isset($data['rows']))
		#{
		#	$data = $data['rows'];
		#}

		// Make a copy of the files array so we can spoof it per field below
		$grid_field_name = $this->_fieldName;
		$files_backup = $_FILES;

		foreach ($data as $row_id => $blockdata)
		{
			if ($row_id == 'blocks_new_row_0')
			{
				// Don't save this. It's from the templates.
				continue;
			}
			// $row_id is something like 'blocks_block_id_1' or 'blocks_new_row_3'
			// $row is an array of the values inside that block.
			$block_id = str_replace('blocks_block_id_', '', $row_id);

			$block = $this->findBlock($blocks, intval($block_id));

			if (is_null($block))
			{
				$blockDefinitionId = intval($blockdata['blockdefinitionid']);
				$blockDefinition = $this->findBlockDefinition($blockDefinitions, $blockDefinitionId);
			}
			else
			{
				$blockDefinition = $this->findBlockDefinition($blockDefinitions, $block->definition->id);
			}

			if (isset($blockdata['deleted']))
			{
				$finalValues[$row_id]['deleted'] = $blockdata['deleted'];
				if ($blockdata['deleted'] == 'true')
				{
					continue;
				}
			}
			else
			{
				$finalValues[$row_id]['deleted'] = false;
			}

			$row = $blockdata['values'];

			$finalValues[$row_id]['id'] = $block_id;
			$finalValues[$row_id]['order'] = $blockdata['order'];
			$finalValues[$row_id]['blockdefinitionid'] = $blockdata['blockdefinitionid'];

			foreach ($blockDefinition->atoms as $atomDefinition)
			{
				$atom_id = 'col_id_blocks_'.$atomDefinition->id;

				// Handle empty data for default input name
				if ( ! isset($row[$atom_id]))
				{
					$row[$atom_id] = NULL;
				}

				// Assign any other input fields to POST data for normal access
				foreach ($row as $key => $value)
				{
					$_POST[$key] = $value;

					// If we're validating, keep these extra values around so
					// fieldtypes can access them on save
					if ($method == 'validate' && ! isset($finalValues[$row_id]['values'][$key]))
					{
						$finalValues[$row_id]['values'][$key] = $value;
					}
				}

				$fieldtype = $this->_ftManager->instantiateFieldtype(
					$atomDefinition,
					$row_id,
					$this->_fieldId,
					$entryId);

				// Pass Grid row ID to fieldtype if it's an existing row
				if (strpos($row_id, 'blocks_block_id_') !== FALSE)
				{
					$fieldtype->setSetting('grid_row_id', 'blocks_' . $block_id);
				}

				// Inside Blocks our files arrays end up being deeply nested.
				// Since the fields access these arrays directly, we set the
				// FILES array to what is expected by the field for each
				// iteration.
				$_FILES = array();

				if (isset($files_backup[$grid_field_name]))
				{
					$newfiles = array();

					foreach ($files_backup[$grid_field_name] as $files_key => $value)
					{
						if (isset($value[$row_id]['values'][$atom_id]))
						{
							$newfiles[$files_key] = $value[$row_id]['values'][$atom_id];
						}
					}

					$_FILES[$atom_id] = $newfiles;
				}

				// For validation, gather errors and validated data
				if ($method == 'validate')
				{
					$this->_ftManager->loadFieldTypePackage($atomDefinition->type);
					$result = $fieldtype->validate($row[$atom_id]);
					$this->_ftManager->unloadFieldTypePackage($atomDefinition->type);

					$error = $result;

					// First, assign the row data as the final value
					$value = $row[$atom_id];

					// Here we extract possible $value and $error variables to
					// overwrite the assumptions we've made, this is a chance for
					// fieldtypes to correct input data or show an error message
					if (is_array($result))
					{
						if (isset($result['value'])) {
							$value = $result['value'];
						}
						if (isset($result['error'])) {
							$error = $result['error'];
						}
					}

					// Assign the final value to the array
					$finalValues[$row_id]['values'][$atom_id] = $value;

					// If column is required and the value from validation is empty,
					// throw an error, except if the value is 0 because that can be
					// a legitimate data entry
					if (isset($atomDefinition->settings['col_required'])
						&& $atomDefinition->settings['col_required'] == 'y'
						&& empty($value)
						&& $value !== 0
						&& $value !== '0')
					{
						$error = lang('blocks_field_required');
					}

					// If there's an error, assign the old row data back so the
					// user can see the error, and set the error message
					if (is_string($error) && ! empty($error))
					{
						$finalValues[$row_id]['values'][$atom_id] = $row[$atom_id];
						$finalValues[$row_id]['values'][$atom_id.'_error'] = $error;
						$errors = lang('blocks_validation_error');
					}
				}
				// 'save' method
				elseif ($method == 'save')
				{
					$this->_ftManager->loadFieldTypePackage($atomDefinition->type);
					$result = $fieldtype->save($row[$atom_id]);
					$this->_ftManager->unloadFieldTypePackage($atomDefinition->type);

					// Flatten array
					if (is_array($result))
					{
						$result = \encode_multi_field($result);
					}

					$finalValues[$row_id]['values'][$atomDefinition->id] = $result;
					if (is_null($block))
					{
						$finalValues[$row_id]['blockdefinitionid'] = $blockdata['blockdefinitionid'];
					}
				}

				# BB: WHAT?
				// Remove previous input fields from POST
				foreach ($row as $key => $value)
				{
					unset($_POST[$key]);
				}
			}
		}

		// reset $_FILES in case it's used in other code
		$_FILES = $files_backup;

		return array('value' => $finalValues, 'error' => $errors);
	}
}
