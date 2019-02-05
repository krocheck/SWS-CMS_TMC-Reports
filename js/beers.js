// JavaScript Document
var totalTapPages = 0;
var totalCellarPages = 0;
var totalConsumedPages = 0;
var totalPendingPages = 0;
var curElement = '#';
var curTap = 1;
var curCel = 1;
var curCon = 1;
var curPen = 1;
var track = 0;
var howItWorks = 0;
var consumedState = 0;
var pendingState = 0;

function showTapPage( id )
{
	if ( curTap != id && id <= totalTapPages && id > 0 )
	{
		track = 1;

		while( track <= totalTapPages )
		{
			curElement = '#tap' + track;
			$( curElement ).addClass('hidden');

			curElement = '#tapbut' + track;
			$( curElement ).removeClass('selected');

			track = track + 1;
		}

		curElement = '#tap' + id;
		$( curElement ).removeClass('hidden');

		curElement = '#tapbut' + id;
		$( curElement ).addClass('selected');

		curTap = id;
	}
}

function nextTapPage()
{
	var temp = curTap;
	temp = temp+1;
	showTapPage( temp );
}

function previousTapPage()
{
	var temp = curTap;
	temp = temp-1;
	showTapPage( temp );
}

function showCellarPage( id )
{
	if ( curCel != id && id <= totalCellarPages && id > 0 )
	{
		track = 1;

		while( track <= totalCellarPages )
		{
			curElement = '#cel' + track;
			$( curElement ).addClass('hidden');

			curElement = '#celbut' + track;
			$( curElement ).removeClass('selected');

			track = track + 1;
		}

		curElement = '#cel' + id;
		$( curElement ).removeClass('hidden');

		curElement = '#celbut' + id;
		$( curElement ).addClass('selected');

		curCel = id;
	}
}

function nextCellarPage()
{
	var temp = curCel;
	temp = temp+1;
	showCellarPage( temp );
}

function previousCellarPage()
{
	var temp = curCel;
	temp = temp-1;
	showCellarPage( temp );
}

function showConsumedPage( id )
{
	if ( curCon != id && id <= totalConsumedPages && id > 0 )
	{
		track = 1;

		while( track <= totalConsumedPages )
		{
			curElement = '#con' + track;
			$( curElement ).addClass('hidden');

			curElement = '#conbut' + track;
			$( curElement ).removeClass('selected');

			track = track + 1;
		}

		curElement = '#con' + id;
		$( curElement ).removeClass('hidden');

		curElement = '#conbut' + id;
		$( curElement ).addClass('selected');

		curCon = id;
	}
}

function nextConsumedPage()
{
	var temp = curCon;
	temp = temp+1;
	showConsumedPage( temp );
}

function previousConsumedPage()
{
	var temp = curCon;
	temp = temp-1;
	showConsumedPage( temp );
}

function showPendingPage( id )
{
	if ( curPen != id && id <= totalPendingPages && id > 0 )
	{
		track = 1;

		while( track <= totalPendingPages )
		{
			curElement = '#pen' + track;
			$( curElement ).addClass('hidden');

			curElement = '#penbut' + track;
			$( curElement ).removeClass('selected');

			track = track + 1;
		}

		curElement = '#pen' + id;
		$( curElement ).removeClass('hidden');

		curElement = '#penbut' + id;
		$( curElement ).addClass('selected');

		curPen = id;
	}
}

function nextPendingPage()
{
	var temp = curPen;
	temp = temp+1;
	showPendingPage( temp );
}

function previousPendingPage()
{
	var temp = curPen;
	temp = temp-1;
	showPendingPage( temp );
}

function toggleHowItWorks()
{
	if ( howItWorks == 0 )
	{
		curElement = '#how-it-works-text'
		$( curElement ).removeClass('hidden');
		howItWorks = 1;
	}
	else
	{
		curElement = '#how-it-works-text'
		$( curElement ).addClass('hidden');
		howItWorks = 0;
	}
}


function toggleConsumed()
{
	if ( consumedState == 0 )
	{
		curElement = '#consumed-text'
		$( curElement ).removeClass('hidden');
		curElement = '#consumed-head-open'
		$( curElement ).removeClass('hidden');
		curElement = '#consumed-head-closed'
		$( curElement ).addClass('hidden');
		consumedState = 1;
	}
	else
	{
		curElement = '#consumed-text'
		$( curElement ).addClass('hidden');
		curElement = '#consumed-head-open'
		$( curElement ).addClass('hidden');
		curElement = '#consumed-head-closed'
		$( curElement ).removeClass('hidden');
		consumedState = 0;
	}
}

function togglePending()
{
	if ( pendingState == 0 )
	{
		curElement = '#pending-text'
		$( curElement ).removeClass('hidden');
		curElement = '#pending-head-open'
		$( curElement ).removeClass('hidden');
		curElement = '#pending-head-closed'
		$( curElement ).addClass('hidden');
		pendingState = 1;
	}
	else
	{
		curElement = '#pending-text'
		$( curElement ).addClass('hidden');
		curElement = '#pending-head-open'
		$( curElement ).addClass('hidden');
		curElement = '#pending-head-closed'
		$( curElement ).removeClass('hidden');
		pendingState = 0;
	}
}

$(function(){
	$('#on-tap').bind('swipeleft', function(event, ui){ nextTapPage(); });
	$('#on-tap').bind('swiperight', function(event, ui){ previousTapPage(); });
	$('#cellar').bind('swipeleft', function(event, ui){ nextCellarPage(); });
	$('#cellar').bind('swiperight', function(event, ui){ previousCellarPage(); });
	$('#consumed').bind('swipeleft', function(event, ui){ nextConsumedPage(); });
	$('#consumed').bind('swiperight', function(event, ui){ previousConsumedPage(); });
	$('#pending').bind('swipeleft', function(event, ui){ nextPendingPage(); });
	$('#pending').bind('swiperight', function(event, ui){ previousPendingPage(); });
})