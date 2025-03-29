document.addEventListener('DOMContentLoaded', function() {
    const themeToggle = document.querySelector('.theme-toggle');
    const lightMode = document.querySelector('.light-mode');
    const darkMode = document.querySelector('.dark-mode');
    
    // Check saved theme or use preferred color scheme
    const savedTheme = localStorage.getItem('theme') || 
        (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
    document.documentElement.setAttribute('data-theme', savedTheme);
    
    // Set initial active button
    if (savedTheme === 'dark') {
        darkMode.classList.add('active');
    } else {
        lightMode.classList.add('active');
    }

    // Toggle theme
    themeToggle.addEventListener('click', function() {
        const currentTheme = document.documentElement.getAttribute('data-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        
        document.documentElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        
        lightMode.classList.toggle('active');
        darkMode.classList.toggle('active');
    });
});