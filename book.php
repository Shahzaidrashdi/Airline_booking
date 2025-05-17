<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['flight_id']) || !isset($_GET['passengers'])) {
    header("Location: flights.php");
    exit();
}

$flight_id = (int)$_GET['flight_id'];
$passengers = (int)$_GET['passengers'];

// Get flight details
$stmt = $pdo->prepare("SELECT * FROM flights WHERE flight_id = ?");
$stmt->execute([$flight_id]);
$flight = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$flight) {
    header("Location: flights.php");
    exit();
}

// Calculate total price
$total_price = $flight['price'] * $passengers;

// Process booking
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Start transaction
        $pdo->beginTransaction();
        
        // Check if seats are still available
        $stmt = $pdo->prepare("SELECT available_seats FROM flights WHERE flight_id = ? FOR UPDATE");
        $stmt->execute([$flight_id]);
        $current_seats = $stmt->fetchColumn();
        
        if ($current_seats < $passengers) {
            throw new Exception("Not enough seats available");
        }
        
        // Create booking
        $stmt = $pdo->prepare("INSERT INTO bookings (user_id, flight_id, passengers, total_price) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $flight_id, $passengers, $total_price]);
        
        // Update available seats
        $stmt = $pdo->prepare("UPDATE flights SET available_seats = available_seats - ? WHERE flight_id = ?");
        $stmt->execute([$passengers, $flight_id]);
        
        $pdo->commit();
        
        header("Location: booking_confirmation.php?booking_id=" . $pdo->lastInsertId());
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Booking failed: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Flight - SkyTravel</title>
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
        <section class="booking-container">
            <h2>Confirm Your Booking</h2>
            
            <?php if(isset($error)): ?>
                <div class="alert error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="booking-summary">
                <div class="flight-details">
                    <h3>Flight Details</h3>
                    <p><strong>Airline:</strong> <?php echo htmlspecialchars($flight['airline']); ?></p>
                    <p><strong>Flight Number:</strong> <?php echo htmlspecialchars($flight['flight_number']); ?></p>
                    <p><strong>From:</strong> <?php echo htmlspecialchars($flight['departure_airport']); ?></p>
                    <p><strong>To:</strong> <?php echo htmlspecialchars($flight['arrival_airport']); ?></p>
                    <p><strong>Departure:</strong> <?php echo date('M j, Y H:i', strtotime($flight['departure_time'])); ?></p>
                    <p><strong>Arrival:</strong> <?php echo date('M j, Y H:i', strtotime($flight['arrival_time'])); ?></p>
                </div>
                
                <div class="price-details">
                    <h3>Price Summary</h3>
                    <p><strong>Price per passenger:</strong> $<?php echo number_format($flight['price'], 2); ?></p>
                    <p><strong>Number of passengers:</strong> <?php echo $passengers; ?></p>
                    <p><strong>Total Price:</strong> $<?php echo number_format($total_price, 2); ?></p>
                </div>
            </div>
            
            <form action="book.php?flight_id=<?php echo $flight_id; ?>&passengers=<?php echo $passengers; ?>" method="post">
                <h3>Passenger Information</h3>
                
                <?php for($i = 1; $i <= $passengers; $i++): ?>
                    <div class="passenger-form">
                        <h4>Passenger <?php echo $i; ?></h4>
                        <div class="form-group">
                            <label for="name<?php echo $i; ?>">Full Name</label>
                            <input type="text" id="name<?php echo $i; ?>" name="passengers[<?php echo $i; ?>][name]" required>
                        </div>
                        <div class="form-group">
                            <label for="passport<?php echo $i; ?>">Passport Number</label>
                            <input type="text" id="passport<?php echo $i; ?>" name="passengers[<?php echo $i; ?>][passport]" required>
                        </div>
                    </div>
                <?php endfor; ?>
                
                <div class="payment-method">
                    <h3>Payment Method</h3>
                    <div class="form-group">
                        <label><input type="radio" name="payment_method" value="credit_card" checked> Credit Card</label>
                        <label><input type="radio" name="payment_method" value="paypal"> PayPal</label>
                    </div>
                    
                    <div id="credit-card-details">
                        <div class="form-group">
                            <label for="card_number">Card Number</label>
                            <input type="text" id="card_number" name="card_number" placeholder="1234 5678 9012 3456">
                        </div>
                        <div class="form-group">
                            <label for="card_name">Name on Card</label>
                            <input type="text" id="card_name" name="card_name">
                        </div>
                        <div class="form-group-row">
                            <div class="form-group">
                                <label for="expiry">Expiry Date</label>
                                <input type="text" id="expiry" name="expiry" placeholder="MM/YY">
                            </div>
                            <div class="form-group">
                                <label for="cvv">CVV</label>
                                <input type="text" id="cvv" name="cvv" placeholder="123">
                            </div>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="btn">Confirm Booking</button>
            </form>
        </section>
    </main>

    <footer>
        <p>&copy; 2023 SkyTravel. All rights reserved.</p>
    </footer>

    <script>
        // Show/hide payment method details
        document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
            radio.addEventListener('change', function() {
                document.getElementById('credit-card-details').style.display = 
                    this.value === 'credit_card' ? 'block' : 'none';
            });
        });
    </script>
</body>
</html>
