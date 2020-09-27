# sy/template 

A simple template engine for PHP

## Installation

Install the latest version with

```bash
$ composer require sy/template
```

## Basic Usage

### Variables

```php
<?php

use Sy\Template\Template;

// create a template with variable slot
$template = new Template();
$template->setFile('mytemplate.tpl');

// fill the variable slot
$template->setVar('NAME', 'World');

// output render
echo $template->getRender();
```

The template file *mytemplate.tpl* content

```
Hello {NAME}
```

The output result

```
Hello World
```

### Blocks

```php
<?php

use Sy\Template\Template;

// create a template with a block
$template = new Template();
$template->setFile('mytemplate.tpl');

// fill the variable slot and repeat the block
foreach (['foo', 'bar', 'baz'] as $name) {
	$template->setVar('NAME', $name);
	$template->setBlock('MY_BLOCK');
}

// output render
echo $template->getRender();
```

The template file *mytemplate.tpl* content

```
<!-- BEGIN MY_BLOCK -->
Hello {NAME}
<!-- END MY_BLOCK -->
```

The output result

```
Hello foo
Hello bar
Hello baz
```