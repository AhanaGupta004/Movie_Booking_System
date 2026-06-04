<?php
/**
 * footer.php
 * This is the complete footer file with social media icons, logo, and styling.
 */
?>

<footer>
    <style>
        /* General Footer Styles */
        

        .footer-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .footer-section {
            flex: 1;
            min-width: 200px;
            margin: 10px 20px;
        }

        .footer-section h3 {
            font-size: 18px;
            margin-bottom: 15px;
            color: #000000;
        }

        .footer-section p {
            font-size: 14px;
            line-height: 1.6;
            margin: 10px 0;
            color: #555555;
        }

        .footer-section ul {
            list-style: none;
            padding: 0;
        }

        .footer-section ul li {
            margin: 10px 0;
        }

        .footer-section ul li a {
            color: #333333;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s ease;
        }

        .footer-section ul li a:hover {
            color: #007BFF;
        }

        /* Footer Logo */
        .footer-logo {
            width: 150px;
            margin-bottom: 15px;
        }

        /* Social Media Icons */
        .social-icons {
            display: flex;
            gap: 15px;
        }

        .social-icons a {
            color: #333333;
            font-size: 20px;
            transition: color 0.3s ease;
        }

        .social-icons a:hover {
            color: #007BFF;
        }

        /* Footer Bottom */
        footer {
    background-color: #ffffff;
    color: #333333;
    padding: 20px 20px; /* Reduced padding */
    font-family: 'Arial', sans-serif;
    border-top: 1px solid #e0e0e0;
}

.footer-bottom {
    text-align: center;
    margin-top: 15px; /* Reduced margin */
    padding-top: 10px; /* Reduced padding */
    border-top: 1px solid #e0e0e0;
    font-size: 14px;
    color: #555555;
}


        /* Responsive Design */
        @media (max-width: 768px) {
            .footer-container {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }

            .footer-section {
                margin: 20px 0;
            }
        }
    </style>

    <div class="footer-container">
        <!-- About Section -->
        <div class="footer-section">
            <img src="logo.jpg" alt="Company Logo" class="footer-logo">
            <p>We are a company dedicated to providing the best services to our customers. Join us on our journey to excellence.</p>
        </div>

        <!-- Quick Links Section -->
        <div class="footer-section">
            <h3>Quick Links</h3>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="about.php">About</a></li>
                <li><a href="services.php">Services</a></li>
                <li><a href="contact.php">Contact</a></li>
            </ul>
        </div>

        <!-- Contact Section -->
        <div class="footer-section">
            <h3>Contact Us</h3>
            <p>Email: info@yourcompany.com</p>
            <p>Phone: +123 456 7890</p>
            <p>Address: 123 Main St, City, Country</p>
        </div>

        <!-- Social Media Section -->
        <div class="footer-section">
            <h3>Follow Us</h3>
            <div class="social-icons">
                <a href="https://facebook.com/yourcompany" target="_blank"><i class="fab fa-facebook-f"></i></a>
                <a href="https://twitter.com/yourcompany" target="_blank"><i class="fab fa-twitter"></i></a>
                <a href="https://instagram.com/yourcompany" target="_blank"><i class="fab fa-instagram"></i></a>
                <a href="https://linkedin.com/yourcompany" target="_blank"><i class="fab fa-linkedin-in"></i></a>
            </div>
        </div>
    </div>

    <!-- Footer Bottom -->
    <div class="footer-bottom">
        <p>&copy; <?php echo date("Y"); ?> Your Company Name. All rights reserved.</p>
    </div>
</footer>

<!-- Include FontAwesome for Icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">