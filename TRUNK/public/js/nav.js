// Based on a script from javascript-array.com
var timeout	= 500;
var closetimer	= 0;
var menuitem	= 0;

// open hidden layer
function azeboopen(id)
{	
    // cancel close timer
    azebocancelclosetime();
    // close old layer
    if(menuitem) menuitem.style.visibility = 'hidden';
    // get new layer and show it
    menuitem = document.getElementById(id);
    menuitem.style.visibility = 'visible';
}

// close showed layer
function azeboclose()
{
    if(menuitem) menuitem.style.visibility = 'hidden';
}

// go close timer
function azeboclosetime()
{
    closetimer = window.setTimeout(azeboclose, timeout);
}

// cancel close timer
function azebocancelclosetime()
{
    if(closetimer)
    {
	window.clearTimeout(closetimer);
	closetimer = null;
    }
}

// close layer when click-out
document.onclick = azeboclose; 
