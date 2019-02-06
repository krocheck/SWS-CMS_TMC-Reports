<?php

/**
 * SWS-CMS System
 *  - Simna Web Services Programming Team
 * 
 * Skinning component to assist with forms and tables
 * Last Updated: $Date: 2010-04-28 13:42:06 -0500 (Wed, 28 Apr 2010) $
 *
 * @author		$Author: krocheck $
 * @copyright	2009 Simna Web Services, LLC
 * @package		SWS-CMS
 * @subpackage	Core
 * @link		http://www.simnaweb.com
 * @since		1.0.0
 * @version		$Revision: 2 $
 */

class AdminSkin extends Command
{
	/**
	 * TD column width array
	 *
	 * @access public
	 * @var array
	 * @since 1.0.0
	 */
	public $td_widths = array();
	/**
	 * TD header title array
	 *
	 * @access public
	 * @var array
	 * @since 1.0.0
	 */
	public $td_header = array();
	/**
	 * Column counter
	 *
	 * @access public
	 * @var int
	 * @since 1.0.0
	 */
	public $td_colspan;

	private $dateJS = FALSE;

	/**
	 * Minor class setup
	 *
	 * @param object $param extra thingy from execute
	 * @return void
	 * @access protected
	 * @since 1.0.0
	 */
	protected function doExecute( $param )
	{
		$this->registry->getDisplay()->addDebug( "Admin Skin Library Loaded" );
	}

	/**
	 * Print a TD row
	 *
	 * @param array $array the tds
	 * @param string $align horizontal alignment, DEFAULT is left
	 * @param string $id css for the cell
	 * @return string
	 * @access public
	 * @since 1.0.0
	 */
	public function addTdBasic( $text = '', $align = 'left', $id = '' )
	{
		$html    = '';
		$colspan = '';

		if ( $text != '' )
		{
			if ( $this->td_colspan > 0 )
			{
				$colspan = " colspan='{$this->td_colspan}'";
			}

			if ( $id != '' )
			{
				$id = " class='{$id}'";
			}

			$html .= "\n\t\t<tr><td align='{$align}'{$id}{$colspan}>{$text}</td></tr>";
		}

		return $html;
	}

	/**
	 * Print a TD row
	 *
	 * @param array $array the tds
	 * @param string $css [DEPRECATED]
	 * @param string $align vertical alignment, DEFAULT is middle
	 * @return string
	 * @access public
	 * @since 1.0.0
	 */
	public function addTdRow( $array, $css = '', $align = 'middle', $id = '' )
	{
		$html = '';

		if ( is_array( $array ) )
		{
			$html = "\n\t\t<tr{$id}>";

			$count = count($array);

			$this->td_colspan = $count;

			for( $i = 0; $i < $count ; $i++ )
			{
				if ( $css != '' )
				{
					$td_col = $css;
				}

				if ( is_array( $array[$i] ) )
				{
					$text    = $array[$i][0];
					$colspan = $array[$i][1];

					$html .= "\n\t\t\t<td colspan='{$colspan}' valign='{$align}'>{$text}</td>";
				}
				else
				{
					if ( isset( $this->td_header[$i][1] ) AND $this->td_header[$i][1] != '')
					{
						$width = " width='{$this->td_header[$i][1]}'";
					}
					else
					{
						$width = '';
					}

					$html .= "\n\t\t\t<td valign='{$align}'{$width}>{$array[$i]}</td>";
				}
			}

			$html .= "\n\t\t</tr>";
		}

		return $html;
	}

	/**
	 * Print a blank TD row
	 *
	 * @return string
	 * @access public
	 * @since 1.0.0
	 */
	public function addTdSpacer()
	{
		if ( $this->td_colspan > 0 )
		{
			$colspan = " colspan='{$this->td_colspan}'";
		}

		return "\n\t\t<tr><td{$colspan}><br /></td></tr>";
	}

	/**
	 * Get a blank.gif
	 *
	 * @param int $width width of the blank, DEFAULT is 17
	 * @param int $height height of the blank, DEFAULT is 17
	 * @return string
	 * @access public
	 * @since 1.0.0
	 */
	function blankIMG( $width = 17, $height = 17 )
	{
		return "<img src='{$this->registry->getConfig('base_url')}images/blank.gif' width='{$width}' height='{$height}' border='0' style='vertical-align:middle' />&nbsp;&nbsp;&nbsp";
	}

	/**
	 * Print a down order button
	 *
	 * @param string $req module for the URL
	 * @param int $data id number for the URL
	 * @return string
	 * @access public
	 * @since 1.0.0
	 */
	function downButton( $url )
	{
		return "<a href='{$url}' title='{$this->lang->getString('move_down')}'><img src='{$this->registry->getConfig('base_url')}/images/down-arrow.gif' width='17' height='17' border='0' style='vertical-align:middle' /></a>&nbsp;&nbsp;&nbsp;";
	}

	/**
	 * Close a fieldset table group
	 *
	 * @return string
	 * @access public
	 * @since 1.0.0
	 */
	public function endFieldset()
	{
		return "\n\t\t\t\t\t</table>\n\t\t\t\t</fieldset>\n\t\t\t</td>\n\t\t</tr>";
	}

	/**
	 * Add a tr with a submit button and close the table
	 *
	 * @param string $text text for the submit button
	 * @param string $js javascript for the submit button
	 * @param string $extra additional text after the submit button
	 * @return string
	 * @access public
	 * @since 1.0.0
	 */
	public function endForm( $text = '', $js = '', $extra = '', $buttonOnly = 0 )
	{
		$html    = '';
		$colspan = '';

		if ( $buttonOnly == 1 )
		{
			$html .= "<input type='submit' value='{$text}' accesskey='s'{$js}>";
		}
		else
		{
			if ( $js != '' )
			{
				$js = ' ' . $js;
			}

			if ( $text != '' )
			{
				if ( $this->td_colspan > 0 )
				{
					$colspan = " colspan='{$this->td_colspan}' ";
				}

				$html .= "\n\t\t<tr><td align='center'{$colspan}><input type='submit' value='{$text}' accesskey='s'{$js}>{$extra}</td></tr>";
			}

			$html .= "\n\t</table>\n</form>";
		
			$this->td_header = array();
		}

		return $html;
	}

	/**
	 * Close a table
	 *
	 * @return string
	 * @access public
	 * @since 1.0.0
	 */
	public function endTable()
	{
		$this->td_header = array();

		return "\n\t</table>";
	}

	/**
	 * Print a checkbox
	 *
	 * @param string $name name of the input
	 * @param int $checked 1=checked, DEFAULT is 0
	 * @param int $val value of checkbox, DEFAULT is 1
	 * @param string $js javascript for the input
	 * @return string
	 * @access public
	 * @since 1.0.0
	 */
	public function formCheckbox( $name, $checked = 0, $val = 1, $js = '' )
	{
		if ( $js != '' )
		{
			$js = ' ' . $js;
		}

		if ( $checked == 1 )
		{
			return "<input type='checkbox' name='{$name}' value='{$val}' checked='checked'{$js}>";
		}
		else
		{
			return "<input type='checkbox' name='{$name}' value='{$val}'{$js}>";
		}
	}

	/**
	 * Print a date field
	 *
	 * @param string $name name of the input
	 * @param DateTime $date the date object for the input
	 * @param string $pair1 name of a related field (left)
	 * @param string $pair2 name of a related field (right)
	 * @return string
	 * @access public
	 * @since 1.0.0
	 */
	public function formDate( $name, $date, $pair1 = '', $pair2 = '' )
	{
		$out = '';

		if ( $this->dateJS == FALSE )
		{
			$this->display->addJavascript("<script language='javascript' src='{$this->registry->getConfig('base_url')}js/calendar/calendar.js'></script>");
			$this->dateJS = TRUE;
		}

		require_once( SWS_JS_PATH . 'calendar/classes/tc_calendar.php');

		ob_start();

		$myCalendar = new tc_calendar($name, true, false);
		$myCalendar->setIcon($this->registry->getConfig('base_url')."js/calendar/images/iconCalendar.gif");
		$myCalendar->setDate($date->format('d'), $date->format('m'), $date->format('Y'));
		$myCalendar->setPath($this->registry->getConfig('base_url')."js/calendar/");
		$myCalendar->setYearInterval(2000, intval(date('Y')));
		$myCalendar->setAlignment('left', 'bottom');

		if ( $pair2 <> '' )
		{
			$myCalendar->setDatePair($name, $pair2, $date->format('Y-m-d'));
		}
		else if ( $pair1 <> '' )
		{
			$myCalendar->setDatePair($pair1, $name, $date->format('Y-m-d'));
		}

		$myCalendar->writeScript();

		$out = ob_get_contents();

		ob_end_clean();

		return $out;
	}

	/**
	 * Print a dropdown field
	 *
	 * @param string $name name of the input
	 * @param array $list array of values
	 * @param string $default selected value
	 * @param string $js javascript for the input
	 * @param string $css css class to apply to input
	 * @return string
	 * @access public
	 * @since 1.0.0
	 */
	public function formDropdown( $name, $list = array(), $default_val = '', $js = '', $css = '')
	{
		if ( $js != '' )
		{
			$js = ' ' . $js;
		}

		if ( $css != '' )
		{
			$css = " class='{$css}'";
		}

		$html = "\n<select name='{$name}'{$js}{$css}>";

		if ( is_array( $list ) && count( $list ) > 0 )
		{
			foreach( $list as $v )
			{
				$selected = "";

				if ( ( $default_val != '' ) and ( $v[0] == $default_val ) )
				{
					$selected = ' selected="selected"';
				}

				$html .= "\n\t<option value='{$v[0]}'{$selected}>{$v[1]}</option>";
			}
		}

		$html .= "\n</select>";

		return $html;
	}

	/**
	 * Print hidden tags
	 *
	 * @param array $hiddens the hidden form values
	 * @return string
	 * @access public
	 * @since 1.0.0
	 */
	public function formHidden( $hiddens = array() )
	{
		$form = '';

		if ( is_array( $hiddens ) && count( $hiddens ) > 0 )
		{
			foreach( $hiddens as $k => $v )
			{
				$form .= "\n\t<input type='hidden' name='{$k}' value='{$v}'>";
			}
		}

		return $form;
	}

	/**
	 * Print a generic input field
	 *
	 * @param string $name name of the input
	 * @param string $value value of the input
	 * @param string $type type of input, DEFAULT is text
	 * @param string $js javascript for the input
	 * @param string $size width of the input, DEFAULT is 30
	 * @return string
	 * @access public
	 * @since 1.0.0
	 */
	public function formInput( $name, $value = '', $type = 'text', $js = '', $size = '30')
	{
		if ( $js != '' )
		{
			$js = ' ' . $js;
		}

		return "<input type='{$type}' name='{$name}' value=\"{$value}\" size='{$size}'{$js}>";
	}

	/**
	 * Print a multi-select field
	 *
	 * @param string $name name of the input
	 * @param array $list array of values
	 * @param array $default array of selected values
	 * @param string $size height of the input, DEFAULT is 5
	 * @param string $js javascript for the input
	 * @return string
	 * @access public
	 * @since 1.0.0
	 */
	public function formMultiselect( $name, $list = array(), $default = array(), $size = '5', $js = '')
	{
		if ( $js != '' )
		{
			$js = ' ' . $js;
		}

		$html = "\n<select name='{$name}' multiple='multiple' size='{$size}'{$js}>";

		foreach( $list as $v )
		{
			$selected = "";

			if ( count( $default ) > 0 )
			{
				if ( in_array( $v[0], $default ) )
				{
					$selected = ' selected="selected"';
				}
			}

			$html .= "\n\t<option value='{$v[0]}'{$selected}>{$v[1]}</option>";
		}

		$html .= "\n</select>";

		return $html;
	}

	/**
	 * Print a rich text editor field
	 *
	 * @param string $name name of the input
	 * @param string $value value of the input
	 * @return string
	 * @access public
	 * @since 1.0.0
	 */
	public function formRTE($name, $value="")
	{
		$value = str_replace("&#39;",  "'",  $value);
		$value = str_replace("\n",     "",   $value);
		$value = str_replace("<br>",   "\n", $value);
		$value = str_replace("<br />", "\n", $value);

		return "<textarea name='{$name}' id='{$name}'>{$value}</textarea>
<script type='text/javascript'>\n\tCKEDITOR.replace( '{$name}' );\n</script>";

	}

	/**
	 * Print a generic text input field
	 *
	 * @param string $name name of the input
	 * @param string $value value of the input
	 * @param string $size width of the input, DEFAULT is 30
	 * @return string
	 * @access public
	 * @since 1.0.0
	 */
	public function formSimpleInput( $name, $value = '', $size = '5' )
	{
		return "<input type='text' name='{$name}' value='{$value}' size='{$size}'>";
	}

	/**
	 * Print a generic textarea field
	 *
	 * @param string $name name of the input
	 * @param string $value value of the input
	 * @param string $cols width of the input, DEFAULT is 60
	 * @param string $rows height of the input, DEFAULT is 5
	 * @param string $wrap textarea wrap setting, DEFAULT is soft
	 * @param string $id form id if different from $name
	 * @param string $style css class to apply to input
	 * @param string $js javascript for the input
	 * @return string
	 * @access public
	 * @since 1.0.0
	 */
	public function formTextarea( $name, $value = '', $cols = '60', $rows = '5', $wrap = 'soft', $id = '', $style = '', $js = '')
	{
		if ( $js != '' )
		{
			$js = ' ' . $js;
		}

		if ( $id != '' )
		{
			$id = " id='{$id}'";
		}
		else
		{
			$id = " id='{$name}'";
		}

		if ( $style )
		{
			$style = " class='{$style}'";
		}

		return "<textarea name='{$name}' cols='{$cols}' rows='{$rows}' wrap='{$wrap}'{$id}{$style}{$js}>{$value}</textarea>";
	}

	/**
	 * Print a file upload input
	 *
	 * @param string $name name the input, DEFAULT is 'FILE_UPLOAD'
	 * @param string $js javascript for form
	 * @return string
	 * @access public
	 * @since 1.0.0
	 */
	public function formUpload( $name = 'FILE_UPLOAD', $js = '' )
	{
		if ( $js != '' )
		{
			$js = ' ' . $js;
		}

		return "<input type='file' size='30' name='{$name}'{$js}>";
	}

	/**
	 * Print a yes, no radio group
	 *
	 * @param string $name name of the input
	 * @param int $default_val default value, 0=no, 1=yes, DEFAULT is 0
	 * @param array $js javascript for the input [{no => '', yes => '']
	 * @return string
	 * @access public
	 * @since 1.0.0
	 */
	public function formYesNo( $name, $default_val = 0, $js = array() )
	{
		$yesJS = '';
		$noJS  = '';

		if ( isset( $js['yes'] ) && $js['yes'] != '' )
		{
			$yesJS = ' ' . $js['yes'];
		}

		if ( isset( $js['no'] ) && $js['no'] != '' )
		{
			$noJS = ' ' . $js['no'];
		}

		if ( $default_val == 1 )
		{
			$yes = "<div class='green yes-no'>Yes &nbsp; <input type='radio' name='{$name}' value='1' checked{$yesJS}></div>";
			$no  = "<div class='red yes-no'><input type='radio' name='{$name}' value='0'{$noJS}> &nbsp; No</div>";
		}
		else
		{
			$yes = "<div class='green yes-no'>Yes &nbsp; <input type='radio' name='{$name}' value='1'{$yesJS}></div>";
			$no  = "<div class='red yes-no'><input type='radio' name='{$name}' value='0' checked{$noJS}> &nbsp; No</div>";
		}

		return "<div class='yes-no-sel'>{$yes}{$no}</div>";
	}

	/**
	 * Open a fieldset table group
	 *
	 * @param string $title name of the set to display in the legend
	 * @return string
	 * @access public
	 * @since 1.0.0
	 */
	function startFieldset( $title = '' )
	{
		if ( $title != '' )
		{
			$title = "\n\t\t\t\t\t<legend><strong>{$title}</strong></legend>";
		}

		if ( $this->td_colspan > 0 )
		{
			$colspan = " colspan='{$this->td_colspan}'";
		}

		return "\n\t\t<tr>\n\t\t\t<td width='100%'{$colspan}>\n\t\t\t\t<fieldset>{$title}\n\t\t\t\t\t<table cellpadding='4' cellspacing='0' border='0' width='100%' class='admin'>";
	}

	/**
	 * Print form start tag with hidden tags
	 *
	 * @param array $hiddens the hidden form values
	 * @param string $name name of form, DEFAULT is 'theAdminForm'
	 * @param string $js javascript for form
	 * @param string $id form id if different from $name
	 * @return string
	 * @access public
	 * @since 1.0.0
	 */
	public function startForm( $hiddens = array(), $name = 'theAdminForm', $js = '', $id = '' )
	{
		if ( $id == '' )
		{
			$id = $name;
		}
		
		if ( $js != '' )
		{
			$js = ' ' . $js;
		}
		
		$form = "<form action='{$this->registry->getConfig('base_url')}". SWS_THIS_APPLICATION ."/' method='post' name='{$name}' id='{$id}'{$js}>";
		
		if ( is_array( $hiddens ) && count( $hiddens ) > 0 )
		{
			foreach( $hiddens as $k => $v )
			{
				$form .= "\n\t<input type='hidden' name='{$k}' value='{$v}' />";
			}
		}
		
		return $form;
	}

	/**
	 * Start a table
	 *
	 * @param string $title text to display above the table as a header
	 * @param string $class css class name, DEFAULT is admin
	 * @return string
	 * @access public
	 * @since 1.0.0
	 */
	public function startTable( $title = '', $class = 'admin', $insert = '' )
	{
		$html = '';
		
		if ( $title != '' )
		{
			$html .= "\t<h2>{$title}</h2>";
		}
		
		$html .= $insert;
		
		$html .= "\n\t<table width='100%' cellspacing='0' cellpadding='4' align='center' class='{$class}'>";
		
		if ( isset( $this->td_header[0] ) )
		{
			$this->td_header[1][0] = ( isset($this->td_header[1][0]) AND $this->td_header[1][0] ) ? $this->td_header[1][0] : '';
			$this->td_header[1][1] = ( isset($this->td_header[1][1]) AND $this->td_header[1][1] ) ? $this->td_header[1][1] : '';
			
			if ( $this->td_header[0][0] == '&nbsp;' && $this->td_header[1][0] == '&nbsp;' && ( ! isset( $this->td_header[2][0] ) ) )
			{
				$this->td_header[0][0] = '{none}';
				$this->td_header[1][0] = '{none}';
			}
			
			$tds = "";
			
			foreach( $this->td_header as $td )
			{
				if ($td[1] != "")
				{
					$width = " width='{$td[1]}'";
				}
				else
				{
					$width = '';
				}
				
				if ( $td[0] != '{none}' )
				{
					$tds .= "\n\t\t\t<th align='center'{$width}>{$td[0]}</th>";
				}
				
				$this->td_colspan++;
			}
			
			if ( $tds )
			{
				$html .= "\n\t\t<tr>{$tds}\n\t\t</tr>";
			}
		}
		
		return $html;
	}

	/**
	 * Print an up order button
	 *
	 * @param string $req module for the URL
	 * @param int $data id number for the URL
	 * @return string
	 * @access public
	 * @since 1.0.0
	 */
	function upButton( $url )
	{
		return "<a href='{$url}' title='{$this->lang->getString('move_up')}'><img src='{$this->registry->getConfig('base_url')}/images/up-arrow.gif' width='17' height='17' border='0' style='vertical-align:middle' /></a>&nbsp;&nbsp;&nbsp;";
	}
}

?>