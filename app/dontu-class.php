<?php
/**
 * The Main Class
 * Contains Basic Logic and Methods for the app.
 *
 * @package dontu
 */
class Dontu {

	public $username;
	private $db;
	private $is_logged = false;
	private $token;

    /**
     * Dontu constructor. Constructor Method for Class
     * Initialises DB Connection and tries auto login.
     *
     * @since   1.0.0
     * @access  public
     */
	public function __construct() {
		$this->db = new Database();
		if( isset( $_COOKIE['dontu_token'] ) ) {
			$this->token = $_COOKIE['dontu_token'];
			$this->is_logged = $this->try_auto_login();
			if( $this->is_logged ) {
				$this->username = $this->db->get_user_from_token( $this->token );
			}
		} else {
			$this->token = '';
			$this->is_logged = false;
		}
	}

    /**
     * Method for switching request for CRUD operations.
     * CRUD ( Create Read Update Delete )
     *
     * @since   1.0.0
     * @access  public
     * @param   array   $post    Post data to be processed.
     * @return string
     */
	public function do_post_request( $post ) {
		$result = array();
		if( isset( $post['name'] ) && $post['name'] == 'new-section-name' ) {
			$array_data = array(
				'name' => str_replace( ' ', '-', strtolower( trim( $post['value'] ) ) ),
				'value' => $post['value'],
			);
			if ( $this->db->insert_section( $array_data ) ) {
				$result['type'] = 'success';
				$result['message'] = 'Sectiune creata cu success!';
			} else {
				$result['type'] = 'error';
				$result['message'] = 'Nu am putut crea sectiunea.';
			}
		} else if ( isset( $post['name'] ) && $post['name'] == 'section-update' ) {
			$array_data = array(
				'name' => str_replace( ' ', '-', strtolower( trim( $post['value'] ) ) ),
				'value' => $post['value'],
			);
			if ( $this->db->update_section( $array_data, $post['pk'] ) ) {
				$result['type'] = 'success';
				$result['message'] = 'Sectiune modificata cu success!';
			} else {
				$result['type'] = 'error';
				$result['message'] = 'Nu am putut modifica sectiunea.';
			}
		} else if ( isset( $post['name'] ) && $post['name'] == 'new-sub-section' ) {
			$array_data = array(
				'id_main' => $post['value']['parent_id'],
				'name' => str_replace( ' ', '-', strtolower( trim( $post['value']['title'] ) ) ),
				'title' => trim( $post['value']['title'] ),
				'value' => trim( $post['value']['content'] ),
			);
			if ( $this->db->insert_subsection( $array_data ) ) {
				$result['type'] = 'success';
				$result['message'] = 'Sub-sectiune creata cu success!';
			} else {
				$result['type'] = 'error';
				$result['message'] = 'Nu am putut crea sub-sectiunea.';
			}
		} else if ( isset( $post['name'] ) && $post['name'] == 'edit-sub-section' ) {
			$array_data = array(
				'name' => str_replace( ' ', '-', strtolower( trim( $post['value']['title'] ) ) ),
				'title' => trim( $post['value']['title'] ),
				'value' => trim( $post['value']['content'] ),
			);
			if ( $this->db->update_subsection( $array_data, $post['pk'] ) ) {
				$result['type'] = 'success';
				$result['message'] = 'Sub-sectiune modificata cu success!';
			} else {
				$result['type'] = 'error';
				$result['message'] = 'Nu am putut modifica sub-sectiunea.';
			}
		} else if ( isset( $post['name'] ) && $post['name'] == 'delete-sub-section' ) {
			$conditions_array = array(
				'id' => $post['id']
			);
			if ( $this->db->delete_subsection( $conditions_array ) ) {
				$result['type'] = 'success';
				$result['message'] = 'Sub-sectiune stearsa cu success!';
			} else {
				$result['type'] = 'error';
				$result['message'] = 'Nu am putut sterge sub-sectiunea.';
			}
		} else if ( isset( $post['name'] ) && $post['name'] == 'delete-section' ) {
			$conditions_array_sub = array(
				'id_main' => $post['id']
			);
			$conditions_array = array(
				'id' => $post['id']
			);
			if ( $this->db->delete_section( $conditions_array_sub, $conditions_array ) ) {
				$result['type'] = 'success';
				$result['message'] = 'Sectiune si sub sectiuni sterse cu success!';
			} else {
				$result['type'] = 'error';
				$result['message'] = 'Nu am putut sterge sectiunea si subsectiunile.';
			}
		} else if ( isset( $post['name'] ) && $post['name'] == 'edit-title' ) {
			$array_data = array(
				'name' => $post['name'],
				'value' => $post['value'],
			);
			$res = $this->save_layout( $array_data, $post['pk'] );
			if( $res ) {
				$result['type'] = 'success';
				$result['message'] = 'Titlu modificat cu success!';
				$result['new_value'] = $post['value'];
			} else {
				$result['type'] = 'error';
				$result['message'] = 'Nu am putut modifica titlul!';
			}
		} else if ( isset( $post['name'] ) && $post['name'] == 'edit-sub-title' ) {
			$array_data = array(
				'name' => $post['name'],
				'value' => $post['value'],
			);
			$res = $this->save_layout( $array_data, $post['pk'] );
			if( $res ) {
				$result['type'] = 'success';
				$result['message'] = 'Subtitlu modificat cu success!';
				$result['new_value'] = $post['value'];
			} else {
				$result['type'] = 'error';
				$result['message'] = 'Nu am putut modifica subtitlul!';
			}
		} else if ( isset( $post['name'] ) && $post['name'] == 'make-history' ) {
			$vagrant = new Vagrant();
			$new_results = $vagrant->get_new_data();
			$array_data = array();
			foreach ( $new_results['win10'] as $index => $value ) {
				if( $index == 'cpu' ) {
					$array_data['cpu_win'] = ( isset( $value ) && $value > 0 ) ? $value : 0;
				}
				if( $index == 'mem' ) {
					$array_data['mem_win'] = ( isset( $value ) && $value > 0 ) ? $value : 0;
				}
				if( $index == 'disk' ) {
					$array_data['dsk_win'] = ( isset( $value ) && $value > 0 ) ? $value : 0;
				}
			}
			foreach ( $new_results['win10'] as $index => $value ) {
				if( $index == 'cpu' ) {
					$array_data['cpu_ubt'] = ( isset( $value ) && $value > 0 ) ? $value : 0;
				}
				if( $index == 'mem' ) {
					$array_data['mem_ubt'] = ( isset( $value ) && $value > 0 ) ? $value : 0;
				}
				if( $index == 'disk' ) {
					$array_data['dsk_ubt'] = ( isset( $value ) && $value > 0 ) ? $value : 0;
				}
			}
			$array_data['read_date'] = date( 'Y-m-d H:i:s' );
			$res = $this->save_history( $array_data );
			if( $res ) {
				$result['type'] = 'success';
				$result['message'] = 'History adaugat cu success';
			} else {
				$result['type'] = 'error';
				$result['message'] = 'Nu am putut adauga history!';
			}
		} else if ( isset( $post['name'] ) && $post['name'] == 'mock-history' ) {
			$array_data = array();
			$array_data['cpu_win'] = mt_rand (1.5*10, 75*10) / 10;
			$array_data['mem_win'] = mt_rand (3.5*10, 75*10) / 10;
			$array_data['dsk_win'] = mt_rand (1.8*10, 95*10) / 10;

			$array_data['cpu_ubt'] = mt_rand (0.4*10, 41.5*10) / 10;
			$array_data['mem_ubt'] = mt_rand (0.3*10, 51.5*10) / 10;
			$array_data['dsk_ubt'] = mt_rand (0.1*10, 41.5*10) / 10;

			$array_data['read_date'] = date( 'Y-m-d H:i:s' );
			$res = $this->save_history( $array_data );
			if( $res ) {
				$result['type'] = 'success';
				$result['message'] = 'History adaugat cu success';
			} else {
				$result['type'] = 'error';
				$result['message'] = 'Nu am putut adauga history!';
			}
		} else {
			$result['type'] = 'error';
			$result['message'] = 'Nu am putut procesa cererea!';
		}
		return json_encode( $result );
	}

	/**
	 * Method to save history data.
	 *
	 * @since   1.0.0
	 * @access  public
	 * @param   array   $array_data Data to be stored.
	 * @return bool
	 */
	public function save_history( $array_data ) {
		$db = $this->db;
		return $db->insert_history( $array_data );
	}

    /**
     * Method for saving the layout.
     * It updates the layout if an id is passed.
     *
     * @since   1.0.0
     * @access  public
     * @param   array   $array_data Data to be stored.
     * @param   integer $id         The ID (*optional).
     * @return bool|mixed
     */
	public function save_layout( $array_data, $id ) {
		$db = $this->db;
		if( $id == 0 ) {
			return $db->insert_layout( $array_data );
		} else {
			return $db->update_layout( $array_data, $id );
		}
	}

    /**
     * Return logged status. (True | False)
     *
     * @since   1.0.0
     * @access  public
     * @return bool
     */
	public function is_logged() {
		return $this->is_logged;
	}

    /**
     * Method to try login.
     *
     * @since   1.0.0
     * @access  public
     * @param   string  $username   The username.
     * @param   string  $pass       The password.
     * @return bool
     */
	public function login( $username, $pass ) {
		$db = $this->db;
		if ( $db->try_login( trim( $username ), trim( $pass ) ) ) {
			$this->username = $username;
			$this->is_logged = true;
		}
		return false;
	}

    /**
     * Method to log out.
     *
     * @since   1.0.0
     * @access  public
     * @param   string  $username   The username to be logged out.
     */
	public function logout( $username ) {
		$db = $this->db;
		$this->is_logged = false;
		$db->logout( trim( $username ) );
	}

    /**
     * Method to do auto login if token is set.
     *
     * @since   1.0.0
     * @access  private
     * @return bool
     */
	private function try_auto_login() {
		$db = $this->db;
		if ( $db->check_token( $this->token ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Method to retrieve history data.
	 *
	 * @since   1.0.0
	 * @access  public
	 * @param   string  $search_q    The search query.
	 * @return array
	 */
	public function get_history() {
		$db = $this->db;
		return $db->get_history_data();
	}

    /**
     * Method to retrieve main sections based on search query.
     *
     * @since   1.0.0
     * @access  public
     * @param   string  $search_q    The search query.
     * @return array
     */
	public function get_main_sections( $search_q = '' ) {
		$db = $this->db;
		return $db->get_main_sections( $search_q );
	}

    /**
     * Method for retrieving subsections of specific section ID based on search query.
     *
     * @since   1.0.0
     * @access  public
     * @param   integer $id         The section ID.
     * @param   string  $search_q   The seach query.
     * @return array
     */
	public function get_sub_sections( $id, $search_q ) {
		$db = $this->db;
		return $db->get_sub_sections( $id, $search_q );
	}

    /**
     * Method for retrieving the layoud data.
     *
     * @since   1.0.0
     * @access  public
     * @return array
     */
	public function get_layout_data() {
		$db = $this->db;
		$result = $db->get_layout_data();
		$layout_data = array();
		foreach ( $result as $row ) {
			$layout_data[ $row['name'] ] = array(
				'id' => $row['id'],
				'value' => $row['value'],
			);
		}
		return $layout_data;
	}
}