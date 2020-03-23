<?php

namespace CAC\GroupLibrary\CLI\Command;

use \WP_CLI;
use \WP_CLI_Command;

class DatabaseCommand extends WP_CLI_Command {
	/**
	 * Install the database table.
	 */
	// phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
	public function install( $args, $assoc_args ) {
		$schema = cac_group_library()->schema;
		$schema->install_table();
		WP_CLI::success( 'Successfully installed table!' );
	}
}
