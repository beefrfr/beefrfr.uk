<?php
	require_once($_SERVER['DOCUMENT_ROOT'] . '/libraries/requires.php');

	$page = Array(
		"tabTitle" => "Unknown Page",
		"pageTitle" => "",
		"content" => ""
	);

	$user = new User("admin_users");

	$user->login($_POST["u"], $_POST['p']);
	if ($user->loggedIn()) {
		header("Location: main");
	}
	header("Location: login");
?>