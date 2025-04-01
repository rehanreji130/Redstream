<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us | Blood Availability</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #e63946;
            --primary-light: #fce8ea;
            --secondary: #1d3557;
            --dark: #2b2d42;
            --light: #ffffff;
            --gray: #f8f9fa;
            --border-radius: 16px;
            --box-shadow: 0 12px 32px rgba(0,0,0,0.08);
            --transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
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
        
        .main-header {
            background: linear-gradient(135deg, var(--primary), #c1121f);
            color: var(--light);
            padding: 5rem 0 4rem;
            text-align: center;
            position: relative;
            overflow: hidden;
            clip-path: polygon(0 0, 100% 0, 100% 90%, 0 100%);
            margin-bottom: 3rem;
        }
        
        .header-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            position: relative;
            z-index: 2;
        }
        
        .header-container h1 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            letter-spacing: -0.5px;
        }
        
        .header-container p {
            font-size: 1.25rem;
            max-width: 700px;
            margin: 0 auto;
            opacity: 0.9;
            font-weight: 300;
        }
        
        .content-section {
            max-width: 1200px;
            margin: -3rem auto 5rem;
            padding: 0 2rem;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            position: relative;
            z-index: 3;
        }
        
        .about-section, .team-section {
            background-color: var(--light);
            border-radius: var(--border-radius);
            padding: 3rem;
            box-shadow: var(--box-shadow);
            border: 1px solid rgba(0,0,0,0.03);
        }
        
        .section-title {
            color: var(--secondary);
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 2rem;
            position: relative;
            letter-spacing: -0.5px;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: -12px;
            left: 0;
            width: 50px;
            height: 4px;
            background: var(--primary);
            border-radius: 2px;
        }
        
        .about-section p {
            margin-bottom: 1.75rem;
            color: #555;
            font-size: 1.05rem;
            line-height: 1.8;
        }
        
        .feature-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
            margin: 2.5rem 0;
        }
        
        .feature-card {
            background: var(--gray);
            border-radius: 12px;
            padding: 1.75rem;
            transition: var(--transition);
            border-left: 4px solid var(--primary);
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        }
        
        .feature-card h3 {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
            color: var(--secondary);
            display: flex;
            align-items: center;
        }
        
        .feature-card h3 i {
            margin-right: 12px;
            color: var(--primary);
            font-size: 1.4rem;
        }
        
        .feature-card p {
            margin-bottom: 0;
            font-size: 0.95rem;
            color: #666;
            line-height: 1.7;
        }
        
        .highlight {
            background-color: var(--primary-light);
            color: var(--primary);
            padding: 0.25rem 0.75rem;
            border-radius: 6px;
            font-weight: 500;
            display: inline-block;
        }
        
        /* Team Section Styles */
        .team-members {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
            margin-top: 2.5rem;
        }
        
        .team-member {
            background: var(--gray);
            padding: 2rem;
            border-radius: 12px;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(0,0,0,0.05);
        }
        
        .team-member::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: var(--primary);
        }
        
        .team-member:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        }
        
        .member-name {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--secondary);
            margin-bottom: 0.25rem;
            letter-spacing: -0.5px;
        }
        
        .member-role {
            color: var(--primary);
            font-size: 0.95rem;
            font-weight: 500;
            margin-bottom: 1.25rem;
            display: inline-block;
            background: var(--primary-light);
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
        }
        
        .member-bio {
            color: #666;
            font-size: 0.95rem;
            line-height: 1.7;
            margin-bottom: 1.5rem;
        }
        
        .social-link {
            color: var(--secondary);
            font-size: 1.1rem;
            transition: var(--transition);
            margin-right: 1rem;
        }
        
        .social-link:hover {
            color: var(--primary);
            transform: scale(1.1);
        }
        
        .main-footer {
            background: linear-gradient(135deg, var(--secondary), #0d1b2a);
            color: var(--light);
            padding: 4rem 0;
            text-align: center;
            clip-path: polygon(0 10%, 100% 0, 100% 100%, 0% 100%);
        }
        
        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }
        
        .footer-logo {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 1rem;
            display: inline-block;
        }
        
        .footer-links {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 1.5rem;
            margin: 2rem 0;
        }
        
        .footer-links a {
            color: var(--light);
            text-decoration: none;
            font-weight: 400;
            transition: var(--transition);
            opacity: 0.8;
            font-size: 0.95rem;
        }
        
        .footer-links a:hover {
            opacity: 1;
            text-decoration: underline;
        }
        
        .copyright {
            font-size: 0.9rem;
            opacity: 0.7;
            margin-top: 2rem;
        }
        
        /* Responsive Styles */
        @media (max-width: 1024px) {
            .content-section {
                grid-template-columns: 1fr;
                gap: 2rem;
            }
            
            .header-container h1 {
                font-size: 2.5rem;
            }
        }
        
        @media (max-width: 768px) {
            .main-header {
                padding: 4rem 0 3rem;
                clip-path: polygon(0 0, 100% 0, 100% 95%, 0 100%);
            }
            
            .header-container h1 {
                font-size: 2rem;
            }
            
            .header-container p {
                font-size: 1.1rem;
            }
            
            .content-section {
                margin-top: -2rem;
            }
            
            .about-section, .team-section {
                padding: 2rem;
            }
            
            .section-title {
                font-size: 1.5rem;
            }
        }
        
        /* Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .about-section, .team-section {
            animation: fadeIn 0.6s ease-out forwards;
        }
        
        .team-section {
            animation-delay: 0.2s;
        }
    </style>
</head>
<body>
    <header class="main-header">
        <div class="header-container">
            <h1>About Blood Availability</h1>
            <p>Revolutionizing blood donation systems through innovative technology and community engagement</p>
        </div>
    </header>

    <div class="content-section">
        <section class="about-section">
            <h2 class="section-title">Our Vision</h2>
            <p>
                At Blood Availability, we're transforming the blood donation ecosystem by creating <span class="highlight">real-time connections</span> between hospitals, donors, and recipients. Our platform leverages cutting-edge technology to ensure that blood reaches those in need <span class="highlight">when it matters most</span>.
            </p>
            
            <div class="feature-grid">
                <div class="feature-card">
                    <h3><i class="fas fa-hospital-alt"></i> Hospital Solutions</h3>
                    <p>Advanced donor management system with automated notifications, inventory tracking, and predictive analytics to anticipate blood needs before shortages occur.</p>
                </div>
                
                <div class="feature-card">
                    <h3><i class="fas fa-search-location"></i> Recipient Access</h3>
                    <p>Intelligent location-based search with real-time availability updates and route optimization to the nearest blood banks with matching supply.</p>
                </div>
                
                <div class="feature-card">
                    <h3><i class="fas fa-bell"></i> Donor Engagement</h3>
                    <p>Personalized notification system with donation history tracking, health insights, and community impact reporting to encourage regular donations.</p>
                </div>
            </div>
        </section>

        <section class="team-section">
            <h2 class="section-title">Leadership Team</h2>
            <p>The brilliant minds driving our mission forward</p>
            
            <div class="team-members">
                <div class="team-member">
                    <div class="member-name">Rehan Reji</div>
                    <div class="member-role">Project Lead & Developer</div>
                    <a href="https://www.linkedin.com/in/rehan-reji-620268278/" class="social-link" target="_blank">
                        <i class="fab fa-linkedin"></i>
                    </a>
                </div>
                
                <div class="team-member">
                    <div class="member-name">Rithika Jobi</div>
                    <div class="member-role">Backend Developer</div>
                    <a href="https://www.linkedin.com/in/rithika-jobi-32660a355/" class="social-link" target="_blank">
                        <i class="fab fa-linkedin"></i>
                    </a>
                </div>
                
                <div class="team-member">
                    <div class="member-name">Mariam C Wilson</div>
                    <div class="member-role">Frontend Developer</div>
                    <a href="https://www.linkedin.com/in/mariam-c-wilson-921509259/" class="social-link" target="_blank">
                        
                        <i class="fab fa-linkedin"></i>
                    </a>
                </div>
                
                <div class="team-member">
                    <div class="member-name">Jomiya John</div>
                    <div class="member-role">Database Specialist</div>
                    <a href="https://www.linkedin.com/in/jomiya-john-331960277/" class="social-link" target="_blank">
                        <i class="fab fa-linkedin"></i>
                    </a>
                </div>
            </div>
        </section>
    </div>

    <footer class="main-footer">
        <div class="footer-container">
            <div class="footer-logo">Blood Availability</div>
            <p>Connecting compassion with technology to save lives</p>
            <div class="footer-links">
                <a href="privacy-policy.php">Privacy</a>
                <a href="terms-of-service.php">Terms</a>
            </div>
            <div class="copyright">
                &copy; 2025 Blood Availability. All rights reserved.
            </div>
        </div>
    </footer>
</body>
</html>