/**
 * Background Timer - Continues running even when app is in background
 */

class BackgroundTimer {
    constructor() {
        this.timerKey = 'workout_rest_timer';
        this.wakeLock = null;
    }

    // Start timer
    async start(duration, exerciseIndex, onTick, onComplete) {
        const endTime = Date.now() + (duration * 1000);
        
        // Save to localStorage
        localStorage.setItem(this.timerKey, JSON.stringify({
            endTime,
            exerciseIndex,
            duration,
            startTime: Date.now()
        }));

        // Request wake lock to keep screen on
        await this.requestWakeLock();

        // Request notification permission
        await this.requestNotificationPermission();

        // Start checking
        this.checkTimer(onTick, onComplete);
    }

    checkTimer(onTick, onComplete) {
        const timerData = this.getTimerData();
        if (!timerData) return;

        const remaining = Math.max(0, Math.ceil((timerData.endTime - Date.now()) / 1000));

        if (remaining > 0) {
            onTick(remaining, timerData.exerciseIndex);
            setTimeout(() => this.checkTimer(onTick, onComplete), 1000);
        } else {
            this.complete(onComplete);
        }
    }

    complete(onComplete) {
        const timerData = this.getTimerData();
        this.stop();
        
        // Show notification
        this.showNotification('Rest Complete!', 'Time to start your next set 💪');
        
        // Vibrate
        if ('vibrate' in navigator) {
            navigator.vibrate([200, 100, 200, 100, 200]);
        }

        if (onComplete && timerData) {
            onComplete(timerData.exerciseIndex);
        }
    }

    stop() {
        localStorage.removeItem(this.timerKey);
        this.releaseWakeLock();
    }

    getTimerData() {
        const data = localStorage.getItem(this.timerKey);
        return data ? JSON.parse(data) : null;
    }

    getRemainingTime() {
        const timerData = this.getTimerData();
        if (!timerData) return 0;
        return Math.max(0, Math.ceil((timerData.endTime - Date.now()) / 1000));
    }

    isRunning() {
        return this.getTimerData() !== null;
    }

    async requestWakeLock() {
        if ('wakeLock' in navigator) {
            try {
                this.wakeLock = await navigator.wakeLock.request('screen');
                console.log('Wake Lock activated');
            } catch (err) {
                console.log('Wake Lock error:', err);
            }
        }
    }

    releaseWakeLock() {
        if (this.wakeLock) {
            this.wakeLock.release();
            this.wakeLock = null;
        }
    }

    async requestNotificationPermission() {
        if ('Notification' in window && Notification.permission === 'default') {
            await Notification.requestPermission();
        }
    }

    showNotification(title, body) {
        if ('Notification' in window && Notification.permission === 'granted') {
            new Notification(title, {
                body,
                icon: '/assets/images/NutriLogo.png',
                badge: '/assets/images/NutriLogo.png',
                vibrate: [200, 100, 200],
                tag: 'rest-timer',
                requireInteraction: false
            });
        }
    }

    addTime(seconds) {
        const timerData = this.getTimerData();
        if (timerData) {
            timerData.endTime += (seconds * 1000);
            localStorage.setItem(this.timerKey, JSON.stringify(timerData));
        }
    }
}

window.BackgroundTimer = BackgroundTimer;
