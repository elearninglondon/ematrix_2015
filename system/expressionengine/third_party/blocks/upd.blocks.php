<?php if (! defined('BASEPATH')) exit('Invalid file request');

require_once PATH_THIRD.'blocks/config.php';
require_once __DIR__ . '/libraries/autoload.php';

class Blocks_upd
{

	var $version = BLOCKS_VERSION;

	/**
	 * Constructor
	 */
	function __construct()
	{
		$this->EE =& ee();
	}

	// --------------------------------------------------------------------

	/**
	 * Install
	 */
	function install()
	{
		$this->EE->load->dbforge();

		$this->EE->db->insert('modules', array(
			'module_name'        => BLOCKS_NAME,
			'module_version'     => BLOCKS_VERSION,
			'has_cp_backend'     => 'y',
			'has_publish_fields' => 'n'
		));

		$versions = array('1.0.0');

		foreach ($versions as $version)
		{
			$procedureName = $this->procedureNameFromVersion($version);
			$query = file_get_contents(__DIR__ . '/db/' . $version . '.sql');

			$this->EE->db->query("DROP PROCEDURE IF EXISTS {$procedureName}");
			$this->EE->db->query("CREATE PROCEDURE {$procedureName}() BEGIN \n" . $query . " \nEND");
			$this->EE->db->query("CALL {$procedureName}()");
			$this->EE->db->query("DROP PROCEDURE IF EXISTS {$procedureName}");
		}

		return TRUE;
	}

	private function procedureNameFromVersion($version)
	{
		$sanitizedVersion = str_replace(array('.', '-'), '_', $version);
		$prefix = 'exp_blocks_update_';
		$proc = $prefix . $sanitizedVersion;
		return $proc;
	}

	function update($current = '')
	{
		return TRUE;
	}

	function uninstall()
	{
		// remove row from exp_modules
		$this->EE->db->delete('modules', array('module_name' => BLOCKS_NAME));

		$this->EE->load->dbforge();

		$this->EE->db->query('DROP TABLE IF EXISTS exp_blocks_blockfieldusage');
		$this->EE->db->query('DROP TABLE IF EXISTS exp_blocks_atom');
		$this->EE->db->query('DROP TABLE IF EXISTS exp_blocks_block');
		$this->EE->db->query('DROP TABLE IF EXISTS exp_blocks_atomdefinition');
		$this->EE->db->query('DROP TABLE IF EXISTS exp_blocks_blockdefinition');

		return TRUE;
	}

}
