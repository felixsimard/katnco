<?php
/* CLASS FOR CONVERTING TIME TO AGO
class convertToAgo {

    function convert_datetime($str) {
	
   		list($date, $time) = explode(' ', $str);
    	list($year, $month, $day) = explode('-', $date);
    	list($hour, $minute, $second) = explode(':', $time);
    	$timestamp = mktime($hour, $minute, $second, $month, $day, $year);
    	return $timestamp;
    }

    function makeAgo($timestamp){
	
   		$difference = time() - $timestamp;
   		$periods = array("sec", "min", "hour", "day", "week", "month", "year");
   		$lengths = array("60","60","24","7","4.35","12");
   		for($j = 0; $difference >= $lengths[$j]; $j++)
   			$difference /= $lengths[$j];
   			$difference = round($difference);
   		if($difference != 1 && $periods[$j] != "min" && $periods[$j] != "sec") $periods[$j].= "s";
   			$text = "$difference $periods[$j] ago";
   			return $text;
    }
	
}  END CLASS */
?>
<?php

function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'w',
        'd' => 'd',
        'h' => 'h',
        'i' => 'min',
        's' => 's',
    );
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . '' . $v . ($diff->$k > 1 ? '' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . '' : 'just now';
}

?>