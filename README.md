Recharg - A Command-line Parsing Library for PHP
================================================

Recharg is a small, object-oriented, command-line parsing library for
PHP. Despite being very simple, Recharg is extremeley powerful. It support
both short and long options, with or without arguments. Options can be
mandatory or optional, and you can attach converters/validators, so that
only valid data reaches your code.

Recharg also support subcommands, allowing you to build command lines as
modularized (or complicated) as you want. Lastly, Recharg provides full
support for automatic help generation for commands and options.


How to Use
----------

1. Include the Recharg autoloader:

        require '/path/to/recharcg/src/_autoload.php';

2. Create a command line:

        $cmdline = new Recharg\CommandLine();

3. Configure the command line:

        $cmdline
            ->setOperands('FILE1 FILE2')
            ->setDescription('This program will demungifugnate FILE1 into FILE2 using the Foo-Bar second-order demungifugnation algorithm. Is is an example of command line parsing using Recharg.')
            ->setFooter('Send bug reports to bugs@example.com');

4. Add some options:

        $option = new Recharg\Option('help', ['h', 'help']);
        $option
            ->setHelp('Display this help message');
        $cmdline->addOption($option);

        $option = new Recharg\Option('tempdir', ['T', 'temp-dir']);
        $option
            ->setAcceptsArguments(TRUE)
            ->setPlaceholder('DIR')
            ->setDefault(sys_get_temp_dir())
            ->setHelp('Use DIR to store temporary files');
        $cmdline->addOption($option);

		// NB: This is the same as Option('tweak', ['tweak'])
		$option = new Recharg\Option('tweak');
		$option
			->setValue(M_PI)
			->setHelp('Tweak the algorithm by using PI as the Kappa constant in the demungifugnation algorithm');
		$cmdline->addOption($option);

        $option = new Recharg\Option('verbose', ['v', 'verbose']);
        $option
            ->setMultipleHandling(Recharg\Option::MULTIPLE_COUNT)
            ->setHelp('Enable verbose mode. Each occurence of this option will make the program issue more messages');
        $cmdline->addOption($option);

5. Create the parser object for this command line:

        $parser = new Recharg\Parser($cmdline);

6. Now parse this programs command line:

        try {
            $results = $parser->parse();
        } catch (Recharg\ParserException $ex) {
            print $ex->getMessage() . "\n";
            print "Try the --help option.\n";
            exit(1);
        }

7. Inspect the parser results to determine which options, arguments, and
   operands where present.

        if ($results['help']) {
            print $cmdline->getHelp() . "\n";
            exit(0);
        }

        // Get everything in the command line that is not an option or
        // option arguments (a.k.a. operands)
        $operands = $results->getOperands();
        if (count($operands) != 2) {
            print "Number of operands wrong. Please try --help.\n";
            exit (1);
        }

        if ($results['verbose']) {
            print "Starting the demungifugnation...\n";
        }

        if ($results['verbose'] > 1 && filesize($operands[0]) > 1000) {
            print "Input file is big. This will take a lot of time.\n";
        }

		if ($results['tweak'] && $results['verbose']) {
            print "Note: using the tweaked algorithm\n";
		}

        demungifugnate($operands[0], $operands[1], $results['tweak']);

        if ($results['verbose']) {
            print "Done\n";
        }

Now let's test the parser:

	$ php cli.php
    Number of operands wrong. Please try --help.

	$ php cli.php --help
	Usage: cli.php [-hv] [-T DIR] [--tweak] FILE1 FILE2
	This program will demungifugnate FILE1 into FILE2 using the Foo-Bar
	second-order demungifugnation algorithm. Is is an example of command line
	parsing using Recharg.

	Options and arguments:

	  -h, --help                Display this help message
      -T [DIR],
          --temp-dir[=DIR]      Use DIR to store temporary files
	  --tweak                   Tweak the algorithm by using PI as the Kappa
                                  constant in the demungifugnation algorithm
	  -v, --verbose             Enable verbose mode. Each occurence of this
                         		  option will make the program issue more
								  messages

	Send bug reports to bugs@example.com

	$ php cli.php -v /etc/passwd output
	Starting the demungifugnation...
	Done

	$ php cli.php -vv /etc/passwd output
	Starting the demungifugnation...
	Input file is big. This will take a lot of time.
	Done

See the [file `mv.php`](examples/mv.php) in the `examples/` directory for a
more complete example.


Using Sub-commands
------------------

To use sub-commands with Recharg, just create a `CommandLine` object for
your sub-command options, and add it to your main command line using the
`addCommand()` method:

    $foo_cmdline = new Recharg\CommandLine();
	$foo_cmdline->setSummary('Foobricate your files');
	$foo_cmdline->addOption(new Recharg\Option('option'));
	//(...add more options...)

    $bar_cmdline = new Recharg\CommandLine();
	$bar_cmdline->setSummary('Baronify directories');
	$bar_cmdline->addOption(new Recharg\Option('foobar'));
	//(...add more options...)

    $cmdline->addCommand('foo', $foo_cmdline);
    $cmdline->addCommand('bar', $bar_cmdline);

You can add sub-commands to sub-commands to create rich and intuitive
command lines. There's no limit on how deep your command lines can be.

See the [file `commands.php`](examples/commands.php) in the `examples/`
directory for a more complete example.


Legal
-----

Copyright (c) 2016-2017 Flavio Veloso.

Recharg is licensed under the MIT license. A copy of the License is in the
[`LICENSE` file](LICENSE).
