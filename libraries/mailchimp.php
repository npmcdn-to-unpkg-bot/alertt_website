<?php
namespace DrewM\MailChimp;

class MailChimp
{
    private $api_key;
    private $api_endpoint  = 'https://<dc>.api.mailchimp.com/3.0';

    public  $verify_ssl    = false;
    private $last_error    = '';
    private $last_response = array();
    private $last_request  = array();

    public function __construct($api_key)
    {
        $this->api_key = $api_key;
        list(, $datacentre)  = explode('-', $this->api_key);
        $this->api_endpoint  = str_replace('<dc>', $datacentre, $this->api_endpoint);
        $this->last_response = array('headers'=>null, 'body'=>null);
    }

    public function new_batch($batch_id=null)
    {
        return new Batch($this, $batch_id);
    }

    public function subscriberHash($email)
    {
        return md5(strtolower($email));
    }

    public function getLastError()
    {
        if ($this->last_error) return $this->last_error;
        return false;
    }

    public function getLastResponse()
    {
        return $this->last_response;
    }

    public function getLastRequest()
    {
        return $this->last_request;
    }

    public function delete($method, $args=array(), $timeout=10)
    {
        return $this->makeRequest('delete', $method, $args, $timeout);
    }

    public function get($method, $args=array(), $timeout=10)
    {
        return $this->makeRequest('get', $method, $args, $timeout);
    }

    public function patch($method, $args=array(), $timeout=10)
    {
        return $this->makeRequest('patch', $method, $args, $timeout);
    }

    public function post($method, $args=array(), $timeout=10)
    {
        return $this->makeRequest('post', $method, $args, $timeout);
    }

    public function put($method, $args=array(), $timeout=10)
    {
        return $this->makeRequest('put', $method, $args, $timeout);
    }

    private function makeRequest($http_verb, $method, $args=array(), $timeout=10)
    {
        if (!function_exists('curl_init') || !function_exists('curl_setopt')) {
            throw new \Exception("cURL support is required, but can't be found.");
        }
        $url = $this->api_endpoint.'/'.$method;
        $this->last_error    = '';
        $response            = array('headers'=>null, 'body'=>null);
        $this->last_response = $response;
        $this->last_request  = array(
            'method' => $http_verb,
            'path'   => $method,
            'url'    => $url,
            'body'   => '',
            'timeout'=> $timeout,
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/vnd.api+json',
            'Content-Type: application/vnd.api+json',
            'Authorization: apikey '.$this->api_key));
        curl_setopt($ch, CURLOPT_USERAGENT, 'DrewM/MailChimp-API/3.0 (github.com/drewm/mailchimp-api)');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->verify_ssl);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($ch, CURLOPT_ENCODING, '');
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        switch($http_verb) {
            case 'post':
                curl_setopt($ch, CURLOPT_POST, true);
                $this->attachRequestPayload($ch, $args);
                break;
            case 'get':
                $query = http_build_query($args);
                curl_setopt($ch, CURLOPT_URL, $url.'?'.$query);
                break;
            case 'delete':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
            case 'patch':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
                $this->attachRequestPayload($ch, $args);
                break;

            case 'put':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                $this->attachRequestPayload($ch, $args);
                break;
        }
        $response['body']    = curl_exec($ch);
        $response['headers'] = curl_getinfo($ch);
        $this->last_request['headers'] = $response['headers']['request_header'];

        if ($response['body'] === false) {
            $this->last_error = curl_error($ch);
        }

        curl_close($ch);
        return $this->formatResponse($response);
    }

    private function attachRequestPayload(&$ch, $data)
    {
        $encoded = json_encode($data);
        $this->last_request['body'] = $encoded;
        curl_setopt($ch, CURLOPT_POSTFIELDS, $encoded);
    }

    private function formatResponse($response)
    {
        $this->last_response = $response;
        if (!empty($response['body'])) {
            $d = json_decode($response['body'], true);

            if (isset($d['status']) && $d['status']!='200' && isset($d['detail'])) {
                $this->last_error = sprintf('%d: %s', $d['status'], $d['detail']);
            }

            return $d;
        }
        return false;
    }
}