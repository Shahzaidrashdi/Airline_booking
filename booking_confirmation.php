<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['booking_id'])) {
    header("Location: mybookings.php");
    exit();
}

$booking_id = (int)$_GET['booking_id'];

// Get booking details
$stmt = $pdo->prepare("SELECT b.*, f.* FROM bookings b JOIN flights f ON b.flight_id = f.flight_id WHERE b.booking_id = ? AND b.user_id = ?");
$stmt->execute([$booking_id, $_SESSION['user_id']]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$booking) {
    header("Location: mybookings.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation - SkyTravel</title>
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
        <section class="confirmation-container">
            <div class="confirmation-card">
                <div class="confirmation-header">
                    <h2>Booking Confirmed!</h2>
                    <p>Your booking reference is: <strong>SKY<?php echo str_pad($booking['booking_id'], 6, '0', STR_PAD_LEFT); ?></strong></p>
                </div>
                
                <div class="confirmation-details">
                    <div class="flight-info">
                        <h3>Flight Information</h3>
                        <p><strong>Airline:</strong> <?php echo htmlspecialchars($booking['airline']); ?></p>
                        <p><strong>Flight Number:</strong> <?php echo htmlspecialchars($booking['flight_number']); ?></p>
                        <p><strong>Route:</strong> <?php echo htmlspecialchars($booking['departure_airport']); ?> to <?php echo htmlspecialchars($booking['arrival_airport']); ?></p>
                        <p><strong>Departure:</strong> <?php echo date('M j, Y H:i', strtotime($booking['departure_time'])); ?></p>
                        <p><strong>Arrival:</strong> <?php echo date('M j, Y H:i', strtotime($booking['arrival_time'])); ?></p>
                    </div>
                    
                    <div class="booking-info">
                        <h3>Booking Details</h3>
                        <p><strong>Booking Date:</strong> <?php echo date('M j, Y H:i', strtotime($booking['booking_date'])); ?></p>
                        <p><strong>Passengers:</strong> <?php echo $booking['passengers']; ?></p>
                        <p><strong>Total Price:</strong> $<?php echo number_format($booking['total_price'], 2); ?></p>
                        <p><strong>Status:</strong> <span class="status-confirmed"><?php echo ucfirst($booking['status']); ?></span></p>
                    </div>
                </div>
                
                <div class="confirmation-actions">
                    <a href="mybookings.php" class="btn">View All Bookings</a>
                    <a href="index.php" class="btn secondary">Back to Home</a>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <p>&copy; 2023 SkyTravel. All rights reserved.</p>
    </footer>
</body>
</html>
