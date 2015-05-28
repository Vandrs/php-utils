<?php



require "/vendor/google/apiclient/src/Google/autoload.php";

class GooglePlusUtil{
    
    /** @property \Google_Client  $this->googleClient */
    private static $CLIENT_ID = "";
    private static $CLIENT_SECRET = "";
    private static $API_KEY = "";
    private static $APP_NAME = "";

    /** Caminha de onde as imagens devem ser salvas DEVE TERMINAR COM '/'    */
    private static $UPLOAD_PATH = "";

    /** URl de redirecionamento após login no google */ 
    private static $REDIRECT_URL;

    private $googleClient;
    
    public function __construct() {
        $this->initGoogleClient();
    }
    
    private function initGoogleClient(){
        $this->googleClient = new \Google_Client();
        $this->googleClient->setClientId(static::$CLIENT_ID);
        $this->googleClient->setClientSecret(static::$CLIENT_SECRET);
        $this->googleClient->setDeveloperKey(static::$API_KEY);
        $this->googleClient->setRedirectUri(static::$REDIRECT_URL);
    }
    
    public function getUrlLogin(){
        $this->googleClient->setScopes(array('https://www.googleapis.com/auth/plus.login'));
        return $this->googleClient->createAuthUrl();
    }
    
    public function requireUserToken($code){
        $this->googleClient->setScopes(array('https://www.googleapis.com/auth/plus.login'));
        $this->googleClient->authenticate($code);
        return  $this->googleClient->getAccessToken();
    }
    
    public function requireUserInfo($token){
        $this->googleClient->setAccessToken($token);
        $plus = new \Google_Service_Plus($this->googleClient);  
        return $plus->people->get('me');    
    }
    
    public function downloadUserImage($imageObject,$id = rand(0,1000),$large = FALSE){
        if($large){
            $url = str_replace("?sz=50", "", $imageObject->getUrl());
            $prefix = "gp-large";
        } else {
            $url = $imageObject->getUrl();
            $prefix = "gp-small";
        }
        return $this->downloadPicture($url, $id, $prefix);
    }
    
    private function downloadPicture($url,$id,$prefix){
        $imgName = FALSE;
        try{
            $img = file_get_contents($url);
            $imgName = $prefix."-".$id.".jpg";
            $imgPath = $this->getPicturePath().$imgName;
            file_put_contents($imgPath, $img);
        } catch (Exception $e){
            $imgName = FALSE;
        }        
        return $imgName;
    }
    
    private function getPicturePath(){
        return self::$UPLOAD_PATH;
    }
    
    /** Carrega a sdk em js do google plus */
    public static function loadScriptSDK(){
        return  '<script src="https://apis.google.com/js/platform.js" async defer>'.
                    '{lang: "pt-BR"}'.
                '</script>';
    }
}