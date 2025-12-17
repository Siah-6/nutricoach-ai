/**
 * Service Worker for Background Timer Support
 */

const CACHE_NAME = 'nutricoach-v1';

// Install event
self.addEventListener('install', (event) => {
    console.log('Service Worker installing...');
    self.skipWaiting();
});

// Activate event
self.addEventListener('activate', (event) => {
    console.log('Service Worker activating...');
    event.waitUntil(clients.claim());
});

// Handle messages from the main thread
self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'TIMER_UPDATE') {
        // Store timer state
        const timerData = event.data.data;
        console.log('Timer update received:', timerData);
    }
});

// Handle push notifications (for timer completion)
self.addEventListener('push', (event) => {
    const data = event.data.json();
    
    const options = {
        body: data.body,
        icon: '/assets/images/NutriLogo.png',
        badge: '/assets/images/NutriLogo.png',
        vibrate: [200, 100, 200],
        tag: 'rest-timer',
        requireInteraction: true
    };
    
    event.waitUntil(
        self.registration.showNotification(data.title, options)
    );
});

// Handle notification clicks
self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    
    event.waitUntil(
        clients.openWindow('/pages/workout-ai.php')
    );
});
