<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Availability - Redstream</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #e63946;
            --primary-light: #ff6b6b;
            --primary-dark: #c1121f;
            --secondary-color: #457b9d;
            --light-color: #f1faee;
            --dark-color: #1d3557;
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
            color: var(--dark-color);
            overflow-x: hidden;
            background-color:rgb(249, 249, 249);
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        /* Header styles */
        .main-header {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            padding: 4rem 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .header-container {
            position: relative;
            z-index: 2;
            max-width: 800px;
            margin: 0 auto;
        }

        .main-header h1 {
            font-size: 3.5rem;
            margin-bottom: 1rem;
            letter-spacing: 1px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }

        .main-header p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }

        .blood-drop {
            position: absolute;
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 50% 50% 50% 50% / 60% 60% 40% 40%;
            transform: translateY(-20px) rotate(45deg);
            z-index: 1;
            animation: float 5s infinite ease-in-out;
        }

        @keyframes float {
            0%, 100% { transform: translateY(-20px) rotate(45deg); }
            50% { transform: translateY(20px) rotate(45deg); }
        }

        /* Navigation styles */
        .main-nav {
            background-color: var(--dark-color);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 100;
            transition: var(--transition);
        }

        .main-nav.scrolled {
            padding: 0.5rem 0;
        }

        .main-nav ul {
            display: flex;
            justify-content: center;
            list-style: none;
            padding: 1rem 0;
            max-width: 1200px;
            margin: 0 auto;
        }

        .main-nav li {
            margin: 0 1.5rem;
        }

        .main-nav a {
            font-size: 1.1rem;
            font-weight: 500;
            position: relative;
            transition: var(--transition);
            color: white;
        }

        .main-nav a:hover {
            color: var(--primary-color);
        }

        .main-nav a::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            background-color: var(--dark-color);
            bottom: -5px;
            left: 0;
            transition: var(--transition);
        }

        .main-nav a:hover::after {
            width: 100%;
        }

        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            padding: 1rem;
            color: var(--dark-color);
        }

        /* Intro section styles */
        .intro-section {
            padding: 2rem 2rem;
            max-width: 1200px;
            margin: 0 auto;
            text-align: center;
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.8s ease, transform 0.8s ease;
        }

        .intro-section.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .intro-section h2 {
            font-size: 2.5rem;
            margin-bottom: 2rem;
            position: relative;
            display: inline-block;
        }

        .intro-section h2::after {
            content: '';
            position: absolute;
            width: 50%;
            height: 3px;
            background-color: var(--primary-color);
            bottom: -10px;
            left: 25%;
        }

        .intro-section p {
            max-width: 800px;
            margin: 0 auto 2rem;
            font-size: 1.1rem;
        }

        /* Blood types grid */
        .blood-types {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1.5rem;
            margin: 3rem 0;
        }

        .blood-type {
            background-color: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            cursor: pointer;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .blood-type::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background-color: var(--primary-color);
            transform: scaleX(0);
            transform-origin: left;
            transition: var(--transition);
        }

        .blood-type:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(230, 57, 70, 0.2);
        }

        .blood-type:hover::before {
            transform: scaleX(1);
        }

        .blood-type h3 {
            font-size: 2rem;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .blood-type p {
            font-size: 0.9rem;
            margin-bottom: 0;
            color: var(--secondary-color);
        }

        /* CTA Button */
        .cta-button {
            display: inline-block;
            background-color: var(--primary-color);
            color: white;
            padding: 0.8rem 2rem;
            border-radius: 30px;
            font-weight: 600;
            letter-spacing: 1px;
            box-shadow: 0 4px 12px rgba(230, 57, 70, 0.3);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            z-index: 1;
        }

        .cta-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: var(--primary-dark);
            z-index: -1;
            transform: scaleX(0);
            transform-origin: left;
            transition: var(--transition);
        }

        .cta-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(230, 57, 70, 0.4);
        }

        .cta-button:hover::before {
            transform: scaleX(1);
        }

        /* Footer styles */
        .main-footer {
            background-color: var(--dark-color);
            color: white;
            padding: 3rem 2rem;
            text-align: center;
        }

        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .social-links {
            margin-bottom: 2rem;
        }

        .social-links a {
            display: inline-block;
            width: 40px;
            height: 40px;
            background-color: rgba(255, 255, 255, 0.1);
            margin: 0 0.5rem;
            border-radius: 50%;
            line-height: 40px;
            transition: var(--transition);
        }

        .social-links a:hover {
            background-color: var(--primary-color);
            transform: translateY(-3px);
        }

        .main-footer p {
            margin-bottom: 1rem;
            opacity: 0.8;
        }

        .main-footer a {
            color: var(--primary-light);
            transition: var(--transition);
        }

        .main-footer a:hover {
            color: white;
        }

        /* Blood type popup */
        .blood-popup {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: var(--transition);
        }

        .blood-popup.active {
            opacity: 1;
            visibility: visible;
        }

        .blood-popup-content {
            background-color: white;
            padding: 2rem;
            border-radius: 10px;
            max-width: 500px;
            width: 90%;
            position: relative;
            transform: translateY(-50px);
            transition: var(--transition);
        }

        .blood-popup.active .blood-popup-content {
            transform: translateY(0);
        }

        .blood-popup-close {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 1.5rem;
            background: none;
            border: none;
            cursor: pointer;
            color: var(--dark-color);
        }

        .blood-popup-header {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .blood-popup-header h3 {
            font-size: 2.5rem;
            color: var(--primary-color);
        }

        .blood-popup-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1.5rem;
        }

        .blood-popup-details div {
            width: 48%;
        }

        .blood-popup-details h4 {
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
            color: var(--secondary-color);
        }

        .blood-popup-details ul {
            list-style: none;
            padding-left: 0;
        }

        .blood-popup-details li {
            padding: 0.5rem 0;
            border-bottom: 1px solid #eee;
        }

        .blood-popup-details li:last-child {
            border-bottom: none;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .main-header h1 {
                font-size: 2.5rem;
            }

            .main-header {
                padding: 6rem 1rem;
            }

            .mobile-menu-btn {
                display: block;
                position: absolute;
                right: 1rem;
                top: 1rem;
            }

            .main-nav ul {
                flex-direction: column;
                align-items: center;
                padding: 0;
                max-height: 0;
                overflow: hidden;
                transition: max-height 0.3s ease;
            }

            .main-nav ul.active {
                max-height: 300px;
                padding: 1rem 0;
            }

            .main-nav li {
                margin: 0.5rem 0;
            }

            .blood-types {
                grid-template-columns: repeat(2, 1fr);
            }

            .blood-popup-details {
                flex-direction: column;
            }

            .blood-popup-details div {
                width: 100%;
                margin-bottom: 1rem;
            }
        }

        @media (max-width: 480px) {
            .blood-types {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header class="main-header">
        <div class="header-container">
            <h1>Welcome to Redstream</h1>
            <p>Your trusted platform for blood donation and requests</p>
            <a href="#learn-more" class="cta-button">Learn More</a>
        </div>
        <!-- Blood drop decorations will be added by JS -->
    </header>
    
    <nav class="main-nav">
        <button class="mobile-menu-btn" id="mobileMenuBtn">☰</button>
        <ul id="mainMenu">
            <li><a href="hospital_panel/hospital_login.php">Hospital Login</a></li>
            <li><a href="recipient_panel/recipient_login.php">Recipient Login</a></li>
            <li><a href="about.php">About Us</a></li>
            <li><a href="contact.php">Contact</a></li>
        </ul>
    </nav>
    
    <section class="intro-section" id="learn-more">
        <h2>How it Works</h2>
        <p>
            Redstream connects hospitals, blood donors, and recipients through our innovative platform. 
            Hospitals can quickly request blood supplies, donors can easily find where their contribution 
            is most needed, and recipients can locate nearby blood sources in critical moments.
        </p>
        <p>
            Our mission is to create a seamless network that ensures blood is available when and where 
            it's needed most. Join us today and be part of this life-saving ecosystem!
        </p>
        
        <div class="blood-types">
            <div class="blood-type" data-type="A+">
                <h3>A+</h3>
                <p>35.7% of population</p>
            </div>
            <div class="blood-type" data-type="A-">
                <h3>A-</h3>
                <p>6.3% of population</p>
            </div>
            <div class="blood-type" data-type="B+">
                <h3>B+</h3>
                <p>8.5% of population</p>
            </div>
            <div class="blood-type" data-type="B-">
                <h3>B-</h3>
                <p>1.5% of population</p>
            </div>
            <div class="blood-type" data-type="AB+">
                <h3>AB+</h3>
                <p>3.4% of population</p>
            </div>
            <div class="blood-type" data-type="AB-">
                <h3>AB-</h3>
                <p>0.6% of population</p>
            </div>
            <div class="blood-type" data-type="O+">
                <h3>O+</h3>
                <p>37.4% of population</p>
            </div>
            <div class="blood-type" data-type="O-">
                <h3>O-</h3>
                <p>6.6% of population</p>
            </div>
        </div>
        
        <a href="recipient_panel/recipient_register.php" class="cta-button">Join Redstream Now</a>
    </section>
    
    <footer class="main-footer">
        <div class="footer-container">
            <p>&copy; 2025 Redstream Blood Availability System</p>
            <p><a href="privacy-policy.php">Privacy Policy</a> | <a href="terms-of-service.php">Terms of Service</a></p>
        </div>
    </footer>

    <!-- Blood type popup -->
    <div class="blood-popup" id="bloodPopup">
        <div class="blood-popup-content">
            <button class="blood-popup-close" id="popupClose">×</button>
            <div class="blood-popup-header">
                <h3 id="popupBloodType">A+</h3>
            </div>
            <div class="blood-popup-details">
                <div>
                    <h4>Can receive from:</h4>
                    <ul id="popupReceiveList"></ul>
                </div>
                <div>
                    <h4>Can donate to:</h4>
                    <ul id="popupDonateList"></ul>
                </div>
            </div>
            <p id="popupDescription"></p>
        </div>
    </div>

    <script>
        // Blood type information database
        const bloodTypeInfo = {
            'A+': {
                receivesFrom: ['A+', 'A-', 'O+', 'O-'],
                donatesTo: ['A+', 'AB+'],
                description: 'Type A+ is the second most common blood type. People with A+ blood can receive red blood cells from donors with A+, A-, O+, and O- blood types.'
            },
            'A-': {
                receivesFrom: ['A-', 'O-'],
                donatesTo: ['A+', 'A-', 'AB+', 'AB-'],
                description: 'Type A- is relatively rare. People with A- blood are universal donors for anyone with A or AB blood types.'
            },
            'B+': {
                receivesFrom: ['B+', 'B-', 'O+', 'O-'],
                donatesTo: ['B+', 'AB+'],
                description: 'Type B+ is relatively uncommon. People with B+ blood can receive red blood cells from donors with B+, B-, O+, and O- blood types.'
            },
            'B-': {
                receivesFrom: ['B-', 'O-'],
                donatesTo: ['B+', 'B-', 'AB+', 'AB-'],
                description: 'Type B- is one of the rarest blood types. People with B- blood are universal donors for anyone with B or AB blood types.'
            },
            'AB+': {
                receivesFrom: ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'],
                donatesTo: ['AB+'],
                description: 'Type AB+ is known as the universal recipient because people with this blood type can receive red blood cells from all blood types.'
            },
            'AB-': {
                receivesFrom: ['A-', 'B-', 'AB-', 'O-'],
                donatesTo: ['AB+', 'AB-'],
                description: 'Type AB- is the rarest of the eight main blood types. People with AB- blood can receive red blood cells only from A-, B-, AB-, and O- donors.'
            },
            'O+': {
                receivesFrom: ['O+', 'O-'],
                donatesTo: ['A+', 'B+', 'AB+', 'O+'],
                description: 'Type O+ is the most common blood type. People with O+ blood can donate red blood cells to anyone with a positive blood type.'
            },
            'O-': {
                receivesFrom: ['O-'],
                donatesTo: ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'],
                description: 'Type O- is the universal red cell donor. In emergency situations when there is no time to determine blood type, O- blood can be given to anyone.'
            }
        };
        
        // Add blood drop decorations to header
        function createBloodDrops() {
            const header = document.querySelector('.main-header');
            for (let i = 0; i < 10; i++) {
                const drop = document.createElement('div');
                drop.classList.add('blood-drop');
                
                // Random size between 30px and 100px
                const size = Math.random() * 70 + 30;
                drop.style.width = `${size}px`;
                drop.style.height = `${size}px`;
                
                // Random position
                drop.style.left = `${Math.random() * 100}%`;
                drop.style.top = `${Math.random() * 100}%`;
                
                // Random opacity
                drop.style.opacity = Math.random() * 0.4 + 0.1;
                
                // Random animation delay
                drop.style.animationDelay = `${Math.random() * 5}s`;
                
                header.appendChild(drop);
            }
        }
        
        // Handle mobile menu
        function setupMobileMenu() {
            const mobileMenuBtn = document.getElementById('mobileMenuBtn');
            const mainMenu = document.getElementById('mainMenu');
            
            mobileMenuBtn.addEventListener('click', () => {
                mainMenu.classList.toggle('active');
                mobileMenuBtn.textContent = mainMenu.classList.contains('active') ? '✕' : '☰';
            });
        }
        
        // Handle scrolling effects
        function handleScroll() {
            const nav = document.querySelector('.main-nav');
            const introSection = document.querySelector('.intro-section');
            
            window.addEventListener('scroll', () => {
                // Nav shrinking effect
                if (window.scrollY > 50) {
                    nav.classList.add('scrolled');
                } else {
                    nav.classList.remove('scrolled');
                }
                
                // Intro section fade in
                const introSectionPosition = introSection.getBoundingClientRect().top;
                const screenPosition = window.innerHeight / 1.3;
                
                if (introSectionPosition < screenPosition) {
                    introSection.classList.add('visible');
                }
            });
        }
        
        // Smooth scrolling for anchor links
        function setupSmoothScrolling() {
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    
                    const targetId = this.getAttribute('href');
                    if (targetId === '#') return;
                    
                    const targetElement = document.querySelector(targetId);
                    if (targetElement) {
                        window.scrollTo({
                            top: targetElement.offsetTop - 80,
                            behavior: 'smooth'
                        });
                    }
                });
            });
        }
        
        // Blood type popup info
        function setupBloodTypeInfo() {
            const bloodTypes = document.querySelectorAll('.blood-type');
            const popup = document.getElementById('bloodPopup');
            const popupClose = document.getElementById('popupClose');
            const popupBloodType = document.getElementById('popupBloodType');
            const popupReceiveList = document.getElementById('popupReceiveList');
            const popupDonateList = document.getElementById('popupDonateList');
            const popupDescription = document.getElementById('popupDescription');
            
            bloodTypes.forEach(type => {
                type.addEventListener('click', () => {
                    const bloodType = type.getAttribute('data-type');
                    const info = bloodTypeInfo[bloodType];
                    
                    // Populate popup with data
                    popupBloodType.textContent = bloodType;
                    popupReceiveList.innerHTML = '';
                    popupDonateList.innerHTML = '';
                    popupDescription.textContent = info.description;
                    
                    // Add receive list
                    info.receivesFrom.forEach(type => {
                        const li = document.createElement('li');
                        li.textContent = type;
                        popupReceiveList.appendChild(li);
                    });
                    
                    // Add donate list
                    info.donatesTo.forEach(type => {
                        const li = document.createElement('li');
                        li.textContent = type;
                        popupDonateList.appendChild(li);
                    });
                    
                    // Show popup
                    popup.classList.add('active');
                });
            });
            
            // Close popup on button click
            popupClose.addEventListener('click', () => {
                popup.classList.remove('active');
            });
            
            // Close popup on outside click
            popup.addEventListener('click', (e) => {
                if (e.target === popup) {
                    popup.classList.remove('active');
                }
            });
            
            // Close popup on ESC key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && popup.classList.contains('active')) {
                    popup.classList.remove('active');
                }
            });
        }
        
        // Animate count numbers
        function animateCounters() {
            const bloodTypes = document.querySelectorAll('.blood-type');
            
            const observerOptions = {
                threshold: 0.5
            };
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const percentText = entry.target.querySelector('p');
                        const percentValue = parseFloat(percentText.textContent);
                        
                        let startValue = 0;
                        const duration = 1500;
                        const startTime = performance.now();
                        
                        function updateCounter(currentTime) {
                            const elapsedTime = currentTime - startTime;
                            const progress = Math.min(elapsedTime / duration, 1);
                            const easeProgress = progress === 1 ? 1 : 1 - Math.pow(2, -10 * progress);
                            
                            const currentValue = (percentValue * easeProgress).toFixed(1);
                            percentText.textContent = `${currentValue}% of population`;
                            
                            if (progress < 1) {
                                requestAnimationFrame(updateCounter);
                            }
                        }
                        
                        requestAnimationFrame(updateCounter);
                        observer.unobserve(entry.target);
                    }
                });
            }, observerOptions);
            
            bloodTypes.forEach(type => {
                observer.observe(type);
            });
        }
        
        // Initialize everything when DOM is loaded
        document.addEventListener('DOMContentLoaded', () => {
            createBloodDrops();
            setupMobileMenu();
            handleScroll();
            setupSmoothScrolling();
            setupBloodTypeInfo();
            animateCounters();
            
            // Trigger scroll once to check initial position
            window.dispatchEvent(new Event('scroll'));
        });
    </script>
</body>
</html>