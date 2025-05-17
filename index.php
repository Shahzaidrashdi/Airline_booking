<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SkyTravel - Book Your Flight</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <header>
        <nav>
            <div class="logo">SkyTravel</div>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="flights.php">Flights</a></li>
                <li><a href="mybookings.php">My Bookings</a></li>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li><a href="logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <main>
        <section class="hero">
            <div class="hero-content">
                <h1>Travel the World with SkyTravel</h1>
                <p>Find and book your perfect flight at the best prices</p>
                <a href="flights.php" class="btn">Search Flights</a>
            </div>
        </section>

        <section class="search-box">
            <h2>Find Your Flight</h2>
            <form action="flights.php" method="get">
                <div class="form-group">
                    <label for="from">From</label>
                    <input type="text" id="from" name="from" placeholder="City or Airport">
                </div>
                <div class="form-group">
                    <label for="to">To</label>
                    <input type="text" id="to" name="to" placeholder="City or Airport">
                </div>
                <div class="form-group">
                    <label for="departure">Departure</label>
                    <input type="date" id="departure" name="departure">
                </div>
                <div class="form-group">
                    <label for="passengers">Passengers</label>
                    <select id="passengers" name="passengers">
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5+</option>
                    </select>
                </div>
                <button type="submit" class="btn">Search Flights</button>
            </form>
        </section>

        <section class="features">
            <div class="feature">
                <img src="images/best-price.png" alt="Best Price">
                <h3>Best Price Guarantee</h3>
                <p>We guarantee the best prices for your flights.</p>
            </div>
            <div class="feature">
                <img src="images/customer-support.png" alt="24/7 Support">
                <h3>24/7 Customer Support</h3>
                <p>Our team is always ready to help you.</p>
            </div>
            <div class="feature">
                <img src="images/easy-booking.png" alt="Easy Booking">
                <h3>Easy Booking</h3>
                <p>Simple and fast booking process.</p>
            </div>
        </section>
    </main>

    <footer>
        <p>&copy; 2023 SkyTravel. All rights reserved.</p>
    </footer>
</body>
</html>
