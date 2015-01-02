<?php

namespace EEBlocks\Controller;

if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * A parser and outputter for the root tag of the Blocks fieldtype.
 *
 * This class is primarily used from Blocks_ft::replace_tag
 */
class TagController
{
	private $EE;
	private $_ftManager;
	private $_fieldId;
	private $_prefix;

	/**
	 * Create the controller
	 *
	 * @param object $ee The ExpressionEngine instance.
	 * @param int $fieldId The database ID for the EE field itself.
	 * @param \EEBlocks\Controller\FieldTypeManager $fieldTypeManager The
	 *        object responsible for creating and loading field types.
	 */
	public function __construct($ee, $fieldId, $fieldTypeManager)
	{
		$this->EE = $ee;
		$this->_prefix = 'blocks';
		$this->_fieldId = $fieldId;
		$this->_ftManager = $fieldTypeManager;
	}

	/**
	 * The primary entry point for the Blocks parser
	 *
	 * @param string $tagdata The parsed template that EE gives.
	 * @param \EEBlocks\Model\Block[] $blocks The blocks that will be
	 *        outputted.
	 * @param array $channelRow Top-level row data that EE provides.
	 *        Typically $this->row from the fieldtype.
	 *
	 * @return string
	 */
	public function replace($tagdata, $blocks, $channelRow)
	{
		$output = '';

		foreach ($blocks as $block)
		{
			$output .= $this->_renderBlockSections(
				$tagdata,
				$block,
				$channelRow);
		}

		return $output;
	}

	// Given the root $tagdata object and the current $block, do the correct
	// replacements.
	protected function _renderBlockSections($tagdata, $block, $channelRow)
	{
		$foundsections = $this->EE->api_channel_fields->get_pair_field(
			$tagdata,
			$block->definition->shortname,
			'');

		$output = '';

		//
		// There can be multiple sections.
		//
		// {block-field}
		//   {simple}
		//    <p>{content}</p>
		//   {/simple}
		//
		//   {simple}
		//   <div>Why would anybody do this?</div>
		//   {/simple}
		// {/block-field}
		//
		// So we need to run the process for each section.
		//
		foreach ($foundsections as $foundsection)
		{
			$interiortagdata = $foundsection[1];
			$output .= $this->_renderBlockSection(
				$interiortagdata,
				$block,
				$channelRow);
		}

		return $output;
	}

	protected function _renderBlockSection($tagdata, $block, $channelRow)
	{
		$field_name = ''; // It's just nothing. Period.
		$entryId = $channelRow['entry_id'];

		$tagdata = $this->_parseConditionals($tagdata, $block);
		$grid_row = $tagdata;

		// Gather the variables to parse
		if ( ! preg_match_all(
				"/".LD.'?[^\/]((?:(?:'.preg_quote($field_name).'):?)+)\b([^}{]*)?'.RD."/",
				$tagdata,
				$matches,
				PREG_SET_ORDER)
			)
		{
			return $tagdata;
		}

		foreach ($matches as $match)
		{
			// Get tag name, modifier and params for this tag
			$field = $this->EE->api_channel_fields->get_single_field(
				$match[2],
				$field_name . ':');

			// Get any field pairs
			$pchunks = $this->EE->api_channel_fields->get_pair_field(
				$tagdata,
				$field['field_name'],
				'' // No prefixes required.
				);

			// Work through field pairs first
			foreach ($pchunks as $chk_data)
			{
				list($modifier, $content, $params, $chunk) = $chk_data;

				if ( ! isset($block->atoms[$field['field_name']])) {
					$grid_row = str_replace($chunk, '', $grid_row);
					continue;
				}

				$atom = $block->atoms[$field['field_name']];
				// Prepend the column ID with "blocks_" so it doesn't collide
				// with any real grid columns.
				$columnid = 'col_id_' .
					$this->_prefix .
					'_' .
					$atom->definition->id;
				$channelRow[$columnid] = $atom->value;

				$replace_data = $this->_replaceTag(
					$atom->definition,
					$this->_fieldId,
					$entryId,
					$block->id,
					array(
						'modifier'  => $modifier,
						'params'    => $params),
					$channelRow,
					$content);

				// Replace tag pair
				$grid_row = str_replace($chunk, $replace_data, $grid_row);
			}

			// Now handle any single variables
			if (isset($block->atoms[$field['field_name']]) &&
				strpos($grid_row, $match[0]) !== FALSE)
			{
				$atom = $block->atoms[$field['field_name']];
				$columnid = 'col_id_' .
					$this->_prefix .
					'_' .
					$atom->definition->id;
				$channelRow[$columnid] = $atom->value;

				$replace_data = $this->_replaceTag(
					$atom->definition,
					$this->_fieldId,
					$entryId,
					$block->id,
					$field,
					$channelRow);
			}

			// Check to see if this is a field in the table for
			// this field, e.g. row_id

			// TODO: What's $row? What should we do with it?
			elseif (isset($row[$match[2]]))
			{
				$replace_data = $row[$match[2]];
			}
			else
			{
				$replace_data = $match[0];
			}

			// Finally, do the replacement
			$grid_row = str_replace(
				$match[0],
				$replace_data,
				$grid_row);
		}

		return $grid_row;
	}

	protected function _parseConditionals($tagdata, $block)
	{
		// Compile conditional vars
		$cond = array();

		// Map column names to their values in the DB
		foreach ($block->atoms as $atom)
		{
			$cond[$atom->definition->shortname] = $atom->value;
		}

		$tagdata = $this->EE->functions->prep_conditionals($tagdata, $cond);

		return $tagdata;
	}

	protected function _replaceTag(
		$atomDefinition,
		$fieldId,
		$entryId,
		$rowId,
		$field,
		$data,
		$content = FALSE)
	{
		$colId = $this->_prefix . '_' . $atomDefinition->id;

		$fieldtype = $this->_ftManager->instantiateFieldtype(
			$atomDefinition,
			NULL,
			$fieldId,
			$entryId);

		// Return the raw data if no fieldtype found
		if ( ! $fieldtype)
		{
			return $this->EE->typography->parse_type(
				$this->EE->functions->encode_ee_tags($data['col_id_' . $colId]));
		}

		// Determine the replace function to call based on presence of modifier
		$modifier = $field['modifier'];
		$parse_fnc = ($modifier) ? 'replace_' . $modifier : 'replace_tag';

		$fieldtype->initialize(array(
			'row' => $data,
			'content_id' => $entryId
		));

		// Add row ID to settings array
		$fieldtype->setSetting('grid_row_id', $rowId);

		$this->_ftManager->loadFieldTypePackage($atomDefinition->type);
		$data = $fieldtype->preProcess($data['col_id_' . $colId]);
		$result = $fieldtype->replace(
			$modifier ? $modifier : NULL,
			$data,
			$field['params'],
			$content);
		$this->_ftManager->unloadFieldTypePackage($atomDefinition->type);
		return $result;
	}
}