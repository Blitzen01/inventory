document.addEventListener('DOMContentLoaded', function() {
    const firstNameInput = document.getElementById('gen_first_name');
    const displayUser = document.getElementById('display_username');
    const displayPass = document.getElementById('display_password');

    // 1. Generate Username (firstname + random 3 digits)
    function updateUsername() {
        const name = firstNameInput.value.trim().toLowerCase().replace(/\s+/g, '');
        if (name) {
            const randomID = Math.floor(100 + Math.random() * 900);
            displayUser.value = name + randomID;
        } else {
            displayUser.value = "---";
        }
    }

    // 2. Generate Random Secure Password
    window.generateNewPassword = function() {
        const chars = "ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789!@#"; // Removed confusing chars like 1, l, 0, O
        let pass = "";
        for (let i = 0; i < 10; i++) {
            pass += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        displayPass.value = pass;
    }

    // 3. Copy Function
    window.copyToClipboard = function(elementId, btn) {
        const copyText = document.getElementById(elementId);
        if (copyText.value === "---") return;

        navigator.clipboard.writeText(copyText.value).then(() => {
            // Visual feedback
            const icon = btn.querySelector('i');
            icon.classList.replace('fa-regular', 'fa-solid');
            icon.classList.replace('fa-copy', 'fa-check');
            btn.classList.add('text-success');

            setTimeout(() => {
                icon.classList.replace('fa-solid', 'fa-regular');
                icon.classList.replace('fa-check', 'fa-copy');
                btn.classList.remove('text-success');
            }, 2000);
        });
    }

    // Listeners
    firstNameInput.addEventListener('input', updateUsername);
    
    // Generate initial password when user starts typing first name
    firstNameInput.addEventListener('focus', function() {
        if(displayPass.value === "---") generateNewPassword();
    }, { once: true });
});