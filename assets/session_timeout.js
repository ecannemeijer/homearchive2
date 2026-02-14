/**
 * Session Timeout Handler
 * Automatically logs out user after 30 minutes of inactivity
 */

(function() {
    'use strict';
    
    // Configuration
    const TIMEOUT_DURATION = 30 * 60 * 1000; // 30 minutes in milliseconds
    const WARNING_TIME = 5 * 60 * 1000; // Show warning 5 minutes before timeout
    
    let timeoutTimer;
    let warningTimer;
    
    // Events that reset the timer
    const resetEvents = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'];
    
    /**
     * Show warning modal before logout
     */
    function showWarning() {
        const warningDiv = document.createElement('div');
        warningDiv.id = 'session-timeout-warning';
        warningDiv.innerHTML = `
            <div class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
                <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <i class="fas fa-exclamation-triangle text-yellow-500 text-3xl"></i>
                        <h3 class="text-xl font-bold text-gray-900">Sessie verloopt bijna</h3>
                    </div>
                    <p class="text-gray-700 mb-6">
                        U bent al een tijdje inactief. Uw sessie verloopt over 5 minuten.
                        Klik op "Blijf ingelogd" om door te gaan.
                    </p>
                    <div class="flex gap-3">
                        <button onclick="sessionTimeout.stayLoggedIn()" 
                                class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium transition-all">
                            <i class="fas fa-check"></i> Blijf ingelogd
                        </button>
                        <button onclick="sessionTimeout.logout()" 
                                class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-md font-medium transition-all">
                            <i class="fas fa-sign-out-alt"></i> Uitloggen
                        </button>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(warningDiv);
    }
    
    /**
     * Remove warning modal
     */
    function hideWarning() {
        const warningDiv = document.getElementById('session-timeout-warning');
        if (warningDiv) {
            warningDiv.remove();
        }
    }
    
    /**
     * Logout user
     */
    function logout() {
        window.location.href = '/logout';
    }
    
    /**
     * Reset timeout timers
     */
    function resetTimer() {
        // Clear existing timers
        clearTimeout(timeoutTimer);
        clearTimeout(warningTimer);
        hideWarning();
        
        // Set warning timer (show warning 5 minutes before timeout)
        warningTimer = setTimeout(showWarning, TIMEOUT_DURATION - WARNING_TIME);
        
        // Set logout timer
        timeoutTimer = setTimeout(logout, TIMEOUT_DURATION);
    }
    
    /**
     * Stay logged in (extend session)
     */
    function stayLoggedIn() {
        hideWarning();
        resetTimer();
        
        // Ping server to keep session alive
        fetch('/dashboard', { method: 'HEAD' }).catch(() => {});
    }
    
    // Public API
    window.sessionTimeout = {
        stayLoggedIn: stayLoggedIn,
        logout: logout
    };
    
    // Initialize only if user is logged in
    if (document.body.classList.contains('no-scroll-x')) { // User pages have this class
        // Attach event listeners
        resetEvents.forEach(event => {
            document.addEventListener(event, resetTimer, true);
        });
        
        // Start timer
        resetTimer();
        
        console.log('Session timeout initialized: ' + (TIMEOUT_DURATION / 60000) + ' minutes');
    }
})();
