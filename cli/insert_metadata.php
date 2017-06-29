<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This script creates config.php file and prepares database.
 *
 * This script is not intended for beginners!
 * Potential problems:
 * - su to apache account or sudo before execution
 * - not compatible with Windows platform
 *
 * @package    local_massinsertmetadata
 * @category local
 * @subpackage cli
 * @author Moodle Code Sprint Moodle Moot 2017 France (Lyon)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

global $CLI_VMOODLE_PRECHECK;

define('CLI_SCRIPT', true);
$CLI_VMOODLE_PRECHECK = true;

// Force first config to be minimal.

require(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');

require_once($CFG->dirroot.'/lib/clilib.php');

// Cli only functions.

// Now get cli options.
list($options, $unrecognized) = cli_get_params(
    array('host'              => false,
          'file'              => false,
          'encoding'          => false,
          'separator'         => false,
          'help'              => false),
    array('h' => 'help',
          'f' => 'file',
          'e' => 'encoding',
          's' => 'separator',
          'H' => 'host')
);

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options['help']) {
    $help = "
Command line metadata file importer (VMoodle compatible).

Options:
-H, --host                Switches to this host virtual configuration before processing
-f, --file                Physical CSV file to import.
-e, --encoding            Physical CSV file encoding.
-s, --separator           Physical CSV file field separator.
-h, --help            Print out this help

Example:
\$sudo -u www-data /usr/bin/php local/massinsertmetadata/cli/insert_metadata.php --file=\"datafile.csv\" [--encoding=UTF-8] [--separator=\";\"] [--host=http://my.virtual.moodle.org]
"; // TODO: localize - to be translated later when everything is finished.

    echo $help;
    die;
}

if (!empty($options['host'])) {
    // Arms the vmoodle switching.
    echo('Arming for '.$options['host']."\n"); // Mtrace not yet available.
    define('CLI_VMOODLE_OVERRIDE', $options['host']);
}

// Replay full config whenever. If vmoodle switch is armed, will switch now config.

require(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php'); // Global moodle config file.
echo 'Config check : playing for '.$CFG->wwwroot."\n";

// 