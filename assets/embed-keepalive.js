// ------------------------------------------------------------------
// GooseRSS the YouTube and EZTV RSS Generator
// ------------------------------------------------------------------
// Check if tab has focus and if so, prevent screen from sleeping while playing longer videos. 
// Wake Lock is released when tab goes out of focus or is closed.
// ------------------------------------------------------------------
// Version: 1.0
// ------------------------------------------------------------------

// Ensure that the Wake Lock API is available
if ('wakeLock' in navigator) {
	let wakelock = null;
    
	// Ask the browser to do a wake lock or show an error in console why it can't
	async function requestWakeLock() {
		try {
			// Ask for a lock
			wakelock = await navigator.wakeLock.request('screen');
// 			console.log('Wake Lock is active!');
		
			// Listen for release event
//			wakelock.addEventListener('release', () => {
//				console.log('Wake Lock was released');
//			});
		} catch (err) {
			console.error(`Could not obtain wake lock: ${err.name}, ${err.message}`);
		}
	}

	// Automatically release the wake lock when another tab is in focus
	document.addEventListener('visibilitychange', () => {
		if (wakelock !== null && document.hidden) {
			// Get rid of the lock
			wakelock.release();
		} else if (!document.hidden) {
			// Ask for a new lock
			requestWakeLock();
		}
	});

	// Request the screen wake lock after clicking/tapping somewhere
	document.addEventListener('click', () => {     
		// Ask for a lock
		requestWakeLock();
	})
	
	// Ask for a lock on pageload (probably won't work)
	requestWakeLock();
}