<?php

namespace GovtNZ\SilverStripe\SqlExplorer;

use SilverStripe\Forms\GridField\GridFieldDetailForm_ItemRequest;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Convert;
use SilverStripe\Security\Member;
use SilverStripe\Security\Permission;
use SilverStripe\Security\Security;
use SilverStripe\ORM\ArrayList;

/**
 * This subclass is required because the detail form requires extra actions
 * on the edit form. While SS documentation says this is easy to do, by the model
 * implementing getCMSActions, grid fields editor completely ignores it, and provides
 * no way for the DataObject to define it's own behaviour, only extensions, which is
 * undesirable for our case.
 * So this solution: override the _ItemRequest class of the grid field, and tell the
 * grid field about it. This will need to handle any custom actions.
 */
class SQLExplorerQueryGrid_ItemRequest extends GridFieldDetailForm_ItemRequest
{

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

    // API method to get data for ajax request. Returns an application/json response.
    public function getData()
    {
        $controller = Controller::curr();
        $controller->getResponse()->addHeader('Content-Type', 'application/json');

        try {
            $response = $this->getDataInternal();
            if (!is_array($response)) {
                throw new Exception('Could not execute data');
            }

            return Convert::raw2json($response);
        } catch (Exception $e) {
            $response = array(
                'status' => 'error',
                'error' => $e->getMessage(),
                'temCount' => 0,
                'totalCount' => 0
            );
            return Convert::raw2json($response);
        }
    }

    // A simple error handler that is set for the SQL execution, which takes a user error
    // and throws it as an exception. This lets us catch it and return it in the ajax response.
    public function errorHandler($errorNo, $message)
    {
        throw new Exception($message);
    }

    // Actual implementation to fetch the query, check security and execute it. Returns a map with
    // response properties. Errors are thrown as exceptions.
    protected function getDataInternal()
    {
        if (!Permission::checkMember(Member::currentUser(), 'ADMIN')) {
            return Security::permissionFailure($this);
        }

        // Set an error handler to catch SQL errors
        set_error_handler(array('SQLExplorerQueryGrid_ItemRequest', 'errorHandler'));

        $sql = $_REQUEST['query'];
        if (!$this->validSQL($sql)) {
            throw new Exception("Invalid SQL");
        }

        // Execute the query, with errors being generated as a warning, so the error handler will see it.
        // (The default is fatal, and the error handler is not invoked.)
        $raw = DB::query($sql, E_USER_WARNING);

        // return the data as a JSON object containing a collection, each record being an object in that
        // collection. We need to be mindful that the dataset may be large.

        $items = array();
        foreach ($raw as $record) {
            foreach ($record as $key => $value) {
                if ($this->columnOK($key)) {
                    $record[$key] = Convert::raw2xml($value);
                } else {
                    unset($record[$key]);
                }
            }
            $items[] = $record;
        }

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
    protected function validSQL($sql)
    {
        $sql = trim($sql);
        if (strtoupper(substr($sql, 0, 7)) != "SELECT ") {
            return false;
        }
        return true;
    }

    protected function columnOK($colName)
    {
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

    public function export()
    {
        try {
            if ($fileData = $this->generateExportFileData()) {
                return HTTPRequest::send_file($fileData, "extract.csv", 'text/csv');
            }
        } catch (Exception $e) {
            return "Could not extract query: " . $e->getMessage();
        }
    }

    public function generateExportFileData()
    {
        $data = $this->getDataInternal();
        if (!is_array($data)) {
            throw new Exception("Result is not an array");
        }

        $data = $data["items"];

        if (count($data) == 0) {
            // no data - nothing to download, and can't figure the columns
            throw new Exception("No data");
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
