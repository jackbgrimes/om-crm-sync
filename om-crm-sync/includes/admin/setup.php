<?php

namespace OM\CRM\Admin;

add_action( 'admin_menu', __NAMESPACE__ . '\\admin_page_setup' );

function admin_page_setup() {
	add_management_page( 'CRM Tools', 'CRM', 'manage_options', 'omcrm/tools.php', __NAMESPACE__ . '\\admin_page' );
}

function admin_page() {

    $wp_nonce = wp_create_nonce( 'omcrm_admin_tools' );
    $cron_schedules = get_option( 'cron', true );
    $endpoint = admin_url( 'admin-ajax.php?action=omcrm_tools' );
	$last_run = 'n/a';
	$next_run = 'n/a';
	$last_prune = 'n/a';
	
    foreach( $cron_schedules as $timestamp => $schedule ){

        if( !isset( $schedule['omcrm_daily'] ) ){ continue; }

        $last = array_pop( $schedule[ 'omcrm_daily' ] );
        $offset = $last['interval'];
        $format = 'F j, Y g:i:s a';

        $last_run = date( $format, ( $timestamp - $offset ) );
        $next_run = date( $format, $timestamp );

    } ?>

    <h1>CRM Tools</h1>

    <form method="post" action="<?= $endpoint; ?>">
        <input type="hidden" name="wp_nonce" value="<?= $wp_nonce; ?>" />

        <h2>Sync</h2>
        <small>
            <strong>Last Sync Scheduled:</strong> <?= $last_run; ?> || <strong>Next Sync Scheduled:</strong> <?= $next_run; ?>
        </small>

        <table>
            <tr>
                <td>
                    <button name="perform" class="button button-primary" value="sync">Sync Products & Vendors</button>
                </td>
            </tr>
        </table>

        <h2>Prune</h2>

        <small>
            <strong>Last Prune:</strong> <?= $last_prune; ?>
        </small>

        <table>
            <tr>
                <td>
                    <button name="perform" class="button button-primary" value="prune" disabled >Prune Products & Vendors</button>
                </td>
            </tr>
        </table>

	    <h2>Reports</h2>

        <table>
            <tr>
                <td>
                    <button name="perform" class="button button-primary" value="product_report">Products Report</button>
                    <button name="perform" class="button button-primary" value="vendor_report">Vendors Report</button>
                </td>
            </tr>
        </table>

    </form>

	<?php
}