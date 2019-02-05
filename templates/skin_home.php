<?php

class skin_home extends Command {

protected function doExecute( $param )
{
}

public function conquered( $consumed, $breweries, $tempCount ) {

$this->consumedPage = 0;

$pageTotal = 5;

$consumedCount = 6;
$consumedTotal = 1;

$ELMHTML = <<<EOF
		<div class="box red-box" id='consumed'>
			<a onClick="toggleConsumed()">
				<div id="consumed-head">
					<div class="check">&nbsp;</div>
					<div id="consumed-head-closed">
						SEE THE <span class='bold'>{$tempCount}</span> BEERS YOU'VE CONQUERED
					</div>
					<div id="consumed-head-open" class="hidden">
						THE <span class='bold'>{$tempCount}</span> BEERS YOU'VE CONQUERED
					</div>
				</div>
			</a>
			<div id="consumed-text" class="hidden">

EOF;

if ( count( $consumed ) > 0 )
{
	foreach( $consumed as $id => $beer )
	{
		if ( $consumedCount > $pageTotal )
		{
			if ( $this->consumedPage > 0 )
			{
$ELMHTML .= <<<EOF
				</div>

EOF;
			}

			$hidden = " class='consumed-page'";

			if ( $this->consumedPage > 0 )
			{
				$hidden = " class='consumed-page hidden'";
			}

			$this->consumedPage++;
			$consumedCount = 0;


$ELMHTML .= <<<EOF
				<div id="con{$this->consumedPage}"{$hidden}>

EOF;
		}

		if ( isset( $beer['url'] ) && strlen( $beer['url'] ) > 6 )
		{
$ELMHTML .= <<<EOF
					<a href="{$beer['url']}" target="_blank">
EOF;
		}

$ELMHTML .= <<<EOF
					<div class="beer">
						<p class="name">{$consumedTotal}. {$beer['title']}</p>
					</div>

EOF;

		if ( isset( $beer['url'] ) && strlen( $beer['url'] ) > 6 )
		{
$ELMHTML .= <<<EOF
					</a>
EOF;
		}

		$consumedCount++;
		$consumedTotal++;

	}

$ELMHTML .= <<<EOF
				</div>

EOF;

}

if ( $this->consumedPage > 1 )
{
$ELMHTML .= <<<EOF
				<div id='consumed-control'>
					<div class="previous"><a onClick="previousConsumedPage()"><img src="{$this->registry->getConfig('base_url')}images/blank.gif" width='24' height='24' /></a></div>
					<div class="next"><a onClick="nextConsumedPage()"><img src="{$this->registry->getConfig('base_url')}images/blank.gif" width='24' height='24' /></a></div>

EOF;

	for( $count = 1; $count <= $this->consumedPage; $count++ )
	{
		$extra = '';
		
		if ( $count == 1 )
		{
			$extra = " selected";
		}
$ELMHTML .= <<<EOF
					<div class="button{$extra}" id="conbut{$count}"><a onClick="showConsumedPage({$count})"><img src="{$this->registry->getConfig('base_url')}images/blank.gif" width='24' height='24' /></a></div>

EOF;
	}

$ELMHTML .= <<<EOF
				</div>

EOF;
}

$ELMHTML .= <<<EOF
			</div>
		</div>

EOF;
//--endhtml--//
return $ELMHTML;

}

public function pending( $pending, $breweries, $tempCount ) {

$this->pendingPage = 0;

$pageTotal = 5;

$pendingCount = 6;
$pendingTotal = 1;

$ELMHTML = <<<EOF
		<div class="box red-box" id='pending'>
			<a onClick="togglePending()">
				<div id="pending-head">
					<div class="check">&nbsp;</div>
					<div id="pending-head-closed">
						SEE YOUR <span class='bold'>{$tempCount}</span> PENDING BEERS
					</div>
					<div id="pending-head-open" class="hidden">
						YOUR <span class='bold'>{$tempCount}</span> PENDING BEERS
					</div>
				</div>
			</a>
			<div id="pending-text" class="hidden">

EOF;

if ( count( $pending ) > 0 )
{
	foreach( $pending as $id => $beer )
	{
		if ( $pendingCount > $pageTotal )
		{
			if ( $this->pendingPage > 0 )
			{
$ELMHTML .= <<<EOF
				</div>

EOF;
			}

			$hidden = " class='pending-page'";

			if ( $this->pendingPage > 0 )
			{
				$hidden = " class='pending-page hidden'";
			}

			$this->pendingPage++;
			$pendingCount = 0;


$ELMHTML .= <<<EOF
				<div id="pen{$this->pendingPage}"{$hidden}>

EOF;
		}

$ELMHTML .= <<<EOF
					<div class="beer">
						<p class="name">{$pendingTotal}. {$beer['title']}</p>
					</div>

EOF;

		$pendingCount++;
		$pendingTotal++;

	}

$ELMHTML .= <<<EOF
				</div>

EOF;

}

if ( $this->pendingPage > 1 )
{
$ELMHTML .= <<<EOF
				<div id='pending-control'>
					<div class="previous"><a onClick="previousPendingPage()"><img src="{$this->registry->getConfig('base_url')}images/blank.gif" width='24' height='24' /></a></div>
					<div class="next"><a onClick="nextPendingPage()"><img src="{$this->registry->getConfig('base_url')}images/blank.gif" width='24' height='24' /></a></div>

EOF;

	for( $count = 1; $count <= $this->pendingPage; $count++ )
	{
		$extra = '';
		
		if ( $count == 1 )
		{
			$extra = " selected";
		}
$ELMHTML .= <<<EOF
					<div class="button{$extra}" id="penbut{$count}"><a onClick="showPendingPage({$count})"><img src="{$this->registry->getConfig('base_url')}images/blank.gif" width='24' height='24' /></a></div>

EOF;
	}

$ELMHTML .= <<<EOF
				</div>

EOF;
}

$ELMHTML .= <<<EOF
			</div>
		</div>

EOF;
//--endhtml--//
return $ELMHTML;

}

public function redeem( $rewardID ) {

$ELMHTML = <<<EOF
		<div class="box green-box" id='redeem'>
			<a >
				<div id="redeem-head">
					<div class="check">&nbsp;</div>
					<div id="redeem-head-closed"><span class="bold">REDEEM YOUR REWARD</span></div>
				</div>
			</a>
		</div>

EOF;
//--endhtml--//
return $ELMHTML;

}

public function tap( $onTap, $breweries, $consumed, $currentBeers ) {

$this->tapPage = 0;

$pageTotal = 6;

$tapCount = 10;
$tapCat = 0;
$tapTotal = 1;
$categories = $this->cache->getCache("categories");

$ELMHTML = <<<EOF
	<div class="box white-box" id='on-tap'>
			<div id="on-tap-head">CURRENT BEERS ON TAP</div>

EOF;

if ( count( $onTap ) > 0 )
{
	foreach( $onTap as $id => $beer )
	{
		if ( $tapCount > $pageTotal )
		{
			if ( $this->tapPage > 0 )
			{
$ELMHTML .= <<<EOF
				</div>
			</div>

EOF;
			}

			if ( $tapCat != $beer['category_id'] )
			{
				$tapCat = $beer['category_id'];
			}

			$hidden = " class='on-tap-page'";

			if ( $this->tapPage > 0 )
			{
				$hidden = " class='on-tap-page hidden'";
			}

			$this->tapPage++;
			$tapCount = 0;


$ELMHTML .= <<<EOF
			<div id="tap{$this->tapPage}"{$hidden}>
				<div id="on-tap-beers">
					<div class="on-tap-group">{$categories[$tapCat]['title']}</div>

EOF;
		}

		if ( $beer['category_id'] != $tapCat )
		{
			$tapCat = $beer['category_id'];

$ELMHTML .= <<<EOF
				</div>
				<br />
				<br />
				<div id="on-tap-beers">
					<div class="on-tap-group">{$categories[$tapCat]['title']}</div>

EOF;
		}

		$beer['price_print'] = substr( $beer['price'], 0, -3 );
		$beer['price_print'] = " <span class='smaller'>&#36;{$beer['price_print']}</span>";

		if ( isset( $currentBeers[ $beer['menu_item_id'] ] ) )
		{
			$beer['consumed'] = " check";
		}
		else if ( isset( $consumed[ $beer['menu_item_id'] ] ) )
		{
			$beer['consumed'] = " check-green";
		}
		else
		{
			$beer['consumed'] = "";
		}

		$beer['points_print'] = '';
		
		if ( $beer['points'] > 1 )
		{
			 $beer['points_print'] = " <span class='smaller'>({$beer['points']}&nbsp;points)</span>";
		}

		if ( isset( $beer['url'] ) && strlen( $beer['url'] ) > 6 )
		{
$ELMHTML .= <<<EOF
					<a href="{$beer['url']}" target="_blank">
EOF;
		}

$ELMHTML .= <<<EOF
					<div class="beer{$beer['consumed']}">
						<p class="name">{$beer['title']}</p>
					</div>

EOF;

		if ( isset( $beer['url'] ) && strlen( $beer['url'] ) > 6 )
		{
$ELMHTML .= <<<EOF
					</a>
EOF;
		}

		$tapCount++;
		$tapTotal++;

	}

$ELMHTML .= <<<EOF
				</div>
			</div>

EOF;

}

if ( $this->tapPage > 1 )
{
$ELMHTML .= <<<EOF
			<div id='on-tap-control'>
				<div class="previous"><a onClick="previousTapPage()"><img src="{$this->registry->getConfig('base_url')}images/blank.gif" width='24' height='24' /></a></div>
				<div class="next"><a onClick="nextTapPage()"><img src="{$this->registry->getConfig('base_url')}images/blank.gif" width='24' height='24' /></a></div>

EOF;

	for( $count = 1; $count <= $this->tapPage; $count++ )
	{
		$extra = '';
		
		if ( $count == 1 )
		{
			$extra = " selected";
		}
$ELMHTML .= <<<EOF
				<div class="button{$extra}" id="tapbut{$count}"><a onClick="showTapPage({$count})"><img src="{$this->registry->getConfig('base_url')}images/blank.gif" width='24' height='24' /></a></div>

EOF;
	}

$ELMHTML .= <<<EOF
			</div>

EOF;
}

$ELMHTML .= <<<EOF
		</div>

EOF;
//--endhtml--//
return $ELMHTML;

}

public function cellar( $cellar, $breweries, $consumed, $currentBeers ) {

$this->cellarPage = 0;
$cellarCount = 6;
$cellarTotal = 1;

$ELMHTML = <<<EOF
		<div class="box white-box" id='cellar'>
				<div id="cellar-head">CELLAR RESERVE</div>
				<div class="red-hr">&nbsp;</div>

EOF;

if ( count( $cellar ) > 0 )
{
	foreach( $cellar as $id => $beer )
	{
		if ( $cellarCount > 3 )
		{
			if ( $this->cellarPage > 0 )
			{
$ELMHTML .= <<<EOF
				</div>

EOF;
			}

			$hidden = " class='cellar-page'";

			if ( $this->cellarPage > 0 )
			{
				$hidden = " class='cellar-page hidden'";
			}

			$this->cellarPage++;
			$cellarCount = 0;


$ELMHTML .= <<<EOF
				<div id="cel{$this->cellarPage}"{$hidden}>

EOF;
		}

		$beer['price_print'] = substr( $beer['price'], 0, -3 );
		$beer['price_print'] = " <span class='smaller'>&#36;{$beer['price_print']}</span>";

		if ( $beer['points'] > 1 )
		{
			 $beer['points_print'] = " <span class='smaller'>({$beer['points']}&nbsp;points)</span>";
		}

		if ( isset( $currentBeers[ $beer['menu_item_id'] ] ) )
		{
			$beer['consumed'] = " check";
		}
		else if ( isset( $consumed[ $beer['menu_item_id'] ] ) )
		{
			$beer['consumed'] = " check-green";
		}
		else
		{
			$beer['consumed'] = "";
		}

		if ( isset( $beer['url'] ) && strlen( $beer['url'] ) > 6 )
		{
$ELMHTML .= <<<EOF
					<a href="{$beer['url']}" target="_blank">
EOF;
		}

$ELMHTML .= <<<EOF
					<div class="beer{$beer['consumed']}">
						<p class="name">{$beer['title']}</p>
					</div>

EOF;

		if ( isset( $beer['url'] ) && strlen( $beer['url'] ) > 6 )
		{
$ELMHTML .= <<<EOF
					</a>
EOF;
		}

		$cellarCount++;
		$cellarTotal++;

	}

$ELMHTML .= <<<EOF
				</div>

EOF;

}

if ( $this->cellarPage > 1 )
{
$ELMHTML .= <<<EOF
				<div id='cellar-control'>
					<div class="previous"><a onClick="previousCellarPage()"><img src="{$this->registry->getConfig('base_url')}images/blank.gif" width='24' height='24' /></a></div>
					<div class="next"><a onClick="nextCellarPage()"><img src="{$this->registry->getConfig('base_url')}images/blank.gif" width='24' height='24' /></a></div>

EOF;

	for( $count = 1; $count <= $this->cellarPage; $count++ )
	{
		$extra = '';
		
		if ( $count == 1 )
		{
			$extra = " selected";
		}
$ELMHTML .= <<<EOF
					<div class="button{$extra}" id="celbut{$count}"><a onClick="showCellarPage({$count})"><img src="{$this->registry->getConfig('base_url')}images/blank.gif" width='24' height='24' /></a></div>

EOF;
	}

$ELMHTML .= <<<EOF
				</div>

EOF;
}

$ELMHTML .= <<<EOF
			</div>


EOF;
//--endhtml--//
return $ELMHTML;

}

public function how( $text = '' ) {

$ELMHTML = <<<EOF
		<div class="box dark-box" id="how-it-works">
			<div id="how-it-works-head"><a onClick="toggleHowItWorks()"><div id="how-it-works-beer">&nbsp;</div>HOW THE STUB CLUB WORKS</a></div>
			<div id="how-it-works-text" class="hidden">
{$text}
			</div>
		</div>

EOF;
//--endhtml--//
return $ELMHTML;

}

public function facebook() {

$ELMHTML = <<<EOF
		<a href="http://www.facebook.com/pages/Stubbys-Pub-and-Grub/143353952355870?ref=ts" target="_blank"><div class="box" id="facebook-link">&nbsp;</div></a>

EOF;
//--endhtml--//
return $ELMHTML;

}

public function menu() {

$ELMHTML = <<<EOF
		<a href="http://stubbyspubandgrub.com/menu/" target="_blank"><div class="box" id="menu-link">&nbsp;</div></a>

EOF;
//--endhtml--//
return $ELMHTML;

}

public function userPanel( $user, $toGoNum ) {

$ELMHTML .= <<<EOF
		<div class="box dark-box" id="user-panel">
			Member: <em>{$user->getFirstName()} {$user->getLastName()}</em> (Club #{$user->getClubID()})
			<div id='user-stats'>
				<span class='bold'>{$toGoNum}</span> MORE POINTS 'TIL YOUR NEXT REWARD
			</div>
		</div>

EOF;
//--endhtml--//
return $ELMHTML;

}

//===========================================================================
// Normal wrapper
//===========================================================================
public function wrapper( $onTap, $cellar, $text, $consumed, $consumedNum, $toGoNum, $currentBeers, $pending ) {

$this->tapPage = 0;
$this->cellarPage = 0;
$this->consumedPage = 0;
$this->pendingPage = 0;

$user = $this->registry->getUser();
//$breweries = $this->cache->getCache('breweries');
$right = '';

if ( is_object( $user ) )
{
	$right = " logged-in";
}

$ELMHTML = "";
//--starthtml--//
$ELMHTML .= <<<EOF
	<div id="content">

EOF;

if ( is_object( $user ) )
{
$tempPend  = count($pending);
$tempCount = count($consumed);
$tempTotal = count($onTap);

$ELMHTML .= <<<EOF
{$this->userPanel( $user, $toGoNum )}
		<br />
EOF;

if ( $tempPend > 0 )
{
$ELMHTML .= <<<EOF
{$this->pending( $pending, $breweries, $tempPend )}
		<br />
EOF;
}
$ELMHTML .= <<<EOF
{$this->conquered( $consumed, $breweries, $tempCount )}
		<br />

EOF;

}

$ELMHTML .= <<<EOF
{$this->how( $text )}
		<br />
{$this->tap( $onTap, $breweries, $consumed, $currentBeers )}
		<br />
{$this->cellar( $cellar, $breweries, $consumed, $currentBeers )}
		<br />
{$this->menu()}
		<br />
{$this->facebook()}

	</div>


<script type="text/javascript">
totalTapPages = {$this->tapPage};
totalCellarPages = {$this->cellarPage};
totalConsumedPages = {$this->consumedPage};
totalPendingPages = {$this->pendingPage};
</script>

EOF;
//--endhtml--//
return $ELMHTML;
}

}

?>