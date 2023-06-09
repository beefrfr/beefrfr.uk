<?php
	require_once($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/libraries/db.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/libraries/templateHandler.php');
	
	class ContentHandler {
		protected $db;

		public $template;
		public $navTemplate;

		protected $user;

		public function __construct() {
			$this->db = DB::getInstance();
			$this->loadTemplates();
			$this->parsedown = new Parsedown();
			$this->parsedown->setBreaksEnabled(true);
			$this->parsedown->setSafeMode(true);
			$this->getUser();
		}

		protected function getUser() {
			$this->user = new User('users');
		}

		protected function loadTemplates() {
			$this->template = new TemplateHandler('/templates/template.html');
			$this->navTemplate = new TemplateHandler('/templates/navtemplate.html');
			$this->navItemTemplate = new TemplateHandler('/templates/navitemtemplate.html');
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

		public function getSettings() {
			$navbarsticky = $this->getSetting('navbarsticky') == '1' ? 'sticky-top' : '';
			$darkmode = $this->getSetting('darkmode') == '1';
			$navbardark = $darkmode ? 'navbar-dark bg-dark' : 'navbar-light bg-primary';
			$backgroundcolor = $darkmode ? '333' : 'ddd';
			$textcolor = $darkmode ? 'fff' : '111';
			$websitetitle = $this->getSetting('websitetitle');
			$rootpage = $this->getSetting('rootpage');
			return Array(
				'navbarsticky' => $navbarsticky,
				'darkmode' => $darkmode,
				'navbardark' => $navbardark,
				'backgroundcolor' => $backgroundcolor,
				'textcolor' => $textcolor,
				'websitetitle' => $websitetitle,
				'rootpage' => $rootpage
			);
		}

		protected function generateNavBar($pageUrl, $websiteTitle) {
			if (!$this->db->connected()) {
				return false;
			}
			$navBar = '';
			$navResult = $this->db->query("SELECT * FROM `navbar` WHERE `visible`='1' ORDER BY `navOrder` ASC");
			while ($nav = $navResult->fetch_assoc()) {
				$active = '';
				$current = '';
				if (strtolower($nav['url']) == $pageUrl) {
					$active = 'active';
					$current = '<span class="sr-only">(current)</span>';
				}
				$navBar .= $this->navItemTemplate->prepare([
					['key' => '$link', 'value' => $nav['url']],
					['key' => '$name', 'value' => $nav['name']],
					['key' => '$current', 'value' => $current],
					['key' => '$active', 'value' => $active]
				]);
			}
			if ($navBar != '') {
				return $this->navTemplate->prepare([
					['key' => '$menuItems', 'value' => $navBar],
					['key' => '$websiteTitle', 'value' => $websiteTitle]
				]);
			}
			return '';
		}

		protected function loadPage($pageUrl) {
			$page = Array(
				'tabTitle' => 'Unknown Page',
				'pageTitle' => '',
				'content' => ''
			);
			if (!$this->db->connected()) {
				$page['content'] = 'Unknown page - ' . $pageUrl;
				return $page;
			}
			$pagesResult = $this->db->query("SELECT * FROM `pages` WHERE `url` = ?", [$pageUrl]);
			if ($pagesResult->num_rows != 1) {
				$page['content'] = 'Unknown page - ' . $pageUrl;
				return $page;
			}

			while ($pageData = $pagesResult->fetch_assoc()) {
				$page['pageTitle'] = $pageData['pageTitle'];
				$page['tabTitle'] = $pageData['tabTitle'];

				$contentResult = $this->db->query("SELECT * FROM `content` WHERE `page` = ? ORDER BY `contentOrder` ASC", [$pageData['id']]);
				while ($content = $contentResult->fetch_assoc()) {
					$currentContent = '';
					if ($content['contentType'] == 'markdown') {
						$currentContent .= sprintf('%s', $this->parsedown->text($content['content']));
					} else if ($content['contentType'] == 'html') {
						$currentContent .= sprintf('%s', $content['content']);
					} else if ($content['contentType'] == 'image') {
						if ($content['imageWidthType'] == 'col') {
							$currentContent .= sprintf('<div class="row"><img src="%s" alt="%s" style="height:auto !important;" class="col-%s"></div>', $content['content'], $content['imgAltText'], $content['imageWidth']);
						} else if ($content['imageWidthType'] == 'pixels') {
							$currentContent .= sprintf('<div class="row"><img src="%s" alt="%s" style="height:auto !important;width: %spx"></div>', $content['content'], $content['imgAltText'], $content['imageWidth']);
						}
					}

					$page['content'] .= sprintf('<div id="%s" class="col-%s col-md-%s col-lg-%s">%s&nbsp;</div>', $content['name'], $content['smallWidth'], $content['mediumWidth'], $content['largeWidth'], $currentContent);
				}
				
			}
			return $page;
		}

		public function getPage($pageUrl) {
			$settings = $this->getSettings();
			$page = Array(
				'tabTitle' => 'Unknown Page',
				'pageTitle' => '',
				'content' => '',
				'navbar' => true
			);
			$pageAbsolutePath = $_SERVER['DOCUMENT_ROOT'] . '/custom/' . $pageUrl;
			if (file_exists($pageAbsolutePath . '.php')) {
				include $pageAbsolutePath . '.php';
			} else if (file_exists($pageAbsolutePath . '.html')) {
				$page['content'] = file_get_contents($pageAbsolutePath . '.html');
			} else {
				$page = $this->loadPage($pageUrl);
			}
			$websiteTitle = $settings['websitetitle'];
			if ($page['pageTitle'] != '') {
				$websiteTitle = $page['pageTitle'];
			}
			$navBar = '';
			if ($page['navbar']) {
				$navBar = $this->generateNavBar($pageUrl, $websiteTitle);
			}

			return $this->prepareTemplate($settings['rootpage'], $page['tabTitle'], $navBar, $settings, $page['content']);
		}

		protected function prepareTemplate($websiteRoot, $tabTitle, $navBar, $settings, $content) {
			return $this->template->prepare([
				['key' => '$websiteRoot', 'value' => $websiteRoot],
				['key' => '$tabTitle', 'value' => $tabTitle],
				['key' => '$navBar', 'value' => $navBar],
				['key' => '$navbarsticky', 'value' => $settings['navbarsticky']],
				['key' => '$navbardark', 'value' => $settings['navbardark']],
				['key' => '$backgroundcolor', 'value' => $settings['backgroundcolor']],
				['key' => '$textcolor', 'value' => $settings['textcolor']],
				['key' => '$content', 'value' => $content]
			]);
		}
	}
?>