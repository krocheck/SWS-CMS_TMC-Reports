<?php

class skin_public extends Command {

protected function doExecute( $param )
{
}

//===========================================================================
// Navigation breadcrumb
//===========================================================================
public function breadcrumbWrapper( $urls )
{
$HTML = "";
//--starthtml--//

$HTML .= <<<EOF
		<div id="breadcrumb">
			<ul>

EOF;

foreach( $urls as $uri )
{

$HTML .= <<<EOF
				<li
EOF;
if ( isset( $uri['css'] ) ) { $HTML .= " class='{$uri['css']}'"; }
$HTML .= <<<EOF
><a href='{$uri['url']}'>{$uri['string']}</a></li>

EOF;
}
$HTML .= <<<EOF
			</ul>
		</div>

EOF;
//--endhtml--//
return $HTML;
}

//===========================================================================
// Logged in link
//===========================================================================
public function loggedIn( $id, $name )
{
$HTML = "";
//--starthtml--//

$HTML .= <<<EOF
		<div class="log-status">{$this->lang->getString('logged_in')} $name</div>
EOF;

//--endhtml--//
return $HTML;
}

//===========================================================================
// Navigation
//===========================================================================
public function navigationWrapper( $urls, $indent = "				", $location = 'outer' )
{
$indent .= "	";
$HTML = "";
//--starthtml--//

$HTML .= <<<EOF
{$indent}<ul>

EOF;
foreach( $urls as $uri )
{

	if ( isset( $uri['extra'] ) && is_array( $uri['extra'] ) && count( $uri['extra'] ) > 0 )
	{

$HTML .= <<<EOF
{$indent}	<li
EOF;

		if ( isset( $uri['css'] ) && $uri['css'] != "" ) { $HTML .= " class='{$uri['css']}'"; }

$HTML .= <<<EOF
><a
EOF;

		if ( isset( $uri['current'] ) && $uri['current'] != "" ) { $HTML .= " class='{$uri['current']}'"; }

$HTML .= <<<EOF
 href="{$uri['url']}">{$uri['string']}<!--[if gt IE 6]><!--></a><!--<![endif]--><!--[if lt IE 7]><table border="0" cellpadding="0" cellspacing="0"><tr><td><![endif]-->
{$this->navigationWrapper( $uri['extra'], $indent."	", 'inner' )}
{$indent}	</li>

EOF;

	}
	else
	{

$HTML .= <<<EOF
{$indent}	<li
EOF;

		if ( isset( $uri['css'] ) && $uri['css'] != "" ) { $HTML .= " class='{$uri['css']}'"; }

$HTML .= <<<EOF
><a
EOF;

		if ( isset( $uri['current'] ) && $uri['current'] != "" ) { $HTML .= " class='{$uri['current']}'"; }

$HTML .= <<<EOF
 href="{$uri['url']}">{$uri['string']}</a></li>

EOF;

	}
}

if ( $location == 'inner' ) {

$HTML .= <<<EOF
{$indent}</ul><!--[if lte IE 6]></td></tr></table></a><![endif]-->

EOF;

}
else
{

$HTML .= <<<EOF
{$indent}</ul>
EOF;

}

//--endhtml--//
return $HTML;
}

//===========================================================================
// Normal wrapper
//===========================================================================
public function userLinks( $links )
{
$HTML = "";
//--starthtml--//

$HTML .= <<<EOF
	<div id="control-panel">

EOF;

if ( count( $links ) > 0 )
{

foreach( $links as $info )
{
	
$HTML .= <<<EOF
		<a href="{$info['url']}"><div class='user-button'>{$info['text']}</div></a> 

EOF;

}

}

$HTML .= <<<EOF
	</div>

EOF;

//--endhtml--//
return $HTML;
}

//===========================================================================
// Normal wrapper
//===========================================================================
public function wrapper( $title, $navigation, $breadcrumb, $userlinks, $loggedin, $content, $bodyCss, $errors = "", $debug = "", $js = "" )
{
$HTML = "";
//--starthtml--//

$HTML .= <<<EOF
<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="{$this->registry->getConfig('base_url')}styles/design.css" media="all" />
{$js}
<title>{$title}</title>
</head>

<body>
{$content}

{$debug}
</body>
</html>

EOF;

//--endhtml--//
return $HTML;
}

}

?>