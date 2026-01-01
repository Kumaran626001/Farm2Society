/**
 * Falling Vegetables Animation Script
 * Handles creating and animating floating emojis for auth pages.
 */
document.addEventListener("DOMContentLoaded", function () {
    const container = document.createElement('div');
    container.classList.add('animation-container');
    document.body.appendChild(container);

    const veggies = ['🥕', '🥦', '🍅', '🍆', '🌽', '🌶️', '🥔', '🥬'];
    const count = 20;

    for (let i = 0; i < count; i++) {
        const ball = document.createElement('div');
        ball.classList.add('falling-ball');

        // Random Vegetable emoji
        ball.innerText = veggies[Math.floor(Math.random() * veggies.length)];

        // Random Size (40px to 80px)
        const size = Math.random() * 40 + 40;
        ball.style.fontSize = `${size}px`;

        // Random Horizontal Position
        ball.style.left = `${Math.random() * 95}%`;

        // Random Speed (20s to 40s) and Delay
        const duration = Math.random() * 20 + 20;
        const delay = Math.random() * 20 * -1;

        ball.style.animationDuration = `${duration}s`;
        ball.style.animationDelay = `${delay}s`;

        container.appendChild(ball);
    }
});
