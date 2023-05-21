<?php
	require_once($_SERVER['DOCUMENT_ROOT'] . '/libraries/db.php');
	if (session_status() === PHP_SESSION_NONE) {
		session_start();
	}
	class User {
		private $user;
		private $loggedIn;

		private $db;

		private $table;

		public function __construct($table='users') {
			$this->db = DB::getInstance();

			$this->table = $table;
			if (!($this->db->connected() && $this->issetSession('loggedinuserid') && $this->issetSession('loggedinhash') && $this->issetSession('loggedin'))) {
				$this->logout();
			} else {
				$this->loggedIn = $this->checkLogin();
			}
		}

		public function issetSession($id) {
			$sessionID = $this->table . $id;
			return isset($_SESSION[$sessionID]);
		}

		public function getSession($id) {
			$sessionID = $this->table . $id;
			if (!isset($_SESSION[$sessionID])) {
				return false;
			}
			return $_SESSION[$sessionID];
		}

		private function setSession($id, $value) {
			$sessionID = $this->table . $id;
			$_SESSION[$sessionID] = $value;
		}

		private function unsetSession($id) {
			$sessionID = $this->table . $id;
			unset($_SESSION[$sessionID]);
		}

		public function loggedIn() {
			return $this->loggedIn;
		}

		public function logout() {
			$this->unsetSession('loggedinuserid');
			$this->unsetSession('loggedinhash');
			$this->unsetSession('loggedin');
			$this->user = NULL;
			$this->loggedIn = false;
		}

		public function checkLogin() {
			$checkHash = hash('sha512', $_SERVER['REMOTE_ADDR'] . $this->getSession('loggedinuserid'));
			if ($checkHash != $this->getSession('loggedinhash')) {
				$this->logout();
				return false;
			}
			$userResult = $this->db->query("SELECT * FROM `' . $this->table . '` WHERE `id` = ?", [$this->getSession('loggedinuserid')]);
			if ($userResult == false) {
				$this->logout();
				return false;
				return false;
			}
			if ($userResult->num_rows != 1) {
				$this->logout();
				return false;
			}
			$this->user = $userResult->fetch_assoc();
			return true;
		}

		public function login($username, $password) {
			$this->logout();
			$userResult = $this->db->query("SELECT * FROM `' . $this->table . '` WHERE `username` = ? OR `email` = ?", [$username, $username]);
			if ($userResult->num_rows != 1) {
				$this->logout();
				return false;
			}
			while ($user = $userResult->fetch_assoc()) {
				if ($user['password'] == hash('sha512', $password . $user['salt'])) {
					$this->user = $user;
					$this->loggedIn = true;
					$this->setSession('loggedinuserid', $user['id']);
					$this->setSession('loggedinhash', hash('sha512', $_SERVER['REMOTE_ADDR'] . $user['id']));
					$this->setSession('loggedin', true);
					return true;
				}
			}
			return false;
		}

		private function generateSalt() {
			$characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+{}|:"<>?-=[]\;\',./~`';
			$length = rand(100, 255);
			$salt = '';

			for ($i = 0; $i < $length; $i++) {
				$salt .= $characters[rand(0, strlen($characters) - 1)];
			}

			return $salt;
		}

		public function createUser($username, $email, $password) {
			/* Check username and email are unique */
			$checkResult = $this->db->query("SELECT * FROM `' . $this->table . '` WHERE `username` = ? OR `email` = ?", [$username, $email]);
			if ($userResult->num_rows != 0) {
				while ($user = $checkResult->fetch_assoc()) {
					if ($user['email'] == $email) {
						return 1;
					}
					if ($user['username'] == $username) {
						return 2;
					}
				}
				return 2;
			}
			$salt = $this->generateSalt();
			$hashedPassword = hash('sha512', $password . $salt);
			$checkResult = $this->db->query("INSERT INTO `" . $this->table . "`(`username`, `email`, `password`, `salt`) VALUES (?','?','?','?')", [$username, $email, $hashedPassword, $salt]);
		}
	}

?>