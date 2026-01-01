<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Farm2Society</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <header>
        <nav>
            <a href="index.php" class="brand"><i class="fas fa-leaf"></i> Farm2Society</a>
            <div class="menu-toggle" onclick="document.querySelector('header nav ul').classList.toggle('nav-active')">
                <i class="fas fa-bars"></i>
            </div>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="login.php">Login</a></li>
                <li><a href="consumer/dashboard.php" class="btn-nav">Get Started</a></li>
            </ul>
        </nav>
    </header>

    <div class="hero">
        <div class="hero-content">
            <span class="badge-new">Now Live in Your Society</span>
            <h1>Fresh Vegetables <br> Direct from Farmers</h1>
            <p>Experience the taste of pure freshness. Hand-picked daily and delivered to your doorstep.</p>
            <div class="hero-buttons">
                <a href="consumer/dashboard.php" class="btn-hero primary">Shop Now <i
                        class="fas fa-arrow-right"></i></a>
                <a href="register.php" class="btn-hero secondary">Join as Farmer</a>
            </div>
        </div>
    </div>

    <div class="features container">
        <div class="feature-card">
            <div class="icon-wrapper"><i class="fas fa-seedling"></i></div>
            <h3>100% Organic</h3>
            <p>Grown with care, free from harmful chemicals.</p>
        </div>
        <div class="feature-card">
            <div class="icon-wrapper"><i class="fas fa-truck-fast"></i></div>
            <h3>Fast Delivery</h3>
            <p>From farm to your table in less than 24 hours.</p>
        </div>
        <div class="feature-card">
            <div class="icon-wrapper"><i class="fas fa-hand-holding-heart"></i></div>
            <h3>Fair Prices</h3>
            <p>Farmers get their due, you get the best rates.</p>
        </div>
    </div>

    <footer>
        <div class="container">
            <div class="footer-content">
                <p>&copy; 2026 Farm2Society. Fresh produce, connected community.</p>
                <div class="social-links">
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-facebook"></i></a>
                </div>
            </div>
        </div>
    </footer>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Create Firefly Container
            const container = document.createElement('div');
            container.classList.add('firefly-container');
            document.body.appendChild(container);

            // Generate Fireflies
            for (let i = 0; i < 30; i++) {
                const fly = document.createElement('div');
                fly.classList.add('firefly');

                // Random Position
                fly.style.left = `${Math.random() * 100}vw`;
                fly.style.top = `${Math.random() * 100}vh`;

                // Random Size Variation
                const size = Math.random() * 4 + 2; // 2px to 6px
                fly.style.width = `${size}px`;
                fly.style.height = `${size}px`;

                // Random Animation Duration & Delay
                fly.style.animationDuration = `${Math.random() * 10 + 10}s`;
                fly.style.animationDelay = `-${Math.random() * 10}s`;

                // Random Glow Color (Mint or Warm White)
                const colors = ['rgba(116, 198, 157, 0.8)', 'rgba(255, 239, 150, 0.6)']; // Mint or Warm Yellow
                const color = colors[Math.floor(Math.random() * colors.length)];
                fly.style.background = color;
                fly.style.boxShadow = `0 0 10px ${color}, 0 0 20px ${color}`;

                container.appendChild(fly);
            }
        });
    </script>
</body>

</html>