<?php

class skin_agenda extends Command {

protected function doExecute( $param )
{
}

//===========================================================================
// Normal wrapper
//===========================================================================
public function wrapper( $members ) {

$nextMonday = strtotime('next monday');
$meetingDate = date('F j, Y', $nextMonday);

$ELMHTML = "";
//--starthtml--//
$ELMHTML .= <<<EOF
	<div id="header">
		<h1 class="float">STAFF MEETING: {$meetingDate}</h1>
		<a href="{$this->display->buildURL( array() )}"><img src="{$this->registry->getConfig('base_url')}images/admin-logo.png" /></a></div>
	<div class="content">

EOF;
foreach( $members as $v ) {
$ELMHTML .= <<<EOF
		<div class="group">
			<hr />
			<h2>{$v->getName()}</h2>
			<div>
{$v->getContent()}
			</div>
		</div>

EOF;
}
$ELMHTML .= <<<EOF
		</div>

EOF;
//--endhtml--//
return $ELMHTML;
}

}

?>