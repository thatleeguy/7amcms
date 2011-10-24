<?php

function format_date($unix)
{
	if ($unix == '' || ! is_numeric($unix))
	{
		return $unix;
	}
	if (empty($unix) or $unix == '0')
	{
		return 'n/a';
	}
	return date(PAN::setting('date_format'), $unix);
}

function format_seconds($seconds, $pad_hrs = false)
{
	$o = '';
	$hrs = intval(intval($seconds) / 3600);
	$o .= ($pad_hrs) ? str_pad($hrs, 2, '0', STR_PAD_LEFT) : $hrs;
	$o .= ':';
	$mns = intval(($seconds / 60) % 60);
	$o .= str_pad($mns, 2, '0', STR_PAD_LEFT);
	$o .= ':';
	$secs = intval($seconds % 60);
	$o .= str_pad($secs, 2, '0', STR_PAD_LEFT);
	return $o;
}

function read_date_picker($string)
{
    return round($string / 1000);
}

function get_date_picker_format() {
    $php_format = PAN::setting('date_format');
    $js_format = '';
    $parts = array();
    
    # Step 1 = Get rid of all literals in php format.
    preg_match('/\\\\./', $php_format, $parts);
    foreach ($parts as $key => $part) {
        $php_format = implode('{'.$key. '}', explode($part, $php_format, 2));
    }
    
    $length = strlen($php_format);
    $key = count($parts) - 1;
    
    $buffer = $php_format;
    $php_format = '';
    
    for ($i=0; $i<$length; $i++) {
        if (!in_array($buffer[$i], array('d', 'j', 'z', 'D', 'l', 'n', 'm', 'M', 'F', 'y', 'Y', 'U'))) {
            # It's a literal, let's take it out.
            $key = $key + 1;
            $parts[$key] = $buffer[$i];
            $php_format .= '{'.$key.'}';
        } else {
            # It's not a literal, let's leave it.
            $php_format .= $buffer[$i];
        }
    }
    
    $php_format = str_replace('d', 'dd', $php_format);
    $php_format = str_replace('j', 'd', $php_format);
    $php_format = str_replace('z', 'o', $php_format);
    $php_format = str_replace('D', 'D', $php_format);
    $php_format = str_replace('l', 'DD', $php_format);
    $php_format = str_replace('n', 'm', $php_format);
    $php_format = str_replace('m', 'mm', $php_format);
    $php_format = str_replace('M', 'M', $php_format);
    $php_format = str_replace('F', 'MM', $php_format);
    $php_format = str_replace('y', 'y', $php_format);
    $php_format = str_replace('Y', 'yy', $php_format);
    $php_format = str_replace('U', '@', $php_format);
    
    foreach ($parts as $key => $part) {
        $part = ($part == ' ') ? ' ' : "'$part'";
        $php_format = str_replace('{'.$key. '}', $part, $php_format);
    }
    
    # Run it over again, because it needs to.
    foreach ($parts as $key => $part) {
        $php_format = str_replace("'{''$key''}'", "'".str_replace('\\', '', $part)."'", $php_format);
    }
    
    return $php_format;
    
}

function format_time($unix) {
    if ($unix == '' || ! is_numeric($unix))
	{
		return $unix;
	}
        if (empty($unix) or $unix == '0') {return 'n/a';}
	return date(PAN::setting('time_format'), $unix);
}

function seconds_to_human($seconds = 1)
{
	$CI =& get_instance();
	$CI->lang->load('date');

	if ( ! is_numeric($seconds))
	{
		$seconds = 1;
	}

	
	$str = '';
	$years = floor($seconds / 31536000);

	if ($years > 0)
	{	
		$str .= $years.' '.$CI->lang->line((($years	> 1) ? 'date_years' : 'date_year')).', ';
	}	

	$seconds -= $years * 31536000;
	$months = floor($seconds / 2628000);

	if ($years > 0 OR $months > 0)
	{
		if ($months > 0)
		{	
			$str .= $months.' '.$CI->lang->line((($months	> 1) ? 'date_months' : 'date_month')).', ';
		}	

		$seconds -= $months * 2628000;
	}

	$weeks = floor($seconds / 604800);

	if ($years > 0 OR $months > 0 OR $weeks > 0)
	{
		if ($weeks > 0)
		{	
			$str .= $weeks.' '.$CI->lang->line((($weeks	> 1) ? 'date_weeks' : 'date_week')).', ';
		}
	
		$seconds -= $weeks * 604800;
	}			

	$days = floor($seconds / 86400);

	if ($months > 0 OR $weeks > 0 OR $days > 0)
	{
		if ($days > 0)
		{	
			$str .= $days.' '.$CI->lang->line((($days	> 1) ? 'date_days' : 'date_day')).', ';
		}

		$seconds -= $days * 86400;
	}

	$hours = floor($seconds / 3600);

	if ($days > 0 OR $hours > 0)
	{
		if ($hours > 0)
		{
			$str .= $hours.' '.$CI->lang->line((($hours	> 1) ? 'date_hours' : 'date_hour')).', ';
		}
	
		$seconds -= $hours * 3600;
	}

	$minutes = floor($seconds / 60);

	if ($days > 0 OR $hours > 0 OR $minutes > 0)
	{
		if ($minutes > 0)
		{	
			$str .= $minutes.' '.$CI->lang->line((($minutes	> 1) ? 'date_minutes' : 'date_minute')).', ';
		}
	
		$seconds -= $minutes * 60;
	}

	if ($str == '')
	{
		$str .= $seconds.' '.$CI->lang->line((($seconds	> 1) ? 'date_seconds' : 'date_second')).', ';
	}
		
	return substr(trim($str), 0, -1);
}