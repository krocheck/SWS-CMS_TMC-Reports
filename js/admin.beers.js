// JavaScript Document
var rows = new Array();
var states = {'0':'#selAllState','a':'#selActState','i':'#selInState'};
var cats = new Array();
var nums = new Array();
var idNums = new Array();
var curElement = '#';
var curState = '0';
var curID = '0';
var x = 0;
var y = 0;

function showCells( state, id )
{
	for( x in rows['0'] )
	{
		for( y in rows['0'][x] )
		{
			curElement = '#row' + rows['0'][x][y];
			$( curElement ).addClass('invisible');
		}
	}

	for( x in rows[ state ] )
	{
		if ( x == id || id == 0 )
		{
			for( y in rows[ state ][x] )
			{
				curElement = '#row' + rows[ state ][x][y];
				$( curElement ).removeClass('invisible');
			}
		}
	}
}

function switchState( state )
{
	curState = state;

	showCells( curState, curID );

	for( x in states )
	{
		if ( x == curState )
		{
			curElement = states[x];
			$( curElement ).addClass('active');
		}
		else
		{
			curElement = states[x];
			$( curElement ).removeClass('active');
		}
	}

	for( x in idNums )
	{
		curElement = idNums[x];
		$( curElement ).text( nums[curState][x] )
	}
}

function switchID( id )
{
	curID = id;

	showCells( curState, curID );

	for( x in cats )
	{
		if ( x == curID )
		{
			curElement = cats[x];
			$( curElement ).addClass('active');
		}
		else
		{
			curElement = cats[x];
			$( curElement ).removeClass('active');
		}
	}
}