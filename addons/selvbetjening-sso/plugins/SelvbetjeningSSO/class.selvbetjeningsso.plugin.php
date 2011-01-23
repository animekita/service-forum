<?php if (!defined('APPLICATION')) exit();

$PluginInfo['SelvbetjeningSSO'] = array(
   'Description' => 'Selvbetjening SSO plugin.',
   'Version' => '1.0',
   'Author' => "Casper S. Jensen",
   'AuthorEmail' => 'the@sema.dk',
   'AuthorUrl' => 'http://www.sema.dk',
   'RequiredApplications' => array('Vanilla' => '2.0.11'),
   'MobileFriendly' => TRUE,
   'RequiredTheme' => FALSE, 
   'RequiredPlugins' => FALSE,
);

Gdn_LibraryMap::SafeCache('library', 'class.selvbetjeningssoauthenticator.php', dirname(__FILE__).DS.'class.selvbetjeningssoauthenticator.php');

class SelvbetjeningSSOPlugin extends Gdn_Plugin {

    public function Setup() {
       $this->_Enable(FALSE);
    }
   
    public function AuthenticationController_DisableAuthenticatorSelvbetjeningSSO_Handler(&$Sender) {
       $this->_Disable();
    }
	
    public function AuthenticationController_EnableAuthenticatorSelvbetjeningSSO_Handler(&$Sender) {
        $this->_Enable();
    }
   
    public function EntryController_SignIn_Handler(&$Sender) {
        if (!Gdn::Authenticator()->IsPrimary('selvbetjeningsso')) exit('ERROR: Selvbetjening Authenticator not loaded.');
        
        $Target = FALSE;
        
//        $RawTarget = Gdn::Request()->GetValue('HTTP_REFERER', FALSE);
//        if ($RawTarget && $Components = parse_url($RawTarget)) {
//
//            $ThisHost = Gdn::Request()->GetValue('HTTP_HOST', FALSE);
// 
//            if (isset($Components['host']) && isset($Components['path']) &&
//               $Components['host'] !== FALSE && $Components['host'] == $ThisHost) {
//                  
//               $Target = $Components['path'];
//               
//               if (isset($Components['query'])) {
//
//                   $Target .= '?' . $Components['query'];
//               }
//               
//               if (isset($Components['fragment'])) {
//                   $Target .= '#' . urlencode($Components['fragment']);
//               }
//            }  
//        } 
        
        if ($Target === FALSE) {
            $Target = isset($_GET['Target']) ? $_GET['Target'] : FALSE;
        }
        
        // catch malformed target
        if ($Target === FALSE) {
            $Target = isset($_GET['amp;Target']) ? $_GET['amp;Target'] : FALSE;
        }
        
        if ($Target === FALSE) {
            $Target = Gdn::Config('Garden.Authenticators.selvbetjeningsso.Urls.DefaultPostLoginLandingPage', FALSE);
        }
        
        $SigninURL = Gdn::Authenticator()->GetURL(Gdn_Authenticator::URL_REMOTE_SIGNIN, $Target);

        $RealUserID = Gdn::Authenticator()->GetRealIdentity();
        $Authenticator = Gdn::Authenticator()->GetAuthenticator('selvbetjeningsso');
        
        if ($RealUserID) {
            
            // The user is already signed in. Send them to the default page.
            Redirect(Gdn::Router()->GetDestination('DefaultController'), 302);
        
        } else {
            
            // We have no cookie for this user. Send them to the remote login page.
            $Authenticator->DeAuthenticate();
            
            Redirect($SigninURL, 302);
        }
        
        exit();
    }
    
    public function EntryController_SignOut_Handler(&$Sender) {
        if (!Gdn::Authenticator()->IsPrimary('selvbetjeningsso')) exit('ERROR: Selvbetjening Authenticator not loaded.');
        
        $Authenticator = Gdn::Authenticator()->GetAuthenticator('selvbetjeningsso');
        $Authenticator->DeAuthenticate();
        
        $SignOutURL = Gdn::Authenticator()->GetURL(Gdn_Authenticator::URL_REMOTE_SIGNOUT);
        
        Redirect($SignOutURL, 302);        
    }
    
    public function EntryController_Register_Handler(&$Sender) {
        if (!Gdn::Authenticator()->IsPrimary('selvbetjeningsso')) exit('ERROR: Selvbetjening Authenticator not loaded.');
        
        $RegisterURL = Gdn::Authenticator()->GetURL(Gdn_Authenticator::URL_REMOTE_REGISTER);
        
        Redirect($RegisterURL, 302);
    }
   
    private function _Enable($WriteConfig = TRUE) {
        
        if ($WriteConfig) {
            SaveToConfig('Garden.SignIn.Popup', False);
            SaveToConfig('Garden.UserAccount.AllowEdit', FALSE);
            
            SaveToConfig('Garden.Authenticators.selvbetjeningsso.Name', 'SelvbetjeningSSO');
            
            SaveToConfig('Plugins.SelvbetjeningSSO.Enabled', TRUE);
            
            SaveToConfig('Garden.Authenticators.selvbetjeningsso.Urls.RemoteRegisterUrl', '');
            SaveToConfig('Garden.Authenticators.selvbetjeningsso.Urls.RemoteSignInUrl', '');
            SaveToConfig('Garden.Authenticators.selvbetjeningsso.Urls.RemoteSignOutUrl', '');
            SaveToConfig('Garden.Authenticators.selvbetjeningsso.Urls.DefaultPostLoginLandingPage', '');
         
            SaveToConfig('Garden.Authenticators.selvbetjeningsso.APILib.ServiceName', '');
            SaveToConfig('Garden.Authenticators.selvbetjeningsso.APILib.Path', '');
            
            Gdn::Authenticator()->SetDefaultAuthenticator('selvbetjeningsso');
        }
        
        Gdn::Authenticator()->EnableAuthenticationScheme('selvbetjeningsso', $WriteConfig);
    }

    private function _Disable() {
        // Only remove configuration which changes the behaviour of the system
        // all other parts of the configuration is untuched such that the plugin
        // can be added again without much effort.
        
        RemoveFromConfig('Garden.SignIn.Popup');
        RemoveFromConfig('Garden.UserAccount.AllowEdit');
        
        RemoveFromConfig('Plugins.SelvbetjeningSSO.Enabled');
        
        Gdn::Authenticator()->UnsetDefaultAuthenticator('selvbetjeningsso');
    }
   
}