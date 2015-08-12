<?php
 
/**
 * Class to handle all db operations
 * This class will have CRUD methods for database tables
 *
 * @author David Courtney
 * @param User status, 1, registered, 2 account verified
 * @param User active, 1 or 0
 */
class DbHandler {
 
  private $conn;

  function __construct() {
    require_once 'DbConnect.php';
    // opening db connection
    $db = new DbConnect();
    $this->conn = $db->connect();
  }

  /* ------------- `users` table methods ------------------ */

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
  public function checkLogin($ident, $password) {
    // fetching user by email
    $stmt = $this->conn->prepare("SELECT password_hash FROM users WHERE email = ? OR username = ?");
    $stmt->bind_param("ss", $ident, $ident);
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

  /***********************/

  /**
   * Fetching user by email or username
   * @param String $ident email or username
   */
  public function getUserByIdent($ident) {
    $stmt = $this->conn->prepare("SELECT id, name, email, username, api_key, status, created_at, validate_email, validate_count, reset_password, active FROM users WHERE email = ? OR username= ?");
    $stmt->bind_param("ss", $ident, $ident);
    if ($stmt->execute()) {
      $user = $stmt->get_result()->fetch_assoc();
      $stmt->close();
      return $user;
    } else {
      return NULL;
    }
  }  

  /**
   * Fetching user by email
   * @param String $email User email id
   */
  public function getUserByEmail($email) {
    $stmt = $this->conn->prepare("SELECT id, name, email, username, api_key, status, created_at, validate_email, validate_count, reset_password, active FROM users WHERE email = ?");
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
    $stmt = $this->conn->prepare("SELECT id, name, email, username, api_key, status, created_at, validate_email, validate_count, reset_password, active FROM users WHERE id = ?");
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
    $stmt = $this->conn->prepare("SELECT id, name, email, username, api_key, status, created_at, validate_email, validate_count, reset_password, active FROM users WHERE username = ?");
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
  * Checking to see if reset password is allowed 
  * @param String reset password identification
  * Returns user id or null
  **/
  public function checkResetPassword($ident) {
    $userid = substr($ident,0,1);
    $string = substr($ident,1);
    $sql = $this->conn->prepare("SELECT id FROM users WHERE id = ? AND reset_password = ?");
    $sql->bind_param("is", $userid, $string);
    if ($sql->execute()) {
      $user = $sql->get_result()->fetch_assoc();
      $sql->close();
      return $user;
    } else {
      return NULL;
    }
  }


  /**
  * Update the user
  * @param pass the $user you get from getUserByEmail or such function, 
  * Only put what we want in the query that we want the user to be able to update
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
      $update = $this->conn->prepare("UPDATE users SET name = ?, username = ?, email = ?, status = ?, validate_email = ?, validate_count = ?, reset_password = ?, active = ? WHERE id = ?");
      $update->bind_param("sssisssii", $user['name'], $user['username'], $user['email'], $user['status'], $user['validate_email'], $user['validate_count'], $user['reset_password'], $user['active'], $user['id']);    
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
  * Update the password
  * @param password that the user wants to update
  * 
  */
  public function updatePassword($id, $password) {
    require_once 'PassHash.php';
    $password_hash = PassHash::hash($password);
    $update = $this->conn->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
    $update->bind_param("si", $password_hash, $id);
    if ($update->execute()) {
      return USER_UPDATE_SUCCESSFUL;
    } else {
      return USER_UPDATE_FAILED;
    }
    $update->close(); 
  }

  /**
  * Validate the user
  * @param userid
  * @param validate_email
  */
  public function validateUser($userid, $validate_email) {
    $update = $this->conn->prepare("UPDATE users SET validate_email = 1, status = 2 WHERE id = ? AND validate_email = ?");
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

  /** ---------------- `mates` table methods ------------------- **/

  /**
  * Get a list of mates
  * @param userid 
  **/
  public function getMatesList($userid) {
    $sql = $this->conn->prepare("SELECT id, user_id, mate_id, email, nickname, date_added, bet_count, active_bet_count, amount_lost, currency, active FROM mates WHERE user_id = ? ORDER BY date_added DESC");
    $sql->bind_param("i", $userid);
    if ($sql->execute()) {
      $mates = $sql->get_result();
      $sql->close();
      return $mates;
    } else {
      return NULL;
    }
  }

  /**
  * Add a mate
  * @param name, email
  **/
  public function addMate($name, $email, $mate_id, $userid, $datetime) {
    $sql = $this->conn->prepare("INSERT INTO mates (user_id, mate_id, email, nickname, date_added, bet_count, active_bet_count, amount_lost, currency, active) VALUES (?, ?, ?, ?, ?, 0, 0, 0, 'GBP', 1)");
    //echo $sql;
    $sql->bind_param("iisss", $userid, $mate_id, $email, $name, $datetime);
    $result = $sql->execute();
    $sql->close();
    if ($result) {
      return TRUE;
    } else {
      return FALSE;
    }
  }


  /**
  * Get a mate by email
  **/
  public function getMateByEmail($userid, $email) {
    $sql = $this->conn->prepare("SELECT mate_id, email, nickname, date_added, bet_count, active_bet_count, amount_lost, currency, active FROM mates WHERE user_id = ? AND email = ?");
    $sql->bind_param("is", $userid, $email);
    if ($sql->execute()) {
      $mate = $sql->get_result()->fetch_assoc();
      $sql->close();
      return $mate;
    } else {
      return NULL;
    }
  }


  /**
  * Get a mate by nickname
  **/
  public function getMateByNickname($userid, $name) {
    $sql = $this->conn->prepare("SELECT mate_id, email, nickname, date_added, bet_count, active_bet_count, amount_lost, currency, active FROM mates WHERE user_id = ? AND nickname = ?");
    $sql->bind_param("is", $userid, $name);
    if ($sql->execute()) {
      $mate = $sql->get_result()->fetch_assoc();
      $sql->close();
      return $mate;
    } else {
      return NULL;
    }
  }

  /** 
  * Add a simple bet
  **/
  public function addSimpleBet($userid, $description, $name_id, $name, $prize, $datedue) {
    $sql = $this->conn->prepare("INSERT INTO bets (user_id, mate_name, title, prize, datedue) VALUES (?, ?, ?, ?, ?)");
    $sql->bind_param("issss", $userid, $name, $description, $prize, $datedue);
    if ($sql->execute()) {
      $betid = $this->conn->insert_id;
      if ($name_id>0) {
        $sql = $this->conn->prepare("INSERT INTO bet_mate (bet_id, mate_id) VALUES (?, ?)");
        $sql->bind_param("ii", $betid, $name_id);
        if ($sql->execute()) {
          return true;
          $sql->close();
        } else {
          return NULL;
        }
      }
    } else {
      return NULL;
    }
  }

  /**
  * Get a list of bets
  * @param userid 
  **/
  public function getBetsList($userid) {
    $sql = $this->conn->prepare("SELECT id, user_id, mate_name, title, prize, dateadded, datedue FROM bets WHERE user_id = ? ORDER BY dateadded DESC");
    $sql->bind_param("i", $userid);
    if ($sql->execute()) {
      $bets = $sql->get_result();
      $sql->close();
      return $bets;
    } else {
      return NULL;
    }
  }

  public function getBetOpponents() {
    return NULL;
  }



  /**
   * Generating random Unique MD5 String for user Api key
   */
  private function generateApiKey() {
    return md5(uniqid(rand(), true));
  }


}