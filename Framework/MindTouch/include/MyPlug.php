<?php
// this extension can pass auth token as authorization
class MyPlug extends DekiPlug {
    protected $authtoken = false;
    public function __construct($uri = null, $output = 'php', $hostname = null) {
        if (is_object($uri)) {
            $this->authtoken = $uri->authtoken;
        }
        parent::__construct($uri, $output, $hostname);
    }
    public function SetAuthToken ( $authtoken ) {
        // sometimes it's comming here with quotes
        if ( $authtoken[0] == '"') {
            $pices = explode('"', $authtoken);
            $authtoken = $pices[1];
        }
        $this->authtoken = $authtoken;
    }

    /**
     *
     * @param string $input
     * @return DekiResult
     */
    public function Post($input = null) {
        if ($this->authtoken) {
            $this->headers['X-Authtoken'] = $this->authtoken;
        }
        return parent::Post($input);
    }

    /**
     *
     * @param string $input
     * @return DekiResult
     */
    public function Put($input = null) {
        if ($this->authtoken) {
            $this->headers['X-Authtoken'] = $this->authtoken;
        }
        return parent::Put($input);
    }

    /**
     *
     * @return DekiResult
     */
    public function Get() {
        if ($this->authtoken) {
            $this->headers['X-Authtoken'] = $this->authtoken;
        }
        return parent::Get();
    }

    // apply auth token if have one
    protected function ApplyCredentials($curl) {
        parent::ApplyCredentials($curl);
        if ($this->authtoken) {
            $this->headers['X-Authtoken'] = $this->authtoken;
        }
    }
};
?>