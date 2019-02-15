<?php

class skin_team extends Command {

protected function doExecute( $param )
{
}

//===========================================================================
// Normal wrapper
//===========================================================================
public function schedulePDF( $name, $description, $tasks ) {

$date = date('F j, Y');

$ELMHTML = "";
//--starthtml--//
$ELMHTML .= <<<EOF

	<htmlpageheader name="myHeader" style="margin:0; padding:0;">
		<table width="100%" style="margin:0; padding:0; border-spacing: 0; border-collapse: collapse;" id="header">
			<tr>
				<td width="20%"></td>
				<td width="80%" style="text-align: right;"><img src="{$this->registry->getConfig('base_url')}images/print-logo.png" style="width:144px; height:38px;" /></td>
			</tr>
			<tr>
				<td width="22%" style="font-weight: bold; text-align: center; color:#ffffff; background-color:#d25c3; border-bottom: 1px solid #d25c3;">{$date}</td>
				<td width="78%" style="font-weight: bold; border-bottom: 1px solid #d25c3;">{$name} - Production Schedule</td>
			</tr>
		</table>
	</htmlpageheader>
	<p class='description'>{$description}</p>
	<table class="schedule">
		<tr>
			<th width="40%">Description of Task</th>
			<th width="30%">Reponsible Party</th>
			<th width="15%">START</th>
			<th width="15%">END</th>
		</tr>
EOF;
foreach( $tasks as $v ) {
	if ( strpos($v['name'],':') > 0 )
	{
		$v['name'] = substr($v['name'], strpos($v['name'],':')+1);
	}
$ELMHTML .= <<<EOF
		<tr>
			<td class='name'>{$v['name']}</td>
			<td class='party'>{$v['responsible_party']}</td>

EOF;

if ( strlen($v['start']) > 0 && strlen($v['end']) > 0 )
{
$ELMHTML .= <<<EOF
			<td class='date'>{$v['start']}</td>
			<td class='date'>{$v['end']}</td>
		</tr>

EOF;
}
else if ( strlen($v['end']) > 0 )
{
$ELMHTML .= <<<EOF
			<td class='span' colspan='2'>{$v['end']}</td>
		</tr>

EOF;
}
else
{
$ELMHTML .= <<<EOF
			<td class='span' colspan='2'>TBD</td>
		</tr>

EOF;
}

}
$date = date('F j, Y');
$ELMHTML .= <<<EOF
	</table>
	<htmlpagefooter name="myFooter">
		<table width="100%">
			<tr>
				<td width="50%"></td>
				<td width="50%" style="text-align: right;">Page {PAGENO} of {nbpg}</td>
			</tr>
		</table>
		<br />
	</htmlpagefooter>

EOF;
//--endhtml--//
return $ELMHTML;
}
}

?>