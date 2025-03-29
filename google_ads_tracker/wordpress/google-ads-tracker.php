<?php
/**
 * Plugin Name: Google Ads Tracker
 * Description: Tracks clicks from Google Ads campaigns
 * Version: 1.0
 * Author: BlackboxAI
 */

defined('ABSPATH') or die('Direct access not allowed');

class GoogleAdsTracker {
    public function __construct() {
        // Register REST API endpoint
        add_action('rest_api_init', [$this, 'register_api_endpoint']);
        
        // Add tracking script to footer
        add_action('wp_footer', [$this, 'add_tracking_script']);
    }

    public function register_api_endpoint() {
        register_rest_route('google-ads-tracker/v1', '/track', [
            'methods' => 'POST',
            'callback' => [$this, 'handle_tracking_request'],
            'permission_callback' => '__return_true'
        ]);
    }

    public function handle_tracking_request($request) {
        $data = $request->get_json_params();
        
        // Forward to main tracking API
        $response = wp_remote_post(
            'http://your-tracker-domain.com/api/track.php',
            [
                'body' => json_encode($data),
                'headers' => [
                    'Content-Type' => 'application/json'
                ]
            ]
        );

        return new WP_REST_Response(['status' => 'success'], 200);
    }

    public function add_tracking_script() {
        $script_url = plugins_url('../public/wordpress-tracker.js', __FILE__);
        echo '<script src="'.esc_url($script_url).'"></script>';
    }
}

new GoogleAdsTracker();