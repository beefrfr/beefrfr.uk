<?php
	require_once($_SERVER['DOCUMENT_ROOT'] . '/libraries/requires.php');

	$page = rtrim(strtolower($_GET['page']), '/');

	$contentHandler = new ContentHandler();

	echo $contentHandler->getPage($page);
?>