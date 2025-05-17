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
    <title>Booking Details - SkyTravel</title>
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
        <section class="booking-details-container">
            <div class="booking-header">
                <h2>Booking Details</h2>
                <p>Reference #SKY<?php echo str_pad($booking['booking_id'], 6, '0', STR_PAD_LEFT); ?></p>
                <span class="status-<?php echo $booking['status']; ?>"><?php echo ucfirst($booking['status']); ?></span>
            </div>
            
            <div class="booking-content">
                <div class="flight-details">
                    <h3>Flight Information</h3>
                    <div class="detail-row">
                        <span class="detail-label">Airline:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($booking['airline']); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Flight Number:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($booking['flight_number']); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">From:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($booking['departure_airport']); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">To:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($booking['arrival_airport']); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Departure:</span>
                        <span class="detail-value"><?php echo date('M j, Y H:i', strtotime($booking['departure_time'])); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Arrival:</span>
                        <span class="detail-value"><?php echo date('M j, Y H:i', strtotime($booking['arrival_time'])); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Duration:</span>
                        <span class="detail-value">
                            <?php
                                $departure = new DateTime($booking['departure_time']);
                                $arrival = new DateTime($booking['arrival_time']);
                                $interval = $departure->diff($arrival);
                                echo $interval->format('%hh %im');
                            ?>
                        </span>
                    </div>
                </div>
                
                <div class="booking-info">
                    <h3>Booking Information</h3>
                    <div class="detail-row">
                        <span class="detail-label">Booking Date:</span>
                        <span class="detail-value"><?php echo date('M j, Y H:i', strtotime($booking['booking_date'])); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Passengers:</span>
                        <span class="detail-value"><?php echo $booking['passengers']; ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Total Price:</span>
                        <span class="detail-value">$<?php echo number_format($booking['total_price'], 2); ?></span>
                    </div>
                </div>
                
                <div class="actions">
                    <a href="mybookings.php" class="btn">Back to My Bookings</a>
                    <?php if($booking['status'] == 'confirmed'): ?>
                        <a href="cancel_booking.php?booking_id=<?php echo $booking['booking_id']; ?>" class="btn secondary">Cancel Booking</a>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <p>&copy; 2023 SkyTravel. All rights reserved.</p>
    </footer>
</body>
</html>
