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

		$this->aggregate($this->traverse($directory));

		$this->display();
	}

	public function display ()
	{
		$this->presenter = new Tap($this->aggregator);	
		$this->presenter->output();
	}

	public function traverse ($directory, $namespaces = [])
	{
		if (!is_an_array($directory)) {
			$path = \Sauce\Path::info($directory);

			$path->absolute = $directory;
			$path->dirname  = basename(dirname($path->absolute));
			$path->is_dir   = is_dir($path->absolute);

			$directory = $path;
		}

		$namespaces = V($namespaces);
		$namespaces->push($directory->filename);

		$entries = \Sauce\Path::ls($directory->absolute);

		// Add extensive path info to the listing
		$entries = $entries->map(function ($entry) use ($directory) {
			$path = \Sauce\Path::info($entry);
			$path->absolute = \Sauce\Path::join($directory->absolute, $entry);
			$path->dirname  = basename(dirname($path->absolute));
			$path->is_dir   = is_dir($path->absolute);

			return $path;
		});

		$entries = $entries->map(function ($entry) use ($namespaces) {
			if ($entry->is_dir) {
				return $entry;
			}

			$entry->class_name = "\\" . $namespaces->join("\\") . "\\" . $entry->filename;
			$entry->is_class = class_exists($entry->class_name);

			return $entry;
		});

		foreach ($entries->to_array() as $entry) {
			if ($entry->is_dir) {
				// Recurse!
				$entries->push($this->traverse($entry, $namespaces));

				continue;
			}
		}

		return $entries;
	}

	protected function aggregate ($entries = [])
	{
		$entries = V($entries);

		foreach ($entries->to_array() as $entry) {
			if (!$entry->is_class) continue;

			$name = $entry->class_name;
			$test = new $name();

			$this->aggregator->push($test);
		}
	}
}

?>
