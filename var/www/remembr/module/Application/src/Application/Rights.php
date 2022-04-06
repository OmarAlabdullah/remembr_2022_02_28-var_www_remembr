<?php

namespace Application;

class Rights
{
	public static $loggedin = 1;
	public static $friend = 2;
	public static $admin = 4;

	public static $all = 7;
}

\Auth\Rights\Right::init(__NAMESPACE__.'\Rights');
