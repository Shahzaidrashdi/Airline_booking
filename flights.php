<?php
session_start();
include 'config.php';

$from = isset($_GET['from']) ? $_GET['from'] : '';
$to = isset($_GET['to']) ? $_GET['to'] : '';
$departure = isset($_GET['departure']) ? $_GET['departure'] : '';
$passengers = isset($_GET['passengers']) ? (int)$_GET['passengers'] : 1;

// Build the query based on search parameters
$query = "SELECT * FROM flights WHERE available_seats >= ?";
$params = [$passengers];

if (!empty($from)) {
    $query .= " AND departure_airport LIKE ?";
    $params[] = "%$from%";
}

if (!empty($to)) {
    $query .= " AND arrival_airport LIKE ?";
    $params[] = "%$to%";
}

if (!empty($departure)) {
    $query .= " AND DATE(departure_time) = ?";
    $params[] = $departure;
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$flights = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Flights - SkyTravel</title>
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
        <section class="flight-search">
            <h2>Available Flights</h2>
            
            <div class="search-filters">
                <form action="flights.php" method="get">
                    <div class="form-group">
                        <label for="from">From</label>
                        <input type="text" id="from" name="from" value="<?php echo htmlspecialchars($from); ?>">
                    </div>
                    <div class="form-group">
                        <label for="to">To</label>
                        <input type="text" id="to" name="to" value="<?php echo htmlspecialchars($to); ?>">
                    </div>
                    <div class="form-group">
                        <label for="departure">Departure Date</label>
                        <input type="date" id="departure" name="departure" value="<?php echo htmlspecialchars($departure); ?>">
                    </div>
                    <div class="form-group">
                        <label for="passengers">Passengers</label>
                        <select id="passengers" name="passengers">
                            <?php for($i = 1; $i <= 5; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php if($passengers == $i) echo 'selected'; ?>><?php echo $i; ?></option>
                            <?php endfor; ?>
                            <option value="5" <?php if($passengers > 5) echo 'selected'; ?>>5+</option>
                        </select>
                    </div>
                    <button type="submit" class="btn">Search</button>
                </form>
            </div>
            
            <div class="flight-list">
                <?php if(empty($flights)): ?>
                    <p>No flights found matching your criteria.</p>
                <?php else: ?>
                    <?php foreach($flights as $flight): ?>
                        <div class="flight-card">
                            <div class="flight-info">
                                <div class="flight-airline"><?php echo htmlspecialchars($flight['airline']); ?></div>
                                <div class="flight-number">Flight #<?php echo htmlspecialchars($flight['flight_number']); ?></div>
                            </div>
                            <div class="flight-route">
                                <div class="departure">
                                    <div class="time"><?php echo date('H:i', strtotime($flight['departure_time'])); ?></div>
                                    <div class="airport"><?php echo htmlspecialchars($flight['departure_airport']); ?></div>
                                </div>
                                <div class="duration">
                                    <?php 
                                        $departure = new DateTime($flight['departure_time']);
                                        $arrival = new DateTime($flight['arrival_time']);
                                        $interval = $departure->diff($arrival);
                                        echo $interval->format('%hh %im');
                                    ?>
                                </div>
                                <div class="arrival">
                                    <div class="time"><?php echo date('H:i', strtotime($flight['arrival_time'])); ?></div>
                                    <div class="airport"><?php echo htmlspecialchars($flight['arrival_airport']); ?></div>
                                </div>
                            </div>
                            <div class="flight-price">
                                <div class="price">$<?php echo number_format($flight['price'], 2); ?></div>
                                <div class="seats"><?php echo $flight['available_seats']; ?> seats left</div>
                                <?php if(isset($_SESSION['user_id'])): ?>
                                    <a href="book.php?flight_id=<?php echo $flight['flight_id']; ?>&passengers=<?php echo $passengers; ?>" class="btn">Book Now</a>
                                <?php else: ?>
                                    <a href="login.php" class="btn">Login to Book</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <footer>
        <p>&copy; 2023 SkyTravel. All rights reserved.</p>
    </footer>
</body>
</html>
