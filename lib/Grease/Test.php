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

/* The Test class is the base class for all test cases. */
trait Test
{
	protected $should;

	public function __construct ()
	{
		$actual_class_name = get_class($this);

		$this->should = new Should($actual_class_name);

		$this->register();
	}

	abstract public function tests () { }
}

?>
