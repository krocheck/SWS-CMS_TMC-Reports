<?php

class skin_agenda extends Command {

protected function doExecute( $param )
{
}

//===========================================================================
// Normal wrapper
//===========================================================================
public function wrapper( $members ) {

$ELMHTML = "";
//--starthtml--//
$ELMHTML .= <<<EOF
	<div id="header"><a href="{$this->display->buildURL( array() )}"><img src="{$this->registry->getConfig('base_url')}images/admin-logo.gif" /></a></div>
	<div class="content">
EOF;
foreach( $members as $v ) {
$ELMHTML .= <<<EOF
		<h2>{$v->getName()}</h2>
		<div>
{$v->getContent()}
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