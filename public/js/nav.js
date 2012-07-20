// Based on a script from javascript-array.com
var timeout	= 500;
var closetimer	= 0;
var menuitem	= 0;

// open hidden layer
function fopen(id)
{	
    // cancel close timer
    fcancelclosetime();
    // close old layer
    if(menuitem) menuitem.style.visibility = 'hidden';
    // get new layer and show it
    menuitem = document.getElementById(id);
    menuitem.style.visibility = 'visible';
}

// close showed layer
function fclose()
{
    if(menuitem) menuitem.style.visibility = 'hidden';
}

// go close timer
function fclosetime()
{
    closetimer = window.setTimeout(fclose, timeout);
}

// cancel close timer
function fcancelclosetime()
{
    if(closetimer)
    {
	window.clearTimeout(closetimer);
	closetimer = null;
    }
}

// close layer when click-out
document.onclick = fclose; 
