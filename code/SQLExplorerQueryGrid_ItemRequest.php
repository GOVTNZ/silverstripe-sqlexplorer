<?php

/**
 * This subclass is required because the detail form requires extra actions
 * on the edit form. While SS documentation says this is easy to do, by the model
 * implementing getCMSActions, grid fields editor completely ignores it, and provides
 * no way for the DataObject to define it's own behaviour, only extensions, which is
 * undesirable for our case.
 * So this solution: override the _ItemRequest class of the grid field, and tell the
 * grid field about it. This will need to handle any custom actions.
 */
class SQLExplorerQueryGrid_ItemRequest extends GridFieldDetailForm_ItemRequest {

	private static $allowed_actions = array(
		'edit',
		'view',
		'ItemEditForm',
		'getData',
		'export'
	);

	private static $url_handlers = array(
		'$Action!' => '$Action',
		'' => 'edit',
	);

	public function getData() {
		$response = $this->getDataInternal();
		if (!is_array($response)) {
			// Might have hit an error
			return;
		}

		return Convert::raw2json($response);
	}

	// Actual implementation to fetch the query, check security and execute it. Returns a map with 
	// response properties.
	protected function getDataInternal() {
		if (!Permission::checkMember(Member::currentUser(), 'ADMIN')) {
			return Security::permissionFailure($this);
		}

		$sql = $_REQUEST['query'];
		if (!$this->validSQL($sql)) {
			throw new Exception("Invalid SQL");
		}

		$raw = DB::query($sql);

		// return the data as a JSON object containing a collection, each record being an object in that
		// collection. We need to be mindful that the dataset may be large.

		$items = array();
		foreach($raw as $record) {
			foreach ($record as $key => $value) {
				if ($this->columnOK($key)) {
					$record[$key] = Convert::raw2xml($value);
				} else {
					unset($record[$key]);
				}
			}
			$items[] = $record;
		}

		$controller = Controller::curr();
		$controller->getResponse()->addHeader('Content-Type', 'application/json');

		$response = array(
			"status" => "ok",
			"items" => $items,
			"itemCount" => count($items),
			"totalCount" => count($items)
		);

		return $response;
	}

	// Determine if this SQL statement should allowed to be executed. We only support read-only queries, so it's
	// got to start with a "select".
	// @todo determine if it is at all possible for a select state to have side effects.
	protected function validSQL($sql) {
		$sql = trim($sql);
		if (strtoupper(substr($sql, 0, 7)) != "SELECT ") {
			return false;
		}
		return true;
	}

	protected function columnOK($colName) {
		// These are fields on member that are not extracted for security reasons. If a user really wants these
		// they have to be aliased. Ideally, we'd understand what table these are from, but we don't. So if you
		// have fields with these names on other DataObjects, you're unlucky.
		$protectedColumns = array(
			'TempIDHash',
			'TempIDExpired',
			'Password',
			'AutoLoginHash',
			'PasswordEncryption',
			'RememberLoginToken',
			'Salt',
		);

		if (in_array($colName, $protectedColumns)) {
			return false;
		}
		return true;
	}

	public function export() {
		if ($fileData = $this->generateExportFileData()){
			return SS_HTTPRequest::send_file($fileData, "extract.csv", 'text/csv');
		}
	}

	public function generateExportFileData() {
		$data = $this->getDataInternal();
		if (!is_array($data)) {
			return;
		}

		$data = $data["items"];

		if (count($data) == 0) {
			// no data - nothing to download, and can't figure the columns
			return;
		}

		// @todo consider moving separate and whether we want a header to the saved query object.
		$separator = ",";
		$fileData = '';
		$columnData = array();
		$fieldItems = new ArrayList();

		// Generate the header row
		$headers = array();

		foreach ($data[0] as $key => $value) {
			$headers[] = $key;
		}

		$fileData .= "\"" . implode("\"{$separator}\"", array_values($headers)) . "\"";
		$fileData .= "\n";


		foreach ($data as $item) {
			$columnData = array();

			foreach ($item as $key => $value) {
				$value = str_replace(array("\r", "\n"), "\n", $value);
				$columnData[] = '"' . str_replace('"', '""', $value) . '"';
			}
			$fileData .= implode($separator, $columnData);
			$fileData .= "\n";
		}

		return $fileData;
	}
}

