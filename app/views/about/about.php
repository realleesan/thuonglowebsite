<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Eduma</title>
    <link rel="stylesheet" href="/assets/fonts/awesome-5x/css/all.min.css">
    <link rel="stylesheet" href="/assets/fonts/flaticon/flaticon.css">
    <link rel="stylesheet" href="/assets/fonts/ion-icons/css/ionicons.min.css">
    <link rel="stylesheet" href="/assets/fonts/pe-icon-7/css/pe-icon-7-stroke.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }

        /* Header */
        .header {
            background: #4A90E2;
            color: white;
            padding: 15px 0;
            position: relative;
        }

        .header-top {
            background: #3A7BD5;
            padding: 8px 0;
            font-size: 12px;
            text-align: center;
        }

        .header-main {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .logo {
            display: flex;
            align-items: center;
            font-size: 24px;
            font-weight: bold;
        }

        .logo i {
            margin-right: 10px;
            color: #FFD700;
        }

        .nav-menu {
            display: flex;
            list-style: none;
            gap: 30px;
        }

        .nav-menu a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }

        .nav-menu a:hover {
            color: #FFD700;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .search-btn, .login-btn {
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            padding: 8px 15px;
            border-radius: 4px;
            transition: background 0.3s;
        }

        .login-btn {
            background: #FFD700;
            color: #333;
            font-weight: 500;
        }

        .login-btn:hover {
            background: #FFC107;
        }

        /* Breadcrumb */
        .breadcrumb {
            background: #bfdbfe;
            padding: 15px 0;
        }

        .breadcrumb-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            font-size: 14px;
        }

        .breadcrumb a {
            color: #666;
            text-decoration: none;
        }

        .breadcrumb a:hover {
            color: #4A90E2;
        }

        /* Hero Section */
        .hero-section {
            background: white;
            color: #333;
            text-align: center;
            padding: 80px 20px;
        }

        .hero-content h1 {
            font-size: 48px;
            font-weight: 700;
            margin-bottom: 20px;
            line-height: 1.2;
        }

        .hero-content h1 .highlight {
            color: #4A90E2;
        }

        .hero-content p {
            font-size: 18px;
            max-width: 600px;
            margin: 0 auto 40px;
            opacity: 0.9;
        }

        /* Stats Section */
        .stats-section {
            background: #356df1;
            color: white;
            padding: 60px 20px;
        }

        .stats-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 60px;
            align-items: center;
        }

        .stats-text h2 {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .stats-text p {
            font-size: 16px;
            opacity: 0.9;
            line-height: 1.6;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.1);
            padding: 30px 20px;
            border-radius: 8px;
            text-align: center;
            backdrop-filter: blur(10px);
        }

        .stat-card:nth-child(1) {
            background: #e1e9fd;
            color: #333;
        }

        .stat-card:nth-child(2) {
            background: #d4f5e7;
            color: #333;
        }

        .stat-card:nth-child(3) {
            background: #ffdf9e;
            color: #333;
        }

        .stat-card:nth-child(4) {
            background: #ffdaf5;
            color: #333;
        }

        .stat-card.highlight {
            background: #FFD700;
            color: #333;
        }

        .stat-number {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .stat-label {
            font-size: 14px;
            opacity: 0.8;
        }

        /* What Makes Us Special */
        .special-section {
            padding: 80px 20px;
            background: white;
        }

        .special-container {
            max-width: 1200px;
            margin: 0 auto;
            text-align: center;
        }

        .special-container h2 {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .special-container h2 .highlight {
            color: #4A90E2;
        }

        .special-container > p {
            font-size: 16px;
            color: #666;
            max-width: 600px;
            margin: 0 auto 60px;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 40px;
            margin-bottom: 80px;
        }

        .feature-card {
            text-align: left;
        }

        .feature-image {
            width: 100%;
            height: 200px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 20px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #666;
            font-size: 48px;
        }

        .feature-card h3 {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 15px;
            color: #333;
        }

        .feature-card p {
            font-size: 14px;
            color: #666;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-top">
            You're welcome to our site. We have a 15% discount on all courses
        </div>
        <div class="header-main">
            <div class="logo">
                <i class="fas fa-graduation-cap"></i>
                Eduma
            </div>
            <nav>
                <ul class="nav-menu">
                    <li><a href="/">Home</a></li>
                    <li><a href="/courses">Courses</a></li>
                    <li><a href="/events">Events</a></li>
                    <li><a href="/pages">Pages</a></li>
                    <li><a href="/blog">Blog</a></li>
                    <li><a href="/contact">Contact</a></li>
                </ul>
            </nav>
            <div class="header-actions">
                <button class="search-btn"><i class="fas fa-search"></i></button>
                <button class="login-btn">Login</button>
            </div>
        </div>
    </header>

    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <div class="breadcrumb-content">
            <a href="/">Home</a> / <span>About Us</span>
        </div>
    </div>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-content">
            <h1>Learn <span class="highlight">with passion</span><br>to live <span class="highlight">with purpose</span></h1>
            <p>We help you unlock your potential with our comprehensive courses and expert guidance. Join thousands of students who have transformed their careers with us.</p>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="stats-container">
            <div class="stats-text">
                <h2>We Just Keep Growing</h2>
                <p>At Eduma, we're proud to be one of the leading online education platforms. Our commitment to quality education and student success has helped us build a thriving community of learners and educators.</p>
            </div>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number">54832</div>
                    <div class="stat-label">Students Enrolled</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">10223</div>
                    <div class="stat-label">Completed Courses</div>
                </div>
                <div class="stat-card highlight">
                    <div class="stat-number">25678</div>
                    <div class="stat-label">Students Enrolled</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">2678</div>
                    <div class="stat-label">Certified Teachers</div>
                </div>
            </div>
        </div>
    </section>

    <!-- What Makes Us Special -->
    <section class="special-section">
        <div class="special-container">
            <h2>What Make Us <span class="highlight">Special</span> ?</h2>
            <p>We combine cutting-edge technology with proven educational methods to deliver an exceptional learning experience that adapts to your needs and helps you achieve your goals.</p>
            
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-image">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3>Why We Are ?</h3>
                    <p>We are a team of passionate educators and technology experts dedicated to making quality education accessible to everyone, everywhere. Our mission is to empower learners with the skills they need to succeed in today's rapidly changing world.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-image">
                        <i class="fas fa-lightbulb"></i>
                    </div>
                    <h3>What we do ?</h3>
                    <p>We create comprehensive online courses, provide personalized learning experiences, and offer expert guidance to help students achieve their educational and career goals. Our platform combines interactive content with real-world applications.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-image">
                        <i class="fas fa-rocket"></i>
                    </div>
                    <h3>How it work ?</h3>
                    <p>Our innovative learning management system adapts to your pace and learning style. Through interactive lessons, practical exercises, and continuous assessment, we ensure you master each concept before moving forward.</p>
                </div>
            </div>
        </div>
    </section>
    <!-- Testimonial Section -->
    <section class="testimonial-section">
        <div class="testimonial-container">
            <div class="testimonial-image">
                <img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzAwIiBoZWlnaHQ9IjQwMCIgdmlld0JveD0iMCAwIDMwMCA0MDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIzMDAiIGhlaWdodD0iNDAwIiBmaWxsPSIjRjhGOUZBIi8+CjxjaXJjbGUgY3g9IjE1MCIgY3k9IjEyMCIgcj0iNDAiIGZpbGw9IiM0QTkwRTIiLz4KPHJlY3QgeD0iMTAwIiB5PSIyMDAiIHdpZHRoPSIxMDAiIGhlaWdodD0iMTAwIiBmaWxsPSIjNEE5MEUyIi8+Cjx0ZXh0IHg9IjE1MCIgeT0iMzUwIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBmaWxsPSIjNjY2IiBmb250LXNpemU9IjE0Ij5UZXN0aW1vbmlhbCBJbWFnZTwvdGV4dD4KPC9zdmc+" alt="Testimonial">
            </div>
            <div class="testimonial-content">
                <div class="quote-icon">"</div>
                <p class="testimonial-text">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris.</p>
                <div class="testimonial-author">John Smith, Co-Founder</div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="cta-container">
            <div class="cta-tag">WE ARE HIRING</div>
            <h2 class="cta-title">If You're Looking To Make An Impact,<br>We're Looking For You</h2>
            
            <div class="cta-features">
                <div class="cta-feature">
                    <div class="cta-feature-icon">
                        <svg width="80" height="80" viewBox="0 0 60 60" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M54.9141 26.3326L54.2891 25.25C54.1172 24.954 53.7344 24.8527 53.4375 25.0241L45 29.9309V28.6925C45 26.6285 43.3203 24.954 41.25 24.954H30V29.7985C30 31.0446 29.1484 32.1973 27.9219 32.392C26.3594 32.6491 25 31.4418 25 29.9309V22.5863C25 21.5115 25.5547 20.5145 26.4688 19.946L29.0781 18.3182C29.9688 17.7652 31 17.4692 32.0547 17.4692H40.1172L49.375 12.2509C49.6719 12.0795 49.7813 11.6979 49.6094 11.4019L48.9844 10.3115C48.8125 10.0156 48.4297 9.90654 48.1328 10.0779L39.4609 14.9847H32.0625C30.5391 14.9847 29.0469 15.413 27.7578 16.2153L25.1406 17.8431C23.7734 18.6998 22.8985 20.0706 22.625 21.6205L17.7422 24.5023C16.0547 25.5148 15 27.3606 15 29.3234V32.3297L5.31251 37.8129C5.01564 37.9842 4.90626 38.3658 5.07814 38.6618L5.70314 39.7444C5.87501 40.0404 6.25783 40.1494 6.5547 39.9781L17.5 33.7784V29.3234C17.5 28.233 18.086 27.2049 19.0156 26.6441L22.5 24.5879V29.7206C22.5 32.3219 24.3985 34.6507 27 34.9C29.9766 35.1881 32.5 32.8516 32.5 29.9387V27.4463H41.25C41.9375 27.4463 42.5 28.0071 42.5 28.6925V31.1848C42.5 31.8702 41.9375 32.431 41.25 32.431H39.375V35.2349C39.375 36.777 38.125 38.0231 36.5781 38.0231H35.3203V39.2693C35.3203 40.9984 33.9141 42.4003 32.1797 42.4003H23.6328L14.6016 47.642C14.3047 47.8133 14.2031 48.195 14.375 48.4909L15 49.5658C15.1719 49.8617 15.5547 49.963 15.8516 49.7916L24.3047 44.8926H32.1797C34.8984 44.8926 37.1797 42.9611 37.711 40.3987C40.0859 39.8768 41.875 37.7583 41.875 35.2349V34.8688C42.9375 34.6897 43.7969 34.0432 44.3594 33.1787L54.6875 27.1815C54.9844 27.0102 55.086 26.6285 54.9141 26.3326Z" fill="#356DF1"/>
                        </svg>
                    </div>
                    <h4>We work together as a friendly, supportive team</h4>
                    <p>We strive to find the best solution, not the easy one</p>
                </div>
                <div class="cta-feature">
                    <div class="cta-feature-icon">
                        <svg width="80" height="80" viewBox="0 0 60 60" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M30 14.9999C25.1719 15.0078 21.2578 18.9218 21.25 23.7499C21.25 24.4374 21.8125 24.9999 22.5 24.9999C23.1875 24.9999 23.75 24.4374 23.75 23.7499C23.75 20.2968 26.5469 17.4999 30 17.4999C30.6875 17.4999 31.25 16.9374 31.25 16.2499C31.25 15.5624 30.6875 14.9999 30 14.9999ZM30 9.99995C21.9609 9.99995 16.2422 16.5312 16.25 23.7656C16.25 27.0859 17.4609 30.2968 19.6562 32.7968C21.6641 35.0781 23.5859 38.8046 23.75 39.9999L23.7578 45.8749C23.7578 46.1171 23.8281 46.3593 23.9688 46.5624L25.8828 49.4374C26.1172 49.789 26.5078 49.9921 26.9219 49.9921H33.0859C33.5078 49.9921 33.8984 49.7812 34.125 49.4374L36.0391 46.5546C36.1719 46.3515 36.25 46.1093 36.25 45.8671V39.9999C36.4219 38.7734 38.3594 35.0624 40.3438 32.7968C45.3438 27.0859 44.7656 18.4062 39.0547 13.4062C36.5547 11.2109 33.3359 9.99995 30 9.99995ZM33.7422 45.4921L32.4062 47.4999H27.5859L26.25 45.4921V44.9999H33.75L33.7422 45.4921ZM33.75 42.4999H26.25L26.2422 39.9999H33.75V42.4999ZM38.4688 31.1484C37.375 32.3984 35.6328 34.9062 34.5156 37.4999H25.4844C24.3672 34.9062 22.625 32.3984 21.5312 31.1484C19.7344 29.1015 18.75 26.4687 18.75 23.7499C18.7422 17.7343 23.4688 12.4999 30 12.4999C36.2031 12.4999 41.25 17.5468 41.25 23.7499C41.25 26.4687 40.2578 29.1015 38.4688 31.1484ZM12.5 23.7499C12.5 23.0624 11.9375 22.4999 11.25 22.4999H6.25C5.5625 22.4999 5 23.0624 5 23.7499C5 24.4374 5.5625 24.9999 6.25 24.9999H11.25C11.9375 24.9999 12.5 24.4374 12.5 23.7499ZM46.25 14.9999C46.4453 14.9999 46.6328 14.9531 46.8125 14.8671L51.8125 12.3671C52.4297 12.0546 52.6797 11.3046 52.375 10.6874C52.0703 10.0703 51.3125 9.82026 50.6953 10.1249L45.6953 12.6249C45.0781 12.9374 44.8281 13.6874 45.1328 14.3046C45.3438 14.7343 45.7734 14.9999 46.25 14.9999ZM53.75 22.4999H48.75C48.0625 22.4999 47.5 23.0624 47.5 23.7499C47.5 24.4374 48.0625 24.9999 48.75 24.9999H53.75C54.4375 24.9999 55 24.4374 55 23.7499C55 23.0624 54.4375 22.4999 53.75 22.4999ZM14.3125 12.6328L9.3125 10.1328C8.69531 9.82026 7.94531 10.0703 7.63281 10.6874C7.32031 11.3046 7.57031 12.0546 8.1875 12.3671L13.1875 14.8671C13.3594 14.9531 13.5547 14.9999 13.75 14.9999C14.4375 14.9999 15 14.4374 15 13.7499C15 13.2734 14.7344 12.8437 14.3125 12.6328ZM51.8125 35.1328L46.8125 32.6328C46.1953 32.3203 45.4453 32.5703 45.1328 33.1953C44.8203 33.8124 45.0703 34.5624 45.6953 34.8749L50.6953 37.3749C51.3125 37.6874 52.0625 37.4374 52.375 36.8124C52.6797 36.1953 52.4297 35.4374 51.8125 35.1328ZM13.75 32.4999C13.5547 32.4999 13.3672 32.5468 13.1875 32.6328L8.1875 35.1328C7.57031 35.4453 7.32031 36.1953 7.625 36.8124C7.9375 37.4296 8.6875 37.6796 9.30469 37.3749L14.3047 34.8749C14.9219 34.5703 15.1719 33.8203 14.8672 33.1953C14.6562 32.7656 14.2266 32.4999 13.75 32.4999Z" fill="#356DF1"/>
                        </svg>
                    </div>
                    <h4>We strive to find the best solution, not the easy one</h4>
                    <p>We go the extra mile to deliver work we're proud of</p>
                </div>
                <div class="cta-feature">
                    <div class="cta-feature-icon">
                        <svg width="80" height="80" viewBox="0 0 60 60" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M54.8125 25.3984L49.0703 11.5468C48.5391 10.2734 47.0781 9.664 45.8047 10.1953L37.3203 13.7109C36.1328 14.2031 35.5625 15.5078 35.9141 16.7187L10.8672 28.8593C10.1797 29.1874 9.84375 29.9218 10.0937 30.5312L10.7734 32.1796L5.77344 34.2499C5.13281 34.5156 4.83594 35.2421 5.09375 35.8827L6.66406 39.664C6.92969 40.3046 7.65625 40.6015 8.29687 40.3437L13.3047 38.2656L13.9844 39.9218C14.3047 40.6874 15.1875 40.7343 15.7109 40.5546L26.1719 36.9296C26.2734 37.0468 26.3984 37.1484 26.5078 37.2577L22.5312 49.1796C22.4219 49.5078 22.6016 49.8593 22.9297 49.9687C22.9922 49.9921 23.0625 49.9999 23.125 49.9999H24.4375C24.7031 49.9999 24.9453 49.8281 25.0312 49.5703L28.7031 38.5546C29.6719 38.8203 30.3984 38.7968 31.2891 38.5546L34.9609 49.5703C35.0469 49.8281 35.2812 49.9999 35.5547 49.9999H36.875C37.2188 49.9999 37.5 49.7187 37.5 49.3749C37.5 49.3046 37.4922 49.2421 37.4688 49.1796L33.5078 37.3046C34.4297 36.3984 34.9531 35.164 34.9844 33.8749L42.0547 31.4218C42.5781 32.0859 43.7031 32.7109 44.9688 32.1874L53.4531 28.6718C54.7344 28.1406 55.3359 26.6796 54.8125 25.3984ZM8.49219 37.5546L7.88281 36.0781L11.7344 34.4843L12.3437 35.9609L8.49219 37.5546ZM30 36.2499C28.6172 36.2499 27.5 35.1328 27.5 33.7499C27.5 32.3671 28.6172 31.2499 30 31.2499C31.3828 31.2499 32.5 32.3671 32.5 33.7499C32.5 35.1328 31.3828 36.2499 30 36.2499ZM34.3984 31.4218C33.1406 28.9765 30.1406 28.0156 27.6953 29.2734C25.7266 30.289 24.6641 32.4765 25.0859 34.6484L15.8359 37.8593L12.8594 30.664L36.8281 19.039L41.0078 29.1249L34.3984 31.4218ZM44.0156 29.8749L38.2734 16.0156L46.7578 12.4999L52.5 26.3593L44.0156 29.8749Z" fill="#356DF1"/>
                        </svg>
                    </div>
                    <h4>We go the extra mile to deliver work we're proud of</h4>
                    <p>We put our customers at the heart of everything we do</p>
                </div>
                <div class="cta-feature">
                    <div class="cta-feature-icon">
                        <svg width="80" height="80" viewBox="0 0 60 60" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M46.1172 14.8984C41.8594 11.2734 35.4922 11.8749 31.5391 15.9531L30 17.5468L28.4609 15.9609C25.2734 12.664 18.8438 10.6796 13.8828 14.8984C8.97657 19.0859 8.71876 26.6015 13.1094 31.1328L28.2266 46.7421C28.7109 47.2421 29.3516 47.5 29.9922 47.5C30.6328 47.5 31.2735 47.25 31.7578 46.7421L46.875 31.1328C51.2813 26.6015 51.0234 19.0859 46.1172 14.8984ZM45.0938 29.3984L30.0313 45.0078L14.9063 29.3984C11.9063 26.3046 11.2813 20.4062 15.5078 16.8046C19.7891 13.1484 24.8203 15.7968 26.6641 17.7031L30 21.1484L33.336 17.7031C35.1485 15.8281 40.2266 13.1718 44.4922 16.8046C48.7109 20.3984 48.0938 26.2968 45.0938 29.3984Z" fill="#356DF1"/>
                        </svg>
                    </div>
                    <h4>We put our customers at the heart of everything we do</h4>
                    <p>Flexible schedules and comprehensive benefits package</p>
                </div>
            </div>
            
            <button class="cta-button" onclick="openModal()">See Open Positions</button>
        </div>
    </section>

    <!-- Modal for Agent Registration -->
    <div id="agentModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span class="close" onclick="closeModal()">&times;</span>
                <h2>Đăng Ký Làm Agent</h2>
            </div>
            <div class="modal-body">
                <form id="agentForm">
                    <div class="form-group">
                        <label for="fullName">Họ và Tên *</label>
                        <input type="text" id="fullName" name="fullName" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Số Điện Thoại *</label>
                        <input type="tel" id="phone" name="phone" required>
                    </div>
                    <div class="form-group">
                        <label for="address">Địa Chỉ *</label>
                        <textarea id="address" name="address" rows="3" required></textarea>
                    </div>
                    <button type="submit" class="submit-btn">Gửi Phê Duyệt</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-section">
                <div class="logo">
                    <i class="fas fa-graduation-cap"></i>
                    Eduma
                </div>
                <p style="margin-top: 15px; color: #bdc3c7; font-size: 14px;">Empowering learners worldwide with quality education and innovative learning solutions.</p>
            </div>
            <div class="footer-section">
                <h3>Categories</h3>
                <ul>
                    <li><a href="#">Design</a></li>
                    <li><a href="#">Development</a></li>
                    <li><a href="#">Marketing</a></li>
                    <li><a href="#">Business</a></li>
                    <li><a href="#">Lifestyle</a></li>
                    <li><a href="#">Photography</a></li>
                    <li><a href="#">Music</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Links</h3>
                <ul>
                    <li><a href="#">About Us</a></li>
                    <li><a href="#">Contact</a></li>
                    <li><a href="#">News & Blog</a></li>
                    <li><a href="#">Library</a></li>
                    <li><a href="#">Career</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Company</h3>
                <ul>
                    <li><a href="#">About Us</a></li>
                    <li><a href="#">Contact</a></li>
                    <li><a href="#">News & Blog</a></li>
                    <li><a href="#">Library</a></li>
                    <li><a href="#">Career</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <div>© 2024 Eduma. All Rights Reserved.</div>
            <div class="social-links">
                <a href="#"><i class="fab fa-facebook"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-linkedin"></i></a>
                <a href="#"><i class="fab fa-youtube"></i></a>
            </div>
        </div>
    </footer>

    <style>
        /* Additional Styles for Testimonial and Modal */
        .testimonial-section {
            background: #f8f9fa;
            padding: 80px 20px;
        }

        .testimonial-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 60px;
            align-items: center;
        }

        .testimonial-image {
            text-align: center;
        }

        .testimonial-image img {
            width: 300px;
            height: 400px;
            object-fit: cover;
            border-radius: 8px;
        }

        .testimonial-content {
            padding-left: 40px;
        }

        .quote-icon {
            font-size: 120px;
            color: #4A90E2;
            margin-bottom: 5px;
        }

        .testimonial-text {
            font-size: 18px;
            line-height: 1.6;
            color: #333;
            margin-bottom: 30px;
            font-style: italic;
        }

        .testimonial-author {
            font-size: 16px;
            font-weight: 600;
            color: #333;
        }

        /* CTA Section */
        .cta-section {
            background: white;
            padding: 80px 20px;
            text-align: center;
        }

        .cta-container {
            max-width: 800px;
            margin: 0 auto;
        }

        .cta-tag {
            color: #4A90E2;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 20px;
        }

        .cta-title {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 40px;
            color: #333;
        }

        .cta-features {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 40px;
            margin-bottom: 50px;
        }

        .cta-feature {
            text-align: center;
        }

        .cta-feature-icon {
            width: 80px;
            height: 80px;
            background: transparent;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 5px;
            color: #4A90E2;
            font-size: 32px;
        }

        .cta-feature h4 {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 10px;
            color: #333;
        }

        .cta-feature p {
            font-size: 14px;
            color: #666;
            line-height: 1.5;
        }

        .cta-button {
            background: #4A90E2;
            color: white;
            padding: 15px 40px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }

        .cta-button:hover {
            background: #357ABD;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 0;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
            position: relative;
        }

        .modal-header {
            background: #4A90E2;
            color: white;
            padding: 20px;
            border-radius: 8px 8px 0 0;
            text-align: center;
        }

        .modal-header h2 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }

        .close {
            position: absolute;
            right: 15px;
            top: 15px;
            color: white;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            opacity: 0.7;
        }

        .modal-body {
            padding: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #4A90E2;
        }

        .submit-btn {
            background: #4A90E2;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: background 0.3s;
        }

        .submit-btn:hover {
            background: #357ABD;
        }

        /* Footer */
        .footer {
            background: #2c3e50;
            color: white;
            padding: 60px 20px 20px;
        }

        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 40px;
            margin-bottom: 40px;
        }

        .footer-section h3 {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            color: white;
        }

        .footer-section ul {
            list-style: none;
        }

        .footer-section ul li {
            margin-bottom: 10px;
        }

        .footer-section ul li a {
            color: #bdc3c7;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s;
        }

        .footer-section ul li a:hover {
            color: white;
        }

        .footer-bottom {
            border-top: 1px solid #34495e;
            padding-top: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 14px;
            color: #bdc3c7;
        }

        .social-links {
            display: flex;
            gap: 15px;
        }

        .social-links a {
            color: #bdc3c7;
            font-size: 18px;
            transition: color 0.3s;
        }

        .social-links a:hover {
            color: white;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header-main {
                flex-direction: column;
                gap: 20px;
            }

            .nav-menu {
                flex-direction: column;
                gap: 15px;
            }

            .hero-content h1 {
                font-size: 32px;
            }

            .stats-container {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .features-grid {
                grid-template-columns: 1fr;
            }

            .testimonial-container {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .testimonial-content {
                padding-left: 0;
            }

            .cta-features {
                grid-template-columns: repeat(2, 1fr);
            }

            .footer-container {
                grid-template-columns: repeat(2, 1fr);
            }

            .footer-bottom {
                flex-direction: column;
                gap: 15px;
            }
        }
    </style>

    <script>
        function openModal() {
            document.getElementById('agentModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('agentModal').style.display = 'none';
        }

        // Close modal when clicking outside of it
        window.onclick = function(event) {
            var modal = document.getElementById('agentModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }

        // Handle form submission
        document.getElementById('agentForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get form data
            var formData = new FormData(this);
            var data = {};
            for (var pair of formData.entries()) {
                data[pair[0]] = pair[1];
            }
            
            // Here you would typically send the data to your server
            console.log('Agent registration data:', data);
            
            // Show success message
            alert('Đăng ký thành công! Chúng tôi sẽ liên hệ với bạn sớm nhất có thể.');
            
            // Close modal and reset form
            closeModal();
            this.reset();
        });
    </script>
</body>
</html>