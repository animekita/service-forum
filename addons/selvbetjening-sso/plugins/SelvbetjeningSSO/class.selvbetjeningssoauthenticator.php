<?php if (!defined('APPLICATION')) exit();

class Gdn_SelvbetjeningSSOAuthenticator extends Gdn_Authenticator {

    private $_SSO_API = FALSE;
    
    public function __construct() {
        $this->_DataSourceType = Gdn_Authenticator::DATA_NONE;
        
        $apiLibPath = Gdn::Config('Garden.Authenticators.selvbetjeningsso.APILib.Path', FALSE);
        $serviceName = Gdn::Config('Garden.Authenticators.selvbetjeningsso.APILib.ServiceName', FALSE);
            
        if ($apiLibPath !== FALSE and $serviceName !== FALSE) {
            require_once($apiLibPath);
            $this->_SSO_API = new SelvbetjeningIntegrationSSO($serviceName);
        }
        
        parent::__construct();
    }
   
    public function Authenticate() {    
        try {
            if ($this->_SSO_API === FALSE) {
                throw new Exception('Authentication library not loaded');
            }  
            
            $authenticated = $this->_SSO_API->is_authenticated();

            if ($authenticated === FALSE) {
            	throw new Exception('User not authenticated');
            }
            
            $sessionInfo = $this->_SSO_API->get_session_info();
        
            $association = Gdn::Authenticator()->GetAssociation($sessionInfo['id'], 'selvbetjeningsso', Gdn_Authenticator::KEY_TYPE_PROVIDER);
            
            if ($association) {

                $this->SetIdentity($association['UserID'], FALSE);
                Gdn::Authenticator()->Trigger(Gdn_Authenticator::AUTH_SUCCESS);
                
            } else {
                
               throw new Exception('Associated user not found'); 
            }
        
        }
        catch (Exception $e) {
            $this->DeAuthenticate();
        }
        
        return FALSE;
    }

    public function WakeUp() {
            if ($this->_SSO_API === FALSE) {
            // Authentication library not loaded
            return;
        }
        
        $step = $this->CurrentStep();
        
        // Already logged in
        if ($step == Gdn_Authenticator::MODE_REPEAT) {
            if ($this->_SSO_API->is_authenticated() === FALSE) {
                $this->DeAuthenticate();
            }
            return;
        }
        
        $token = $this->_SSO_API->get_auth_token();
        
        if (isset($_COOKIE['selvbetjeningsso_last_token']) &&
            $_COOKIE['selvbetjeningsso_last_token'] == $token) {
            
            // ignore request, user has previously tried to authenticate using this token and failed
            return;
        }
        
        if ($this->Authenticate() === FALSE) {
            // user authentication failed, register token in order to prevent further 
            // authentication attempts through it. If the user authenticates at selvbetjening
            // a new token is issued.
            setcookie('selvbetjeningsso_last_token', $token);
        }
    }
   
    public function CurrentStep() {
        $Id = Gdn::Authenticator()->GetRealIdentity();
        
        if (!$Id) return Gdn_Authenticator::MODE_GATHER;
        if ($Id > 0) return Gdn_Authenticator::MODE_REPEAT;
        if ($Id < 0) return Gdn_Authenticator::MODE_NOAUTH;
    }
   
    public function GetURL($URLType) {
        if (strpos($URLType, 'Remote', 0) === FALSE) return FALSE;
      
        $urls = Gdn::Config('Garden.Authenticators.selvbetjeningsso.Urls', FALSE);
      
        if ($urls && $url = GetValue($URLType, $urls, FALSE)) {
            return $url;
        }
      
        return FALSE;
    }
    
    public function AuthenticatorConfiguration(&$Sender) {
        return FALSE;
    }
    
    public function DeAuthenticate() {
        $this->SetIdentity(NULL);
        return Gdn_Authenticator::AUTH_SUCCESS;
    }
   
    // What to do if entry/auth/* is called while the user is logged out. Should normally be REACT_RENDER
    public function LoginResponse() {
        return Gdn::Authenticator()->RemoteSignInUrl();
    }
   
   // What to do after part 1 of a 2 part authentication process. This is used in conjunction with OAauth/OpenID type authentication schemes
   public function PartialResponse() {
      return Gdn_Authenticator::REACT_REDIRECT;
   }
   
   // What to do after authentication has succeeded. 
   public function SuccessResponse() {
      return Gdn_Authenticator::REACT_REDIRECT;
   }
   
   // What to do if the entry/auth/* page is triggered for a user that is already logged in
   public function RepeatResponse() {
      return Gdn_Authenticator::REACT_REDIRECT;
   }
   
   // What to do if the entry/leave/* page is triggered for a user that is logged in and successfully logs out
   public function LogoutResponse() {
      return Gdn::Authenticator()->RemoteSignOutUrl();
   }
   
   public function FailedResponse() {
      return Gdn_Authenticator::REACT_RENDER;
   }
   
}