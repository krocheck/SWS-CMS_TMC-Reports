<?php

/**
 * SWS-CMS System
 *  - Simna Web Services Programming Team
 * 
 * Display class to handle all HTML output
 * Last Updated: $Date: 2010-07-02 09:29:16 -0500 (Fri, 02 Jul 2010) $
 *
 * @author		$Author: krocheck $
 * @copyright	2009 Simna Web Services, LLC
 * @package		SWS-CMS
 * @subpackage	Core
 * @link		http://www.simnaweb.com
 * @since		1.0.0
 * @version		$Revision: 31 $
 */

class Display
{
	/**
	 * The application registry library
	 *
	 * @access protected
	 * @var Registry
	 * @since 1.0.0
	 */
	protected $registry;
	/**
	 * Array of the loaded template classes
	 *
	 * @access public
	 * @var array
	 * @since 1.0.0
	 */
	public $templates = array();
	/**
	 * Navigation breadcrumb links
	 *
	 * @access protected
	 * @var array
	 * @since 1.0.0
	 */
	protected $breadcrumb        = array();
	/**
	 * DEBUG PRINTOUT
	 *
	 * @access protected
	 * @var array
	 * @since 1.0.0
	 */
	protected $debug             = array();
	/**
	 * Navigation tab links
	 *
	 * @access protected
	 * @var array
	 * @since 1.0.0
	 */
	protected $navigation        = array();
	/**
	 * The body content for the page
	 *
	 * @access protected
	 * @var string
	 * @since 1.0.0
	 */
	protected $content;
	/**
	 * The final html for print
	 *
	 * @access protected
	 * @var string
	 * @since 1.0.0
	 */
	protected $html;
	/**
	 * The json content for the page
	 *
	 * @access protected
	 * @var string
	 * @since 1.0.0
	 */
	protected $json              = array();
	/**
	 * The head title for the page
	 *
	 * @access protected
	 * @var string
	 * @since 1.0.0
	 */
	protected $title;
	/**
	 * includes to be included in the header
	 *
	 * @access protected
	 * @var array
	 * @since 1.0.0
	 */
	protected $js;

	/**
	 * Constructor that loads the registry
	 *
	 * @param Registry $registry the main program registry
	 * @return void
	 * @access public
	 * @since 1.0.0
	 */
	public function __construct( Registry $registry )
	{
		$this->registry = $registry;
		
		$this->addDebug( "Display Library Loaded" );
	}

	/**
	 * Add breadcrumb
	 *
	 * @param string $uri everything that needs to be added to the base url
	 * @param string $string the text to accompany the link
	 * @param string $css class name to apply to item
	 * @return void
	 * @access public
	 * @since 1.0.0
	 */
	public function addBreadcrumb( $url, $string, $css = '' )
	{
		$add = array( 'url' => $url, 'string' => $string );
		
		if ( $css != '' )
		{
			$add['css'] = $css;
		}
		
		$this->breadcrumb[] = $add;
	}

	/**
	 * Add content
	 *
	 * @param string $content content to add
	 * @param boolean $prepend Prepend instead of append
	 * @return void
	 * @access public
	 * @since 1.0.0
	 */
	public function addContent( $content, $prepend=FALSE )
	{
		if( $prepend )
		{
			$this->content = $content . $this->content;
		}
		else
		{
			$this->content .= $content;
		}
	}

	/**
	 * Add debug line
	 *
	 * @param string $string the text to show in debug
	 * @return void
	 * @access public
	 * @since 1.0.0
	 */
	public function addDebug( $string )
	{
		//if ( DEBUG )
		//{
			$this->debug[ microtime() ] = $string;
		//}
	}
	
	/**
	 * Add Javascript
	 *
	 * @param string $javascript content to add
	 * @param boolean $prepend Prepend instead of append
	 * @return void
	 * @access public
	 * @since 1.0.0
	 */
	public function addJavascript( $js, $prepend=FALSE )
	{
		if( $prepend )
		{
			$this->js = $js . $this->js;
		}
		else
		{
			$this->js .= $js;
		}
	}

	/**
	 * Add json
	 *
	 * @param string $key key to add
	 * @param string $value value to add
	 * @return void
	 * @access public
	 * @since 1.0.0
	 */
	public function addJSON( $key, $value )
	{
		if ( $key == 'status' && $value == 'not_found' )
		{
			header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
		}
		else if ( $key == 'status' && $value == 'forbidden' )
		{
			header($_SERVER["SERVER_PROTOCOL"]." 401 Unauthorized");
		}

		$this->json[ $key ] = $value;
	}

	/**
	 * Add navigation tab
	 *
	 * @param array $pages nested array
	 * @return void
	 * @access public
	 * @since 1.0.0
	 */
	public function addNavigation( $pages )
	{
		$this->navigation[] = $pages;
	}

	/**
	 * Add navigation tab
	 *
	 * @param array $pages nested array
	 * @return void
	 * @access public
	 * @since 1.0.0
	 */
	public function addTitle( $text )
	{
		if ( strlen( $text ) > 0 )
		{
			$this->title .= $this->registry->getLang()->getString('title_sep') . $text;
		}
	}

	/**
	 * Makes the logged in display
	 *
	 * @return string html of the area
	 * @access private
	 * @since 1.0.0
	 */
	private function buildLoggedIn()
	{
		$id   = $this->registry->getUser()->getID();
		$name = $this->registry->getUser()->getFirstName() ." " .$this->registry->getUser()->getLastName();
		
		return $this->compiledTemplates('skin_'.SWS_THIS_APPLICATION)->loggedIn( $id, $name );
	}

	/**
	 * Build a proper url with all the good stuff
	 *
	 * @param array $params key=>val array to append to url
	 * @param string $app string of app name if other than public
	 * @return string of the new url
	 * @access public
	 * @since 1.0.0
	 */
	public function buildURL( $params = array(), $app = "" )
	{
		$user      = $this->registry->getUser();
		$sessionID = '';

		if ( is_object( $user ) )
		{
			$sessionID = $user->getSessionID();
		}

		$appAdd    = $app ? "{$app}/" : "";
		$url       = $this->registry->getConfig('base_url') . "{$appAdd}";
		$app       = $app == '' ? 'public' : $app;

		if ( $app != SWS_THIS_APPLICATION )
		{
			$sessionID = '';
		}
		else if ( strlen( $sessionID ) == 32 && strlen( $this->registry->getCookie()->getCookie(SWS_THIS_APPLICATION) ) != 32 )
		{
			$params['s'] = $sessionID;
		}

		if( $this->registry->getSetting('seo_url') == 1 )
		{
			$app = $this->registry->getApp( $app );

			if ( is_object( $app ) )
			{
				$back = $app->buildSEOURI( $params );

				if ( is_array( $back ) && count( $back ) == 2 )
				{
					$url   .= $back['uri'];
					$params = $back['params'];
				}
			}
		}
		else
		{
			$url .= "index.php?";
		}

		if ( count( $params ) > 0 )
		{
			if ( $this->registry->getSetting('seo_url') == 1 )
			{
				$url .= $this->registry->getConfig('seo_marker') . '/';
			}

			if ( isset( $params['extra'] ) && is_array( $params['extra'] ) )
			{
				if ( count( $params['extra'] ) > 0 && $this->registry->getSetting('seo_url') == 1 )
				{
					foreach( $params['extra'] as $v )
					{
						$url .= "{$v}/";
					}
				}
				else if ( $this->registry->getSetting('seo_url') == 1 ) {}
				else
				{
					$url .= "extra=".serialize( $params['extra'] )."&amp;";
				}

				unset( $params['extra'] );
			}

			foreach( $params as $k => $v )
			{
				if ( $this->registry->getSetting('seo_url') == 1 )
				{
					$url .= "{$k}{$this->registry->getConfig('seo_param_sep')}{$v}/";
				}
				else
				{
					$url .= "{$k}={$v}&amp;";
				}
			}
		}

		return $url;
	}

	/**
	 * Returns a template file and initializes the object into the templates array if needed
	 *
	 * @param string $key the template code
	 * @return object skin class
	 * @access public
	 * @since 1.0.0
	 */
	public function compiledTemplates( $key )
	{
		$out = NULL;

		if ( isset( $this->templates[ $key ] ) && is_object( $this->templates[ $key ] ) )
		{
			$out = $this->templates[ $key ];
		}
		else
		{
			$out = $this->loadTemplates( $key );
		}

		return $out;
	}

	/**
	 * Compiles the error page, prints, and exits
	 *
	 * @return void
	 * @access public
	 * @since 1.0.0
	 */
	public function doError()
	{
		$debug = "";
		
		if ( DEBUG )
		{
			$debug = $this->getDebugOutput();
		}
		
		$this->html = $this->compiledTemplates('skin_global')->errorWrapper( $this->title, $this->content, $debug );
		
		print( $this->html );
		
		$this->registry->cleanUp();
		
		exit;
	}

	/**
	 * Compiles the json output, prints, and exits
	 *
	 * @return void
	 * @access public
	 * @since 1.0.0
	 */
	public function doJSON()
	{
		header('Content-type: application/json');
		
		$this->registry->cleanUp();
		
		if ( DEBUG )
		{
			$this->json['debug'] = $this->getDebugJSON();
		}
		
		$this->html = json_encode($this->json);
		
		print( $this->html );
		
		exit;
	}

	/**
	 * Compiles the page, prints, and exits
	 *
	 * @param string $css class name to apply to the content block
	 * @return void
	 * @access public
	 * @since 1.0.0
	 */
	public function doOutput( $css = '' )
	{
		$navigation = "";
		$breadcrumb = "";
		$userlinks  = "";
		$loggedIn   = "";
		$errors     = "";
		$debug      = "";

		if ( is_object( $this->registry->getUser() ) )
		{
			$loggedIn   = $this->buildLoggedIn();
		}

		$userlinks  = $this->registry->getApp()->buildUserLinks();

		if ( is_array( $this->registry->getError()->getErrors() ) && count( $this->registry->getError()->getErrors() ) > 0 )
		{
			foreach( $this->registry->getError()->getErrors() as $val )
			{
				$errors .= "<li>" . $val . "</li>";
			}
		}

		$navigation = $this->compiledTemplates('skin_'.SWS_THIS_APPLICATION)->navigationWrapper( $this->navigation );
		$breadcrumb = $this->compiledTemplates('skin_'.SWS_THIS_APPLICATION)->breadcrumbWrapper( $this->breadcrumb );

		$this->registry->cleanUp();

		if ( DEBUG )
		{
			$debug = $this->getDebugOutput();
		}

		if ( substr( $this->title, -3, 3 ) == $this->registry->getLang()->getString('title_sep') )
		{
			$this->title = substr( $this->title, 0, -3 );
		}

		if ( $this->registry->getInput('do') == 'pdf' )
		{
			require_once(SWS_VENDOR_PATH . 'autoload.php');
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Nicola Asuni');
$pdf->SetTitle('TCPDF Example 006');
$pdf->SetSubject('TCPDF Tutorial');
$pdf->SetKeywords('TCPDF, PDF, example, test, guide');

// set default header data
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 006', PDF_HEADER_STRING);

// set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// set some language-dependent strings (optional)
if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
    require_once(dirname(__FILE__).'/lang/eng.php');
    $pdf->setLanguageArray($l);
}

// ---------------------------------------------------------

// set font
$pdf->SetFont('dejavusans', '', 10);

// add a page
$pdf->AddPage();

// writeHTML($html, $ln=true, $fill=false, $reseth=false, $cell=false, $align='')
// writeHTMLCell($w, $h, $x, $y, $html='', $border=0, $ln=0, $fill=0, $reseth=true, $align='', $autopadding=true)

// create some HTML content
$html = '<h1>HTML Example</h1>
Some special characters: &lt; € &euro; &#8364; &amp; è &egrave; &copy; &gt; \\slash \\\\double-slash \\\\\\triple-slash
<h2>List</h2>
List example:
<ol>
    <li><img src="images/logo_example.png" alt="test alt attribute" width="30" height="30" border="0" /> test image</li>
    <li><b>bold text</b></li>
    <li><i>italic text</i></li>
    <li><u>underlined text</u></li>
    <li><b>b<i>bi<u>biu</u>bi</i>b</b></li>
    <li><a href="http://www.tecnick.com" dir="ltr">link to http://www.tecnick.com</a></li>
    <li>Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo.<br />Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt.</li>
    <li>SUBLIST
        <ol>
            <li>row one
                <ul>
                    <li>sublist</li>
                </ul>
            </li>
            <li>row two</li>
        </ol>
    </li>
    <li><b>T</b>E<i>S</i><u>T</u> <del>line through</del></li>
    <li><font size="+3">font + 3</font></li>
    <li><small>small text</small> normal <small>small text</small> normal <sub>subscript</sub> normal <sup>superscript</sup> normal</li>
</ol>
<dl>
    <dt>Coffee</dt>
    <dd>Black hot drink</dd>
    <dt>Milk</dt>
    <dd>White cold drink</dd>
</dl>
<div style="text-align:center">IMAGES<br />
<img src="images/logo_example.png" alt="test alt attribute" width="100" height="100" border="0" /><img src="images/tcpdf_box.svg" alt="test alt attribute" width="100" height="100" border="0" /><img src="images/logo_example.jpg" alt="test alt attribute" width="100" height="100" border="0" />
</div>';

// output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');


// output some RTL HTML content
$html = '<div style="text-align:center">The words &#8220;<span dir="rtl">&#1502;&#1494;&#1500; [mazel] &#1496;&#1493;&#1489; [tov]</span>&#8221; mean &#8220;Congratulations!&#8221;</div>';
$pdf->writeHTML($html, true, false, true, false, '');

// test some inline CSS
$html = '<p>This is just an example of html code to demonstrate some supported CSS inline styles.
<span style="font-weight: bold;">bold text</span>
<span style="text-decoration: line-through;">line-trough</span>
<span style="text-decoration: underline line-through;">underline and line-trough</span>
<span style="color: rgb(0, 128, 64);">color</span>
<span style="background-color: rgb(255, 0, 0); color: rgb(255, 255, 255);">background color</span>
<span style="font-weight: bold;">bold</span>
<span style="font-size: xx-small;">xx-small</span>
<span style="font-size: x-small;">x-small</span>
<span style="font-size: small;">small</span>
<span style="font-size: medium;">medium</span>
<span style="font-size: large;">large</span>
<span style="font-size: x-large;">x-large</span>
<span style="font-size: xx-large;">xx-large</span>
</p>';

$pdf->writeHTML($html, true, false, true, false, '');

// reset pointer to the last page
$pdf->lastPage();

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
// Print a table

// add a page
$pdf->AddPage();

// create some HTML content
$subtable = '<table border="1" cellspacing="6" cellpadding="4"><tr><td>a</td><td>b</td></tr><tr><td>c</td><td>d</td></tr></table>';

$html = '<h2>HTML TABLE:</h2>
<table border="1" cellspacing="3" cellpadding="4">
    <tr>
        <th>#</th>
        <th align="right">RIGHT align</th>
        <th align="left">LEFT align</th>
        <th>4A</th>
    </tr>
    <tr>
        <td>1</td>
        <td bgcolor="#cccccc" align="center" colspan="2">A1 ex<i>amp</i>le <a href="http://www.tcpdf.org">link</a> column span. One two tree four five six seven eight nine ten.<br />line after br<br /><small>small text</small> normal <sub>subscript</sub> normal <sup>superscript</sup> normal  bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla<ol><li>first<ol><li>sublist</li><li>sublist</li></ol></li><li>second</li></ol><small color="#FF0000" bgcolor="#FFFF00">small small small small small small small small small small small small small small small small small small small small</small></td>
        <td>4B</td>
    </tr>
    <tr>
        <td>'.$subtable.'</td>
        <td bgcolor="#0000FF" color="yellow" align="center">A2 € &euro; &#8364; &amp; è &egrave;<br/>A2 € &euro; &#8364; &amp; è &egrave;</td>
        <td bgcolor="#FFFF00" align="left"><font color="#FF0000">Red</font> Yellow BG</td>
        <td>4C</td>
    </tr>
    <tr>
        <td>1A</td>
        <td rowspan="2" colspan="2" bgcolor="#FFFFCC">2AA<br />2AB<br />2AC</td>
        <td bgcolor="#FF0000">4D</td>
    </tr>
    <tr>
        <td>1B</td>
        <td>4E</td>
    </tr>
    <tr>
        <td>1C</td>
        <td>2C</td>
        <td>3C</td>
        <td>4F</td>
    </tr>
</table>';

// output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');

// Print some HTML Cells

$html = '<span color="red">red</span> <span color="green">green</span> <span color="blue">blue</span><br /><span color="red">red</span> <span color="green">green</span> <span color="blue">blue</span>';

$pdf->SetFillColor(255,255,0);

$pdf->writeHTMLCell(0, 0, '', '', $html, 'LRTB', 1, 0, true, 'L', true);
$pdf->writeHTMLCell(0, 0, '', '', $html, 'LRTB', 1, 1, true, 'C', true);
$pdf->writeHTMLCell(0, 0, '', '', $html, 'LRTB', 1, 0, true, 'R', true);

// reset pointer to the last page
$pdf->lastPage();

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
// Print a table

// add a page
$pdf->AddPage();

// create some HTML content
$html = '<h1>Image alignments on HTML table</h1>
<table cellpadding="1" cellspacing="1" border="1" style="text-align:center;">
<tr><td><img src="images/logo_example.png" border="0" height="41" width="41" /></td></tr>
<tr style="text-align:left;"><td><img src="images/logo_example.png" border="0" height="41" width="41" align="top" /></td></tr>
<tr style="text-align:center;"><td><img src="images/logo_example.png" border="0" height="41" width="41" align="middle" /></td></tr>
<tr style="text-align:right;"><td><img src="images/logo_example.png" border="0" height="41" width="41" align="bottom" /></td></tr>
<tr><td style="text-align:left;"><img src="images/logo_example.png" border="0" height="41" width="41" align="top" /></td></tr>
<tr><td style="text-align:center;"><img src="images/logo_example.png" border="0" height="41" width="41" align="middle" /></td></tr>
<tr><td style="text-align:right;"><img src="images/logo_example.png" border="0" height="41" width="41" align="bottom" /></td></tr>
</table>';

// output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');

// reset pointer to the last page
$pdf->lastPage();

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
// Print all HTML colors

// add a page
$pdf->AddPage();

$textcolors = '<h1>HTML Text Colors</h1>';
$bgcolors = '<hr /><h1>HTML Background Colors</h1>';

foreach(TCPDF_COLORS::$webcolor as $k => $v) {
    $textcolors .= '<span color="#'.$v.'">'.$v.'</span> ';
    $bgcolors .= '<span bgcolor="#'.$v.'" color="#333333">'.$v.'</span> ';
}

// output the HTML content
$pdf->writeHTML($textcolors, true, false, true, false, '');
$pdf->writeHTML($bgcolors, true, false, true, false, '');

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

// Test word-wrap

// create some HTML content
$html = '<hr />
<h1>Various tests</h1>
<a href="#2">link to page 2</a><br />
<font face="courier"><b>thisisaverylongword</b></font> <font face="helvetica"><i>thisisanotherverylongword</i></font> <font face="times"><b>thisisaverylongword</b></font> thisisanotherverylongword <font face="times">thisisaverylongword</font> <font face="courier"><b>thisisaverylongword</b></font> <font face="helvetica"><i>thisisanotherverylongword</i></font> <font face="times"><b>thisisaverylongword</b></font> thisisanotherverylongword <font face="times">thisisaverylongword</font> <font face="courier"><b>thisisaverylongword</b></font> <font face="helvetica"><i>thisisanotherverylongword</i></font> <font face="times"><b>thisisaverylongword</b></font> thisisanotherverylongword <font face="times">thisisaverylongword</font> <font face="courier"><b>thisisaverylongword</b></font> <font face="helvetica"><i>thisisanotherverylongword</i></font> <font face="times"><b>thisisaverylongword</b></font> thisisanotherverylongword <font face="times">thisisaverylongword</font> <font face="courier"><b>thisisaverylongword</b></font> <font face="helvetica"><i>thisisanotherverylongword</i></font> <font face="times"><b>thisisaverylongword</b></font> thisisanotherverylongword <font face="times">thisisaverylongword</font>';

// output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');

// Test fonts nesting
$html1 = 'Default <font face="courier">Courier <font face="helvetica">Helvetica <font face="times">Times <font face="dejavusans">dejavusans </font>Times </font>Helvetica </font>Courier </font>Default';
$html2 = '<small>small text</small> normal <small>small text</small> normal <sub>subscript</sub> normal <sup>superscript</sup> normal';
$html3 = '<font size="10" color="#ff7f50">The</font> <font size="10" color="#6495ed">quick</font> <font size="14" color="#dc143c">brown</font> <font size="18" color="#008000">fox</font> <font size="22"><a href="http://www.tcpdf.org">jumps</a></font> <font size="22" color="#a0522d">over</font> <font size="18" color="#da70d6">the</font> <font size="14" color="#9400d3">lazy</font> <font size="10" color="#4169el">dog</font>.';

$html = $html1.'<br />'.$html2.'<br />'.$html3.'<br />'.$html3.'<br />'.$html2;

// output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
// test pre tag

// add a page
$pdf->AddPage();

$html = <<<EOF
<div style="background-color:#880000;color:white;">
Hello World!<br />
Hello
</div>
<pre style="background-color:#336699;color:white;">
int main() {
    printf("HelloWorld");
    return 0;
}
</pre>
<tt>Monospace font</tt>, normal font, <tt>monospace font</tt>, normal font.
<br />
<div style="background-color:#880000;color:white;">DIV LEVEL 1<div style="background-color:#008800;color:white;">DIV LEVEL 2</div>DIV LEVEL 1</div>
<br />
<span style="background-color:#880000;color:white;">SPAN LEVEL 1 <span style="background-color:#008800;color:white;">SPAN LEVEL 2</span> SPAN LEVEL 1</span>
EOF;

// output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

// test custom bullet points for list

// add a page
$pdf->AddPage();

$html = <<<EOF
<h1>Test custom bullet image for list items</h1>
<ul style="font-size:14pt;list-style-type:img|png|4|4|images/logo_example.png">
    <li>test custom bullet image</li>
    <li>test custom bullet image</li>
    <li>test custom bullet image</li>
    <li>test custom bullet image</li>
<ul>
EOF;

// output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

// reset pointer to the last page
$pdf->lastPage();

// ---------------------------------------------------------

//Close and output PDF document
$pdf->Output('example_006.pdf', 'I');
		}
		else
		{
			$this->html = $this->compiledTemplates('skin_'.SWS_THIS_APPLICATION)->wrapper( $this->title, $navigation, $breadcrumb, $userlinks, $loggedIn, $this->content, $css, $errors, $debug, $this->js );
			print( $this->html );
		}

		exit;
	}

	/**
	 * Gets all the necessary debug info
	 *
	 * @return array the info
	 * @access public
	 * @since 1.0.0
	 */
	public function getDebugJSON()
	{
		$out = array();

		$out['sql'] = $this->registry->getDB()->getQueries();
		$out['input'] = $this->registry->getInputs();
		$out['server'] = $_SERVER;
		$out['debug'] = $this->debug;

		return $out;
	}

	/**
	 * Gets all the necessary debug info
	 *
	 * @return string the info
	 * @access public
	 * @since 1.0.0
	 */
	public function getDebugOutput()
	{
		$out = "";

		ob_start();

		print("<div id='debug'>\n\t<fieldset>\n\t\t<legend>DEBUG</legend>\n\t\t<div>\n\t\t<h2>SQL</h2>\n\t\t<pre>");
		print_r($this->registry->getDB()->getQueries());
		print("\t\t</pre>\n\t\t<h2>Input</h2>\n\t\t<pre>");
		print_r($this->registry->getInputs());
		print("\t\t</pre>\n\t\t<h2>SERVER</h2>\n\t\t<pre>");
		print_r($_SERVER);
		print("\t\t</pre>\n\t\t<h2>DEBUG-CODE</h2>\n\t\t<pre>");
		print_r($this->debug);
		print("\t\t</pre>\n\t\t</div>\n\t</fieldset>\n</div>");

		$out = ob_get_contents();

		ob_end_clean();

		return $out;
	}

	/**
	 * Gets the navigation HTML, used if navigation needs to be embedded in design
	 *
	 * @return string the HTML
	 * @access public
	 * @since 1.0.0
	 */
	public function getErrorHTML()
	{
		$out = "";

		if ( is_array( $this->registry->getError()->getErrors() ) && count( $this->registry->getError()->getErrors() ) > 0 )
		{
			$out .= "<ul class='error'>";

			foreach( $this->registry->getError()->getErrors() as $val )
			{
				$out .= "<li>" . $val . "</li>";
			}

			$out .= "</ul>";
		}

		return $out;
	}

	/**
	 * Gets the navigation array
	 *
	 * @return array the navigation pieces
	 * @access public
	 * @since 1.0.0
	 */
	public function getNavigation()
	{
		return $this->navigation;
	}

	/**
	 * Gets the navigation HTML, used if navigation needs to be embedded in design
	 *
	 * @return string the HTML
	 * @access public
	 * @since 1.0.0
	 */
	public function getNavigationHTML()
	{
		$out = "";

		if ( count( $this->navigation ) > 0 )
		{
			$out = $this->compiledTemplates('skin_'.SWS_THIS_APPLICATION)->navigationWrapper( $this->navigation );
		}

		return $out;
	}

	/**
	 * Build pages to keep things organized
	 *
	 * @param string $table the table to count
	 * @param array $params link parameters
	 * @param string $app the app name if other than public
	 * @param string $where additional query info
	 * @return string html
	 * @access public
	 * @since 1.0.0
	 */
	public function getPagelinks( $table, $params, $app = "", $where = '' )
	{
		$out = "";

		// Establish the page number
		$pageNumber = is_numeric( $this->registry->getInput('page') ) ? $this->registry->getInput('page') : 1;

		// Establish default results per page
		$perPage = $this->registry->getSetting('items_per_page');

		// Establish a padding value
		$padding = $this->registry->getSetting('padding');

		// Get total number of database entires
		$this->registry->getDB()->query( "SELECT COUNT(*) AS count FROM {$table} {$where}" );
		$count = $this->registry->getDB()->fetchRow();

		if( $count['count'] <= $perPage)
		{
			$pageNumber = '';
			$firstPage  = '';
			$lastPage   = '';
			$nextPage   = '';
		}
		else
		{
			// Get total number of pages
			$numOfPages = ceil($count['count'] / $perPage);

			// If there is only one page result let's get rid of First otherwise output it
			$params['page'] = 1;
			$baselink = $this->buildURL( $params, $app );
			$firstPage = (($count['count'] > $perPage) && ($pageNumber > 1)) ? "<a href='{$baselink}'>{$this->registry->getLang()->getString('first')}</a> " : "";

			// Here will will generate our 'previous' link...
			$previousValue = ($pageNumber - 1);
			$params['page'] = $previousValue;
			$baselink = $this->buildURL( $params, $app );
			$previousPage = ($pageNumber <= $numOfPages && $pageNumber > 1) ? " <a href='{$baselink}'>{$this->registry->getLang()->getString('previous')}</a> " : "";

			// If value is greater than 1...
			if ( ( $pageNumber - $padding ) > 1)
			{
				$lowerLimit = $pageNumber - $padding;

				// Print all padded numbers between lowerLimit and current page
				$pageLinks.= '...';

				for($i = $lowerLimit; $i < $pageNumber; $i++)
				{
					$params['page'] = $i;
					$baselink = $this->buildURL( $params, $app );
					$pageLinks.= " <a href='{$baselink}'>{$i}</a> ";
				}
			}
			else
			{
				// Print all numbers between current page and first page
				for($i = 1; $i < $pageNumber; $i++)
				{
					$params['page'] = $i;
					$baselink = $this->buildURL( $params, $app );
					$pageLinks.= " <a href='{$baselink}'>{$i}</a> ";
				}
			}

			// Let's print out the current page
			$pageLinks.= "<b>" .$pageNumber. "</b>";

			// If our current page, plus our padding, is less than the total number of pages
			if(($pageNumber + $padding) < $numOfPages)
			{
				// Set upper limit
				$upperLimit = $pageNumber + $padding;

				// Print all numbers from padded pages above current page
				for($i = ($pageNumber + 1); $i <= $upperLimit; $i++)
				{
					$params['page'] = $i;
					$baselink = $this->buildURL( $params, $app );
					$pageLinks.= " <a href='{$baselink}'>{$i}</a> ";
				}

				$pageLinks.= '...';
			}
			else
			{
				// Print all page numbers between number of pages and current page
				for($i = ($pageNumber + 1); $i <= $numOfPages; $i++)
				{
					$params['page'] = $i;
					$baselink = $this->buildURL( $params, $app );
					$pageLinks.= " <a href='{$baselink}'>{$i}</a> ";
				}
			}

			// Here will will generate our 'next' link...
			$nextValue = ($pageNumber += 1);
			$params['page'] = $nextValue;
			$baselink = $this->buildURL( $params, $app );
			$nextPage = ($numOfPages >= $pageNumber) ? " <a href='{$baselink}'>{$this->registry->getLang()->getString('next')}</a> " : "";

			// If there is only one page result let's get rid of last otherwise output it
			$params['page'] = $numOfPages;
			$baselink = $this->buildURL( $params, $app );
			$lastPage = (($count['count'] > $perPage) && ($numOfPages >= $pageNumber)) ? " <a href='{$baselink}'>{$this->registry->getLang()->getString('last')}</a>" : "";

			$out = $firstPage . $previousPage . $pageLinks . $nextPage . $lastPage;
		}
		
		return $out;
	}

	/**
	 * Loads a template file and initializes the object into the templates array
	 *
	 * @param string $key the template code
	 * @return void
	 * @access public
	 * @since 1.0.0
	 */
	public function loadTemplates( $key )
	{
		$out = NULL;

		if ( ! isset( $this->templates[ $key ] ) || ! is_object( $this->templates[ $key ] ) )
		{
			if ( file_exists( SWS_SKIN_PATH . strtolower($key) . '.php' ) )
			{
				require_once( SWS_SKIN_PATH . strtolower($key) . '.php' );
				
				if ( class_exists( $key ) )
				{
					$this->templates[ $key ] = new $key();

					$this->templates[ $key ]->execute( $this->registry );

					$out = $this->templates[ $key ];

					$this->addDebug( "Templates Loaded: {$key}" );
				}
			}
		}

		if ( ! isset( $this->templates[ $key ] ) && ! is_object( $this->templates[ $key ] ) )
		{
			if ( is_object( $this->registry->getError() ) )
			{
				$this->registry->getError()->raiseError( 'template_file_missing', TRUE, array( 'skin' => $key ) );
			}
			else
			{
				echo( 'FATAL ERROR!  Template not found.  Please notify the administrator at:  temp@localhost' );
				exit();
			}
		}

		return $out;
	}

	/**
	 * Set page content
	 *
	 * @param string $content new content
	 * @return void
	 * @access public
	 * @since 1.0.0
	 */
	public function setContent( $content )
	{
		$this->content = $content;
	}

	/**
	 * Set javascript includes
	 *
	 * @param string $js for javascript
	 * @return void
	 * @access public
	 * @since 1.0.0
	 */
	public function setJavascript( $js )
	{
		$this->js = $js;
	}

	/**
	 * Set json content
	 *
	 * @param string $json for json
	 * @return void
	 * @access public
	 * @since 1.0.0
	 */
	public function setJSON( $json )
	{
		$this->json = $json;
	}

	/**
	 * Set page title
	 *
	 * @param string $title new title
	 * @return void
	 * @access public
	 * @since 1.0.0
	 */
	public function setTitle( $title )
	{
		if ( substr( $title, 0, strlen( $this->registry->getLang()->getString('title_sep') ) ) != $this->registry->getLang()->getString('title_sep') )
		{
			$title = $this->registry->getLang()->getString('title_sep') . $title;
		}

		$this->title = $this->registry->getLang()->getString('site_title') . $title;
	}

	/**
	 * Qucikly redirects the user to a new page
	 *
	 * @param string $stringURL
	 * @return void
	 * @access public
	 * @since 1.0.0
	 */
	public function silentRedirect( $stringURL )
	{
		header('Location: '. $stringURL);
		exit();
	}
}

?>