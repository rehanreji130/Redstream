<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us | Blood Availability</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary: #e63946;
            --primary-light: #fce8ea;
            --secondary: #1d3557;
            --dark: #2b2d42;
            --light: #ffffff;
            --gray: #f8f9fa;
            --border-radius: 8px;
            --box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            line-height: 1.6;
            color: var(--dark);
            background-color: var(--gray);
            -webkit-font-smoothing: antialiased;
        }
        
        /* Header */
        .main-header {
            background: linear-gradient(135deg, var(--primary), #c1121f);
            color: var(--light);
            padding: 4rem 0;
            text-align: center;
            position: relative;
            overflow: hidden;
            clip-path: polygon(0 0, 100% 0, 100% 90%, 0 100%);
            margin-bottom: 2rem;
        }
        
        .header-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 1.5rem;
            position: relative;
            z-index: 2;
        }
        
        .header-container h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            animation: fadeInDown 0.8s ease-out;
        }
        
        .header-container p {
            font-size: 1.1rem;
            opacity: 0.9;
            animation: fadeInUp 0.8s ease-out 0.3s forwards;
            opacity: 0;
        }
        
        /* Contact Section */
        .contact-section {
            max-width: 800px;
            margin: 0 auto 4rem;
            padding: 0 1.5rem;
            text-align: center;
        }
        
        .section-title {
            color: var(--secondary);
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            position: relative;
            display: inline-block;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 0;
            width: 50px;
            height: 3px;
            background: var(--primary);
        }
        
        .contact-section p {
            color: #555;
            margin-bottom: 2rem;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .contact-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .contact-card {
            background: var(--light);
            border-radius: var(--border-radius);
            padding: 2rem;
            box-shadow: var(--box-shadow);
            transition: var(--transition);
            text-align: center;
            border-top: 3px solid var(--primary);
        }
        
        .contact-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }
        
        .contact-icon {
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: 1.5rem;
        }
        
        .contact-card h3 {
            font-size: 1.3rem;
            margin-bottom: 1rem;
            color: var(--secondary);
        }
        
        .contact-link {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
            display: inline-block;
            margin-top: 0.5rem;
        }
        
        .contact-link:hover {
            color: #c1121f;
            text-decoration: underline;
        }
        
        /* Footer */
        .main-footer {
            background: linear-gradient(135deg, var(--secondary), #0d1b2a);
            color: var(--light);
            padding: 2rem 0;
            text-align: center;
            clip-path: polygon(0 10%, 100% 0, 100% 100%, 0% 100%);
        }
        
        .footer-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }
        
        .footer-links {
            display: flex;
            justify-content: center;
            gap: 1.5rem;
            margin: 1.5rem 0;
        }
        
        .footer-links a {
            color: var(--light);
            text-decoration: none;
            transition: var(--transition);
        }
        
        .footer-links a:hover {
            color: var(--primary-light);
        }
        
        .copyright {
            font-size: 0.9rem;
            opacity: 0.8;
        }
        
        /* Animations */
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .header-container h1 {
                font-size: 2rem;
            }
            
            .contact-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header class="main-header">
        <div class="header-container">
            <h1>Contact Us</h1>
            <p>We're here to help. Reach out to us for any questions or support.</p>
        </div>
    </header>

    <section class="contact-section">
        <h2 class="section-title">Get In Touch</h2>
        <p>Have questions or need assistance? We're just a message or call away.</p>
        
        <div class="contact-grid">
            <div class="contact-card">
                <div class="contact-icon">
                    <i class="fas fa-envelope"></i>
                </div>
                <h3>Email Us</h3>
                <a href="mailto:redstreamproject@gmail.com" class="contact-link">redstreamproject@gmail.com</a>
            </div>
            
            <div class="contact-card">
                <div class="contact-icon">
                    <i class="fas fa-phone"></i>
                </div>
                <h3>Call Us</h3>
                <a href="tel:+919072811591" class="contact-link">+91 9072811591</a>
            </div>
        </div>
    </section>

    <footer class="main-footer">
        <div class="footer-container">
            <div class="footer-links">
                <a href="privacy-policy.php">Privacy Policy</a>
                <a href="terms-of-service.php">Terms of Service</a>
            </div>
            <p class="copyright">&copy; 2025 Blood Availability. All rights reserved.</p>
        </div>
    </footer>

    <script>
        // Enhanced interactive elements
        document.addEventListener('DOMContentLoaded', function() {
            // Animate contact cards on scroll
            const contactCards = document.querySelectorAll('.contact-card');
            
            const animateOnScroll = () => {
                contactCards.forEach((card, index) => {
                    const cardPosition = card.getBoundingClientRect().top;
                    const screenPosition = window.innerHeight / 1.3;
                    
                    if(cardPosition < screenPosition) {
                        card.style.animation = `fadeInUp 0.5s ease-out ${index * 0.1}s forwards`;
                        card.style.opacity = '0';
                    }
                });
            };
            
            window.addEventListener('scroll', animateOnScroll);
            animateOnScroll(); // Run once on load
        });
    </script>
</body>
</html>