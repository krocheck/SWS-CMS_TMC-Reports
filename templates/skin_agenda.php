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
$meetingDate = (date('D') == 'Mon' ? date('F j, Y') : date('F j, Y', $nextMonday));

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
		<div class="group"{$v->getID()}>
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
		<htmlpagefooter name="myFooter">
			<table width="100%">
				<tr>
					<td width="33%">Tri-Marq Reports | Weekly Staff Meeting Agenda</td>
					<td width="33%" align="center"></td>
					<td width="33%" style="text-align: right;">Page: {PAGENO} of {nbpg}</td>
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

$ELMHTML = "";
//--starthtml--//
$ELMHTML .= <<<EOF
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
//--endhtml--//
return $ELMHTML;
}

}

?>