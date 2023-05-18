<?php
	if (session_status() === PHP_SESSION_NONE) {
		session_start();
	}
	require_once($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/libraries/db.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/libraries/user.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/libraries/admin/adminContentHandler.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/libraries/contentHandler.php');
?>