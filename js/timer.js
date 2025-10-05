let timerInterval;
let totalTime = 1 * 60; // 1 minute in seconds
let isRunning = false;

function updateTimerDisplay() {
    const minutes = Math.floor(totalTime / 60);
    const seconds = totalTime % 60;
    document.getElementById("timer").textContent =
        `${minutes.toString().padStart(2,'0')}:${seconds.toString().padStart(2,'0')}`;
}

function startTimer() {
    if (isRunning) return; // Prevent multiple intervals
    isRunning = true;

    timerInterval = setInterval(() => {
        if (totalTime <= 0) {
            clearInterval(timerInterval);
            isRunning = false;
            alert("🎉 Great job! Keep up the good work! 💪");
            totalTime = 1 * 60; // Reset timer to 1 minute
            updateTimerDisplay();
        } else {
            totalTime--;
            updateTimerDisplay();
        }
    }, 1000);
}

function stopTimer() {
    if (!isRunning) return; // If not running, do nothing
    clearInterval(timerInterval);
    isRunning = false;
    alert("⏸️ Timer paused! You can resume it anytime 💪");
}// Initialize display
updateTimerDisplay();
