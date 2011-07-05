<?php if (!defined('APPLICATION')) exit(); ?>

<div class="Tabs Headings">
<?php
if ($this->Data('Conversation.Subject') && C('Conversations.Subjects.Visible')) {
   echo '<h2>'.htmlspecialchars($this->Data('Conversation.Subject')).'</h2>';
   echo '<div class="controls"><span>(' . $this->Participants . ')</span></div>';
} else {
   echo '<h2>'.$this->Participants.'</h2>';
}
?>
</div>

<?php
$this->FireEvent('BeforeConversation');
echo $this->Pager->ToString('less');
?>
<ul class="MessageList Conversation">
   <?php
   $MessagesViewLocation = $this->FetchViewLocation('messages');
   include($MessagesViewLocation);
   ?>
</ul>
<?php echo $this->Pager->ToString(); ?>
<div id="MessageForm">
   <?php
   echo $this->Form->Open(array('action' => Url('/messages/addmessage/')));
   echo Wrap($this->Form->TextBox('Body', array('MultiLine' => TRUE, 'class' => 'MessageBox')), 'div', array('class' => 'TextBoxWrapper'));

   echo '<div class="Buttons">',
      $this->Form->Button('Send Message'),
      '</div>';

   echo $this->Form->Close();
   ?>
</div>
