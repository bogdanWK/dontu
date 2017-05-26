<?php
/**
 * The Database Class
 * Contains Database Logic and Methods for
 * interacting with the MySQL DB.
 *
 * @package dontu
 */
class Database {
	private $dbhost;
	private $dbname;
	private $dbuser;
	private $dbpass;
	private $myc;

	/**
	 * Database constructor.
	 * Sets the class variables.
	 *
	 * @since   1.0.0
	 * @access  public
	 */
	public function __construct() {
		if( file_exists( ROOT . 'app/config.php' ) ) {
			require_once ROOT . 'app/config.php';
			$this->dbhost = $configs['database']['dbhost'];
			$this->dbname = $configs['database']['dbname'];
			$this->dbuser = $configs['database']['dbuser'];
			$this->dbpass = $configs['database']['dbpass'];
		} else {
			$this->dbhost = 'localhost';
			$this->dbname = '';
			$this->dbuser = '';
			$this->dbpass = '';
		}

		$this->myc = $this->connect();
	}

    /**
     * Creates a new user in DB.
     *
     * @since   1.0.0
     * @access  public
     * @param   string  $username   The username.
     * @param   string  $pass       The password.
     * @return string
     */
	public function add_user( $username, $pass ) {
		$hashed_pass = password_hash( $pass, PASSWORD_DEFAULT );
		$add_array = array(
			'username' => $username,
			'pass' => $hashed_pass,
		);

		if( $this->insert( 'users', $add_array ) ) {
			return 'User `' . $username . '` added!';
		} else {
			return 'Could NOT add user `' . $username . '`!';
		}
	}

    /**
     * Utility method for logout.
     * Un sets the token for the specified user.
     *
     * @since   1.0.0
     * @access  public
     * @param   string  $username   The username.
     * @return bool
     */
	public function logout( $username ) {
		$update_array = array(
			'token' => '',
		);
		$conditions = array(
			'username' => $username
		);
		return $this->update( 'users', $update_array, $conditions );
	}

    /**
     * Utility method for logging user in.
     * If successful sets a token for the logged user.
     *
     * @since   1.0.0
     * @access  public
     * @param   string  $username   The username.
     * @param   string  $pass       The password.
     * @return bool
     */
	public function try_login( $username, $pass ) {
		$result = $this->find( 'SELECT * FROM `users` WHERE `username` = "' . $this->myc->real_escape_string( $username ) . '"' );

		if( $this->myc->affected_rows > 0 ) {
			if ( password_verify( $pass ,$result[0]['pass'] ) ) {
				$token = base64_encode( $username . date('dmYH:i:s') );
				setcookie( 'dontu_token', $token, time() + (86400 * 30), '/' );
				$update_array = array(
					'token' => $token,
				);
				$conditions = array(
					'id' => $result[0]['id']
				);
				return $this->update( 'users', $update_array, $conditions );
			} else {
				return false;
			}
		}

		return false;
	}

    /**
     * Utility method to retrieve a user from a passed token.
     *
     * @since   1.0.0
     * @access  public
     * @param   string  $token  The token to filter by.
     * @return bool
     */
	public function get_user_from_token( $token ) {
		$result = $this->find( 'SELECT * FROM `users` WHERE `token` = "' . $this->myc->real_escape_string( $token ) . '"' );
		if( $this->myc->affected_rows == 1 ) {
			return $result[0]['username'];
		} else {
			return false;
		}
	}

    /**
     * Utility method to check token exists in DB.
     *
     * @since   1.0.0
     * @access  public
     * @param   string  $token  The token.
     * @return bool
     */
	public function check_token( $token ) {
		$result = $this->find( 'SELECT * FROM `users` WHERE `token` = "' . $this->myc->real_escape_string( $token ) . '"' );
		if( $this->myc->affected_rows == 1 ) {
			return $result[0]['token'];
		} else {
			return false;
		}
	}

	/**
	 * Opens a new connection to DB.
	 *
	 * @since   1.0.0
	 * @access  private
	 * @return bool|mysqli
	 * @throws Exception
	 */
	private function connect() {
		$myc = mysqli_connect( $this->dbhost, $this->dbuser, $this->dbpass, $this->dbname );
		if ( ! $myc ) {
			throw new Exception('Could NOT connect to Database. ' . mysqli_connect_error() . PHP_EOL );
		}
		return $myc;
	}

	/**
	 * Closes the connection to DB.
	 *
	 * @since   1.0.0
	 * @access  private
	 */
	private function disconnect() {
		mysqli_close( $this->myc );
	}

	private function delete( $table_name, $conditions ) {
		if ( isset( $table_name ) && $table_name != '' ) {
			if( isset( $conditions ) && ! empty( $conditions ) ) {
				$table_name = $this->myc->real_escape_string( $table_name );
				$conditions_values = array();
				foreach ( $conditions as $name => $value ) {
					$conditions_values[] = '`' . $this->myc->real_escape_string( $name ) . '`="' . $this->myc->real_escape_string( $value ) . '"';
				}
				$conditions_string = implode( ' AND ', $conditions_values );

				$sql = 'DELETE FROM ' . $table_name . ' WHERE ' . $conditions_string;

				if ( $this->myc->query( $sql ) ) {
					return true;
				} else {
					return false;
				}
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

    /**
     * Utility method to update a table data in DB.
     *
     * @since   1.0.0
     * @access  private
     * @param   string  $table_name     The name of the table to update.
     * @param   array   $assoc_array    The associative array of columns and data to be updated.
     * @param   array   $conditions     The associative array of conditions.
     * @return bool
     */
	private function update( $table_name, $assoc_array, $conditions ) {
		if ( isset( $table_name ) && $table_name != '' ) {
			if( isset( $assoc_array ) && ! empty( $assoc_array ) && isset( $conditions ) && ! empty( $conditions ) ) {
				$table_name = $this->myc->real_escape_string( $table_name );
				$set_values = array();
				$conditions_values = array();
				foreach ( $assoc_array as $name => $value ) {
					$set_values[] = '`' . $this->myc->real_escape_string( $name ) . '`="' . $this->myc->real_escape_string( $value ) . '"';
				}
				$set_values[] = '`modified`="' . $this->current_date() . '"';
				foreach ( $conditions as $name => $value ) {
					$conditions_values[] = '`' . $this->myc->real_escape_string( $name ) . '`="' . $this->myc->real_escape_string( $value ) . '"';
				}

				$set_string = implode( ', ', $set_values );
				$conditions_string = implode( ' AND ', $conditions_values );

				$sql = 'UPDATE ' . $table_name . ' SET ' . $set_string . ' WHERE ' . $conditions_string;
				//echo $sql;

				if ( $this->myc->query( $sql ) ) {
					return true;
				} else {
					return false;
				}
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

    /**
     * Utility method to insert a table data in DB.
     *
     * @since   1.0.0
     * @access  private
     * @param   string  $table_name     The name of the table to insert into.
     * @param   array   $assoc_array    The associative array of columns and data to be inserted.
     * @return bool
     */
	private function insert( $table_name, $assoc_array ) {
		if ( isset( $table_name ) && $table_name != '' ) {
			if( isset( $assoc_array ) && ! empty( $assoc_array ) ) {
				$table_name = $this->myc->real_escape_string( $table_name );
				$tmp_array_parts = array();
				foreach ( $assoc_array as $name => $value ) {
					$tmp_array_parts['fields'][] = '`' . $this->myc->real_escape_string( $name ) . '`';
					$tmp_array_parts['values'][] = '"' . $this->myc->real_escape_string( $value ) . '"';
				}

				$tmp_array_parts['fields'][] = '`created`';
				$tmp_array_parts['values'][] = '"' . $this->current_date() . '"';
				$tmp_array_parts['fields'][] = '`modified`';
				$tmp_array_parts['values'][] = '"' . $this->current_date() . '"';

				$fields = implode( ',', $tmp_array_parts['fields'] );
				$values = implode( ',', $tmp_array_parts['values'] );

				$sql = 'INSERT INTO ' . $table_name . ' (' . $fields . ') VALUES (' . $values . ')';
				//echo $sql;

				if ( $this->myc->query( $sql ) ) {
					return $this->myc->insert_id;
				} else {
					return false;
				}
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

    /**
     * Utility method to insert a new section.
     *
     * @since   1.0.0
     * @access  public
     * @param   array   $array_data The data to be inserted.
     * @return bool
     */
	public function insert_section( $array_data ) {
		return $this->insert( 'main_sections', $array_data );
	}

    /**
     * Utility method to insert a new subsection.
     *
     * @since   1.0.0
     * @access  public
     * @param   array   $array_data The data to be inserted.
     * @return bool
     */
	public function insert_subsection( $array_data ) {
		return $this->insert( 'sub_sections', $array_data );
	}

	/**
	 * Utility method to insert a new history entry.
	 *
	 * @since   1.0.0
	 * @access  public
	 * @param   array   $array_data The data to be inserted.
	 * @return bool
	 */
	public function insert_history( $array_data ) {
		return $this->insert( 'history', $array_data );
	}

    /**
     * Utility method to update a section.
     *
     * @since   1.0.0
     * @access  public
     * @param   array   $array_data The data to be updated.
     * @param   integer $id         The ID of the row in DB.
     * @return bool
     */
	public function update_section( $array_data, $id ) {
		$conditions = array(
			'id' => $id
		);
		return $this->update( 'main_sections', $array_data, $conditions );
	}

    /**
     * Utility method to update a subsection.
     *
     * @since   1.0.0
     * @access  public
     * @param   array   $array_data The data to be updated.
     * @param   integer $id         The ID of the row in DB.
     * @return bool
     */
	public function update_subsection( $array_data, $id ) {
		$conditions = array(
			'id' => $id
		);
		return $this->update( 'sub_sections', $array_data, $conditions );
	}

	/**
	 * Utility method to delete history.
	 *
	 * @since   1.0.0
	 * @access  public
	 * @param   array   $conditions_array   The conditions to filter by.
	 * @return bool
	 */
	public function delete_history( $conditions_array ) {
		return $this->delete( 'history', $conditions_array );
	}

    /**
     * Utility method to delete subsection.
     *
     * @since   1.0.0
     * @access  public
     * @param   array   $conditions_array   The conditions to filter by.
     * @return bool
     */
	public function delete_subsection( $conditions_array ) {
		return $this->delete( 'sub_sections', $conditions_array );
	}

    /**
     * Utility method to delete section.
     *
     * @since   1.0.0
     * @access  public
     * @param   array   $conditions_array_sub   The conditions to filter by subsections.
     * @param   array   $conditions_array   The conditions to filter by sections.
     * @return bool
     */
	public function delete_section( $conditions_array_sub, $conditions_array ) {
		$sub_res = $this->delete( 'sub_sections', $conditions_array_sub );
		$main_res = $this->delete( 'main_sections', $conditions_array );
		if( $sub_res && $main_res ) {
			return true;
		} else {
			return false;
		}
	}

    /**
     * Utility method to insert into layout.
     *
     * @since   1.0.0
     * @access  public
     * @param   array   $array_data The data to be inserted.
     * @return bool
     */
	public function insert_layout( $array_data ) {
		return $this->insert( 'layout_data', $array_data );
	}

    /**
     * Utility method to update a layout.
     *
     * @since   1.0.0
     * @access  public
     * @param   array   $array_data The data to be updated.
     * @param   integer $id         The ID of the row from DB.
     * @return bool
     */
	public function update_layout( $array_data, $id ) {
		$conditions = array(
			'id' => $id
		);
		return $this->update( 'layout_data', $array_data, $conditions );
	}

	/**
	 * Utility method to retrieve the history data from DB.
	 *
	 * @since   1.0.0
	 * @access  public
	 * @param   integer $limit  The rows limit. (*optional. Default 100)
	 * @return array
	 */
	public function get_history_data( $limit = 100 ) {
		$sql = '
			SELECT * FROM `history` 
			ORDER BY `read_date` DESC
			LIMIT '. $limit;
		return $this->find( $sql );
	}

    /**
     * Utility method to retrieve the layout data from DB.
     *
     * @since   1.0.0
     * @access  public
     * @param   integer $limit  The rows limit. (*optional. Default 100)
     * @return array
     */
	public function get_layout_data( $limit = 100 ) {
		$sql = 'SELECT * FROM `layout_data` LIMIT '. $limit;
		return $this->find( $sql );
	}

    /**
     * Utility method to retrieve sections from DB.
     *
     * @since   1.0.0
     * @access  public
     * @param   string  $query  The query to filter by.
     * @param   integer $limit  The rows limit. (*optional. Default 100)
     * @return array
     */
	public function get_main_sections( $query='', $limit = 100 ) {
		$search = ' ORDER BY `created` ASC ';
		if( isset( $query ) && $query != '' && strlen( $query ) > 3 ) {
			$search = ' WHERE `id` IN ( SELECT `id_main` FROM `sub_sections` WHERE MATCH(`value`) AGAINST("' . $query . '" IN BOOLEAN MODE) ) ';
		}
		$sql = 'SELECT * FROM `main_sections` ' . $search . ' LIMIT '. $limit;
		return $this->find( $sql );
	}

    /**
     * Utility method to retrieve subsections from DB.
     *
     * @since   1.0.0
     * @access  public
     * @param   integer $section_id  The section ID.
     * @param   string  $query       The query to filter by.
     * @param   integer $limit       The rows limit. (*optional. Default 100)
     * @return array
     */
	public function get_sub_sections( $section_id, $query='', $limit = 100 ) {
		$search = ' ORDER BY `created` ASC ';
		if( isset( $query ) && $query != '' && strlen( $query ) > 3 ) {
			$search = ' AND MATCH(`value`) AGAINST("' . $query . '" IN BOOLEAN MODE) ';
		}
		$sql = 'SELECT * FROM `sub_sections` WHERE `id_main` = "' . $section_id . '" ' . $search . ' LIMIT '. $limit;
		return $this->find( $sql );
	}

    /**
     * Utility method to fetch data from DB
     *
     * @since   1.0.0
     * @access  private
     * @param   string  $sql    The sql query to run.
     * @return array
     */
	private function find( $sql ) {
		$res_array = array();
		//echo $sql;
		$result = $this->myc->query( $sql );
		while ( $row = $result->fetch_assoc() ) {
			$res_array[] = $row;
		}
		$result->free();
		//var_dump( $res_array );
		return $res_array;
	}

    /**
     * Utility method to return NOW() date.
     *
     * @since   1.0.0
     * @access  private
     * @return false|string
     */
	private function current_date() {
		return date('Y-m-d H:i:s');
	}
}