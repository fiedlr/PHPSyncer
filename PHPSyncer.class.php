<?php
/* PHPSyncer v0.1.1 <github.com/fiedlr/PHPSyncer> | (c) 2015 Adam Fiedler | @license <opensource.org/licenses/MIT> */

class PHPSyncer
{
	// Settable attributes
	private $pattern, $source, $target;

	// Inner data
	private $map;

	public function __construct($pattern, RecursiveDirectoryIterator $source, RecursiveDirectoryIterator $target)
	{
		if (!is_string($pattern) || empty($pattern)) throw new Exception("Pattern must be of type string and not null.", 1);
		$this->pattern = $pattern;
		$this->source = $source;
		$this->target = $target;
	}

	private function loop(RecursiveDirectoryIterator $src)
	{
		$map = array();

		$iterator = new RecursiveIteratorIterator($src);

		foreach ($iterator as $filePath => $file)
		{
			if (!in_array($file->getFilename(), array(".", "..", ".DS_Store")))
			{
				$contents = file_get_contents($filePath);

				// Save each match to the array with its filename
				preg_match_all($this->pattern, $contents, $matches, PREG_OFFSET_CAPTURE);

				if (!empty($matches[0]))
				{
					foreach ($matches[0] as $i => $match)
					{
						$piece = substr($contents, $match[1]);

						list($n, $startpos, $endpos) = 0;

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

							$map[$src->getSubPathname()]["patch({$i})"] = $piece; 
						}
					}
				}
			}
		}

		return $map;
	}

	public function extract()
	{
		// Reset the map
		$this->map = array_merge_recursive($this->loop($this->target), $this->loop($this->source));

		return $this;
	}

	public function getMatches()
	{
		if (!isset($this->map))
		{
			throw new Exception("No extraction has run yet.", 1);
		}

		return $this->map;
	}

	private function saveTo($file, $content)
	{
		$status = false;

		if (is_file($file)) 
		{
			// Open the map file
			$fileHandler = fopen($file, "w");

			// Insert the map
			$status = fwrite($fileHandler, $content);

			// Close the file
			fclose($fileHandler);
		}

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

		// Loop through map
		foreach ($this->map as $fileName => $file)
		{
			// Get content 
			$originalString = file_get_contents($this->target->key().$fileName);
			$replacedString = $originalString;

			// Replace each match
			foreach ($file as $filePath => $patch)
			{
				$replacedString = str_replace($patch[0], $patch[1], $replacedString);
			}

			// Save result
			$result[$fileName] = ($replacedString != $originalString ? $this->saveTo($this->target->key().$fileName, $replacedString) : "No changes.");
		}

		return $result;
	}
}