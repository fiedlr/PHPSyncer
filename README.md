# PHPSyncer

Sync your legacy PHP code with its new versions. Actually, any code.

**Highly experimental** - always back up your files first.

- Although copy&pasting code is considered a bad habit, you might have instances where it occurred (especially in older projects). Or you have some sort of a code pattern throughout your project that you want to edit.

- This class looks for it in each file and automatically adjusts the code within according to your needs. It is helpful especially when you have similar files in different projects and you want to edit only their mutual parts.

> In principle: Code logic does not use DOM. There are no parents and children which you can choose with a pattern selector. But it does contain logic blocks (`ifs, loops, etc.`) and when a certain group has similar characteristics, there's a possibility you could pinpoint the blocks you want and edit its internal mechanisms.

> **Why PHP?**
You can run it on any server out there (even if it's shared). It is flexible, easy to read and unless you edit thousands of long files, you shouldn't run into performance problems.

## How it works

- Make all the necessary changes in your source folder

> Files you want to sync *must have the same filename* both in source and target folder. A PHPSyncer script must be outside of its scope. Only selectable parts of code can be synced, i.e. they need to be inside a block *matching the set pattern*.

- Once you have your source folder ready with your changes, create a PHP script you're going to run to proceed with the sync. It has to include `PHPSyncer.class.php`. The constructor takes 3 arguments:

```php
$sync = new PHPSyncer($pattern, new RecursiveDirectoryIterator($source)), new RecursiveDirectoryIterator($target));`
$sync->extract()->apply();
```

> You have to run extract() before apply() if you don't use a map!

## Documentation

### Methods

#### extract
Finds and extracts the matches between the source and the target.

#### getMatches
Returns the found matches in an array.

#### setTarget
Sets a different target.

Arguments: `RecursiveDirectoryIterator $newTarget`

#### saveMap
If you have several folders with the same patterns, this saves a map for use in `apply` without looping through all the files again.
> This can run only after using `extract`

Arguments: `$mapFile`

#### decodeMap
Opens a saved map and returns it as an array.

Arguments: `$mapFile`

#### apply
Applies the found changes from the **source** folder to the **target** folder.
> Use a map for repeated syncs

Arguments: `$map = null`

## Copyright

Released under the [MIT License](http://opensource.org/licenses/MIT).
