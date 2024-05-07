

document.addEventListener('DOMContentLoaded', () =>{

    // ***************************************************** Remember me js starts
    /*const rememberMe = document.querySelector('.js-remeber-me');
    rememberMe.addEventListener('click', () => {
        if(rememberMe.innerHTML === "Remember Me"){
            rememberMe.innerHTML = "Remember Me checked";
            rememberMe.style.color = 'green';
        }else{
            rememberMe.innerHTML = "Remember Me";
            rememberMe.style.color = 'black';
        }
    });*/

    // Function to update dashboard content
    function updateDashboard() {
        $.ajax({
            url: 'update_dashboard.php', // PHP script to handle AJAX request
            type: 'GET',
            dataType: 'html',
            success: function(data) {
                $('#dashboardContent').html(data); // Update dashboard content
            },
            error: function(xhr, status, error) {
                console.error('Error updating dashboard:', error);
            }
        });
    }

    // Update dashboard every 10 seconds
    setInterval(updateDashboard, 10000); 


    // ***************************************************** Remember me js ends

    // ***************************************************** header me js starts
    const userBtn = document.querySelector('#user-btn');
    const profile = document.querySelector('.profile');
    const navbar = document.querySelector('.navbar');
    const menuBtn = document.querySelector('#menu-btn');

    menuBtn.onclick = () => {
        navbar.classList.toggle('active');
        profile.classList.remove('active');
        toggleScroll();
    };

    userBtn.onclick = () => {
        profile.classList.toggle('active');
        navbar.classList.remove('active');
        toggleScroll();
    };

    // Function to toggle scroll
    function toggleScroll() {
        document.body.classList.toggle('no-scroll');
    }

    // Close navbar when a navigation link is clicked
    const navLinks = document.querySelectorAll('.navbar a');
    navLinks.forEach((link) => {
        link.addEventListener('click', () => {
            navbar.classList.remove('active');
            toggleScroll();
        });
    });

    // Close navbar when scrolling

    window.onscroll = () => {
        profile.classList.remove('active');
        navbar.classList.remove('active');
        toggleScroll();
    }
    
    // ***************************************************** header me js ends


    // Add event listener to the password input field
    /*var passwordInput = document.getElementById('password');
    passwordInput.addEventListener('input', function() {
        var password = this.value; // Get the value of the password input field
        checkPasswordStrength(password); // Call the checkPasswordStrength function
    });


    function checkPasswordStrength(password) {
        var strength = 0;
        var progress = document.getElementById('password-strength-bar');

        // Add points for length
        strength += password.length * 2;

        // Check for special characters
        var specialChars = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/;
        if (specialChars.test(password)) {
            strength += 5;
        }

        // Check for uppercase and lowercase letters
        if (/[a-z]/.test(password) && /[A-Z]/.test(password)) {
            strength += 20;
        }

        // Check for numbers
        if (/\d/.test(password)) {
            strength += 20;
        }

        // Check if password matches the regex pattern
        if (/^[a-zA-Z0-9!@#$%^&*()_+}{:;?]{8}$/.test(password)) {
            // Password meets the requirements, set progress to 100%
            progress.style.width = '100%';
            progress.classList.remove('bg-danger', 'bg-warning');
            progress.classList.add('bg-success');
        } else {
            // Password does not meet the requirements, set progress based on strength
            progress.style.width = strength + '%';
            // Change color based on strength
            if (strength < 30) {
                progress.classList.remove('bg-success', 'bg-warning');
                progress.classList.add('bg-danger');
            } else if (strength < 60) {
                progress.classList.remove('bg-danger', 'bg-success');
                progress.classList.add('bg-warning');
            } else { // Adjusted threshold for bg-success
                progress.classList.remove('bg-danger', 'bg-warning');
                progress.classList.add('bg-success');
            }
        }
    }*/


});

