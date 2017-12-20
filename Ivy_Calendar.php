<?php
/**
 * Calendar module for IVY3.0.
 *
 * Generate a calendar based on the given month/year
 *
 * @author James Randell <james.randell@hotmail.co.uk>
 * @version 1.0
 * @package Calendar
 */
class Ivy_Calendar
{

/**
 * Data array, holds the selected month
 * 
 * @access public
 * @return array
 */
public $data = array ();

/**
 * Holds the selected month
 * 
 * @access public
 * @return int
 */
public $month = 1;

/**
 * Holds the selected year
 * 
 * @access public
 * @return int
 */
public $year = 2000;

/**
 * Stores a Unix timestamp to rows/columns location marker
 * 
 * When the calendar array is created in data, this private array is built to
 * maintain indexes between a day in the calendar and it's timestamp
 * 
 * @access	private
 * @var	array
 */
private $dateReference = array ();
	
/**
 * Builds the calendar array for the desired month and year
 * 
 * Starts by setting the month/year to the $_GET variables (if present). Then
 * organizes the calendar by rows and columns (rows n++ with columns meaning
 * days). Also create a private referral data array with day Unix timestamps for
 * keys, with the row/column for location.
 * 
 * @access public
 */
public function __construct ()
{
	$this->month = date("F");
	$this->year = date("Y");
	
	if (isset($_GET['month'])) {
		$this->month = ucfirst($_GET['month']);
	}
	
	if (isset($_GET['year'])) {
		if (is_numeric($_GET['year'])) {
			$this->year = $_GET['year'];
		}
	}
	
	/*
	 * We find the first day of the selected month
	 */
	$this->selectedDate = strtotime("1 $this->month $this->year");
	$this->selectedDateFirstDay = date("N", $this->selectedDate);
	$this->selectedDateDays = date("t", $this->selectedDate);
	
	$row = 0;
	for ($i = 1; $i <= $this->selectedDateDays; $i++) {
		$time = strtotime("$i $this->month $this->year");
		$date = date("N", $time) - 1;
		$day = date("l", $time);
	
		if ($date == 0) {
			++$row;
		}
		
		
		$this->data[$row][$date]['date'] = $i;
		$this->data[$row][$date]['date2'] = date("jS", $time);
		$this->data[$row][$date]['day'] = $day;
		$this->data[$row][$date]['daysmall'] = date("D", $time);
		$this->data[$row][$date]['letter'] = $day;
		$this->data[$row][$date]['letter'] = $this->data[$row][$date]['letter'][0];
		$this->data[$row][$date]['dayno'] = $date;
		if (date("j", time()) == $i) {
			$this->data[$row][$date]['today'] = true;
		}
		
		$this->data[$row][$date]['month'] = $this->month;
		$this->data[$row][$date]['year'] = $this->year;
		
		$this->dateReference[$time]['row'] = $row;
		$this->dateReference[$time]['column'] = $date;
	}
	
	$lastLine = count($this->data) - 1;
	for ($i = 0; $i <= 6; $i++) {
		if (isset($this->data[0][$i])) {
			$t = $this->data[0][$i];
		}
		$this->data[0][$i] = $t;
		$q = '';
		if (isset($this->data[$lastLine][$i])) {
			$q = $this->data[$lastLine][$i];
		}
		$this->data[$lastLine][$i] = $q;			
	}

	foreach ($this->data as $week => $data) {
		ksort($this->data[$week]);
		foreach ($data as $day => $value) {
			if (!isset($value['date'])) {
				#unset($this->data[$week][$day]);
			}
		}
	}
	
	ksort($this->data);
}


/**
 * Loops through the dataset adding records to the calendar
 * 
 * With the $field specified the method takes that field as the date and adds
 * that record to that date in the calendar.
 * 
 * @access	public
 * @param	array	$recordSet	Record set from a select query
 * @param	string	$field		Name of the field to use as the date
 */	
public function insert ($recordSet, $field)
{
	(array) $location = array ();
	
	if (!is_array($recordSet)) {
		trigger_error
		('Data to be added to the calendar object is expected to be an array.');
		return false;
	}
	
	foreach ($recordSet as $id => $data) {
		if ($field[0] === '_') {
			trigger_error('Field needs to be a Unix timestamp');
			return false;
		}
		$location = $this->getDateLocation($data['_'.$field]);

		if (isset($this->data[ $location['row'] ][ $location['column'] ])) {
			$this->data[ $location['row'] ][ $location['column'] ]['data'][] = $data;
		}

		$location = array ();
	}

}
	
/**
 * Uses the date lookup table to retrieve date information location.
 * 
 * Accepts a Unix timestamp, converts it to the start of the day and returns the
 * appropriate row/column location of that day i nthe main data array.
 * 
 * @access	private
 * @return	array
 * @param	string	$date	The date to turn into a Unix timstamp
 */
private function getDateLocation ($date)
{
	$date = strtotime(date("j F Y", $date));
	
	return array(	'row'	=>	$this->dateReference[$date]['row'],
					'column'=>	$this->dateReference[$date]['column']
				);
}


}

?>