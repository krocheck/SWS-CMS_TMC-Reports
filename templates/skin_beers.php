<?php

class skin_beers extends Command {

protected function doExecute( $param )
{
}

//===========================================================================
// Filters
//===========================================================================
public function beerFilters( $counts, $ids, $cats, $numIDs, $categories )
{
$HTML = "";
//--starthtml--//

$catString = implode(',', $cats );
$numString = implode(',', $numIDs );

$HTML .= <<<EOF

<script type="text/javascript">

cats = {{$catString}};
idNums = {{$numString}};

EOF;

foreach( $ids as $id => $rows)
{

if ( is_array( $rows ) && count( $rows ) > 0 )
{

$HTML .= <<<EOF
rows['{$id}'] = new Array();
nums['{$id}'] = new Array();
nums['{$id}']['0'] = {$counts[$id][0]};

EOF;

foreach ( $rows as $cat => $catData )
{
$data = "'".implode("','", $catData)."'";

$HTML .= <<<EOF
rows['{$id}'][{$cat}] = new Array({$data});
nums['{$id}'][{$cat}] = {$counts[$id][$cat]};

EOF;
}
}
}
$HTML .= <<<EOF
</script>

<p>
	<a id='selAllState' href='#' onClick="switchState('0')">{$this->lang->getString('all')} ({$counts['0'][0]})</a> &bull; 
	<a id='selActState' href='#' onClick="switchState('a')">{$this->lang->getString('active')} ({$counts['a'][0]})</a> &bull; 
	<a id='selInState' href='#' onClick="switchState('i')">{$this->lang->getString('inactive')} ({$counts['i'][0]})</a>
</p>
<p>

EOF;

foreach ( $cats as $id => $val )
{

if ( $id == 0 )
{
	$name = $this->lang->getString('all');
}
else
{
	$name = $categories[ $id ]['title'];
}

$HTML .= <<<EOF
	<a id='sel{$id}ID' href='#' onClick="switchID('{$id}')">{$name} (<span id='num{$id}ID'>{$counts['0'][ $id ]}</span>)</a> &bull; 

EOF;
}
$HTML = substr( $HTML, 0, -9 );

$HTML .= <<<EOF

</p>
<script type="text/javascript">
window.onload = function() {
switchState('a');
switchID('0');
}
</script>

EOF;
//--endhtml--//
return $HTML;
}

}

?>