<?php
/* PHPSyncer v0.1.2 <github.com/fiedlr/PHPSyncer> | (c) 2015 Adam Fiedler | @license <opensource.org/licenses/MIT> */

class PHPSyncer
{
	// Settable attributes
	private $pattern, $source, $target;

	// Inner data
	private $map;

	public function __construct($pattern, RecursiveDirectoryIterator $source, RecursiveDirectoryIterator $target)
	{
		if (!is_string($pattern) || empty($pattern)) 
		{
			throw new Exception('$pattern must be of type string and not null.', 1);
		}

		$this->pattern = $pattern;
		$this->source = $source;
		$this->target = $target;
	}

	private function loop(RecursiveDirectoryIterator $src)
	{
		$data = array();

		$iterator = new RecursiveIteratorIterator($src);

		// Loop through files
		foreach ($iterator as $filePath => $file)
		{
			if (!in_array($file->getFilename(), array(".", "..", ".DS_Store")))
			{
				$contents = file_get_contents($filePath);

				// Save each match to the array with its filename
				preg_match_all($this->pattern, $contents, $matches, PREG_OFFSET_CAPTURE);

				if (!empty($matches[0]))
				{
					// Loop through matches
					foreach ($matches[0] as $i => $match)
					{
						$piece = substr($contents, $match[1]);

						list($n, $startpos, $endpos) = 0;

						// Loop through its characters
						foreach (str_split($piece) as $y => $char)
						{
							if ($char == "{")
							{
								if (!$startpos)
								$startpos = $y + 1;
								$n++;
							}
							else if ($char == "}")
							{
								if ($n == 1)
								{
									$endpos = $y; 
									break;
								} 
								else
								$n--;
							}
						}

						if ($endpos > $startpos)
						{
							$piece = substr($piece, $startpos, $endpos - $startpos);

							$data[$src->getSubPathname()]["patch({$i})"] = $piece; 
						}
					}
				}
			}
		}

		return $data;
	}

	public function extract($mapFile = null)
	{
		if ($mapFile !== null && !is_string($mapFile)) 
		{
			throw new Exception('$mapFile must be of type string.', 1);
		}

		// Extract data from a preprocessed map or from the files themselves
		$this->map = (empty($mapFile) ? array_merge_recursive($this->loop($this->target), $this->loop($this->source)) : $this->decodeMap($mapFile));

		return $this;
	}

	public function getMatches()
	{
		if (!isset($this->map))
		{
			throw new Exception('No extraction has run yet.', 1);
		}

		return $this->map;
	}

	private function saveTo($file, $content)
	{
		if (!is_string($file) || !is_file($file)) 
		{
			throw new Exception("{$file} is not a file or does not exist.", 1);
		}

		$fileHandler = fopen($file, "w");

		$status = fwrite($fileHandler, $content);

		fclose($fileHandler);

		return $status;
	}

	public function saveMap($mapFile)
	{
		return $this->saveTo($mapFile, json_encode($this->map));
	}

	public function decodeMap($mapFile)
	{
		return json_decode(file_get_contents($mapFile), true);
	}

	public function apply()
	{
		$result = array();

		// Loop through what to replace and how
		foreach ($this->map as $fileName => $file)
		{
			// Get contents 
			$originalString = file_get_contents($this->target->key().$fileName);
			$replacedString = $originalString;

			// Replace each match in the file
			foreach ($file as $filePath => $patch)
			{
				$replacedString = str_replace($patch[0], $patch[1], $replacedString);
			}

			// Save results
			$result[$fileName] = ($replacedString != $originalString ? $this->saveTo($this->target->key().$fileName, $replacedString) : -1);
		}

		return $result;
	}
}