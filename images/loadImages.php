<?php
	if (!isset($_GET['id'])) {
		echo 'here';
		header('Location: /');
	}
	echo $_GET['id'];
	/*
		In here use the ID to access the images DB, ID will be the image row ID
	*/
?>