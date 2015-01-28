<?php namespace TimetrackerBundle\Services\Calendar;

class Day extends \DateTime {

	public function isWeekend()
	{
		return $this->format('N') >= 6;
	}

	public function isWeekday()
	{
		return ! $this->isWeekend();
	}

	public function getYear()
	{
		return $this->format('Y');
	}

	public function getMonth()
	{
		return $this->format('m');
	}

	public function getDay()
	{
		return $this->format('d');
	}
}