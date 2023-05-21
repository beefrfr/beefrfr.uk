<?php
	require_once($_SERVER['DOCUMENT_ROOT'] . '/libraries/requires.php');

	$page = rtrim(strtolower($_GET['page']), '/');

	$contentHandler = new ContentHandler();

	if ($page == '__index__') {
		$page = $contentHandler->getSettings()['rootpage'];
	}

	echo $contentHandler->getPage($page);
?>