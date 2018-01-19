<?php

// Not sure if needed?
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Sender.net Api Class
 * Handles communication with sender
 *
 * TODO:
 *     Now you must explicitly call setApiKey method
 *     on new ApiClient object to set which key to use
 *
 *
 *
 */
class SenderApiClient
{
    private $apiKey;
    private $apiEndpoint;
    private $commerceEndpoint;
    // private $baseUrl = 'https://app.sender.net';
    // Debug
    private $baseUrl = 'http://sinergijait.lt/Vytautas/wipsistema';

    public function __construct()
    {
        $this->apiKey = null;
        $this->apiEndpoint = $this->baseUrl . '/api';
        $this->commerceEndpoint = $this->baseUrl . '/commerce/v1';
    }
    
    /**
     * Returns current Api key
     *
     * @return type
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * Return base URL
     *
     * @return type
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }
    
    /**
     *
     * @param type $key
     * @return boolean
     */
    public function setApiKey($key = null)
    {
        if (!$key) {
            return false;
        }
        
        $this->apiKey = $key;
        
        return true;
    }
    
    /**
     * Try to make api call to check whether
     * the api key is valid
     *
     *
     * @return boolean | true if valid key
     */
    public function checkApiKey()
    {
        if (!$this->getApiKey()) { // No api key
            return false;
        }
        
        // Try
        $response = $this->addToList('', '');
        
        if (isset($response->error->code)) { // Wrong api key
            if ($response->error->code == 007) {
                return false;
            }
        }
        
        return true;
    }


    /**
     * Retrieve all mailinglists
     *
     * @return type
     */
    public function getAllLists()
    {
        $data = array(
            "method" => "listGetAllLists",
            "params" => array(
                "api_key" => $this->apiKey,
 
            )
        );
        
        return $this->makeApiRequest($data);
    }
    
    /**
     * Retrieve all forms
     *
     * @return type
     */
    public function getAllForms()
    {
        $data = array(
            "method" => "formGetAll",
            "params" => array(
                "api_key" => $this->apiKey,
            )
        );

        return $this->makeApiRequest($data);
    }
    
    /**
     * Retrieve push project script url
     *
     * @return type
     */
    public function getPushProject()
    {
        $data = array(
            "method" => "pushGetProject",
            "params" => array(
                "api_key" => $this->apiKey,
            )
        );
        
        return $this->makeApiRequest($data);
    }
    
    /**
     * Retrieve specific form via ID
     *
     * @param type $id
     * @return type
     */
    public function getFormById($id)
    {
        $data = array(
            "method" => "formGetById",
            "params" => array(
                "form_id" => $id,
            )
        );

        return $this->makeApiRequest($data);
    }
    
    /**
     * Add user or info to mailinglist
     *
     * @param type $email
     * @param type $listId
     * @param type $fname
     * @param type $lname
     * @return type
     */
    public function addToList($email, $listId, $fname = '', $lname = '')
    {
        $data = array(
            "method" => "listSubscribe",
            "params" => array(
                "list_id" => $listId,
                "emails" => array(
                    'email' => $email,
                    'firstname' => $fname,
                    'lastname' => $lname)
            )
        );
        
        return $this->makeApiRequest($data);
    }
    
    /**
     * Sends cart data to Sender
     *
     * $params['url'] = get_site_url() . '/?hash={$cart_hash}'; TODO
     *
     * @param type $params
     * @return type
     */
    public function cartTrack($params)
    {
        return $this->makeCommerceRequest($params, 'cart_track');
    }

    /**
     * Get cart from sender
     *
     * @param type $cartHash
     * @return type
     */
    public function cartGet($cartHash)
    {
        $params = array(
                      "cart_hash" => $cartHash,
                  );
        
        return $this->makeCommerceRequest($params, 'cart_get');
    }
    
    /**
     * Convert cart
     *
     * @param type $cartId
     * @return type
     */
    public function cartConvert($cartId)
    {
        $params = array(
                      "external_id" => $cartId,
                  );
        
        return $this->makeCommerceRequest($params, 'cart_convert');
    }
    
    /**
     * Delete cart
     *
     * @param type $cartId
     * @return type
     */
    public function cartDelete($cartId)
    {
        $params = array(
                      "external_id" => $cartId,
                  );
        
        return $this->makeCommerceRequest($params, 'cart_delete');
    }

    /**
     * Handle requests to commerce endpoint
     *
     * @param type $params
     * @param type $method
     * @return type
     */
    private function makeCommerceRequest($params, $method)
    {

        ini_set('display_errors', 'Off');

        $params['api_key'] = $this->apiKey;

        $options = array(
            'http' => array(
                'method'  => 'POST',
                'header' => 'Content-Type: application/x-www-form-urlencoded',
                'content' => http_build_query($params)
            )
        );
        $context = stream_context_create($options);
        $result = file_get_contents($this->commerceEndpoint . '/' . $method, false, $context);
        $response = json_decode($result);
        return $response;
    }

    /**
     * Handle requests to API endpoint
     *
     * @param type $params
     * @return type
     */
    private function makeApiRequest($data)
    {
        ini_set('display_errors', 'Off');

        $data['params']['api_key'] = $this->apiKey;

        $options = array(
            'http' => array(
                'method'  => 'POST',
                'header' => 'Content-Type: application/x-www-form-urlencoded',
                'content' => http_build_query(array('data' => json_encode($data)))
            )
        );
        $context = stream_context_create($options);
        $result = file_get_contents($this->apiEndpoint, false, $context);
        $response = json_decode($result);
        return $response;
    }
}