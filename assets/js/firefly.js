// Firefly Animation Script
document.addEventListener("DOMContentLoaded", function () {
    const container = document.createElement('div');
    container.classList.add('firefly-container');
    document.body.appendChild(container);

    const count = 40;

    for (let i = 0; i < count; i++) {
        const f = document.createElement('div');
        f.classList.add('firefly');

        // Random Start Position (Horizontal)
        f.style.left = `${Math.random() * 100}vw`;
        f.style.top = '0px'; // Force top to 0 for translateY animation

        // Random Size - Increased
        const size = Math.random() * 6 + 4; // 4px to 10px
        f.style.width = `${size}px`;
        f.style.height = `${size}px`;

        // Random Animation Properties - Slower
        const duration = Math.random() * 30 + 20; // 20s to 50s
        const delay = Math.random() * 50 * -1;

        // Directly set the shorthand animation property
        f.style.animation = `rise ${duration}s linear infinite`;
        f.style.animationDelay = `${delay}s`;

        container.appendChild(f);
    }
});
