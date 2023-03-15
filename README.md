# sy/template

A simple template engine for PHP

## Installation

Install the latest version with

```bash
composer require sy/template
```

## Basic Usage

### Variables

```php
<?php

use Sy\Template\Template;

// Create a template with variable slot
$template = new Template();
$template->setFile('mytemplate.tpl');

// Fill the variable slot
$template->setVar('NAME', 'World');

// Output render
echo $template->getRender();
```

The template file *mytemplate.tpl* content:

```
Hello {NAME}
```

The output result:

```
Hello World
```

### Blocks

```php
<?php

use Sy\Template\Template;

// Create a template with a block
$template = new Template();
$template->setFile('mytemplate.tpl');

// This variable will be overrided below
$template->setVar('NAME', 'Hello world');

// Fill the variable slot and repeat the block
foreach (['foo', 'bar', 'baz'] as $name) {
	$template->setVar('NAME', $name);
	$template->setBlock('MY_BLOCK');
}

// Output render
echo $template->getRender();
```

The template file *mytemplate.tpl* content:

```
{NAME}
<!-- BEGIN MY_BLOCK -->
Hello {NAME}
<!-- END MY_BLOCK -->
```

The output result:

```
baz
Hello foo
Hello bar
Hello baz
```

### Isolated variables for a block

```php
<?php

use Sy\Template\Template;

// Create a template with a block
$template = new Template();
$template->setFile('mytemplate.tpl');

// This variable will not be overrided below because the block use isolated variables
$template->setVar('NAME', 'Hello world');

// Fill the variable slot and repeat the block
foreach (['foo', 'bar', 'baz'] as $name) {
	// Use isolated variables for this block
	$template->setBlock('MY_BLOCK', ['NAME' => $name]);
}

// Output render
echo $template->getRender();
```

The template file *mytemplate.tpl* content:

```
{NAME}
<!-- BEGIN MY_BLOCK -->
Hello {NAME}
<!-- END MY_BLOCK -->
```

The output result:

```
Hello world
Hello foo
Hello bar
Hello baz
```

## Advanced Usage

### ELSE block

You can set a default ouput for a block when this one is not set using the ELSE block:

```php
<?php

use Sy\Template\Template;

// Create a template with a block
$template = new Template();
$template->setFile('mytemplate.tpl');

// No setBlock here

// Output render
echo $template->getRender();
```

The template file *mytemplate.tpl* content:

```
<!-- BEGIN MY_BLOCK -->
Hello {NAME}
<!-- ELSE MY_BLOCK -->
Block not set
<!-- END MY_BLOCK -->
```

The output result:

```
Block not set
```

### Slot advanced syntax

#### 1. Simple or double quotes around slot name

You can use double or simple quotes around the slot name:

```
{"Hello"}, {'my name is'}...
```

If these slots are not set, the output will be the string inside of the simple or double quotes:

```
Hello, my name is...
```

#### 2. Slot default value

You can set a default value for a slot when this one is not set using a slash caracter after the slot name:

```
Hello {NAME/John Doe}
```

If the slot ```NAME``` is not set, the output will be:

```
Hello John Doe
```

### Slot default behavior

#### Version 1

The slot default behavior changed on the version 2. Before, on the version 1, an unset slot is cleared on output:

```
Hello {NAME}
```

If the slot ```NAME``` is not set, the output will be:
```
Hello
```

#### Version 2

Starting at version 2:

```
Hello {NAME}
```

If the slot ```NAME``` is not set, the output will be:
```
Hello {NAME}
```

#### How to clear the slot automatically

You can use the "Slot default value" feature to achieve that.
The slot will be replaced by the string after the slash, so if you put an emtpy string after the slash, the slot will be replaced by an empty string:

```
Hello {NAME/}
```

If the slot ```NAME``` is not set, the output will be:
```
Hello
```