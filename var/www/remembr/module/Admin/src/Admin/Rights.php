<?php

namespace Admin;

class Rights
{
	public static $banners = 1;
	public static $payments = 2;
	public static $cms = 4;

	public static $all = 7;
}

\Auth\Rights\Right::init(__NAMESPACE__.'\Rights');
