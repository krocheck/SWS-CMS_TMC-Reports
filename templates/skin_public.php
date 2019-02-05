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
			<div id='login-head'>STUB CLUB MEMEBER SIGN IN</div><br />
			<label class='text'>E-mail Address: </label>
			<input type="text" id="email" name="email" maxlength="150" width="40" tabindex="1" value="" class='text' /><br />
			<label class='text'>Password: </label>
			<input type="password" id="password" name="password" maxlength="150" width="40" tabindex="2" value="" class='text' /><br />
			<br />
			<input type="image" id="submit" name="submit" tabindex="4" src="{$this->registry->getConfig('base_url')}/images/signin.png" alt="{$this->lang->getString('login_submit')}" />
			<input type="checkbox" name="remember" value="1" id='remember' class='check' tabindex="3" /> Remember Me<br />
			<a href='{$this->display->buildURL( array( 'register' => 'true' ) )}'>
				<div id='register-button' class='red-button'>FIRST TIME HERE?<br />REGISTER YOUR ACCOUNT</div>
			</a>
		</form>
	</div>
	<a href="{$this->registry->getConfig('base_url')}">
		<div id='guest-login' class='red-button'>I&#39;M NOT A MEMBER BUT I WANT TO SEE YOUR BEER</div>
	</a>

EOF;

//--endhtml--//
return $HTML;
}

//===========================================================================
// Login Form
//===========================================================================
public function registerForm( $hiddenInputs )
{
$HTML = "";
//--starthtml--//

$HTML .= <<<EOF
	<div id='register'>
		<form action="{$this->registry->getConfig('base_url')}" method="post" id="register" name="name">
			<input type="hidden" name="register" value="true" />

EOF;

foreach( $hiddenInputs as $k => $v )
{

$HTML .= <<<EOF
			<input type="hidden" name="{$k}" value="{$v}" />

EOF;

}

$HTML .= <<<EOF
			<div id='register-head'>STUB CLUB ACCOUNT ACTIVATION</div><br />
			If you are not a Stub Club Member yet, please ask your server or bar tender about becoming a memeber during your next visit.
			<div class="red-hr"></div><br />
			If you are already Stub Club member, please use this form to activate your online account.<br />
			{$this->display->getErrorHTML()}<br />
			<input type="hidden" name="do" value="create" />
			<label class='text'>Club #: </label>
			<input type="text" id="club_id" name="club_id" maxlength="5" width="40" tabindex="1" value="" class='text' /><br />
			<label class='text'>First Name: </label>
			<input type="text" id="first_name" name="first_name" maxlength="150" width="40" tabindex="2" value="" class='text' /><br />
			<label class='text'>Last Name: </label>
			<input type="text" id="last_name" name="last_name" maxlength="150" width="40" tabindex="3" value="" class='text' /><br />
			<label class='text'>E-mail Address: </label>
			<input type="text" id="email" name="email" maxlength="150" width="40" tabindex="4" value="" class='text' /><br />
			<label class='text'>Confirm E-mail: </label>
			<input type="text" id="email_confirm" name="email_confirm" maxlength="150" width="40" tabindex="5" value="" class='text' /><br />
			<label class='text'>Password: </label>
			<input type="password" id="password_new" name="password_new" maxlength="150" width="40" tabindex="6" value="" class='text' /><br />
			<label class='text'>Confirm Password: </label>
			<input type="password" id="password_confirm" name="password_confirm" maxlength="150" width="40" tabindex="7" value="" class='text' /><br />
			<br />
			<input type="submit" class="red-button" id="submit" name="submit" tabindex="8" value='REGISTER ACCOUNT' />
		</form>
	</div>

EOF;

//--endhtml--//
return $HTML;
}

//===========================================================================
// Login Form
//===========================================================================
public function profileForm( $hiddenInputs, $visibleInputs )
{
$HTML = "";
//--starthtml--//

$HTML .= <<<EOF
	<div id='account'>
		<form action="{$this->registry->getConfig('base_url')}" method="post" id="account" name="name">
			<input type="hidden" name="account" value="true" />

EOF;

foreach( $hiddenInputs as $k => $v )
{

$HTML .= <<<EOF
			<input type="hidden" name="{$k}" value="{$v}" />

EOF;

}

$HTML .= <<<EOF
			<div id='account-head'>STUB CLUB PROFILE</div><br />
			Please use the form below to update your account information.
			<div class="red-hr"></div><br />
			If you are changing your e-mail address, you must provide your current password.  You cannot change your password and e-mail address at the same time.<br />
			{$this->display->getErrorHTML()}<br />
			<input type="hidden" name="do" value="save" />
			<label class='text'>Club #: </label>
			<input type="text" id="club_id" name="club_id" maxlength="5" width="40" tabindex="1" value="{$visibleInputs['club_id']}" class='text' disabled /><br />
			<label class='text'>First Name: </label>
			<input type="text" id="first_name" name="first_name" maxlength="150" width="40" tabindex="2" value="{$visibleInputs['first_name']}" class='text' /><br />
			<label class='text'>Last Name: </label>
			<input type="text" id="last_name" name="last_name" maxlength="150" width="40" tabindex="3" value="{$visibleInputs['last_name']}" class='text' /><br />
			<label class='text'>E-mail Address: </label>
			<input type="text" id="email" name="email" maxlength="150" width="40" tabindex="4" value="{$visibleInputs['email']}" class='text' /><br />
			<label class='text'>Confirm E-mail: </label>
			<input type="text" id="email_confirm" name="email_confirm" maxlength="150" width="40" tabindex="5" value="" class='text' /><br />
			<label class='text'>Password: </label>
			<input type="password" id="password_new" name="password_new" maxlength="150" width="40" tabindex="6" value="" class='text' /><br />
			<label class='text'>Confirm Password: </label>
			<input type="password" id="password_confirm" name="password_confirm" maxlength="150" width="40" tabindex="7" value="" class='text' /><br />
			<br />
			<input type="submit" class="red-button" id="submit" name="submit" tabindex="8" value='SAVE PROFILE' />
		</form>
	</div>

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
public function mobileWrapper( $title, $navigation, $breadcrumb, $userlinks, $loggedin, $content, $bodyCss, $errors = "", $debug = "", $js = "" )
{
$HTML = "";
//--starthtml--//

$HTML .= <<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="{$this->registry->getConfig('base_url')}styles/design.css" media="all" />
{$js}
<title>{$title}</title>
</head>

<body>
{$userlinks}
	<div id="header"><a href="{$this->display->buildURL( array() )}"><img src="{$this->registry->getConfig('base_url')}images/blank.gif" width="900" height="140" /></a></div>
{$content}
	<div id="footer">
		{$this->lang->getString('copyright')}
	</div>
{$debug}
</body>
</html>

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
<link rel="stylesheet" type="text/css" href="{$this->registry->getConfig('base_url')}styles/mobile-design.css" media="all" />
{$js}
<title>{$title}</title>
</head>

<body>
{$userlinks}
	<div id="header"><a href="{$this->display->buildURL( array() )}"><img src="{$this->registry->getConfig('base_url')}images/blank.gif" width="200" height="70" /></a></div>
{$content}
	<div id="footer">
		{$this->lang->getString('copyright')}
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