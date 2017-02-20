<?php

require __DIR__ . "/../src/_autoload.php";

function main() {

	// Create the option set to process the command line.
	$cmdline = new Recharg\CommandLine();

	$progname = $GLOBALS['argv'][0];

	$cmdline->setUsage(<<<ETX
$progname [OPTION]... [-T] SOURCE DEST
   or: $progname [OPTION]... SOURCE... DIRECTORY
   or: $progname [OPTION]... -t DIRECTORY SOURCE...
ETX
    );

	// Add description text, displayed above the option list.
	$cmdline
		->setDescription('Rename SOURCE to DEST, or move SOURCE(s) to DIRECTORY.');

	$opt = new Recharg\Option('help');
	$opt->setHelp('display this help and exit');
	$cmdline->addOption($opt);

	$opt = new Recharg\Option('version');
	$opt->setHelp('output version information and exit');
	$cmdline->addOption($opt);

	// Note: if no matches are provided, Recharg will assume "--name" for a
	// an option named "name".
	$opt = new Recharg\Option('backup');
	$opt
		->setDefault('existing')
		->setAcceptsArguments(TRUE)
		->setPlaceholder('CONTROL')
		->setHelp('make a backup of each existing destination file');
	$cmdline->addOption($opt);

	// If a name consisting of a single character is passed, the option is
	// actually created with name "opt_X" (where "X" is the character
	// passed), and a single option "X"). In other words:
	//
	//   new Option('a')
	//
	// is equivalent to
	//
	//   new Option('opt_a', ['a']);
	$opt = new Recharg\Option('b');
	$opt
		->setHelp('like --backup but does not accept an argument');
	$cmdline->addOption($opt);

	$opt = new Recharg\Option('force', ['f', 'force']);
	$opt
		->setHelp('do not prompt before overwriting');
	$cmdline->addOption($opt);

	$opt = new Recharg\Option('interactive', ['i', 'interactive']);
	$opt
		->setHelp('prompt before overwriting');
	$cmdline->addOption($opt);

	$opt = new Recharg\Option('no-clobber', ['n', 'no-clobber']);
	$opt
		->setHelp('do not overwrite an existing file');
	$cmdline->addOption($opt);

	$opt = new Recharg\Option('strip-trailing-slashes');
	$opt
		->setHelp('remove any trailing slashes from each SOURCE argument');
	$cmdline->addOption($opt);

	$opt = new Recharg\Option('suffix');
	$opt
		->setAcceptsArguments(TRUE)
		->setHelp('override the usual backup suffix');
	$cmdline->addOption($opt);

	$opt = new Recharg\Option('target-directory', ['t', 'target-directory']);
	$opt
		->setAcceptsArguments(TRUE)
		->setPlaceholder('DIRECTORY')
		->setHelp('move all SOURCE arguments into DIRECTORY');
	$cmdline->addOption($opt);

	$opt = new Recharg\Option('no-target-directory',
	                          ['T', 'no-target-directory']);
	$opt
		->setHelp('treat DEST as a normal file');
	$cmdline->addOption($opt);

	$opt = new Recharg\Option('update', ['u', 'update']);
	$opt
		->setHelp('move only when the SOURCE file is newer than the destination file or when the destination file is missing');
	$cmdline->addOption($opt);

	$opt = new Recharg\Option('verbose', ['v', 'verbose']);
	$opt
		->setHelp('explain what is being done');
	$cmdline->addOption($opt);

	$opt = new Recharg\Option('context', ['Z', 'context']);
	$opt
		->setHelp('set SELinux security context of destination file to default type');
	$cmdline->addOption($opt);


	$cmdline->setFooter(<<<ETX
If you specify more than one of -i, -f, -n, only the final one takes effect.

The backup suffix is '~', unless set with --suffix. The version control method may be selected via the --backup option. CONTROL can be:

  none, off       never make backups (even if --backup is given)
  numbered, t     make numbered backups
  existing, nil   numbered if numbered backups exist, simple otherwise
  simple, never   always make simple backups

This is a example command line processor using PHP Recharg.  Visit
http://github.org/flaviovs/recharg for more information.
ETX
    );


	//
	// Parse the command line.
	//
	$p = new Recharg\Parser($cmdline);

	try {
		$res = $p->parse();
	} catch (Recharg\ParserException $ex) {
		// Get current program name. This is set to the current program
		// automatically when we created the CommandLine without specifying a name
		// for it.
		$commands = $ex->getCommands();
		$progname = array_shift($commands);

		// Get the current command sequence.
		$commands = implode(' ', $commands);

		$prefix = $progname;
		if ($commands) {
			$prefix .= " $commands";
		}

		// FIXME - move this command building logic to the exception

		$hint = "$progname --help";
		if ($commands) {
			$hint .= " $commands";
		}

		// Display the error message.
		print "$prefix: " . $ex->getMessage() . "\n";
		print "Try \"$hint\"\n";

		exit(1);
	}

	// Display help if "--help" was passed.
	if ($res['help']) {
		print $cmdline->getHelp($res->getCommands()) . "\n";
		exit(0);
	}

	// Act on parser results.
	print "* Execute command: " . implode(' ', $res->getCommands()) . "\n";
	print "* Options:\n";
	print_r($res->getArguments());
	print "* Operands:\n";
	print_r($res->getOperands());
}

function exception_error_handler($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        // This error code is not included in error_reporting
        return;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
}
set_error_handler("exception_error_handler");

main();
