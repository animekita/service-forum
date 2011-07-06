<?php if (!defined('APPLICATION')) exit();
$Session = Gdn::Session();

echo '<div class="Tabs Headings CategoryHeadings"><h2>';

$Breadcrumbs = Gdn::Controller()->Data('Breadcrumbs');
if ($Breadcrumbs) {
   $First = TRUE;
   foreach ($Breadcrumbs as $Breadcrumb) {
 	  if (!$First) {
		 echo ' &raquo; ';
	  } else {
		 $First = FALSE;
	  }

	  echo $Breadcrumb['Name'];
   }

} else {
   echo T('All Discussions');
}

echo '</h2><div class="controls">';

   echo '<span>' . Anchor(T("Show All Categories"), "/categories/all/") . '</span>';
   if (Gdn::Session()->IsValid()) {
	  echo '<span>' . Anchor(T("Mark All Viewed"), "/discussions/markallviewed") . '</span>';
   }

echo '</div></div>';

if (!function_exists('WriteDiscussion'))
   include($this->FetchViewLocation('helper_functions', 'discussions', 'vanilla'));


$Alt = '';
if (property_exists($this, 'AnnounceData') && is_object($this->AnnounceData)) {
	foreach ($this->AnnounceData->Result() as $Discussion) {
		$Alt = $Alt == ' Alt' ? '' : ' Alt';
		WriteDiscussion($Discussion, $this, $Session, $Alt);
	}
}

$Alt = '';
foreach ($this->DiscussionData->Result() as $Discussion) {
   $Alt = $Alt == ' Alt' ? '' : ' Alt';
   WriteDiscussion($Discussion, $this, $Session, $Alt);
}