<?php if (!defined('APPLICATION')) exit();
$Session = Gdn::Session();

echo '<div class="Tabs Headings CategoryHeadings"><div class="ItemHeading">';

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