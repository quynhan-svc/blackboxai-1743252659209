// Google Ads Click Tracker - WordPress Integration
document.addEventListener('DOMContentLoaded', function() {
    // Generate unique session ID
    const sessionId = localStorage.getItem('ga_session_id') || 
        Math.random().toString(36).substring(2, 15) + 
        Math.random().toString(36).substring(2, 15);
    localStorage.setItem('ga_session_id', sessionId);

    // Collect click data
    const clickData = {
        session_id: sessionId,
        ip: '', // Will be detected by server
        useragent: navigator.userAgent,
        referrer: document.referrer,
        gad_url: window.location.href,
        page_url: window.location.pathname,
        timestamp: new Date().toISOString()
    };

    // Verify if coming from Google Ads
    if (isGoogleAdsReferrer(clickData.referrer)) {
        // Send to tracking API
        navigator.sendBeacon('/google_ads_tracker/api/track.php', 
            JSON.stringify(clickData));
    }

    // Track subsequent page views
    window.addEventListener('popstate', trackPageView);
});

function isGoogleAdsReferrer(url) {
    return /(google|doubleclick)\.(com|net)/i.test(url) && 
        /[?&]gclid=/i.test(url);
}

function trackPageView() {
    const pageData = {
        page_url: window.location.pathname,
        timestamp: new Date().toISOString()
    };
    navigator.sendBeacon('/google_ads_tracker/api/pageview.php', 
        JSON.stringify(pageData));
}