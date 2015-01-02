<?php

require_once PATH_THIRD.'blocks/config.php';

$lang = array(

// -------------------------------------------
//  Module CP
// -------------------------------------------

'blocks_module_name' => BLOCKS_NAME,
'blocks_module_description' => BLOCKS_DESCRIPTION,

'blocks_fieldsettings_associateblocks' => 'Block types',
'blocks_fieldsettings_associateblocks_info' => 'Select the types of blocks to use for this field',
'blocks_fieldsettings_noblocksdefined' => 'No block types have been defined. Block types must be defined before being associated with a field.',
'blocks_fieldsettings_manageblockdefinitions' => 'Edit Block Types',

'blocks_blockdefinitions_title' => 'Block Types',
'blocks_blockdefinitions_name' => 'Block Type',
'blocks_blockdefinitions_shortname' => 'Short Name',
'blocks_blockdefinitions_edit' => 'Edit',
'blocks_blockdefinitions_delete' => 'Delete',
'blocks_blockdefinitions_add' => 'Create block type',

'blocks_blockdefinition_title' => '', // Not used; use the name of the block
                                      // definition instead
'blocks_blockdefinition_settings' => 'Block Settings',
'blocks_blockdefinition_name' => 'Name',
'blocks_blockdefinition_name_info' => 'This is the name that will appear in the PUBLISH page',
'blocks_blockdefinition_shortname' => 'Short Name',
'blocks_blockdefinition_shortname_info' => 'Single word, no spaces. Underscores and dashes allowed',
'blocks_blockdefinition_shortname_invalid' => 'The shortname must be a single word with no spaces. Underscores and dashes are allowed.',
'blocks_blockdefinition_shortname_inuse' => 'The shortname provided is already in use.',
'blocks_blockdefinition_instructions' => 'Instructions',
'blocks_blockdefinition_instructions_info' => 'Instructions for authors on how or what to enter into this field when submitting an entry.',
'blocks_blockdefinition_submit' => 'Save',

'blocks_blockdefinition_atomdefinition_type' => 'Type',
'blocks_blockdefinition_atomdefinition_name' => 'Name',
'blocks_blockdefinition_atomdefinition_shortname' => 'Short Name',
'blocks_blockdefinition_atomdefinition_instructions' => 'Instructions',
'blocks_blockdefinition_atomdefinition_extra' => 'Is this data...',
'blocks_blockdefinition_atomdefinition_extra_required' => 'Required?',
'blocks_blockdefinition_atomdefinition_settings' => 'Settings',


'blocks_confirmdelete_title' => 'Delete Block Definition',
'blocks_confirmdelete_content' => 'Are you sure you want to permanently delete this Block Definition?',
'blocks_confirmdelete_submit' => 'Delete',

'blocks_validation_error' => 'There was an error in one or more blocks',
'blocks_field_required' => 'This field is required',

''=>''
);
