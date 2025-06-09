<?php

namespace Arsol_Projects_For_Woo;

if (!defined('ABSPATH')) {
    exit;
}

class Woocommerce_Subscriptions {

    /**
     * Converts a billing period string into a number of days.
     *
     * @param string $period The billing period (day, week, month, year).
     * @return int The number of days in the period.
     */
    private static function get_days_in_period($period) {
        switch (strtolower($period)) {
            case 'day':
                return 1;
            case 'week':
                return 7;
            case 'month':
                return 30.417; // Average days in a month (365 / 12)
            case 'year':
                return 365;
            default:
                return 0;
        }
    }

    /**
     * Calculates the normalized daily cost of a subscription.
     *
     * @param float $price The price of the subscription.
     * @param int   $interval The billing interval (e.g., 1, 2, 3).
     * @param string $period The billing period (day, week, month, year).
     * @return float The calculated daily cost.
     */
    public static function get_daily_cost($price, $interval, $period) {
        $price = (float) $price;
        $interval = (int) $interval;
        
        if ($interval === 0) {
            return 0;
        }

        $days_in_period = self::get_days_in_period($period);
        if ($days_in_period === 0) {
            return 0;
        }
        
        $total_days_in_cycle = $days_in_period * $interval;
        if ($total_days_in_cycle === 0) {
            return 0;
        }

        return $price / $total_days_in_cycle;
    }

    /**
     * Calculates the average monthly cost of a subscription.
     *
     * @param float $price The price of the subscription.
     * @param int   $interval The billing interval (e.g., 1, 2, 3).
     * @param string $period The billing period (day, week, month, year).
     * @return float The calculated average monthly cost.
     */
    public static function get_monthly_cost($price, $interval, $period) {
        $daily_cost = self::get_daily_cost($price, $interval, $period);
        return $daily_cost * 30.417; // Average days in a month
    }

    /**
     * Calculates the normalized annual cost of a subscription.
     *
     * @param float $price The price of the subscription.
     * @param int   $interval The billing interval (e.g., 1, 2, 3).
     * @param string $period The billing period (day, week, month, year).
     * @return float The calculated annual cost.
     */
    public static function get_annual_cost($price, $interval, $period) {
        $daily_cost = self::get_daily_cost($price, $interval, $period);
        return $daily_cost * 365;
    }
}