<?php if (!defined('APPLICATION')) exit();

$PluginInfo['KitaMisc'] = array(
   'Description' => 'Various minor changes used by Kita.',
   'Version' => '1.0',
   'Author' => "Casper S. Jensen",
   'AuthorEmail' => 'the@sema.dk',
   'AuthorUrl' => 'http://www.sema.dk',
   'RequiredApplications' => array('Vanilla' => '2.0.17'),
   'MobileFriendly' => TRUE,
   'RequiredTheme' => FALSE,
   'RequiredPlugins' => FALSE,
);

class KitaMiscPlugin extends Gdn_Plugin {

    public function Setup() {

    }

}