<div class="sql-explorer-query-field">
	<textarea $AttributesHTML>$Value</textarea>
	<p>
		The SQL query must be a single SELECT statement. Table and column names should be enclosed in double quotes. Certain properties are
		removed from the result for security purposes.
	</p>
	<% if isProduction %>
		<p class="warning">
			It is recommended to test query execution before running potentially expensive queries on a production database.
		</p>
		<p class="warning">
			Consider the data extraction and communication policy of your organisation before running queries in production.
		</p>
	<% end_if %>
	<div>
		<button class="button action-execute">Execute</button>
		<button class="button action-export">Export</button>
	</div>
</div>