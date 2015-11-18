# PHPSyncer

Sync your legacy PHP code with its new versions. Actually, any code.

**Highly experimental** - always back up your files first.

## How it works

- Although copy&pasting code is considered a bad habit, you might have instances where it occurred (especially in older projects). Or you have some sort of a code pattern throughout your project that you want to edit.

- This class looks for it in each file and automatically adjusts the code within according to your needs. It is helpful especially when you have similar files in different projects and you want to edit only their mutual parts.

> In principle: Code logic does not use DOM. There are no parents and children which you can choose with a pattern selector. But it does contain logic blocks (`ifs, loops, etc.`) and when a certain group has similar characteristics, there's a possibility you could pinpoint the blocks you want and edit its internal mechanisms.

> **Why PHP?**
You can run it on any server out there (even if it's shared). It is flexible, easy to read and unless you edit thousands of long files, you shouldn't run into performance problems.

## Documentation
Documentation should be available soon.

## Copyright

Released under the [MIT License](http://opensource.org/licenses/MIT).
