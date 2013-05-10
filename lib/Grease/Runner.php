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

class Runner
{
	protected $directory;
	protected $aggregator;
	protected $presenter;

	public function __construct ($directory)
	{
		$this->aggregator = new Aggregator();

		$entries = \Sauce\Path::ls($directory);

		// Add extensive path info to the listing
		$entries = $entries->map(function ($entry) use ($directory) {
			$path = \Sauce\Path::info($entry);
			$path->absolute = \Sauce\Path::join($directory, $entry);
			$path->dirname  = basename(dirname($path->absolute));
			$path->is_dir   = is_dir($entry);

			return $path;
		});

		// Select only non-directory entries
		// TODO: Change this to recursively gather test cases (and support sub-namespaces)
		$entries = $entries->select(function ($entry) { return !$entry->is_dir; });

		// Add the class_name (\Namespace\ClassName) to the path info
		$entries = $entries->map(function ($entry) {
			$entry->class_name = "\\" . $entry->dirname . "\\" . $entry->filename;
			$entry->is_class = class_exists($entry->class_name);

			return $entry;
		});

		foreach ($entries->to_array() as $entry) {
			if (!$entry->is_class) continue;

			$name = $entry->class_name;
			$test = new $name();

			$this->aggregator->push($test);
		}

		$this->presenter = new Tap($this->aggregator);	

		$this->presenter->output();
	}
}

?>
