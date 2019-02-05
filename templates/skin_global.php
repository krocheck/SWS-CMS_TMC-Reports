<?php

class skin_global extends Command {

protected function doExecute( $param )
{
}

//===========================================================================
// Name: dropdown_wrapper
//===========================================================================
function dropdown_wrapper($name="",$data="")
{
$HTML = "";
//--starthtml--//

$HTML .= <<<EOF
<select class='input_select' name='{$name}'>
{$data}
</select>
EOF;

//--endhtml--//
return $HTML;
}

//===========================================================================
// Error display
//===========================================================================
public function errorWrapper( $title, $content, $debug )
{
$HTML = "";
//--starthtml--//

$HTML .= <<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="{$this->registry->getConfig('base_url')}styles/admin-design.css" media="all" />
<title>{$this->registry->getLang()->getString('site_title')}</title>
</head>

<body>
<div class="admin-main">
	<div class="topbanner">
		<a href="{$this->registry->getConfig('base_url')}"><img src="{$this->registry->getConfig('base_url')}images/admin-logo.png" alt="Tri-Marq Logo" /></a>
	</div>
<h1>{$this->registry->getLang()->getString('site_title')}</h1>
<h3>{$content}</h3>
</div>
{$debug}
</body>
</html>

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
public function rteJSHead()
{
$HTML = "";
//--starthtml--//

$HTML .= <<<EOF
<script type="text/javascript" src="{$this->registry->getConfig('base_url')}js/ckeditor/ckeditor.js"></script>

EOF;

//--endhtml--//
return $HTML;
}

}

?>