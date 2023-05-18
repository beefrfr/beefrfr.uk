<?php
	require_once($_SERVER['DOCUMENT_ROOT'] . '/libraries/requires.php');
	$user = new User();
	
	if (!$user->loggedIn()) {
		header("Location: login");
	}

	$page = Array(
		"tabTitle" => "Admin | Main",
		"pageTitle" => "Admin",
		"content" => "Logged in main page"
	);

?>