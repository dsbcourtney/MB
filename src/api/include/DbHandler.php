<?php
 
/**
 * Class to handle all db operations
 * This class will have CRUD methods for database tables
 *
 * @author David Courtney
 */
class DbHandler {
 
  private $conn;

  function __construct() {
    require_once 'DbConnect.php';
    // opening db connection
    $db = new DbConnect();
    $this->conn = $db->connect();
  }

  /* ------------- `users` table method ------------------ */

  /**
   * Creating new user
   * @param String $name User full name
   * @param String $email User login email id
   * @param String $password User login password
   */
  public function createUser($name, $email, $password, $username) {
    require_once 'PassHash.php';
    $response = array();

    $doCreate = true;
    // First check if user already existed in db
    if ($this->isUserExistsEmail($email)) {
      $doCreate = false;
      return USER_EMAIL_ALREADY_EXISTS;
    } elseif ($this->isUserExistsUsername($username)) {
      $doCreate = false;
      return USER_USERNAME_ALREADY_EXISTS;
    }

    if ($doCreate) {
      // Generating password hash
      $password_hash = PassHash::hash($password);

      // Generating API key
      $api_key = $this->generateApiKey();

      // insert query
      $stmt = $this->conn->prepare("INSERT INTO users(name, email, username, password_hash, api_key, status) values(?, ?, ?, ?, ?, 1)");
      $stmt->bind_param("sssss", $name, $email, $username, $password_hash, $api_key);
      $result = $stmt->execute();

      $stmt->close();

      // Check for successful insertion
      if ($result) {
        // User successfully inserted
        return USER_CREATED_SUCCESSFULLY;
      } else {
        // Failed to create user
        return USER_CREATE_FAILED;
      }
    }

    return $response;
  }

  /**
   * Checking user login
   * @param String $email User login email id
   * @param String $password User login password
   * @return boolean User login status success/fail
   */
  public function checkLogin($email, $password) {
    // fetching user by email
    $stmt = $this->conn->prepare("SELECT password_hash FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($password_hash);
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
      // Found user with the email
      // Now verify the password
      $stmt->fetch();
      $stmt->close();

      if (PassHash::check_password($password_hash, $password)) {
        // User password is correct
        return TRUE;
      } else {
        // user password is incorrect
        return FALSE;
      }
    } else {
      $stmt->close();

      // user not existed with the email
      return FALSE;
    }
  }

  /**
   * Checking for duplicate user by email address
   * @param String $email email to check in db
   * @param Integer $id if updating email we don't check the users row for the same email   
   * @return boolean
   */
  private function isUserExistsEmail($email, $id=0) {
    $stmt = $this->conn->prepare("SELECT id from users WHERE email = ? AND id <> ? ");
    $stmt->bind_param("si", $email, $id);
    $stmt->execute();
    $stmt->store_result();
    $num_rows = $stmt->num_rows;
    $stmt->close();
    return $num_rows > 0;
  }

  /**
   * Checking for duplicate user by username
   * @param String $username to check in db
   * @param Integer $id if updating username we don't check the users row for the same username
   * @return boolean
   */
  private function isUserExistsUsername($username, $id=0) {
    $stmt = $this->conn->prepare("SELECT id from users WHERE username = ? AND id <> ? ");
    $stmt->bind_param("si", $username, $id);
    $stmt->execute();
    $stmt->store_result();
    $num_rows = $stmt->num_rows;
    $stmt->close();
    return $num_rows > 0;
  }  

  /**
   * Fetching user by email
   * @param String $email User email id
   */
  public function getUserByEmail($email) {
    $stmt = $this->conn->prepare("SELECT id, name, email, username, api_key, status, created_at, validate_email, reset_password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    if ($stmt->execute()) {
      $user = $stmt->get_result()->fetch_assoc();
      $stmt->close();
      return $user;
    } else {
      return NULL;
    }
  }

  /**
   * Fetching user by id
   * @param Integer $id User 
   */
  public function getUserById($id) {
    $stmt = $this->conn->prepare("SELECT id, name, email, username, api_key, status, created_at, validate_email, reset_password FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
      $user = $stmt->get_result()->fetch_assoc();
      $stmt->close();
      return $user;
    } else {
      return NULL;
    }
  }

  /**
   * Fetching user by username
   * @param String $username User 
   */
  public function getUserByUsername($username) {
    $stmt = $this->conn->prepare("SELECT id, name, email, username, api_key, status, created_at, validate_email, reset_password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    if ($stmt->execute()) {
      $user = $stmt->get_result()->fetch_assoc();
      $stmt->close();
      return $user;
    } else {
      return NULL;
    }
  }  


  /**
  * Update the user
  * @param pass the $user you get from getUserByEmail or such function, 
  */
  public function updateUser($user) {

    $doUpdate = true;
    if ($this->isUserExistsUsername($user['username'], $user['id'])) {
      $doUpdate = false;
      return USER_USERNAME_ALREADY_EXISTS;
    } elseif ($this->isUserExistsEmail($user['email'], $user['id'])) {
      $doUpdate = false;
      return USER_EMAIL_ALREADY_EXISTS;
    } 

    if ($doUpdate) {
      $update = $this->conn->prepare("UPDATE users SET name = ?, username = ?, status = ?, validate_email = ?, reset_password = ? WHERE id = ?");
      $update->bind_param("ssissi", $user['name'], $user['username'], $user['status'], $user['validate_email'], $user['reset_password'], $user['id']);    
      // Check for successful update
      if ($update->execute()) {
        // User successfully inserted
        return USER_UPDATE_SUCCESSFUL;
      } else {
        // Failed to update user
        return USER_UPDATE_FAILED;
      }
      $update->close();

    }
  }

  /**
  * Validate the user
  * @param userid
  * @param validate_email
  */
  public function validateUser($userid, $validate_email) {
    $update = $this->conn->prepare("UPDATE users SET validate_email = 1 WHERE id = ? AND validate_email = ?");
    $update->bind_param("is", $userid, $validate_email);
    $update->execute();
    $result = $update->affected_rows;
    $update->close();
    if ($result==1) {
      return VALIDATION_SUCCESS;
    } else {
      return VALIDATION_FAILURE;
    }
    
  }

  /**
   * Fetching user api key
   * @param String $user_id user id primary key in user table
   */
  public function getApiKeyById($user_id) {
    $stmt = $this->conn->prepare("SELECT api_key FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    if ($stmt->execute()) {
      $api_key = $stmt->get_result()->fetch_assoc();
      $stmt->close();
      return $api_key;
    } else {
      return NULL;
    }
  }

  /**
   * Fetching user id by api key
   * @param String $api_key user api key
   */
  public function getUserId($api_key) {
    $stmt = $this->conn->prepare("SELECT id FROM users WHERE api_key = ?");
    $stmt->bind_param("s", $api_key);
    if ($stmt->execute()) {
      $user_id = $stmt->get_result()->fetch_assoc();
      $stmt->close();
      return $user_id;
    } else {
      return NULL;
    }
  }

  /**
   * Validating user api key
   * If the api key is there in db, it is a valid key
   * @param String $api_key user api key
   * @return boolean
   */
  public function isValidApiKey($api_key) {
    $stmt = $this->conn->prepare("SELECT id from users WHERE api_key = ?");
    $stmt->bind_param("s", $api_key);
    $stmt->execute();
    $stmt->store_result();
    $num_rows = $stmt->num_rows;
    $stmt->close();
    return $num_rows > 0;
  }

  /**
   * Generating random Unique MD5 String for user Api key
   */
  private function generateApiKey() {
    return md5(uniqid(rand(), true));
  }


}