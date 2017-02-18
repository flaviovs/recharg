<?php

require __DIR__ . "/../src/_autoload.php";

function main() {

	// Create the option set to process the command line.
	$cmdline = new Recharg\CommandLine();

	// Let's configure it.
	$cmdline
		->setDescription('Example of command parsing using Recharg.')
		->setFooter('Visit http://github.org/flaviovs/recharg for more information.');

	// Add the '--help' option.
	$opt = new Recharg\Option('help', ['help', 'h']);
	$opt->setHelp('Display help. Use "--help COMMAND" to get help for COMMAND');
	$cmdline->addOption($opt);


	//
	// Add the 'clone' command.
	//
	$clone_cmdline = new Recharg\CommandLine();
	$clone_cmdline
		->setOperands('REPOSITORY [DIRECTORY]')
		->setSummary('Clone a repository into a new directory')
		->setDescription('Clones the repository at REPOSITORY into a newly created directory, creates remote-tracking branches for each branch in the cloned repository, and creates and checks out an initial branch that is forked from the cloned repository’s currently active branch.');

	// clone --template
	$opt = new Recharg\Option('template', ['template'], TRUE);
	$opt->setHelp("Specify the directory from which templates will be used.");
	$clone_cmdline->addOption($opt);

	// clone --local
	$opt = new Recharg\Option('local', ['l', 'local']);
	$opt->setHelp('Clone from a local repository');
	$clone_cmdline->addOption($opt);

	// clone --bare
	$opt = new Recharg\Option('bare');
	$opt->setHelp('Make a bare repository');
	$clone_cmdline->addOption($opt);

	// Add the command to our main option set.
	$cmdline->addCommand('clone', $clone_cmdline);


	//
	// Add the 'remote' command.
	//
	$remote_cmdline = new Recharg\CommandLine();
	$remote_cmdline
		->setSummary("Manage set of tracked repositories")
		->setDescription('Manage the set of repositories ("remotes") whose branches you track.');

	// Add "remote add" command.
	$remote_add_cmdline = new Recharg\CommandLine();
	$remote_add_cmdline
		->setSummary("Adds a remote")
		->setOperands("NAME URL")
		->setDescription("Adds a remote named NAME for the repository at URL. The command 'fetch NAME' can then be used to create and update remote-tracking branches NAME/BRANCH.");

	// remote add --branch
	$opt = new Recharg\Option('branch', ['t'], TRUE);
	$opt->setHelp('Specify that a refspec to track only BRANCH should be created
');
	$remote_add_cmdline->addOption($opt);

	// Add "remote show" command.
	$remote_show_cmdline = new Recharg\CommandLine();
	$remote_show_cmdline
		->setSummary("Shows a remote")
		->setOperands("NAME")
		->setDescription("Gives some information about the remote NAME.");

	// remote show -n
	$opt = new Recharg\Option('dryrun', ['n']);
	$opt->setHelp('Do not query remote heads (faster)');
	$remote_show_cmdline->addOption($opt);


	// Add 'add' & 'show' commands to 'remote'
	$remote_cmdline->addCommand('show', $remote_show_cmdline);
	$remote_cmdline->addCommand('add', $remote_add_cmdline);

	// Now add 'remote' to main option set
	$cmdline->addCommand('remote', $remote_cmdline);

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
