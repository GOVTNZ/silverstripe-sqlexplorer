<?php

// SQLExplorerTable is an in-memory only object that represents a table in the database.
class SQLExplorerTable extends ViewableData {

	// A fake, allocated ID so that URL routing works, otherwise we see every view as a create.
	var $ID;

	// Name of the database table.
	var $TableName;

	// Required to get grid field to display the table name. We copy the table name here.
	var $Title;

	private static $singular_name = "Table";

	private static $db = array(
		'TableName' => 'Varchar(255)'
	);

	private static $summary_fields = array(
		'TableName' => 'TableName'
	);

	private static $searchable_fields = array(
		'TableName'
	);

	public function i18n_singular_name() {
		return _t($this->class.'.SINGULARNAME', $this->singular_name());
	}

	public static function singular_name() {
		return self::$singular_name;
	}

	public function getDefaultSearchContext() {
		return new SQLExplorerTableSearchContext(
			$this->class
			// $this->scaffoldSearchFields(), 
			// $this->defaultSearchFilters()
		);
	}

	public function canCreate($member = null) {
		return false;
	}

	public function summaryFields() {
		return self::$summary_fields;
	}

	public function canDelete($member = null) {
		return false;
	}

	public function canEdit($member = null) {
		return false;
	}

	public function canView($member = null) {
		return true;
	}

	public function Title() {
		return $this->TableName;
	}

	public function getCMSFields() {
		$fields = new FieldList();

		$tabs = new TabSet(
			$name = 'Root',
			new Tab(
				$title = "Data",
				$this->getTableDataField()
			),
			new Tab(
				$title = "Structure",
				$this->getTableStructureField()
			)
		);

		// $tabs = $this->getTableDataField();
		$fields->push($tabs);

		// $fields->addFieldToTab('Root.Data', $this->getTableDataField());

		return $fields;
	}

	// Return a GridField that shows a pagination list of all the data in this table.
	function getTableDataField() {
		$list = $this->getTableData();

		$config = GridFieldConfig_RecordViewer::create();

		$field = new GridField("TableData", "Data", $list, $config);
		$field->setModelClass('SQLExplorerTable');

		return $field;
	}

	// Return table in an ArrayList. If there are no records, return null.
	function getTableData() {
		$raw = DB::query("select * from \"" . $this->TableName . "\"");
		if (count($raw) == 0) {
			return null;
		}

		$result = new ArrayList();

		foreach ($raw as $item) {
			$result->push(new ArrayData($item));
		}

		return $result;
	}

	// Return a GridField that shows the columns for this table, including column metadata.
	function getTableStructureField() {
		$field = new GridField("TableColumns", "Columns", null);
		return $field;
	}
}