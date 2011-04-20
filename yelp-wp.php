<?php
/*
Plugin Name: Yelp WP
*/

define('YELP_WP_API_KEY_OPTION_KEY', 'yelp_wp_api_key');

require_once "curlparty.php";
require_once "view_object.php";

View_Object::set_basepath(dirname(__FILE__).'/views/');

add_action('admin_menu', 'add_yelp_option_menu');

function add_yelp_option_menu() {
	
	add_option(YELP_WP_API_KEY_OPTION_KEY, '');
	
	add_options_page(
		'Yelp WP Options', 
		'Yelp WP', 
		'manage_options', 
		'yelp-wp', 
		'yelp_options'
	);
	
}

function yelp_options() 
{
	if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}
	
	$option_view = View_Object::factory('options');
	
	if ($_POST && isset($_POST['yelp_wp_save']) && $_POST['yelp_wp_save'] === 'Y')
	{
		update_option(YELP_WP_API_KEY_OPTION_KEY, $_POST['yelp_wp_api_key']);
		$option_view->yelp_api_key = $_POST['yelp_wp_api_key'];
		$option_view->message = 'Options Saved!';
	}
	else
	{
		$option_view->yelp_api_key = get_option(YELP_WP_API_KEY_OPTION_KEY);
		$option_view->message = NULL;
	}
	
	echo $option_view->render();
}

function get_yelp_businesses($location = '') {
	Yelp_Businesses::$YWSID = get_option(YELP_WP_API_KEY_OPTION_KEY);	
	$business = new Yelp_Businesses;
	return $business->get_by_location($location);
}

function get_yelp_businesses_list($location = '') {
	Yelp_Businesses::$YWSID = get_option(YELP_WP_API_KEY_OPTION_KEY);
	$business = new Yelp_Businesses;
	$business->get_by_location($location);
	$list = View_Object::factory('list', array('businesses' => $business));
	return $list->render();
}


/**
 * Simple Wrapper around CurlParty for Yelp API request
 *
 * @package Yelp WP
 * @author th3mus1cman
 **/
class Yelp_Businesses implements Iterator {
	
	public static $YWSID = '';
	
	protected $curl;
	
	protected $businesses;
	
	/**
	 * __construct
	 *
	 * @access public
	 * @param  void	
	 * @return void
	 * 
	 **/
	public function __construct() 
	{
		$this->curl = new CurlParty;
	}
	
	/**
	 * get_by_location
	 *
	 * @access public
	 * @param  string location address used to search the Yelp API with	
	 * @return void
	 * 
	 **/
	public function get_by_location($location = '') 
	{
		$params = array(
			'ywsid' 		=> Yelp_Businesses::$YWSID,
			'location'	=> $location,
		);

		$response = $this->curl->get('http://api.yelp.com/business_review_search', $params);

		if ($response->ok())
		{
			$json = json_decode($response->body);
			$this->businesses = $json->businesses;
		}
		
		return $this;
	}

	protected $_current; 
	
	protected $_index = 0;
	
	protected $_valid;

	
	/**
	 * rewind
	 *
	 * @access public
	 * @param  void
	 * @return void
	 * 
	 **/
	public function rewind() {
		$this->_index = 0;
		$this->_current = $this->businesses[$this->_index];
	}

	/**
	 * valid
	 *
	 * @access public
	 * @param  void
	 * @return bool whether or not the end of the array has been reached
	 * 
	 **/
	public function valid() {
		return (count($this->businesses) != ($this->_index + 1)) ? TRUE : FALSE;
	}

	/**
	 * key
	 *
	 * @access public
	 * @param  current
	 * @return int current position in the businesses array
	 * 
	 **/
	public function key() {
		return $this->_index;
	}

	/**
	 * next
	 *
	 * @access public
	 * @param  void
	 * @return void
	 * 
	 **/
	public function next() {
		$this->_index++;
		$this->_current = $this->businesses[$this->_index];
	}

	/**
	 * current
	 *
	 * @access public
	 * @param  void
	 * @return stdClass current businesses array item
	 * 
	 **/
	public function current() {
		if ($this->_current == NULL)
		{
			$this->_current = $this->businesses[$this->_index];
		}
		return $this->_current;
	}

	
}


?>