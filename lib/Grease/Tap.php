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

class Tap implements Presenter
{
	protected $tests = [];

	public function __construct ($tests = [])
	{
		if ($tests instanceof \Grease\Should) {
			$this->tests = V();
			$this->tests->push($tests);

			return;
		}

		if ($tests instanceof \Grease\Aggregator) {
			$this->tests = V($tests);

			return;
		}

		$class = get_class($tests);

		throw new \InvalidArgumentException("Expected instance of \Grease\Should or \Grease\Aggregator. Got instance of {$class}");
	}

	public function output ()
	{
		foreach ($this->tests->to_array() as $should) {
			printf("%d..%d\n", 1, $should->results()->count());
			printf("# %s:\n", $should->name());

			foreach ($should->results()->to_array() as $i => $result) {
				$success = $result->success ? \Sauce\CliColor::green('ok') : \Sauce\CliColor::red('not ok');

				printf("%s %d - %s\n", $success, ($i + 1), $result->name, $success);
			}
		}
	}
}

?>
