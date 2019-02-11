<?php

class skin_agenda extends Command {

$check = 0;

protected function doExecute( $param )
{
}

//===========================================================================
// Normal wrapper
//===========================================================================
public function wrapper( $members ) {

$nextMonday = strtotime('next monday');
$meetingDate = (date('D') == 'Mon' ? date('F j, Y') : date('F j, Y', $nextMonday));

$ELMHTML = "";
//--starthtml--//
$ELMHTML .= <<<EOF

	<div id="print-link"><a href="{$this->display->buildURL( array('do' => "pdf") )}">Generate PDF</a>&nbsp;&nbsp;&nbsp;</div>
	<htmlpageheader name="myHeader" style="margin:0; padding:0;">
		<table width="100%" style="margin:0; padding:0;">
			<tr>
				<td width="50%"><a href="{$this->display->buildURL( array() )}"><img src="{$this->registry->getConfig('base_url')}images/admin-logo.png" /></a></td>
				<td width="50%" style="text-align: right;"><h1>STAFF MEETING: {$meetingDate}</h1></td>
			</tr>
		</table>
		<hr style="margin:0; color:#fdb514;" />
	</htmlpageheader>
	<div class="content">

EOF;
foreach( $members as $v ) {
$ELMHTML .= <<<EOF
		<div class="group"{$v->getID()}>
			<h2>{$v->getName()}</h2>
			<div>
{$v->getContent()}
			</div>
			<hr />
		</div>

EOF;
}
$ELMHTML .= <<<EOF
		</div>
		<htmlpagefooter name="myFooter">
			<table width="100%">
				<tr>
					<td width="50%">Tri-Marq Reports | Weekly Staff Meeting Agenda</td>
					<td width="50%" style="text-align: right;">Page: {PAGENO} of {nbpg}</td>
				</tr>
			</table>
		</htmlpagefooter>

EOF;
//--endhtml--//
return $ELMHTML;
}
	
//===========================================================================
// Show
//===========================================================================
public function section( $r ) {

$name = substr($r['name'],0,strlen($r['name'])-1);
$this->check = 1;

$ELMHTML = "";
//--starthtml--//
$ELMHTML .= <<<EOF
	<div class='no-break'>
	<h4>{$name}</h4>

EOF;
//--endhtml--//
return $ELMHTML;
}
//===========================================================================
// Show
//===========================================================================
public function show( $r ) {

$tags = $this->cache->getCache('tags');
$date = "";

if ( $r['start_on'] != '0000-00-00' )
{
	$startDate = strtotime($r['start_on']);
	$endDate = strtotime($r['due_on']);

	if ( date('M', $startDate) != date('M', $endDate) )
	{
		$date = date('M j', $startDate) . ' - ' . date('M j', $endDate);
	}
	else
	{
		$date = date('M j', $startDate) . ' - ' . date('j', $endDate);
	}
}
else
{
	$endDate = strtotime($r['due_on']);
	$date = date('M j', $endDate);
}

if ( count($r['tags']) > 0 )
{
	$tagSep = '|';
}
else
{
	$tagSep = '';
}

if ( strlen($r['custom_fields'][512544451401414]) > 0 && strlen($r['custom_fields'][512544451401416]) > 0 )
{
	$location = $r['custom_fields'][512544451401414] . ' @ ' . $r['custom_fields'][512544451401416];
}
else
{
	$location = $r['custom_fields'][512544451401414] . $r['custom_fields'][512544451401416];
}

$ELMHTML = "";
//--starthtml--//
$ELMHTML .= <<<EOF
	<div class="show">
		<strong>{$r['name']}</strong> | {$location} | {$date}<br />
		{$r['custom_fields'][512408346444750]} | Producer: {$r['custom_fields'][512462680735933]} | AE: {$r['custom_fields'][512408346444708]} {$tagSep} 
EOF;
if (count($r['tags']) > 0) {
foreach( $r['tags'] as $v ) {
$ELMHTML .= <<<EOF
<span class="pill {$tags[$v]['color']}">&nbsp;{$tags[$v]['name']}&nbsp;</span>&nbsp;
EOF;
} }
$ELMHTML .= <<<EOF
<br />
{$r['description']}
	</div>

EOF;

if ( $this->check == 1 ) {
$ELMHTML .= <<<EOF
	</div>

EOF;

$this->check = 0;
}

//--endhtml--//
return $ELMHTML;
}

//===========================================================================
// Production
//===========================================================================
public function production( $r ) {

$tags = $this->cache->getCache('tags');
$date = "";

if ( $r['due_on'] != '0000-00-00' )
{
	$endDate = strtotime($r['due_on']);
	$date = 'Due: ' . date('F j', $endDate);

	if ( $endDate < time() )
	{
		$date = "<span class='pill red' style='font-size:13px;'>&nbsp;" . $date . "&nbsp;</span>";
	}
}
else
{
	$date = 'Due: ?';
}

if ( count($r['tags']) > 0 )
{
	$tagSep = '|';
}
else
{
	$tagSep = '';
}

$ELMHTML = "";
//--starthtml--//
$ELMHTML .= <<<EOF
	<div class="production">
		<strong>{$r['name']}</strong> | {$date}<br />
		{$r['custom_fields'][512408346444750]} | Producer: {$r['custom_fields'][512462680735933]} | AE: {$r['custom_fields'][512408346444708]} {$tagSep} 
EOF;
if (count($r['tags']) > 0) {
foreach( $r['tags'] as $v ) {
$ELMHTML .= <<<EOF
<span class="pill {$tags[$v]['color']}">&nbsp;{$tags[$v]['name']}&nbsp;</span>&nbsp;
EOF;
} }
$ELMHTML .= <<<EOF
<br />
{$r['description']}
	</div>

EOF;

if ( $this->check == 1 ) {
$ELMHTML .= <<<EOF
	</div>

EOF;

$this->check = 0;
}

//--endhtml--//
return $ELMHTML;
}

}

?>