<?php

namespace EEBlocks\Controller;

use \InvalidArgumentException;

class FieldTypeWrapper {
	private $_fieldtype;
	private $_contentType = NULL;

	function __construct($fieldtype) {
		$this->_fieldtype = $fieldtype;
		$this->_contentType = $this->_getContentType();
	}

	private function _getContentType() {
		if (!is_callable(array($this->_fieldtype, 'accepts_content_type'))) {
			throw new InvalidArgumentException('Specified fieldtype does not have method accepts_content_type');
		}

		$supportsGrid = $this->_fieldtype->accepts_content_type('grid');
		$supportsBlocks = $this->_fieldtype->accepts_content_type('blocks/1');
		$random = 'blocks/' . rand(1000, 9999);
		$supportsRandom = $this->_fieldtype->accepts_content_type($random);

		if ($supportsBlocks && !$supportsRandom) {
			return 'blocks/1';
		}
		if ($supportsBlocks && !$supportsGrid) {
			// Claims to support blocks but doesn't support Grid? Yeah, right.
			return 'none';
		}
		if ($supportsGrid) {
			return 'grid';
		}
		return 'none';
	}

	function supportsGrid() {
		// We would have thrown an error if it didn't, so it does.
		return $this->_contentType !== 'none';
	}

	function supportsBlocks() {
		return $this->_contentType === 'blocks/1';
	}

	function getContentType() {
		return $this->_contentType;
	}

	public function setSetting($setting, $value)
	{
		$this->_fieldtype->settings[$setting] = $value;
	}

	public function initialize($data)
	{
		$this->_fieldtype->_init($data);
	}

	public function replace($modifier, $data, $params = array(), $tagdata = NULL)
	{
		$ft = $this->_fieldtype;

		if (is_null($modifier)) {
			$modifier = 'tag';
		}

		if (is_callable(array($ft, 'grid_replace_' . $modifier))) {
			return call_user_func(array($ft, 'grid_replace_' . $modifier), $data, $params, $tagdata);
		}

		// Does grid_replace_tag_catchall supercede replace_modifier?

		if (is_callable(array($ft, 'replace_' . $modifier))) {
			return call_user_func(array($ft, 'replace_' . $modifier), $data, $params, $tagdata);
		}

		if ($modifier != 'tag' && is_callable(array($ft, 'replace_tag_catchall'))) {
			return $ft->replace_tag_catchall($data, $params, $tagdata, $modifier);
		}

		// If there's a modifier that wasn't matched, do we fall back to replace_tag, throw an error, or return nothing?

		return $ft->replace_tag($data, $params, $tagdata);
	}

	private function call($methodName, $args, $passthrough)
	{
		$ft = $this->_fieldtype;

		if (is_callable(array($ft, 'grid_' . $methodName)))
		{
			return call_user_func_array(array($ft, 'grid_' . $methodName), $args);
		}

		if (is_callable(array($ft, $methodName))) {
			return call_user_func_array(array($ft, $methodName), $args);
		}

		// Hrmm... this is suspect.
		if ($passthrough)
		{
			return $args[0];
		}
		else
		{
			return null;
		}
	}

	public function preProcess($data)
	{
		return $this->call('pre_process', array($data), true);
	}

	public function displayField($data)
	{
		return $this->call('display_field', array($data), false);
	}

	public function save($data)
	{
		return $this->call('save', array($data), true);
	}

	public function validate($data)
	{
		// AH! I don't know if validate should passthrough or not!
		return $this->call('validate', array($data), true);
	}

	public function displaySettings($data)
	{
		$ft = $this->_fieldtype;

		if (is_callable(array($ft, 'grid_display_settings')))
		{
			return call_user_func_array(array($ft, 'grid_display_settings'), array($data));
		}

		return null;
	}

	public function validateSettings($data)
	{
		$ft = $this->_fieldtype;

		if (is_callable(array($ft, 'grid_validate_settings')))
		{
			return call_user_func_array(array($ft, 'grid_validate_settings'), array($data));
		}

		return null;
	}

	public function saveSettings($data)
	{
		$ft = $this->_fieldtype;

		if (is_callable(array($ft, 'grid_save_settings')))
		{
			return call_user_func_array(array($ft, 'grid_save_settings'), array($data));
		}

		return null;
	}
}
