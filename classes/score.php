<?php

namespace mageekguy\atoum;

use mageekguy\atoum;

class score
{
	private $assertions = array();
	private $exceptions = array();
	private $errors = array();
	private $outputs = array();
	private $durations = array();
	private $memoryUsages = array();

	private static $assertionId = 0;

	public function reset()
	{
		$this->assertions = array();
		$this->exceptions = array();
		$this->errors = array();
		$this->outputs = array();
		$this->durations = array();
		$this->memoryUsages = array();

		return $this;
	}

	public function addPass($file, $line, $class, $method, $asserter)
	{
		$this->assertions[] = array(
			'id' => ++self::$assertionId,
			'class' => $class,
			'method' => $method,
			'file' => $file,
			'line' => $line,
			'asserter' => $asserter,
			'fail' => null
		);

		return self::$assertionId;
	}

	public function addFail($file, $line, $class, $method, $asserter, $reason)
	{
		$this->assertions[] = array(
			'id' => ++self::$assertionId,
			'class' => $class,
			'method' => $method,
			'file' => $file,
			'line' => $line,
			'asserter' => $asserter,
			'fail' => $reason
		);

		return self::$assertionId;
	}

	public function addException($file, $line, $class, $method, \exception $exception)
	{
		$this->exceptions[] = array(
			'class' => $class,
			'method' => $method,
			'file' => $file,
			'line' => $line,
			'value' => (string) $exception
		);

		return $this;
	}

	public function addError($file, $line, $class, $method, $type, $message)
	{
		$this->errors[] = array(
			'class' => $class,
			'method' => $method,
			'file' => $file,
			'line' => $line,
			'type' => $type,
			'message' => trim($message)
		);

		return $this;
	}

	public function addOutput($class, $method, $output)
	{
		if ($output != '')
		{
			$this->outputs[] = array(
				'class' => $class,
				'method' => $method,
				'value' => $output
			);
		}

		return $this;
	}

	public function addDuration($class, $method, $duration)
	{
		if ($duration > 0)
		{
			$this->durations[] = array(
				'class' => $class,
				'method' => $method,
				'value' => $duration
			);
		}

		return $this;
	}

	public function addMemoryUsage($class, $method, $memoryUsage)
	{
		if ($memoryUsage > 0)
		{
			$this->memoryUsages[] = array(
				'class' => $class,
				'method' => $method,
				'value' => $memoryUsage
			);
		}

		return $this;
	}

	public function merge(score $score)
	{
		$this->assertions = array_merge($this->assertions, $score->assertions);
		$this->exceptions = array_merge($this->exceptions, $score->exceptions);
		$this->errors = array_merge($this->errors, $score->errors);
		$this->outputs = array_merge($this->outputs, $score->outputs);
		$this->durations = array_merge($this->durations, $score->durations);
		$this->memoryUsages = array_merge($this->memoryUsages, $score->memoryUsages);

		return $this;
	}

	public function getOutputs()
	{
		return array_values($this->outputs);
	}

	public function getTotalDuration()
	{
		$total = 0.0;

		foreach ($this->durations as $duration)
		{
			$total += $duration['value'];
		}

		return $total;
	}

	public function getDurations()
	{
		return array_values($this->durations);
	}

	public function getTotalMemoryUsage()
	{
		$total = 0.0;

		foreach ($this->memoryUsages as $memoryUsage)
		{
			$total += $memoryUsage['value'];
		}

		return $total;
	}

	public function getMemoryUsages()
	{
		return array_values($this->memoryUsages);
	}

	public function getPassAssertions()
	{
		return self::cleanAssertions(array_filter($this->assertions, function($assertion) { return $assertion['fail'] === null; }));
	}

	public function getFailAssertions()
	{
		return self::cleanAssertions(array_filter($this->assertions, function($assertion) { return $assertion['fail'] !== null; }));
	}

	public function getErrors()
	{
		return $this->errors;
	}

	public function getExceptions()
	{
		return $this->exceptions;
	}

	public function getDurationNumber()
	{
		return sizeof($this->durations);
	}

	public function getOutputNumber()
	{
		return sizeof($this->outputs);
	}

	public function getAssertionNumber()
	{
		return sizeof($this->assertions);
	}

	public function getPassNumber()
	{
		return ($this->getAssertionNumber() - sizeof($this->getFailAssertions()));
	}

	public function getExceptionNumber()
	{
		return sizeof($this->exceptions);
	}

	public function getMemoryUsageNumber()
	{
		return sizeof($this->memoryUsages);
	}

	public function getFailNumber()
	{
		return sizeof($this->getFailAssertions());
	}

	public function getErrorNumber()
	{
		return sizeof($this->errors);
	}

	public function errorExists($message = null, $type = null)
	{
		$messageIsNull = $message === null;
		$typeIsNull = $type === null;

		foreach ($this->errors as $key => $error)
		{
			$messageMatch = $messageIsNull === true ? true : $error['message'] == $message;
			$typeMatch = $typeIsNull === true ? true : $error['type'] == $type;

			if ($messageMatch === true && $typeMatch === true)
			{
				return $key;
			}
		}

		return null;
	}

	public function deleteError($key)
	{
		if (isset($this->errors[$key]) === false)
		{
			throw new \runtimeException('Error key \'' . $key . '\' does not exist');
		}

		unset($this->errors[$key]);

		return $this;
	}

	public function failExists(atoum\asserter\exception $exception)
	{
		$id = $exception->getCode();

		return (sizeof(array_filter($this->assertions, function($assertion) use ($id) { return $assertion['fail'] !== null && $assertion['id'] === $id; })) > 0);
	}

	private static function cleanAssertions(array $assertions)
	{
		return array_map(function ($assertion) { unset($assertion['id']); return $assertion; }, array_values($assertions));
	}
}

?>