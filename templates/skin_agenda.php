<?php

class skin_agenda extends Command {

protected function doExecute( $param )
{
}

//===========================================================================
// Normal wrapper
//===========================================================================
public function wrapper( $members, $title ) {

$ELMHTML = "";
//--starthtml--//
$ELMHTML .= <<<EOF
		<div class="content">
			<h1>{$title}</h1>
			<table width="100%" cellpadding="0" cellspacing="0">

EOF;
foreach( $members as $v ) {
$ELMHTML .= <<<EOF
					<tr>
						<td>
							<h2>{$v['name']}</h2>
{$v['description']}
						</td>
					</tr>

EOF;
}
$ELMHTML .= <<<EOF
			</table>
		</div>

EOF;
//--endhtml--//
return $ELMHTML;
}

}

?>