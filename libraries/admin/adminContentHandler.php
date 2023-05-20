<?php
	require_once($_SERVER['DOCUMENT_ROOT'] . '/libraries/contentHandler.php');
	class AdminContentHandler extends ContentHandler {
		protected $adminPages;

		public function __construct() {
			parent::__construct();
			$this->adminPages = array(
				"main" => "main.php",
				"login" => "login.php",
				"handleLogin" => "loginHandler.php",
				"logout" => "logout.php",
				"pages" => "pages.php",
				"settings" => "settings.php",
				"analytics" => "analytics.php"
			);
		}

		protected function getUser() {
			$this->user = new User("admin_users");
		}

		public function getPage($pageUrl) {
			$page = Array(
				"tabTitle" => "Failed to load",
				"pageTitle" => "",
				"content" => "The content failed to load"
			);
			if (array_key_exists($pageUrl, $this->adminPages)) {
				$pageAbsolutePath = $_SERVER['DOCUMENT_ROOT'] . "/admin/" . $this->adminPages[$pageUrl];
				if (file_exists($pageAbsolutePath)) {
					include $pageAbsolutePath;
				} else {
					header("Location: login");
				}
			} else {
				header("Location: login");
			}

			$settings = $this->getSettings();
			$navBar = $this->generateNavBar($pageUrl);

			$websiteTitle = "Admin";
			if ($page["pageTitle"] != "") {
				$websiteTitle = $page["pageTitle"];
			}

			return $this->prepareTemplate("/admin", $page["tabTitle"], $websiteTitle, $navBar, $settings, $page["content"]);
		}

		public function generateNavBar($pageUrl) {
			if ($this->user->loggedIn()) {
				$nav = Array(
					Array(
						"name" => "Main",
						"url" => "main"
					),
					Array(
						"name" => "Pages",
						"url" => "pages"
					),
					Array(
						"name" => "Settings",
						"url" => "settings"
					),
					Array(
						"name" => "Analytics",
						"url" => "analytics"
					)
				);
				$navBar = "";
				foreach ($nav as $navItem) {
					$active = "";
					$current = "";
					if (strtolower($navItem['url']) == $pageUrl) {
						$active = "active";
						$current = '<span class="sr-only">(current)</span>';
					}
					$navBar .= $this->navTemplate->prepare([
						["key" => '$link', "value" => $navItem['url']],
						["key" => '$name', "value" => $navItem['name']],
						["key" => '$current', "value" => $current],
						["key" => '$active', "value" => $active]
					]);
				}
				return $navBar;
			} else {
				return "";
			}
		}
	}
?>