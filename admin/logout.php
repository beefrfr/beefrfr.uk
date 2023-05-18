<?php
	require_once($_SERVER['DOCUMENT_ROOT'] . '/libraries/requires.php');

	$user = new User();
	$user->logout();
	header("Location: login")
?>