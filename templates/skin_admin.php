<?php

class skin_admin extends Command {

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
// Login Form
//===========================================================================
public function loginForm( $hiddenInputs )
{
$HTML = "";
//--starthtml--//

$HTML .= <<<EOF
		<div id='login'>
			<form action="./" method="post" id="login" name="name">
				<input type="hidden" name="login" value="true" />

EOF;

foreach( $hiddenInputs as $k => $v )
{

$HTML .= <<<EOF
				<input type="hidden" name="{$k}" value="{$v}" />

EOF;

}

$HTML .= <<<EOF
				<label>{$this->lang->getString('login_email')}</label>
				<input type="text" id="email" name="email" maxlength="150" width="40" tabindex="0" value="" /><br />
				<label>{$this->lang->getString('login_password')}</label>
				<input type="password" id="password" name="password" maxlength="150" width="40" tabindex="0" value="" /><br />
				<br />
				<input type="submit" id="submit" name="submit" value="{$this->lang->getString('login_submit')}" />
			</form>
		</div>

EOF;

//--endhtml--//
return $HTML;
}

//===========================================================================
// Navigation
//===========================================================================
public function navigationWrapper( $urls, $indent = "	", $location = 'outer' )
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

		if ( isset( $uri['current'] ) && $uri['current'] != "" ) { $HTML .= " id='{$uri['current']}'"; }

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

		if ( isset( $uri['current'] ) && $uri['current'] != "" ) { $HTML .= " id='{$uri['current']}'"; }

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
public function userLinks( $applink, $appname, $logout )
{
$HTML = "";
//--starthtml--//

$HTML .= <<<EOF
<div class="control-panel">

EOF;

if ( strlen( $applink ) > 0 )
{

$HTML .= <<<EOF
	<a href='{$applink}'>{$this->lang->getString( $appname )}</a> &middot; 
EOF;

}

$HTML .= <<<EOF
	<a href='{$logout}'>{$this->lang->getString('logout')}</a>
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
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="{$this->registry->getConfig('base_url')}styles/admin-design.css" media="all" />
<title>{$title}</title>
{$js}
</head>

<body>
<div class="admin-main">
	<div class="topbanner">
		{$userlinks}
		<div class="title"><h1>{$this->lang->getString('program_title')}</h1></div>
		<a href='{$this->registry->getConfig('base_url')}admin/'><img src="{$this->registry->getConfig('base_url')}images/admin-logo.jpg" alt="SSUSC Logo" /></a>
	</div>
	<div id="navigation"> 
{$loggedin}
{$navigation}
	</div>
	<div class='content'>
{$breadcrumb}
EOF;
if ( strlen( $errors ) > 0 ) {
$HTML .= <<<EOF
<ul class="error" style="color:red;">{$errors}</ul>

EOF;
}
$HTML .= <<<EOF
{$content}
	</div>
</div>
{$debug}
</body>
</html>

EOF;

//--endhtml--//
return $HTML;
}

}

?>