document.addEventListener('DOMContentLoaded', function() {
    const darkModeToggle = document.getElementById('darkModeToggle');
    const body = document.body;

    if (darkModeToggle) {
        darkModeToggle.addEventListener('change', function() {
            const isChecked = this.checked;
            
            // 1. Toggle the CSS class immediately
            if (isChecked) {
                body.classList.add('dark-mode');
            } else {
                body.classList.remove('dark-mode');
            }

            // 2. Send the new setting to the database using AJAX
            // This prevents a full page reload when changing the setting.
            const newValue = isChecked ? '1' : '0';

            // Use the same endpoint as your form submissions
            fetch('../src/php_script/update_settings.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                // Send the setting_key and the new value
                body: `dark_mode_default=${newValue}`
            })
            .then(response => {
                // Check if the response was successful
                if (response.ok) {
                    console.log('Dark mode setting updated successfully in DB.');
                } else {
                    console.error('Failed to update dark mode setting in DB.');
                    // Optional: Revert the toggle state on failure
                    this.checked = !isChecked;
                }
            })
            .catch(error => {
                console.error('Error during fetch:', error);
                this.checked = !isChecked;
            });
        });
    }
});