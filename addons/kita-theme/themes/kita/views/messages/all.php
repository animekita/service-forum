<?php if (!defined('APPLICATION')) exit();
?>
<div class="Tabs Headings">
   <h2><?php echo T('Inbox'); ?></h2>
</div>
<?php
if ($this->ConversationData->NumRows() > 0) {
?>
<ul class="Condensed DataList Conversations">
   <?php
   $ViewLocation = $this->FetchViewLocation('conversations');
   include($ViewLocation);
   ?>
</ul>
<?php
echo $this->Pager->ToString();
} else {
   echo '<div class="Empty">'.T('You do not have any conversations.').'</div>';
}
