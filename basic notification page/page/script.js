document.addEventListener("DOMContentLoaded", () => {
    let buttonClicks = {}; // Object to track button clicks
    let audioUnlocked = false; // Track if audio is allowed
    let currentStatus = ""; // Empty to ensure the first update always happens

    // Global variable for status that can be updated externally
    window.notificationStatus = "warning"; // Default status

    // Unlock audio when the user interacts with the page
    document.addEventListener("click", () => {
        audioUnlocked = true;
    });

    document.querySelectorAll("input[type='checkbox']").forEach(checkbox => {
        // Load saved state from localStorage
        if (localStorage.getItem(checkbox.value) === "true") {
            checkbox.checked = true;
        }

        checkbox.addEventListener("change", function() {
            let option = this.value;
            if (!buttonClicks[option]) {
                buttonClicks[option] = 0;
            }
            buttonClicks[option]++;
            console.log(`Option: ${option} clicked ${buttonClicks[option]} times`);
            
            // Save state to localStorage
            localStorage.setItem(option, this.checked);
        });
    });

    // Function to play notification sound
    function playNotificationSound() {
        let audio = new Audio('./notification.mp3'); // Ensure correct file path
        audio.volume = 1.0; // Set to full volume
        audio.play().then(() => {
            console.log("Notification sound played successfully.");
        }).catch(error => {
            console.error("Audio play failed:", error);
        });
    }

    // Notification handling
    const updateNotification = (newStatus) => {
        const notification = document.getElementById("notification");
        if (!notification) return; // Prevent error if notification element is missing

        // Only play sound if status actually changed
        if (newStatus !== currentStatus) {
            playNotificationSound();
        }

        // Always update the status
        currentStatus = newStatus;
        localStorage.setItem("lastStatus", newStatus); // Save status to localStorage
        notification.className = `notification ${newStatus}`;

        const messages = {
            success: "✅ Your booking has been completed.",
            error: "❌ Your booking has not been made, please double-check your form.",
            warning: "⚠️ Warning! Something is wrong with your booking, please call us."
        };

        // Ensure correct message is displayed
        notification.innerHTML = messages[newStatus] || "ℹ️ Default Notification Message.";

        console.log(`Notification updated to: ${newStatus}`);
    };

    // Function to check for an external status update without modifying it
    function checkForStatusChange() {
        let newStatus = window.notificationStatus;

        // Only update if the status has changed
        if (newStatus !== currentStatus) {
            updateNotification(newStatus);
        }
    }

    // Check if the last stored status is different after refresh
    let lastStoredStatus = localStorage.getItem("lastStatus");
    if (lastStoredStatus && lastStoredStatus !== window.notificationStatus) {
        playNotificationSound(); // Play sound if the status has changed since the last refresh
    }

    // Initial notification update
    updateNotification(window.notificationStatus);

    // Interval to check for status changes without modifying it
    setInterval(checkForStatusChange, 5000); // Check every 5 seconds
});
