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

	public function getTotalWorkingHours()
	{
		$total = new DateInterval('P0D');
		foreach ($this->hours as $hours)
		{
			$total = $this->addTwoIntervals($total, $hours);
		}

		$hours = $total->days * 24 + $total->h;
		$minutes = $total->i;

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