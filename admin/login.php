<?php
	require_once($_SERVER['DOCUMENT_ROOT'] . '/libraries/requires.php');
	$user = new User("admin_users");

	/* TEMPORARY get variable login */
	if (isset($_GET["u"]) && isset($_GET["p"])) {
		$user->login($_GET["u"], $_GET["p"]);
	}

	if ($user->loggedIn()) {
		header("Location: main");
	}

	$page = Array(
		"tabTitle" => "Admin | Log in",
		"pageTitle" => "Admin",
		"content" => "Log in page"
	);

?>