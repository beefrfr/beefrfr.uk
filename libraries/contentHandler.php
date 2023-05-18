<?php
	require_once($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/libraries/db.php');
	
	class ContentHandler {
		protected $db;

		public $template;
		public $navTemplate;

		public function __construct() {
			$this->db = DB::getInstance();
			$this->loadTemplates();
			$this->parsedown = new Parsedown();
			$this->parsedown->setBreaksEnabled(true);
			$this->parsedown->setSafeMode(true);
		}

		protected function loadTemplates() {
			$this->template = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/templates/template.html');
			$this->navTemplate = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/templates/navtemplate.html');
		}

		protected function getSetting($setting) {
			if (!$this->db->connected()) {
				return false;
			}
			$settingsResult = $this->db->query("SELECT * FROM `settings` WHERE `setting` = ?", [$setting]);
			if ($settingsResult->num_rows != 1) {
				return false;
			}
			$setting = $settingsResult->fetch_assoc();
			return $setting['value'];
		}

		protected function getSettings() {
			$navbarsticky = $this->getSetting("navbarsticky") == '1' ? "sticky-top" : "";
			$darkmode = $this->getSetting("darkmode") == '1';
			$navbardark = $darkmode ? "navbar-dark bg-dark" : "navbar-light bg-primary";
			$backgroundcolor = $darkmode ? "333" : "ddd";
			$textcolor = $darkmode ? "fff" : "111";
			$websitetitle = $this->getSetting("websitetitle");
			return Array(
				"navbarsticky" => $navbarsticky,
				"darkmode" => $darkmode,
				"navbardark" => $navbardark,
				"backgroundcolor" => $backgroundcolor,
				"textcolor" => $textcolor,
				"websitetitle" => $websitetitle
			);
		}

		protected function generateNavBar($pageUrl) {
			if (!$this->db->connected()) {
				return false;
			}
			$navBar = "";
			$navResult = $this->db->query("SELECT * FROM `navbar` WHERE `visible`='1' ORDER BY `navOrder` ASC");
			while ($nav = $navResult->fetch_assoc()) {
				$active = "";
				$current = "";
				if (strtolower($nav['url']) == $pageUrl) {
					$active = "active";
					$current = '<span class="sr-only">(current)</span>';
				}
				$currentNav = $this->navTemplate;
				$currentNav = str_replace('$link', $nav['url'], $currentNav);
				$currentNav = str_replace('$name', $nav['name'], $currentNav);
				$currentNav = str_replace('$current', $current, $currentNav);
				$currentNav = str_replace('$active', $active, $currentNav);
				$navBar .= $currentNav;
			}
			return $navBar;
		}

		protected function loadPage($pageUrl) {
			$page = Array(
				"tabTitle" => "Unknown Page",
				"pageTitle" => "",
				"content" => ""
			);
			if (!$this->db->connected()) {
				$page["content"] = "Unknown page - " . $pageUrl;
				return $page;
			}
			$pagesResult = $this->db->query("SELECT * FROM `pages` WHERE `url` = ?", [$pageUrl]);
			if ($pagesResult->num_rows != 1) {
				$page["content"] = "Unknown page - " . $pageUrl;
				return $page;
			}

			while ($pageData = $pagesResult->fetch_assoc()) {
				$page["pageTitle"] = $pageData["pageTitle"];
				$page["tabTitle"] = $pageData["tabTitle"];

				$contentResult = $this->db->query("SELECT * FROM `content` WHERE `page` = ? ORDER BY `contentOrder` ASC", [$pageData['id']]);
				while ($content = $contentResult->fetch_assoc()) {
					$currentContent = "";
					if ($content['contentType'] == "markdown") {
						$currentContent .= sprintf("%s", $this->parsedown->text($content['content']));
					} else if ($content['contentType'] == "html") {
						$currentContent .= sprintf("%s", $content['content']);
					} else if ($content['contentType'] == "image") {
						if ($content['imageWidthType'] == "col") {
							$currentContent .= sprintf("<div class='row'><img src='%s' alt='%s' style='height:auto !important;' class='col-%s'></div>", $content['content'], $content['imgAltText'], $content['imageWidth']);
						} else if ($content['imageWidthType'] == "pixels") {
							$currentContent .= sprintf("<div class='row'><img src='%s' alt='%s' style='height:auto !important;width: %spx'></div>", $content['content'], $content['imgAltText'], $content['imageWidth']);
						}
					}

					$page["content"] .= sprintf("<div id='%s' class='col-%s col-md-%s col-lg-%s'>%s&nbsp;</div>", $content['name'], $content['smallWidth'], $content['mediumWidth'], $content['largeWidth'], $currentContent);
				}
				
			}
			return $page;
		}

		public function getPage($pageUrl) {
			$settings = $this->getSettings();
			$navBar = $this->generateNavBar($pageUrl);
			$page = $this->loadPage($pageUrl);

			$websiteTitle = $settings["websitetitle"];
			if ($page["pageTitle"] != "") {
				$websiteTitle = $page["pageTitle"];
			}

			return $this->prepareTemplate($page["tabTitle"], $websiteTitle, $navBar, $settings, $page["content"]);
		}

		protected function prepareTemplate($tabTitle, $websiteTitle, $navBar, $settings, $content) {
			$template = $this->template;
			$template = str_replace('$tabTitle', $tabTitle, $template);
			$template = str_replace('$websiteTitle', $websiteTitle, $template);
			$template = str_replace('$menuItems', $navBar, $template);
			$template = str_replace('$navbarsticky', $settings["navbarsticky"], $template);
			$template = str_replace('$navbardark', $settings["navbardark"], $template);
			$template = str_replace('$backgroundcolor', $settings["backgroundcolor"], $template);
			$template = str_replace('$textcolor', $settings["textcolor"], $template);
			$template = str_replace('$content', $content, $template);
			return $template;
		}
	}
?>