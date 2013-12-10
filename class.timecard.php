<?php
 /**
  * Timecard class holds on to a start time and end time, with utility functions
  *
  * Use the timecard class to calculate the duration between two times
  * or the overlap between this timecard and another timecard
  *
  * @author  Josh Lee <josh@qccreative.com>
  * 
  * @version 1.0
  *
  * @date 2013-05-15
  * 
  */
class timecard_Timecard {
	protected $start_time = 0;
	protected $end_time = 0;
	
 /**
  * Constructor for Timecard class
  *
  * @param int	$start_time	The start time for the timecard, in unix timestamp or any format that strototime() can understand
  * @param int	$end_time	The end time. Must be after start time.
  */
	public function __construct($start_time, $end_time) {
		//a unix timestamp is an integer
		//if we didn't get integers,
		//run strtotime to convert
		//alternate formats.
		if(!is_integer($start_time)) {
			$start_time = strtotime($start_time);
		}
		if(!is_integer($end_time)) {
			$end_time = strtotime($end_time);
		}

		//End time should be after start time
		if($end_time < $start_time)
			throw new Exception('end_time less than start_time');
		
		//setup our private vars
		$this->start_time = $start_time;
		$this->end_time = $end_time;
	}

 /**
  * Shift the start and end times forward.
  *
  * @param int  $seconds The number of seconds by which to shift start/end times. Negative values OK.
  */		
	public function shift($seconds) {
		if(!is_integer($seconds))
			throw new WrongTypeException('seconds must be an integer');
		$this->end_time = $this->end_time + $seconds;
		$this->start_time = $this->start_time + $seconds;
	}

 /**
  * Changes only the end-time, which will affect the total duration of the timecard.
  *
  * @param int  $seconds The number of seconds by which to shift the end time. Negative values OK.
  */		
	public function extend($seconds) {
		if(!is_integer($seconds))
			throw new WrongTypeException('seconds must be an integer');
		$this->end_time = $this->end_time + $seconds;
	}
	
 /**
  * Get the start time.
  *
  * @return int The start time for the timecard, in unix timestamp.
  */	
	public function start_time() {
		return $this->start_time;
	}
	
 /**
  * Get the end time.
  *
  * @return int The end time for the timecard, in unix timestamp.
  */	
	public function end_time() {
		return $this->end_time;
	}

 /**
  * Convert seconds to hours
  *
  * @param	int	$seconds	The number of seconds to convert (usually from subtracting two timestamps)
  * @param	int	$round_to 	The number of decimals to round to. default = 1.
  * @return int	The number of hours, rounded to the specified decimal.
  */	
	public function seconds_to_hours($seconds, $round_to=1) {
		return round( $seconds / 3600, $round_to );
	}
	
 /**
  * The total duration of the time card.
  *
  * @param	output	$output	 Output "seconds" or "hours". default = "seconds"
  * @return int|float		 The number of hours elapsed by the timecard, rounded to tenths, or the number of seconds.
  */	
  	public function duration($output = "seconds") {
		$return = $this->end_time - $this->start_time;
		if($output == "hours") {
			$return = $this->seconds_to_hours( $return );
		}
		
		return $return;
	}
	
 /**
  * Calculates the length of overlap between this timecard and another
  *
  * @param	Timecard	$alternate		The timecard to compare to.
  * @param	enum		$output			Output "seconds" or "hours". default = "seconds"
  * @return int			The number of seconds elapsed by the timecard, or the number of hours rounded to tenths.
  */
  	public function overlap($alternate, $output = "seconds") {		
		$intersect = $this->intersect( $alternate );
		$return    = ( $intersect ) ? $intersect->duration( $output ) : 0;
		return $return;
	}
	
	
	
 /**
  * Creates a new timecard from the intersection of two timecards.
  * 
  * Returns false if there is no overlap
  *
  * @param	Timecard $alternate 	The timecard to intersect with.
  * @return Timecard|bool           A timecard created from on the overlap of this and alternate.
  */	
	public function intersect( $alternate ) {
		//check if $this->start_time is within bounds of alternate, or vice versa
		if(($this->start_time >= $alternate->start_time() && $this->start_time < $alternate->end_time()) ||
		   ($alternate->start_time() >= $this->start_time && $alternate->start_time() < $this->end_time)) {
			
			//Since we know there is overlap, grabbing the
			//latest start time and the earliest end time
			//will provide the duration of overlap.
			$start = max($this->start_time, $alternate->start_time());
			$end = min($this->end_time, $alternate->end_time());
			
			$return = new timecard_Timecard($start, $end);
			return $return;
		}
		
		return false;	
	}
	
 /**
  * A rounding function capable of rounding a float
  * (up) to any arbitrary float (e.g. 1, .1, 2, 2.5).
  * 
  * Created by a commenter on php.net
  * http://docs.php.net/manual/da/function.round.php 
  *
  * @param	float $number 	The number to round.
  * @param	float $to		The resolution.
  */	
	public function round_to($number, $to){
		$to = 1 / $to; 
		return (ceil($number * $to) / $to);
    }
}