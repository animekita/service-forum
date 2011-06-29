<?php echo '<?xml version="1.0" encoding="utf-8"?>'; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-ca">
<head>
   <?php $this->RenderAsset('Head'); ?>
</head>
<body id="<?php echo $BodyIdentifier; ?>" class="<?php echo $this->CssClass; ?>">
   <div id="Frame">
      <div id="Head">
         <div class="Menu">
            <h1><a class="Title" href="<?php echo Url('/'); ?>"><span><?php echo Gdn_Theme::Logo(); ?></span></a></h1>
            <?php
			      $Session = Gdn::Session();
					if ($this->Menu) {
						$Authenticator = Gdn::Authenticator();
						if ($Session->IsValid()) {
							$Name = $Session->User->Name;

							if (urlencode($Session->User->Name) == $Session->User->Name) {
							   $ProfileSlug = $Session->User->Name;
						    } else {
							   $ProfileSlug = $Session->UserID.'/'.urlencode($Session->User->Name);
							}

							$this->Menu->AddLink('ASignOut', T('Sign Out'), Gdn::Authenticator()->SignOutUrl(), FALSE, array('class' => 'NonTab SignOut'));
							$this->Menu->AddLink('ZUser', $Name, '/profile/'.$ProfileSlug, array('Garden.SignIn.Allow'), array('class' => 'NonTab UserProfile'));

						} else {
							$Attribs = array();
							if (SignInPopup() && strpos(Gdn::Request()->Url(), 'entry') === FALSE)
								$Attribs['class'] = 'SignInPopup';

							$this->Menu->AddLink('Entry', T('Sign In'), Gdn::Authenticator()->SignInUrl(), FALSE, array('class' => 'NonTab'), $Attribs);
						}
						echo $this->Menu->ToString();
					}
				?>
            <div class="Search"><?php
					$Form = Gdn::Factory('Form');
					$Form->InputPrefix = '';
					echo
						$Form->Open(array('action' => Url('/search'), 'method' => 'get')),
						$Form->TextBox('Search'),
						$Form->Button('Go', array('Name' => '')),
						$Form->Close();
				?></div>
         </div>
      </div>
      <div id="Body">
         <div id="Content"><?php $this->RenderAsset('Content'); ?></div>
         <div id="Panel"><?php $this->RenderAsset('Panel'); ?></div>
      </div>
      <div id="Foot">
			<?php
				$this->RenderAsset('Foot');

				$DashboardLink = '';

				if ($Session->User->Admin == '1'||
				    in_array('Garden.Settings.Manage', $Session->GetPermissions())) {

				  $DashboardLink = ' - ' . Anchor(T('Dashboard'), '/dashboard/settings');
				}

				echo Wrap(Anchor(T('Powered by Vanilla'), C('Garden.VanillaUrl')) . $DashboardLink, 'div');
			?>
		</div>
   </div>
	<?php $this->FireEvent('AfterBody'); ?>
</body>
</html>
