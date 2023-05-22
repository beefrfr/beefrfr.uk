<?php
	require_once($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php');
	use Symfony\Component\Yaml\Yaml;
	class DB {
		private static $instance;
		private $host;
		private $db;
		private $username;
		private $password;
		private $connection;
		public $dieOnError;

		private function __construct($dieOnError = false) {
			$this->dieOnError = $dieOnError;
			$dbYaml = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/config/db.yml');
			$dbCredentials = Yaml::parse($dbYaml);
			$dbKeys = ['database_host', 'database_username', 'database_password', 'database_name'];
			if (count(array_diff($dbKeys, array_keys($dbCredentials))) == 0) {
				$this->host	= $dbCredentials['database_host'];
				$this->db	= $dbCredentials['database_name'];
				$this->username	= $dbCredentials['database_username'];
				$this->password	= $dbCredentials['database_password'];
				$this->connect();
			}
		}

		public static function getInstance($dieOnError = false) {
			if (self::$instance === null) {
				self::$instance = new self($dieOnError);
			}
			return self::$instance;
		}

		public function setDieOnError($dieOnError) {
			$this->dieOnError = $dieOnError;
		}

		public function connect() {
			$this->connection = new mysqli($this->host, $this->username, $this->password, $this->db);
			if ($this->connection->connect_error) {
				if ($this->dieOnError) {
					die($this->connection->connect_error);
				}
				return;
			}
		}

		public function connected() {
			return $this->connection->ping();
		}

		public function disconnect() {
			if ($this->connected()) {
				$this->connection->close();
			}
		}

		public function query($sql, $params = []) {
			$statement = $this->connection->prepare($sql);
			if (!$this->connected()) {
				if ($this->dieOnError) {
					die('DB not connected');
				}
				return false;
			} else if (!$statement) {
				if ($this->dieOnError) {
					die($this->connection->error);
				}
				return false;
			}

			if (!empty($params)) {
				$types = '';
				$preparedParams = [];
				foreach ($params as $param) {
					if (is_int($param)) {
						$types .= 'i';
					} elseif (is_float($param)) {
						$types .= 'd';
					} else {
						$types .= 's';
					}
					$preparedParams[] = $this->connection->real_escape_string($param);
				}

				$statement->bind_param($types, ...$preparedParams);
			}

			$statement->execute();
			$result = $statement->get_result();
			$statement->close();

			if (!$result) {
				if ($this->dieOnError) {
					die($this->connection->error);
				}
				return false;
			}

			return $result;
		}
	}
?>