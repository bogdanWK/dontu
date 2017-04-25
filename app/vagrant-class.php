<?php
/**
 * The Main Class
 * Contains Basic Logic and Methods for interacting with Vagrant.
 *
 * @package dontu
 */
class Vagrant {

    /**
     * Variable for enabeling verbouse mode.
     *
     * @var boolean $verbose
     */
    public $verbose;

    /**
     * Vagrant constructor. Constructor Method for Class
     *
     * @since   1.0.0
     * @access  public
     */
    public function __construct( $verbose = false ) {
        $this->verbose = $verbose;
    }

    /**
     * Utility method to run commands
     *
     * @since   1.0.0
     * @access  private
     * @param   string  $cmd    The command to execute.
     * @return string
     */
    private function run( $cmd ) {
        $run_cmd = 'sudo -u bogdan ' . $cmd . '';
        //$run_cmd = $cmd;
        if( $this->verbose ) {
            echo $run_cmd;
        }
        $output = shell_exec( $run_cmd );
        if( $this->verbose ) {
            echo $output;
        }
        return $output;
    }

    /**
     * Method to boot cycle Windows 10 Vagrant Box
     * and generate Json results.
     *
     * @since   1.0.0
     * @access  public
     * @return mixed|null
     */
    public function cycle_win_box() {
        $out = $this->run( 'VAGRANT_CWD=' . ROOT . 'vagrant/win10/ vagrant up --provision && vagrant halt 2>&1' );
        if( $this->verbose ) {
            echo $out;
        }
    }

    /**
     * Method to boot cycle Ubuntu 64 Xenial Vagrant Box
     * and generate Json results.
     *
     * @since   1.0.0
     * @access  public
     * @return mixed|null
     */
    public function cycle_ubt_box() {
        $out = $this->run( 'VAGRANT_CWD=' . ROOT . 'vagrant/ubuntu64/ vagrant up --provision && vagrant halt 2>&1' );
        if( $this->verbose ) {
            echo $out;
        }
    }

    /**
     * Windows 10
     * Method to return array of data from JSON file.
     *
     * @since   1.0.0
     * @access  public
     * @return array
     */
    public function get_win_res() {
        $res = array(
            'win10' => array(
                'name' => 'windows-10',
                'cpu'  => '0.00%',
                'mem'  => '0.00%',
                'disk'  => '0.00%'
            )
        );
        $data_file = ROOT . 'vagrant/data/res_win.json';
        if( file_exists( $data_file ) ) {
            $data = file_get_contents( $data_file );
            $data = str_replace("&quot;", '"', $data);
            $data = trim( $data );
            $res = json_decode( $data, true );
            if( $res == null ) {
                $data = mb_substr( $data, 1 );
                $res = json_decode( $data, true );
            }
        }
        return $res;
    }

    /**
     * Ubuntu Xenial 64
     * Method to return array of data from JSON file.
     *
     * @since   1.0.0
     * @access  public
     * @return array
     */
    public function get_ubt_res() {
        $res = array(
            'ubt64' => array(
                'name' => 'ubuntu-xenial',
                'cpu'  => '0.00%',
                'mem'  => '0.00%',
                'disk'  => '0.00%'
            )
        );
        $data_file = ROOT . 'vagrant/data/res_ubt.json';
        if( file_exists( $data_file ) ) {
            $data = file_get_contents( $data_file );
            $data = str_replace("&quot;", '"', $data);
            $data = trim( $data );
            $res = json_decode( $data, true );
        }
        return $res['ubt64'];
    }

    /**
     * Method to cycle all machines and return results.
     *
     * @since   1.0.0
     * @access  public
     * @return array
     */
    public function get_new_data() {
        $this->cycle_win_box();
        $win_data = $this->get_win_res();
        foreach ( $win_data as $key => $val ) {
            $win_data[$key] = str_replace( '%',  '', $val );
        }

        $this->cycle_ubt_box();
        $ubt_data = $this->get_ubt_res();
        foreach ( $ubt_data as $key => $val ) {
            $ubt_data[$key] = str_replace( '%',  '', $val );
        }

        return array(
            'win10' => $win_data,
            'ubt64' => $ubt_data
        );
    }
}