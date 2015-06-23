<?php

class SQLExplorerSavedQuery extends DataObject {
	static $db = array(
		'Title' => 'Varchar(255)',
		'SQLText' => 'Text'
	);

	private static $singular_name = "Saved Query";

	function getCMSFields() {
		$fields = parent::getCMSFields();

		$fields->removeByName('SQLText');
		$fields->addFieldsToTab('Root.Main', array(
			new SQLExplorerQueryField('SQLText', 'SQL query'),
			new LiteralField('ResultData', '<div class="sql-explorer result-data"></div>')
		));

		return $fields;
	}
}