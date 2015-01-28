<?php namespace TimetrackerBundle\Services\Calendar;

use DateTime, DateInterval, DatePeriod;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Router;
use Doctrine\ORM\EntityManager;

class Calendar {

	/**
	 * Defines the intervals for navigating
	 */
	protected $intervals = [
		'year'	=> 'P1Y',
		'month' => 'P1M',
		'day'	=> 'P1D'
	];

	/**
	 * Defines the anchor tags for navigating
	 */
	protected $anchorTags = [
		'year'	=> ['prev' => 'Vorheriges Jahr', 'next' => 'Nächstes Jahr'],
		'month'	=> ['prev' => 'Vorheriger Monat', 'next' => 'Nächster Monat'],
		'day'	=> ['prev' => 'Vorheriger Tag', 'next' => 'Nächster Tag']
	];


	protected $titleFormats = [
		'year'	=> 'Y',
		'month' => 'm/Y',
		'day'	=> 'd.m.Y'
	];
	
	protected $view;

	protected $year;

	protected $month;

	protected $day;

	protected $period;

	protected $request;

	protected $router;

	protected $em;

	public function __construct(RequestStack $requestStack, Router $router, EntityManager $em)
	{
		$this->request = $requestStack->getCurrentRequest();
		$this->year = $this->request->get('year') ?: date('Y');
		$this->month = $this->request->get('month');
		$this->day = $this->request->get('day');
		$this->router = $router;
		$this->em = $em;

		$this->build();
	}

	public function build()
	{
		$this->defineView();
		$this->definePeriod();
	}

	/*===================================
	=            Define View            =
	===================================*/
	
	protected function defineView()
	{
		if ( ! isset($this->month) )
		{
			$this->view = 'year';
		}
		else if ( ! isset($this->day) )
		{
			$this->view = 'month';
		}
		else
		{
			$this->view = 'day';
		}
	}
	
	/*==============================================
	=            Define Calendar Period            =
	==============================================*/

	public function getDays()
	{
		$days = [];

		foreach ($this->period as $dateTimeObj)
		{
			$days[] = new Day($dateTimeObj->format('Y-m-d H:i:s'));
		}

		return $days;
	}

	public function getPeriod()
	{
		return $this->period;
	}

	protected function definePeriod()
	{
		$begin = $this->getFirstDay();
		$end = $this->getLastDay();
		$interval = new DateInterval('P1D');

		$this->period = new DatePeriod($begin, $interval ,$end);
	}
	
	protected function getFirstDay()
	{
        $month = $this->month ?: '1' ;
        $day = $this->day ?: '1' ;

        $date = sprintf('%s-%02s-%02s', $this->year, $month, $day);

		return new Day($date . '00:00:00');
	}

	protected function getLastDay()
	{
        $month = $this->month ?: '12';
        $day = $this->day ?: $this->getNumberOfDaysInMonth();

        $date = sprintf('%s-%02s-%02s', $this->year, $month, $day);

		return new Day($date . ' 23:59:59');
	}

	protected function getNumberOfDaysInMonth()
	{
		if ( ! isset($this->month)) return 31;

		return cal_days_in_month(CAL_GREGORIAN, $this->month, $this->year);
	}

	/*==================================
	=            Navigation            =
	==================================*/

	/*==========  URLs  ==========*/

	public function getPrevPageUrl()
	{
		return $this->getPageUrl('prev');
	}

	public function getNextPageUrl()
	{
		return $this->getPageUrl('next');
	}

	protected function getPageUrl($direction = 'prev')
	{
		$direction = ($direction == 'prev') ? 'sub' : 'add';
		
		$date = $this->getDatePointer();
		$date->{$direction}($this->getInterval());

		return $this->router->generate($this->request->get('_route'), $this->defineRouteParameters($date));
	}

	protected function defineRouteParameters(DateTime $date)
	{
		$year = $date->format('Y');
		$month = $this->view != 'year' ? $date->format('m') : null;
		$day = $this->view == 'day' ? $date->format('d') : null;

		return compact('year', 'month', 'day');
	}

	/*==========  Anchor Tags  ==========*/

	public function getPrevAnchorTag()
	{
		return $this->getAnchorTag('prev');
	}

	public function getNextAnchorTag()
	{
		return $this->getAnchorTag('next');
	}

	protected function getAnchorTag($direction)
	{
		return $this->anchorTags[$this->view][$direction];
	}

	/*==============================
	=            Header            =
	==============================*/
	
	public function getTitle()
	{
		$date = $this->getDatePointer();

		return $date->format($this->titleFormats[$this->view]);
	}

	/*===============================
	=            Helpers            =
	===============================*/
	
	public function getView()
	{
		return $this->view;
	}

	public function getCurrentDate()
	{
		return $this->getDatePointer();
	}

	protected function getDatePointer()
	{
		$month = $this->month ?: '1';
        $day = $this->day ?: '1';

		return new Day(sprintf('%s-%02s-%02s', $this->year, $month, $day));
	}

	protected function getInterval()
	{
		return new DateInterval($this->intervals[$this->view]);
	}
}