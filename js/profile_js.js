

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
});

