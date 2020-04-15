# Integer Range Sets

This small library allows manipulating integer sets.

# Requirements

- PHP 7.3+

# Installation

```shell script
composer require remorhaz/int-rangesets
```

# Usage
## Introduction 
_Set_ is represented in a form of collection of continuous _ranges_; each range is represented by a pair of integers denoting it's first and last values.

## Range 
Range cannot be empty, but can contain only one integer; in that case it's first and last values are the same.

```php
<?php

use Remorhaz\IntRangeSets\Range;

// Contains values: 5, 6, 7, 8
$range1 = new Range(5, 8);

// Contains value 12
$range2 = new Range(12);
``` 
Ranges are immutable. Any operation on a range creates a new instance, leaving the original one intact.
```php
<?php

use Remorhaz\IntRangeSets\Range;

// Contains values: 5, 6, 7, 8
$range1 = new Range(5, 8);

// New range has first value replaced and contains values: 7, 8
$range2 = $range1->withStart(7);
```

## Range sets 
All ranges in a set are normalized: they follow each other in ascending order and are separated by non-empty gaps, so none of them follow immediately after previous one or overlap.

Range sets are also immutable. Any operation on a set creates a new instance, leaving the original one intact.

```php
<?php

use Remorhaz\IntRangeSets\Range;
use Remorhaz\IntRangeSets\RangeSet;

// Empty range set
$rangeSet1 = new RangeSet();

// Two overlapping ranges [3..5] and [4..10] are added to empty set, creating a new set with single range [3..10]
$rangeSet2 = $rangeSet1->withRanges(new Range(3, 5), new Range(4, 10));
``` 

Merging of ranges requires resources, so there's a fast, but unsafe way to initialize set with ranges. In this case constructing code must take full responsibility for normalization of ranges.

```php
<?php

use Remorhaz\IntRangeSets\Range;
use Remorhaz\IntRangeSets\RangeSet;

// Creates range with two ranges: [2..5] and [7..8].
$rangeSet1 = RangeSet::createUnsafe(new Range(2, 5), new Range(7, 8));

```

*WARNING:* Operations on non-normalized range sets will return incorrect results! Use `addRanges()` method with arbitrary range lists.