<?php

class skin_agenda extends Command {

protected $check = 0;

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

	<htmlpageheader name="myHeader" style="margin:0; padding:0;">
		<table width="100%" style="margin:0; padding:0;">
			<tr>
				<td width="50%"><img src="{$this->registry->getConfig('base_url')}images/print-logo.png" style="width:192px; height:50px;" /></td>
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
			<div class="item">
{$v->getContent()}
			</div>
			<hr />
		</div>

EOF;
}
$date = date('F j, Y g:i A');
$ELMHTML .= <<<EOF
	</div>
	<htmlpagefooter name="myFooter">
		<table width="100%">
			<tr>
				<td width="50%">Generated: {$date}</td>
				<td width="50%" style="text-align: right;">Page {PAGENO} of {nbpg}</td>
			</tr>
		</table>
		<br />
	</htmlpagefooter>

EOF;
//--endhtml--//
return $ELMHTML;
}

//===========================================================================
// Show
//===========================================================================
public function section( $r ) {

$this->check = 1;

$ELMHTML = "";
//--starthtml--//
$ELMHTML .= <<<EOF
	<div class='no-break'>
	<h4>{$r['name']}</h4>

EOF;
//--endhtml--//
return $ELMHTML;
}
//===========================================================================
// Show
//===========================================================================
public function show( $r ) {

$tags = $this->cache->getCache('tags');
$fields = $this->cache->getCache('fields');
$users = $this->cache->getCache('users');
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
else if( $r['due_on'] != '0000-00-00' )
{
	$endDate = strtotime($r['due_on']);
	$date = date('M j', $endDate);
}
else
{
	$date = "Dates TBD";
}

if ( $fields[512462680735933]['enum_options'][$r['custom_fields'][512462680735933]]['name'] != '' )
{
	$producer = $fields[512462680735933]['enum_options'][$r['custom_fields'][512462680735933]]['name'];
}
else
{
	$producer = '?';
}

if ( $fields[512408346444708]['enum_options'][$r['custom_fields'][512408346444708]]['name'] != '' )
{
	$ae = $fields[512408346444708]['enum_options'][$r['custom_fields'][512408346444708]]['name'];
}
else
{
	$ae = '?';
}

if ( $fields[1179336083412405]['enum_options'][$r['custom_fields'][1179336083412405]]['name'] != '' )
{
	$td = $fields[1179336083412405]['enum_options'][$r['custom_fields'][1179336083412405]]['name'];
}
else
{
	$td = '?';
}

if ( $r['custom_fields'][512408346444750] != '' )
{
	$job = $r['custom_fields'][512408346444750];
}
else
{
	$job = date('Y') . '-?';
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

if (strlen($location) > 0)
{
	$location .= ' |';
}

$ELMHTML = "";
//--starthtml--//
$ELMHTML .= <<<EOF
	<div class="show">
		<strong>{$r['name']}</strong> | {$location} {$date}<br />
		{$job} | Producer: {$producer} | TD: {$td} | AE: {$ae} {$tagSep} 
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
// Show
//===========================================================================
public function showArchive( $r ) {

$tags = $this->cache->getCache('tags');
$fields = $this->cache->getCache('fields');
$users = $this->cache->getCache('users');
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

$date .= ' | ';

if ( strlen($r['custom_fields'][512408346444750]) > 0 )
{
	$r['custom_fields'][512408346444750] .= ' | ';
}

$location = $r['custom_fields'][512544451401414];

if ( strlen( $location ) > 0 )
{
	$location .= ' | ';
}

if ( strlen($fields[512462680735933]['enum_options'][$r['custom_fields'][512462680735933]]['name']) > 0 )
{
	$fields[512462680735933]['enum_options'][$r['custom_fields'][512462680735933]]['name'] = 'Producer: ' . $fields[512462680735933]['enum_options'][$r['custom_fields'][512462680735933]]['name'] . ' | ';
}

if ( strlen($fields[512408346444708]['enum_options'][$r['custom_fields'][512408346444708]]['name']) > 0 )
{
	$fields[512408346444708]['enum_options'][$r['custom_fields'][512408346444708]]['name'] = 'AE: ' . $fields[512408346444708]['enum_options'][$r['custom_fields'][512408346444708]]['name'];
}

$ELMHTML = "";
//--starthtml--//
$ELMHTML .= <<<EOF
	<div class="archive">
		<strong>{$r['name']}</strong> | {$r['custom_fields'][512408346444750]}{$date}{$location}{$fields[512462680735933]['enum_options'][$r['custom_fields'][512462680735933]]['name']}{$fields[512408346444708]['enum_options'][$r['custom_fields'][512408346444708]]['name']}
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
$fields = $this->cache->getCache('fields');
$users = $this->cache->getCache('users');
$date = "";

if ( $r['due_date'] != '0000-00-00' )
{
	$endDate = strtotime($r['due_date']);
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

if ( $fields[512462680735933]['enum_options'][$r['custom_fields'][512462680735933]]['name'] != '' )
{
	$producer = $fields[512462680735933]['enum_options'][$r['custom_fields'][512462680735933]]['name'];
}
else
{
	$producer = '?';
}

if ( $fields[512408346444708]['enum_options'][$r['custom_fields'][512408346444708]]['name'] != '' )
{
	$ae = $fields[512408346444708]['enum_options'][$r['custom_fields'][512408346444708]]['name'];
}
else
{
	$ae = '?';
}

if ( $r['custom_fields'][512408346444750] != '' )
{
	$job = $r['custom_fields'][512408346444750];
}
else
{
	$job = date('Y') . '-?';
}

$ELMHTML = "";
//--starthtml--//
$ELMHTML .= <<<EOF
	<div class="production">
		<strong>{$r['custom_fields'][1200086993568098]} | {$r['custom_fields'][1200086994091202]}</strong> | {$date}<br />
		{$job} | Producer: {$producer} | AE: {$ae}
<br />
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