<?php

class SQLExplorerQueryField extends TextAreaField {
	
	// Template uses this to determine whether to display warnings to the user or not.
	public function isProduction() {
		return Director::isLive();
	}
}