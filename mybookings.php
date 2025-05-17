<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get user's bookings
$stmt = $pdo->prepare("SELECT b.*, f.* FROM bookings b JOIN flights f ON b.flight_id = f.flight_id WHERE b.user_id = ? ORDER BY b.booking_date DESC");
$stmt->execute([$_SESSION['user_id']]);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - SkyTravel</title>
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
        <section class="bookings-container">
            <h2>My Bookings</h2>
            
            <?php if(empty($bookings)): ?>
                <p>You haven't made any bookings yet. <a href="flights.php">Search for flights</a> to get started.</p>
            <?php else: ?>
                <div class="bookings-list">
                    <?php foreach($bookings as $booking): ?>
                        <div class="booking-card">
                            <div class="booking-header">
                                <h3>Booking #SKY<?php echo str_pad($booking['booking_id'], 6, '0', STR_PAD_LEFT); ?></h3>
                                <span class="status-<?php echo $booking['status']; ?>"><?php echo ucfirst($booking['status']); ?></span>
                            </div>
                            
                            <div class="booking-details">
                                <div class="flight-info">
                                    <p><strong><?php echo htmlspecialchars($booking['airline']); ?> (<?php echo htmlspecialchars($booking['flight_number']); ?>)</strong></p>
                                    <p><?php echo htmlspecialchars($booking['departure_airport']); ?> to <?php echo htmlspecialchars($booking['arrival_airport']); ?></p>
                                    <p><?php echo date('M j, Y', strtotime($booking['departure_time'])); ?></p>
                                </div>
                                
                                <div class="booking-info">
                                    <p><strong>Passengers:</strong> <?php echo $booking['passengers']; ?></p>
                                    <p><strong>Total Paid:</strong> $<?php echo number_format($booking['total_price'], 2); ?></p>
                                    <p><strong>Booked on:</strong> <?php echo date('M j, Y', strtotime($booking['booking_date'])); ?></p>
                                </div>
                            </div>
                            
                            <div class="booking-actions">
                                <a href="booking_details.php?booking_id=<?php echo $booking['booking_id']; ?>" class="btn">View Details</a>
                                <?php if($booking['status'] == 'confirmed'): ?>
                                    <a href="cancel_booking.php?booking_id=<?php echo $booking['booking_id']; ?>" class="btn secondary">Cancel Booking</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <footer>
        <p>&copy; 2023 SkyTravel. All rights reserved.</p>
    </footer>
</body>
</html>
