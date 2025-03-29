/**
 * Google Ads Tracker - WordPress Integration
 * Version: 1.0
 */
document.addEventListener('DOMContentLoaded', function() {
    // Get current page URL
    const currentUrl = window.location.href;
    const referrer = document.referrer || 'direct';
    const userAgent = navigator.userAgent;
    
    // Prepare tracking data
    const trackingData = {
        gad_url: currentUrl,
        referrer: referrer,
        useragent: userAgent
    };

    // Send tracking request
    fetch('<?php echo esc_url(home_url("/wp-json/google-ads-tracker/v1/track")); ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(trackingData)
    }).catch(error => console.error('Tracking error:', error));
});