(function($) {
	// Handle click on the execute action. This invokes an ajax request to evaluate the SQL statement.
	$(document).on('click', '.sql-explorer-query-field .action-execute', function() {
		var $field = $(this).closest(".sql-explorer-query-field");
		var sql = $('textarea', $field).val();

		var url = controllerURL() + 'getData';
		$.ajax(url, {
			type: 'POST',
			data: {
				query: sql
			},
			success: function(data, textStatus, jQueryXHR) {
				if (data.status == 'ok') {
					showTable($field, data);
				} else {
					showError(data.error);
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				showError(textStatus);
			}
		})
		return false;
	});

	// Handle click on th export action. This creates an iframe, and injects the export URL into it,
	// which invokes the download process.
	$(document).on('click', '.sql-explorer-query-field .action-export', function() {
		var $field = $(this).closest(".sql-explorer-query-field");
		var sql = $('textarea', $field).val();

		var url = controllerURL() + 'export';
		url += '?query=' + sql;

		var iframe = document.createElement("iframe");
		iframe.setAttribute("src", url);
		iframe.setAttribute("style", "display: none");
		document.body.appendChild(iframe);

		return false;
	});

	// Get the URL of the controller, with trailing slash, which acts as a base for any API calls we need to make.
	// When adding or editing a SQLExplorerSavedQuery, the controller is SQLExplorerQueryGrid_ItemRequest.
	var controllerURL = function() {
		var url = window.location.pathname + '/';

		// If we are editing, we'll have an extra "/edit" that we don't want.
		url = url.replace(/edit\/$/, "");
		return url;
	};

	// Format the list of item objects for presentation in a table.
	var getDataAsTable = function(items) {
		var s = '<table>';

		// headings
		s += '<thead><tr>';
		for (var p in items[0]) {
			s += '<td>' + p + '</td>';
		}
		s += '</tr></thead>';

		// data
		for (var i = 0; i < items.length; i++) {
			var item = items[i];
			s += '<tr>';
			for (var p in item) {
				s += '<td>' + item[p] + '</td>';
			}
			s += '</tr>';
		}

		s += '</table>';
		return s;
	};

	// Given a data response, populate a table to show the data.
	var showTable = function($field, data) {
		$('.result-data').html(getDataAsTable(data.items));
		$('.query-error').html('');
	};

	var showError = function(msg) {
		$('.result-data').html('');
		$('.query-error').html(msg);
	};
})(jQuery);