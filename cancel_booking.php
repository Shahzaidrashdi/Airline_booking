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

// Verify the booking belongs to the user
$stmt = $pdo->prepare("SELECT * FROM bookings WHERE booking_id = ? AND user_id = ?");
$stmt->execute([$booking_id, $_SESSION['user_id']]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$booking) {
    header("Location: mybookings.php");
    exit();
}

// Process cancellation
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $pdo->beginTransaction();
        
        // Update booking status
        $stmt = $pdo->prepare("UPDATE bookings SET status = 'cancelled' WHERE booking_id = ?");
        $stmt->execute([$booking_id]);
        
        // Return seats to available inventory
        $stmt = $pdo->prepare("UPDATE flights SET available_seats = available_seats + ? WHERE flight_id = ?");
        $stmt->execute([$booking['passengers'], $booking['flight_id']]);
        
        $pdo->commit();
        
        header("Location: booking_details.php?booking_id=" . $booking_id);
        exit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = "Cancellation failed: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cancel Booking - SkyTravel</title>
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
        <section class="cancel-booking-container">
            <div class="cancel-booking-card">
                <h2>Cancel Booking #SKY<?php echo str_pad($booking_id, 6, '0', STR_PAD_LEFT); ?></h2>
                
                <?php if(isset($error)): ?>
                    <div class="alert error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <p>Are you sure you want to cancel this booking?</p>
                
                <div class="booking-summary">
                    <p><strong>Flight:</strong> <?php echo htmlspecialchars($booking['departure_airport']); ?> to <?php echo htmlspecialchars($booking['arrival_airport']); ?></p>
                    <p><strong>Passengers:</strong> <?php echo $booking['passengers']; ?></p>
                    <p><strong>Total Amount:</strong> $<?php echo number_format($booking['total_price'], 2); ?></p>
                </div>
                
                <form action="cancel_booking.php?booking_id=<?php echo $booking_id; ?>" method="post">
                    <div class="form-group">
                        <label for="reason">Reason for cancellation (optional):</label>
                        <textarea id="reason" name="reason" rows="3"></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn danger">Confirm Cancellation</button>
                        <a href="booking_details.php?booking_id=<?php echo $booking_id; ?>" class="btn secondary">Go Back</a>
                    </div>
                </form>
            </div>
        </section>
    </main>

    <footer>
        <p>&copy; 2023 SkyTravel. All rights reserved.</p>
    </footer>
</body>
</html>
