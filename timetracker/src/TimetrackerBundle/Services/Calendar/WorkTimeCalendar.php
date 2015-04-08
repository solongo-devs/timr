<?php namespace TimetrackerBundle\Services\Calendar;

use DateInterval, DateTime;
use TimetrackerBundle\Entity\Employee;
use TimetrackerBundle\Entity\Log;
use TimetrackerBundle\Entity\Note;

class WorkTimeCalendar extends Calendar {

	protected $employee;

	protected $hours = [];

	protected $statuses = [];

	protected $notes = [];

	public function personalize(Employee $employee)
	{
		$this->employee = $employee;

		$this->includeWorkingHours();
		$this->includeAdditionalData();
	}

	/*=====================================
	=            Working Hours            =
	=====================================*/

	public function getWorkingHours(Day $day)
	{
		if( ! isset($this->hours[$day->format('Y-m-d')]) )
		{
			return null;
		}

		return $this->hours[$day->format('Y-m-d')]->format('%h:%I');
	}

	public function getAverageHoursForMonth() {
		$hours_and_minutes = $this->getTotalWorkingHoursRaw();

		$hours = $hours_and_minutes[0];
		$minutes = $hours * 60 + $hours_and_minutes[1];

		$count = 0;
		foreach( $this->getDays() as $day ) {
			if( $day->getYear() != date('Y') || $day->getMonth() != date('m') || $day->getDay() <= date('d') ) {
				if( $day->isWeekday() && !$this->getStatus($day) && !$this->getHoliday($day) ) {
					$count++;
				}
			}
		}

		if( $count > 0 ) {
			$avg_minutes = $minutes / $count;
			$avg_hours = intval($avg_minutes / 60);	
			$rest_minutes = $avg_minutes % 60;

			return sprintf('%02d:%02d', $avg_hours, $rest_minutes);
		} else {
			return '00:00';
		}

	}

	public function getAverageHoursforYear() {
		
		$start_date = $this->employee->getFirstDay();
		
		$year = $this->year;
		
		$end_month = 12;
		
		if( $year == date('Y') ) {
			$end_month = date('m');
		}

		$start_month = 1;
		if( $start_date->format('Y') == date('Y') ) {
			$start_month = $start_date->format('m');
			$start_day = $start_date->format('d');
		}

		$total = new DateInterval('P0D');

		$hours = 0;
		$minutes = 0;
		$count = 0;

		for( $month = $start_month; $month <= $end_month; $month++ ) {

			unset($calendar);
			$calendar = new WorkTimeCalendar($this->requestStack, $this->router, $this->em, $month);
			$calendar->personalize($this->employee);
			$hours_and_minutes = $calendar->getTotalWorkingHoursRaw();

			$hours += $hours_and_minutes[0];
			$minutes += $hours_and_minutes[0] * 60 + $hours_and_minutes[1];

			foreach( $calendar->getDays() as $day ) {
				if( $day->getYear() != date('Y') || $day->getMonth() != date('m') || $day->getDay() <= date('d') ) {
					if( $day->getYear() == $start_date->format('Y') && $day->getMonth() == $start_date->format('m') && $day->getDay() < $start_day ) {
						continue;
					}
					if( $day->isWeekday() && !$this->getStatus($day) && !$calendar->getHoliday($day) ) {
						$count++;
					}
				}
			}
		
		}

		if( $count > 0 ) {
			$avg_minutes = $minutes / $count;
			$avg_hours = intval($avg_minutes / 60);	
			$rest_minutes = $avg_minutes % 60;

			return sprintf('%02d:%02d', $avg_hours, $rest_minutes);
		} else {
			return '00:00';
		}

	}

	public function getTotalWorkingHoursAllTime() {
		$start_year = 2015;
		$end_year = date('Y');
		$minutes = 0;
		
		for( $i = $start_year; $i <= $end_year; $i++ ) {

			$end_month = 12;
		
			if( $i == date('Y') ) {
				$end_month = date('m');
			}

			for( $month = 1; $month <= $end_month; $month++ ) {
	
				unset($calendar);
				$calendar = new WorkTimeCalendar($this->requestStack, $this->router, $this->em, $month, $i);
				$calendar->personalize($this->employee);
				$hours_and_minutes = $calendar->getTotalWorkingHoursRaw();

				$minutes += $hours_and_minutes[0] * 60 + $hours_and_minutes[1];
			
			}
			
		}
		
		return $minutes;
	}

	public function getTotalWorkdaysAllTime() {

		$start_date = $this->employee->getFirstDay();
			
		$start_year = $start_date->format('Y');
		$end_year = date('Y');
		$minutes = 0;
		
		$count = 0;
		for( $i = $start_year; $i <= $end_year; $i++ ) {

			$end_month = 12;
		
			if( $i == date('Y') ) {
				$end_month = date('m');
			}

			$start_month = 1;
			if( $start_date->format('Y') == date('Y') ) {
				$start_month = $start_date->format('m');
				$start_day = $start_date->format('d');
			}

			for( $month = $start_month; $month <= $end_month; $month++ ) {
				unset($calendar);
				$calendar = new WorkTimeCalendar($this->requestStack, $this->router, $this->em, $month, $i);
				$calendar->personalize($this->employee);
	
				foreach( $calendar->getDays() as $day ) {
					if( $day->getYear() != date('Y') || $day->getMonth() != date('m') || $day->getDay() <= date('d') ) {
						if( $day->getYear() == $start_date->format('Y') && $day->getMonth() == $start_date->format('m') && $day->getDay() < $start_day ) {
							continue;							
						}
						if( $day->isWeekday() && !$this->getStatus($day) && !$calendar->getHoliday($day) ) {
							$count++;
						}
					}
				}
			}

		}		

		return $count;
	
	}

	public function getOvertime() {
		
		$total_work = $this->getTotalWorkingHoursAllTime();

		$total_days = $this->getTotalWorkdaysAllTime();
		
		$total_needed_minutes = $total_days * 8 * 60;

		$overtime_minutes = $total_work - $total_needed_minutes;
		
		$overtime_hours = intval($overtime_minutes/60);
		$overtime_minute_rest = $overtime_minutes % 60;

		if( $overtime_minute_rest < 0 ) {
			$overtime_minute_rest = $overtime_minute_rest * (-1);
		}

		return sprintf('%02d:%02d', $overtime_hours, $overtime_minute_rest);
	}

	public function getTotalWorkingHoursRaw() {
		$total = new DateInterval('P0D');

		foreach ($this->hours as $hours)
		{
			$total = $this->addTwoIntervals($total, $hours);
		}

		$hours = $total->days * 24 + $total->h;
		$minutes = $total->i;
		
		return array($hours, $minutes);	
	}

	public function getTotalWorkingHours()
	{
		$hours_and_minutes = $this->getTotalWorkingHoursRaw();

		$hours = $hours_and_minutes[0];
		$minutes = $hours_and_minutes[1];

		return sprintf('%02d:%02d', $hours, $minutes);
	}

	public function hasWorkingHours(Day $day)
	{
		return isset($this->hours[$day->format('Y-m-d')]);
	}

	public function hasNoWorkingHours(Day $day)
	{
		return ! $this->hasWorkingHours($day);
	}

	public function getHoliday(Day $day) {
		foreach( $this->holidays as $holiday ) {
			if( array_key_exists("DTSTART;VALUE=DATE", $holiday) ) {
				if( $holiday["DTSTART;VALUE=DATE"] == $day->getYear().$day->getMonth().$day->getDay() ) {
					return  $holiday["SUMMARY"];
				}
			}
		}
		
		return null;
	}

	/*==========  Include Working Hours  ==========*/

	protected function includeWorkingHours()
	{
		$this->calculateWorkingHours($this->getLogs());
	}

	protected function dateIsInCalendarPeriod(DateTime $date)
	{
		return ($date >= $this->period->start) and ($date <= $this->period->end);
	}

	protected function calculateWorkingHours($logs)
	{
		while ( $logs->count() >= 2 )
		{
			$logA = $logs->first();
			$logB = $logs->next();
			$logs->removeElement($logA);
			$logs->removeElement($logB);

			$date = $logA->getTime()->format('Y-m-d');

			$hoursSoFar = isset($this->hours[$date]) ? $this->hours[$date] : new DateInterval('P0D');

			$moreHours = $this->getWorkDuration($logA, $logB);

			$this->hours[$date] = $this->addTwoIntervals($hoursSoFar, $moreHours);
		}
	}

	protected function addTwoIntervals(DateInterval $interval1, DateInterval $interval2)
	{
		$zero = new DateTime('00:00');
		$total = clone $zero;
		$total->add($interval1);
		$total->add($interval2);
		return $zero->diff($total);
	}

	protected function getWorkDuration(Log $logA, Log $logB)
	{
		return $logA->getTime()->diff($logB->getTime());
	}

	/*=======================================
	=            Additional Data            =
	=======================================*/

	public function getStatus(Day $day)
	{
		if( ! isset($this->statuses[$day->format('Y-m-d')]) )
		{
			return null;
		}

		return $this->statuses[$day->format('Y-m-d')];
	}

	public function getNote(Day $day)
	{
		if( ! isset($this->notes[$day->format('Y-m-d')]) )
		{
			return null;
		}

		return $this->notes[$day->format('Y-m-d')];
	}
	
	protected function includeAdditionalData()
	{
		$notes = $this->employee->getNotes();

		while ( ! $notes->isEmpty())
		{
			$note = $notes->first();
			$notes->removeElement($note);

			$date = $note->getDate()->format('Y-m-d');

			$this->statuses[$date] = $note->getStatus();
			$this->notes[$date] = $note->getBody();
		}
	}

	public function hasIrregularLogs(Day $day) {
		$logs = $this->getLogs();
		
		$dateString = $day->getYear().$day->getMonth().$day->getDay();
		
		$counter = 0;
		foreach( $logs as $log ) {
			$logDate = $log->getTime()->format('Ymd');
			if( $dateString == $logDate ) {
				$counter++;
			}
		}
		
		$isIrregular = $counter % 2;

		return $isIrregular;
	}

	/*===============================
	=            Routing            =
	===============================*/
	
	protected function defineRouteParameters(DateTime $date)
	{
		$employee = $this->employee->getId();
		$year = $date->format('Y');
		$month = $this->view != 'year' ? $date->format('m') : null;
		$day = $this->view == 'day' ? $date->format('d') : null;

		return compact('employee', 'year', 'month', 'day');
	}

	/*==============================
	=            Helper            =
	==============================*/
	
	public function getLogs()
	{
		return $this->employee->getLogs()->filter(function($log) {
			return $this->dateIsInCalendarPeriod($log->getTime());
		});
	}
	
}