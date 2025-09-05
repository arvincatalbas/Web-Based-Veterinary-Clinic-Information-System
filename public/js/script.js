document.addEventListener('DOMContentLoaded', (event) => {
    const wrapper = document.querySelector('.wrapper');
    const loginLink = document.querySelector('.login-link');
    const registerLink = document.querySelector('.register-link');
    
    registerLink.addEventListener('click', (e) => {
        e.preventDefault();
        wrapper.classList.add('active');
    });
    
    loginLink.addEventListener('click', (e) => {
        e.preventDefault();
        wrapper.classList.remove('active');
    });
});