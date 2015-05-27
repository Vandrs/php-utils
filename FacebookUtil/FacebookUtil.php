<?php
use Facebook\FacebookSession;
use Facebook\FacebookRedirectLoginHelper;
use Facebook\FacebookRequest;
use Facebook\GraphUser;

class FacebookUtil{
    
    private static $APP_ID = "";
    private static $APP_SECRET = "";

    /** Url para o Facebook irá redirecionar após o login */
    private static $REDIRECT_URL = "";

    /** Caminho do diretório onde as fotos serão salvar  TEM QUE UMA / no final do caminho  */
    private static $UPLOAD_DIR = "";

    /** Url para o Facebook irá redirecionar após o logout */
    private static $AFTER_LOGOUT_URL = "";
    
    public function __construct() {
        $this->startSession();
        $this->initFBSession();
    }

    private function startSession(){
        if(!(session_status() == PHP_SESSION_ACTIVE)){
            session_start();
        }
    }

    private function initFBSession(){
        FacebookSession::setDefaultApplication(self::$APP_ID, self::$APP_SECRET);
    }
    
    public function getLoginUrl(){
        $loginHelper = new FacebookRedirectLoginHelper(self::$$REDIRECT_URL);
        return $loginHelper->getLoginUrl();
    }
    
    public function getLogoutUrl($token){
	    $session = new FacebookSession($token);
        $loginHelper = new FacebookRedirectLoginHelper(self::$$REDIRECT_URL);
        return $loginHelper->getLogoutUrl($session, self::$AFTER_LOGOUT_URL);
    }
        
    public function postRedirectGetToken(){
        $loginHelper = new FacebookRedirectLoginHelper(self::$REDIRECT_URL);
        $session = $loginHelper->getSessionFromRedirect();
        $token = $session->getToken();
        return $token;
    }

    public function getUserData($token){
        $session = new FacebookSession($token);
        $request = new FacebookRequest($session, 'GET', '/me');
        $response = $request->execute();
        $graphObject = $response->getGraphObject(GraphUser::className());
        return $graphObject->asArray();
    }
    
    public function getUserFriends($token){
        $session = new FacebookSession($token);
        $request = new FacebookRequest($session,'GET','/me/friends');
        $response = $request->execute();
        $graphObject = $response->getGraphObject();
        return $graphObject->asArray();
    }
    
    public function downloadUserLargePicture($token,$id = rand(0,1000), $prefix = "fb-large"){
        $pictureData = $this->getUserProfilePicture($token,TRUE);
        if($pictureData){
            return $this->downloadFacebookPicture($pictureData['url'], $id, $prefix);
        }
        return FALSE;
    }
    
    public function downloadUserSmallPicture($token,$id = rand(0,1000), $prefix = "fb-small"){
        $pictureData = $this->getUserProfilePicture($token,FALSE);
        if($pictureData){
            return $this->downloadFacebookPicture($pictureData['url'], $id, $prefix);
        }
        return FALSE;
    }

    private function getUserProfilePicture($token,$large = FALSE){
        $params = ["redirect"=>"false"];
        if($large){
            $params["type"] = "large";                        
        }
        $session = new FacebookSession($token);
        $request = new FacebookRequest($session,'GET','/me/picture',$params);
        $response = $request->execute();
        $graphObject = $response->getGraphObject();
        return $graphObject->asArray();
    }
    
    private function downloadFacebookPicture($url,$id,$prefix){
        $imgName = FALSE;
        try{
            $img = file_get_contents($url);
            $imgName = $prefix."-".$id.".jpg";
            $imgPath = self::$UPLOAD_DIR.$imgName;
            file_put_contents($imgPath, $img);
        } catch (Exception $e){
            $imgName = FALSE;
        }        
        return $imgName;
    }
    
    private function getPicturePath(){
        return self::$UPLOAD_DIR;
    }
    
    /** Case seja necessário usar a api facebook-jssdk este código retorna o script necessário */
    public static function loadJavaScriptSDK(){
        return "<div id='fb-root'></div>
                <script type='text/javascript'>
                window.fbAsyncInit = function() {
                    FB.init({
                      appId      : '".self::$APP_ID."',
                      xfbml      : true,
                      version    : 'v2.3'
                    });
                };
                (function(d, s, id){
                   var js, fjs = d.getElementsByTagName(s)[0];
                   if (d.getElementById(id)) {return;}
                   js = d.createElement(s); js.id = id;
                   js.src = '//connect.facebook.net/pt_BR/sdk.js';
                   fjs.parentNode.insertBefore(js, fjs);
                }(document, 'script', 'facebook-jssdk'));
                </script>";
    }
}