<?php

namespace Arsol_Projects_For_Woo\Core;

use Exception;
use Arsol_Projects_For_Woo\Custom_Post_Types\ProjectRequest\Request_Conversion;
use Arsol_Projects_For_Woo\Custom_Post_Types\ProjectProposal\Proposal_Conversion;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Central Conversion Handler
 * Wraps the individual converter classes so other components can trigger
 * conversions without knowing implementation details.
 */
class Conversion_Handler {

    /**
     * Convert a Request CPT to a Proposal CPT.
     * All validation, error handling, and notices are handled inside the
     * Request_Conversion class.
     *
     * @return void
     */
    public static function convert_request_to_proposal() {
        try {
            $converter = new Request_Conversion();
            $converter->convert_request_to_proposal();
        } catch ( Exception $e ) {
            wp_die( $e->getMessage() );
        }
    }

    /**
     * Convert a Proposal CPT to a Project CPT.
     *
     * @param int  $proposal_id    Optional proposal ID.
     * @param bool $is_internal    Flag for internal (front-end) calls.
     *
     * @return void
     */
    public static function convert_proposal_to_project( $proposal_id = 0, $is_internal = false ) {
        try {
            $converter = new Proposal_Conversion();
            $converter->convert_proposal_to_project( $proposal_id, $is_internal );
        } catch ( Exception $e ) {
            if ( $is_internal && function_exists( 'wc_add_notice' ) ) {
                wc_add_notice( $e->getMessage(), 'error' );
            } else {
                wp_die( $e->getMessage() );
            }
        }
    }
}
