<?php 
	require_once 'DbHandler.php';

	Class User extends DbHandler{
		// chuyền vào ApiKey check xem có tồn tại người dùng này không
		public function isValidApiKey($api_key) {
			$stmt = $this->conn->prepare("SELECT healthID from users WHERE api_key = ?");
			$stmt->bind_param("s", $api_key);
			$stmt->execute();
			$stmt->store_result();
			$num_rows = $stmt->num_rows;
			$stmt->close();
			return $num_rows > 0;
		}
		// select healthID ứng với ApiKey chuền vào 

	}
?>