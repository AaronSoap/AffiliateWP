<?php
/**
 * Payout functions
 *
 * @since 1.9
 * @package Affiliate_WP
 */

/**
 * Retrieves a payout object.
 *
 * @since 1.9
 *
 * @param int|AffWP\Payout $payout Payout ID or object.
 * @return AffWP\Payout|false Payout object if found, otherwise false.
 */
function affwp_get_payout( $payout = 0 ) {

	if ( is_object( $payout ) && isset( $payout->payout_id ) ) {
		$payout_id = $payout->payout_id;
	} elseif ( is_numeric( $payout ) ) {
		$payout_id = absint( $payout );
	} else {
		return false;
	}

	return affiliate_wp()->payouts->get_object( $payout_id );
}

/**
 * Retrieves the referrals associated with a payout.
 *
 * @since 1.9
 *
 * @param int|AffWP\Affiliate\Payout $payout Payout ID or object.
 * @return array|false List of referral objects associated with the payout, otherwise false.
 */
function affwp_get_payout_referrals( $payout = 0 ) {
	if ( ! $payout = affwp_get_payout( $payout ) ) {
		return false;
	}

	$referrals = affiliate_wp()->affiliates->payouts->get_referral_ids( $payout );

	return array_map( 'affwp_get_referral', $referrals );
}
