document.addEventListener("DOMContentLoaded", () => {
    let buttonClicks = {}; // Object to track button clicks

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

    // Notification handling
    const updateNotification = (type) => {
        const notification = document.getElementById("notification");
        if (!notification) return;
        
        notification.className = `notification ${type}`;
        
        const messages = {
            success: "✅ Your booking has been completed.",
            error: "❌ Your booking has not been made, please double-check your form.",
            warning: "⚠️ Warning! Something is wrong with your booking, please call us."
        };
        
        notification.innerHTML = messages[type] || "ℹ️ Default Notification Message.";
    };
    
    // Set notification status dynamically from a variable
    const notificationStatus = "warning"; // Change this to 'success' or 'error' to test
    updateNotification(notificationStatus);
});
