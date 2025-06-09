<?php

namespace Arsol\Projects\For_Woo\Classes;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Woocommerce_Subscriptions class
 */
class Woocommerce_Subscriptions
{
    /**
     * Constructor
     */
    public function __construct()
    {
        add_filter('arsol_proposal_product_types', array($this, 'add_subscription_product_types'));
        add_action('arsol_proposal_invoice_line_item_billing_cycle', array($this, 'render_billing_cycle_ui'));
        add_action('arsol_proposal_invoice_recurring_fee_billing_cycle', array($this, 'render_billing_cycle_ui'));
    }

    /**
     * Add subscription product types to the proposal search
     *
     * @param array $product_types
     * @return array
     */
    public function add_subscription_product_types($product_types)
    {
        return array_merge($product_types, array('subscription', 'subscription_variation'));
    }

    /**
     * Render the billing cycle UI
     *
     * @param array $data The item data.
     */
    public function render_billing_cycle_ui($data = array())
    {
        $intervals = function_exists('wcs_get_subscription_period_interval_strings') ? wcs_get_subscription_period_interval_strings() : array_fill_keys(range(1, 12), null);
        $periods = function_exists('wcs_get_subscription_period_strings') ? wcs_get_subscription_period_strings() : array();
        
        $selected_interval = isset($data['billing_cycle_interval']) ? $data['billing_cycle_interval'] : '';
        $selected_period = isset($data['billing_cycle_period']) ? $data['billing_cycle_period'] : '';
        ?>
        <select class="billing_cycle_interval" name="line_item[<#= data.id #>][billing_cycle_interval]" style="width: 100px;">
            <# _.each(<?php echo json_encode($intervals); ?>, function(label, value) { #>
                <option value="{{ value }}" <# if (data.billing_cycle_interval == value) { #>selected="selected"<# } #>>{{ value }}</option>
            <# }); #>
        </select>
        <select class="billing_cycle_period" name="line_item[<#= data.id #>][billing_cycle_period]" style="width: 150px;">
            <# _.each(<?php echo json_encode($periods); ?>, function(label, value) { #>
                <option value="{{ value }}" <# if (data.billing_cycle_period == value) { #>selected="selected"<# } #>>{{ label }}</option>
            <# }); #>
        </select>
        <?php
    }

    /**
     * Get the number of days in a given billing period
     *
     * @param string $period (day, week, month, year)
     * @return integer
     */
    public static function get_days_in_period($period)
    {
        switch ($period) {
            case 'day':
                return 1;
            case 'week':
                return 7;
            case 'month':
                return 30.4375; // Average days in a month (365.25 / 12)
            case 'year':
                return 365.25; // Average days in a year to account for leap years
            default:
                return 0;
        }
    }

    /**
     * Get the daily cost of a recurring price
     *
     * @param float $price
     * @param string $interval
     * @param string $period
     * @return float
     */
    public static function get_daily_cost($price, $interval, $period)
    {
        $days_in_period = self::get_days_in_period($period);
        if ($days_in_period > 0 && $interval > 0) {
            return $price / ($interval * $days_in_period);
        }
        return 0;
    }

    /**
     * Get the monthly cost of a recurring price
     *
     * @param float $price
     * @param string $interval
     * @param string $period
     * @return float
     */
    public static function get_monthly_cost($price, $interval, $period)
    {
        $daily_cost = self::get_daily_cost($price, $interval, $period);
        return $daily_cost * self::get_days_in_period('month');
    }

    /**
     * Get the annual cost of a recurring price
     *
     * @param float $price
     * @param string $interval
     * @param string $period
     * @return float
     */
    public static function get_annual_cost($price, $interval, $period)
    {
        $daily_cost = self::get_daily_cost($price, $interval, $period);
        return $daily_cost * self::get_days_in_period('year');
    }

    /**
     * Returns an array of constants used for date calculations.
     * This allows the constants to be centralized and passed to other scripts.
     *
     * @return array
     */
    public static function get_calculation_constants()
    {
        return array(
            'days_in_month' => self::get_days_in_period('month'),
            'days_in_year'  => self::get_days_in_period('year')
        );
    }
}