<?php

/**
   Copyright 2012-2013 Brainsware

   Licensed under the Apache License, Version 2.0 (the "License");
   you may not use this file except in compliance with the License.
   You may obtain a copy of the License at

       http://www.apache.org/licenses/LICENSE-2.0

   Unless required by applicable law or agreed to in writing, software
   distributed under the License is distributed on an "AS IS" BASIS,
   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
   See the License for the specific language governing permissions and
   limitations under the License.

*/

namespace Grease;

/* Should is a class for general asserts and to check whether code throws or
 * does not throw exceptions of given type as expected.
 *
 * TODO: Examples
 */
class Should
{
	protected $results;
	protected $name;

	public function __construct ($name, $results = [])
	{
		$this->name = $name;
		$this->results = V($results);
	}

	public function name ()
	{
		return $this->name;
	}

	public function results ()
	{
		return $this->results;
	}

	/* #assert checks whether a given closure executes without throwing an
	 * exception and returns true.
	 *
	 * If the closure is not callable, the test does not pass.
	 *
	 * If the closure or any code called therein throws an exception, the test
	 * does not pass and the exception message and its trace are pushed
	 * onto the results vector.
	 *
	 * TODO: Examples
	 */
	public function assert ($name, $description, $fn)
	{
		$success = false;
		$message = '';
		$trace = V();

		if (!is_callable($fn)) {
			$success = false;
			$message = 'Passed test function is not callable';

		} else {
			try {
				$success = true === $fn();

			} catch (\Exception $e) {
				$success = false;
				$message = "An exception was thrown in file {$e->getFile()}:{$e->getLine()}:\n{$e->getMessage()}";
				$trace   = V($e->getTrace());
			}
		}

		$this->results->push(A([
			'name'        => $name,
			'description' => $description,
			'success'     => $success,
			'message'     => $message,
			'trace'       => $trace
		]));
	}

	/* #throw checks whether a given closure executes WITH throwing an
	 * exception of given type.
	 *
	 * If the closure is not callable, the test does not pass.
	 *
	 * If the closure or any code called therein throws an exception other
	 * than the expected, the test does not pass and the exception message
	 * and its trace are pushed onto the results vector.
	 *
	 * TODO: Examples
	 */
	public function _throw ($name, $description, $exception_type, $fn)
	{
		$success = false;
		$message = '';
		$trace   = V();

		if (!is_callable($fn)) {
			$success = false;
			$message = 'Passed test function is not callable';

		} else {
			try {
				$fn();

			} catch (\Exception $e) {
				if ($exception_type === get_class($e)) {
					$success = true;

				} else {
					$type_of_e = get_class($e);

					$message = "An exception other than the expected {$exception_type} was thrown in file {$e->getFile()}:{$e->getLine()}: \n({$type_of_e}) {$e->getMessage()}";
					$trace   = V($e->getTrace());
				}
			}
		}

		$this->results->push(A([
			'name'        => $name,
			'description' => $description,
			'success'     => $success,
			'message'     => $message,
			'trace'       => $trace
		]));
	}

	/* #not_throw checks whether a given closure executes WITHOUT throwing an
	 * exception of given type. This method does not check the closure's
	 * return value.
	 *
	 * If the closure is not callable, the test does not pass.
	 *
	 * If the closure or any code called therein throws an exception the test
	 * does not pass and the exception message and its trace are pushed onto
	 * the results vector.
	 *
	 * TODO: Examples
	 */
	public function not_throw ($name, $description, $fn)
	{
		$success = false;
		$message = '';
		$trace   = V();

		if (!is_callable($fn)) {
			$success = false;
			$message = 'Passed test function is not callable';

		} else {
			try {
				$fn();

				$success = true;

			} catch (\Exception $e) {
				$type_of_e = get_class($e);

				$message = "An exception of type {$type_of_e} was thrown in file {$e->getFile()}:{$e->getLine()}: \n({$type_of_e}) {$e->getMessage()}";
				$trace   = V($e->getTrace());
			}
		}

		$this->results->push(A([
			'name'        => $name,
			'description' => $description,
			'success'     => $success,
			'message'     => $message,
			'trace'       => $trace
		]));
	}

	/* Since we may not define a method called #throw by hand, we have to walk
	 * the extra mile and catch it via #__call. */
	public function __call ($name, $arguments)
	{
		switch ($name) {
			case 'throw': return call_user_func_array([ $this, '_throw' ], $arguments); break;

			default: trigger_error("Call to undefined method ".__CLASS__."::$name()", E_USER_ERROR); break;
		}
	}

	public function implement ($name, $description, $interface, $class_or_object)
	{
		$success = false;
		$message = '';
		$trace   = V();

		// Strip namespace prefix if present; #class_implements returns interfaces without it
		// TODO: Check how this works with interfaces in nested namespaces!
		if ($interface[0] == '\\') {
			$interface = S($interface);
			$interface->sliceF(1, $interface->length() - 1);
			$interface = $interface->to_s();
		}

		try {
			$success = V(class_implements($class_or_object))->includes($interface);

		} catch (\Exception $e) {
			$type_of_e = get_class($e);

			$success = false;
			$message = "An exception of type {$type_of_e} was thrown while checking for the interface {$interface} in file {$e->getFile()}:{$e->getLine()}:\n{$type_of_e}) {$e->getMessage()}";
			$trace = V($e->getTrace());
		}

		$this->results->push(A([
			'name'        => $name,
			'description' => $description,
			'success'     => $success,
			'message'     => $message,
			'trace'       => $trace
		]));
	}

	public function not_implement ($name, $description, $interface, $class_or_object)
	{
		$success = false;
		$message = '';
		$trace   = V();

		// Strip namespace prefix if present; #class_implements returns interfaces without it
		// TODO: Check how this works with interfaces in nested namespaces!
		if ($interface[0] == '\\') {
			$interface = S($interface);
			$interface->sliceF(1, $interface->length() - 1);
			$interface = $interface->to_s();
		}

		try {
			$success = ! (V(class_implements($class_or_object))->includes($interface));

		} catch (\Exception $e) {
			$type_of_e = get_class($e);

			$success = false;
			$message = "An exception of type {$type_of_e} was thrown while checking for the interface {$interface} in file {$e->getFile()}:{$e->getLine()}:\n{$type_of_e}) {$e->getMessage()}";
			$trace = V($e->getTrace());
		}

		$this->results->push(A([
			'name'        => $name,
			'description' => $description,
			'success'     => $success,
			'message'     => $message,
			'trace'       => $trace
		]));
	}
}

?>
