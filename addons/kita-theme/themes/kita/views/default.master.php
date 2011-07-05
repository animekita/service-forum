<?php echo '<?xml version="1.0" encoding="utf-8"?>'; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-ca">

<head>
	<?php $ThemeVersion = GetValue('Version', Gdn::ThemeManager()->GetThemeInfo(Theme()), false);
	if ($ThemeVersion) {
		$ThemeVersion = '?v=' . $ThemeVersion;
	}
	?>
	<link rel="stylesheet" type="text/css" href="<?php echo C('Garden.Kita.StaticUrl'); ?>css/reset.css<?php echo $ThemeVersion; ?>" />

	<?php $this->RenderAsset('Head'); ?>

	<link rel="stylesheet" type="text/css" href="<?php echo C('Garden.Kita.StaticUrl'); ?>css/base.css<?php echo $ThemeVersion; ?>" />
</head>

<?php
	$Session = Gdn::Session();
?>

<body id="<?php echo $BodyIdentifier; ?>" class="<?php echo $this->CssClass; ?>">

	<div id="wrapper">

		<div id="topNavBg"></div>

		<div id="top">
			<a href="http://www.anime-kita.dk"><h1 id="topLogo">Anime Kita</h1></a>

			<div id="topNavBgFade"></div>

			<ul id="topNav">
				<li><a href="<?php echo C('Garden.Kita.HomeUrl'); ?>">home</a></li>
				<li><a class="current" href="<?php echo C('Garden.Kita.ForumUrl'); ?>">forum</a></li>
				<li><a href="<?php echo C('Garden.Kita.GalleryUrl'); ?>">galleri</a></li>
				<li><a href="<?php echo C('Garden.Kita.IntranetUrl'); ?>">intranet</a></li>
			</ul>

			<?php
			if ($Session->IsValid()) {
				$left = Anchor($Session->User->Name, '/profile/'.$Session->User->Name, 'floatLeft');
				$right = Anchor(T('Sign Out'), Gdn::Authenticator()->SignOutUrl());
			} else {
				$left = Anchor(T('Create User'), Gdn::Authenticator()->RegisterUrl(), 'floatLeft');
				$right = Anchor(T('Sign In'), Gdn::Authenticator()->SignInUrl());
			}
			?>
			<div id="topUserNav">
				<?php echo $left; ?><span class="vertSeperatorTiny">|</span><?php echo $right; ?>
			</div>

		</div>  <!-- top end -->

		<div id="topContent">
			<div id="pageControls"></div> &nbsp;
		</div>

   <div id="Frame">
      <div id="Head">
         <div class="Menu">
            <?php
			if ($this->Menu) {
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
		?>
		</div>
   </div>

	<div id="footerFix"></div>
	</div> <!-- end wrapper -->

	<?php
	$DashboardLink = '';

	if ($Session->User->Admin == '1'||
		in_array('Garden.Settings.Manage', $Session->GetPermissions())) {

	  $DashboardLink = '<span class="vertSeperatorMedium">|</span>' . Anchor(T('Dashboard'), '/dashboard/settings');
	}

	?>

	<div id="footer">
	<!-- This is why ... -->
	<span>Anime Kita, 2007 - <?php echo date("Y"); ?> &copy; All rights reserved</span
	><span class="vertSeperatorMedium">|</span
	><a href="http://www.anime-kita.dk/om-os/kontakt/">Kontakt os</a
	><span class="vertSeperatorMedium">|</span
	><a href="http://www.anime-kita.dk/databehandlingspolitik/">Databehandlingspolitik</a><?php echo $DashboardLink; ?>
	</div>

	<?php $this->FireEvent('AfterBody'); ?>
</body>
</html>
