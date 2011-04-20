<?php 

/*
	TODO Cleanup...
*/

/**
 * CurlParty
 *
 **/
class CurlParty 
{
    public $base_url        = NULL;
    
    public $request_url     = NULL;
    
    public $cookie_file     = NULL;

    public $headers         = array();

    public $options         = array();

    public $referer         = '';

    public $user_agent      = '';

    protected $error        = '';

    protected $handle       = NULL;
	
	 public $response 		 = NULL;
	
	 public $request_data 	 = array();
	
    public function __construct($user_agent = 'CurlParty') 
    {
        $this->cookie_file = dirname(__FILE__).'/curl_cookie.txt';
        $this->user_agent = $user_agent;
        
        if (isset($_SERVER['SERVER_PROTOCOL']) AND isset($_SERVER['SERVER_NAME']))
        {
            $protocol = explode('/', $_SERVER['SERVER_PROTOCOL']);
            $protocol = strtolower($protocol[0]);
            $domain = $_SERVER['SERVER_NAME'];
            $this->referer = $protocol.'://'.$domain;
        }
    
    }
    
    /**
     * _compile_url
     *
     * @access public
     * @param  string   
     * @return string
     * 
     **/
    protected function _compile_request_url($uri) 
    {
        if (strpos($uri, 'http://') !== FALSE)
        {
            //we found a http:// so override the base_url setting
            $this->request_url = $uri;
        }
        else
        {
            //no http:// so use the base url
            $this->request_url = $this->base_url.$uri;  
        }
    }
    
    public function get($urn = '', $vars = array()) 
    {
        $this->_compile_request_url($urn);
            
        if (!empty($vars)) 
        {
            $this->request_url .= (stripos($this->request_url, '?') !== false) ? '&' : '?';
            $this->request_url .= http_build_query($vars, '', '&');
        }

        return $this->request('GET', $this->request_url);
    }

    public function post($urn = '', $vars = array()) 
    {
        $this->_compile_request_url($urn);
        return $this->request('POST', $this->request_url, $vars);
    }

    public function put($urn = '', $vars = array()) 
    {
        $this->_compile_request_url($urn);
        
        return $this->request('PUT', $this->request_url, $vars);
    }

    public function delete($urn = '', $vars = array()) 
    {
        $this->_compile_request_url($urn);
        
        return $this->request('DELETE', $this->request_url, $vars);
    }

    protected function request($method, $url, $vars = array()) 
    {
		$data_vars = $vars;
				
		$this->request_data = array_merge($this->request_data, array('method' => $method, 'url' => $url, 'vars' => $data_vars));
		
       $this->handle = curl_init();
       
        # Determine the request method and set the correct CURL option
        switch ($method) 
        {
            case 'GET':
                curl_setopt($this->handle, CURLOPT_HTTPGET, TRUE);
                break;

            case 'POST':
                curl_setopt($this->handle, CURLOPT_POST, 1);
                $post_data = (is_array($vars)) ? http_build_query($vars) : $vars;
                curl_setopt($this->handle, CURLOPT_POSTFIELDS, $post_data);
                break;

            default:
                curl_setopt($this->handle, CURLOPT_CUSTOMREQUEST, $method);
        }

        # Set some default CURL options
        curl_setopt($this->handle, CURLOPT_COOKIEFILE, $this->cookie_file);
        curl_setopt($this->handle, CURLOPT_COOKIEJAR, $this->cookie_file);
        //curl_setopt($this->handle, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($this->handle, CURLOPT_HEADER, TRUE);
        curl_setopt($this->handle, CURLOPT_REFERER, $this->referer);    
        curl_setopt($this->handle, CURLOPT_URL, $url);
        curl_setopt($this->handle, CURLOPT_USERAGENT, $this->user_agent);
        curl_setopt($this->handle, CURLOPT_RETURNTRANSFER, 1);
        
        
        if (strpos($url, 'https://') !== FALSE)
        {
            curl_setopt($this->handle, CURLOPT_SSL_VERIFYPEER, FALSE);        // this line makes it work under https
            curl_setopt($this->handle, CURLOPT_SSL_VERIFYHOST,  FALSE);
        }
        
        //Format custom headers for this request and set CURL option
        $headers = array();
        foreach ($this->headers as $key => $value) 
        {
            $headers[] = $key.': '.$value;
        }

        curl_setopt($this->handle, CURLOPT_HTTPHEADER, $headers);

        # Set any custom CURL options
        foreach ($this->options as $option => $value) 
        {
            curl_setopt($this->handle, constant('CURLOPT_'.str_replace('CURLOPT_', '', strtoupper($option))), $value);
        }
            
        $response = curl_exec($this->handle);
        
        if ($response) 
        {
            $this->response = new CurlPartyResponse($response);
        } 
        else 
        {
            $this->error = curl_errno($this->handle).' - '.curl_error($this->handle);
        }
  

        curl_close($this->handle);
        return $this->response;
    }

    public function error() 
    {
        return $this->error;
    }

}

/**
 * CurlPartyResponse
 **/
class CurlPartyResponse 
{
    public $body = '';

    public $headers = array();

    public function __construct($response) 
    {
        # Extract headers from response
        $pattern = '#HTTP/\d\.\d.*?$.*?\r\n\r\n#ims';
        preg_match_all($pattern, $response, $matches);
        $headers = preg_split("/\r\n/", str_replace("\r\n\r\n", '', array_pop($matches[0])));
        
        # Extract the version and status from the first header
        $version_and_status = array_shift($headers);
        preg_match('#HTTP/(\d\.\d)\s(\d\d\d)\s(.*)#', $version_and_status, $matches);
        $this->headers['Http-Version'] = $matches[1];
        $this->headers['Status-Code'] = $matches[2];
        $this->headers['Status'] = $matches[2].' '.$matches[3];
        
        # Convert headers into an associative array
        foreach ($headers as $header) 
        {
            preg_match('#(.*?)\:\s(.*)#', $header, $matches);
            $this->headers[$matches[1]] = $matches[2];
        }
        
        # Remove the headers from the response body
        $this->body = preg_replace($pattern, '', $response);
    }

	/**
	 * ok
	 *
	 * @access public
	 * @param  void	
	 * @return void
	 * 
	 **/
	public function ok() 
	{
		return (isset($this->headers["Status-Code"]) AND $this->headers["Status-Code"] == 200) ? TRUE : FALSE;
	}

    public function __toString() 
    {
        return $this->body;
    }
}