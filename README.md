#  Movie Booking System

A web-based **Movie Booking System** developed using **PHP, MySQL, HTML, CSS, JavaScript, and Bootstrap**. This application allows users to browse movies, book tickets, select seats, purchase snacks, and make payments, while providing an admin panel to manage movies, halls, bookings, and users.

---

##  Features

###  User Module
- User Registration & Login
- Browse Available Movies
- View Movie Details
- Select Show Date & Time
- Interactive Seat Selection
- Book Movie Tickets
- Add Snacks to Booking
- Secure Payment Page
- Booking Confirmation
- Booking History
- Submit Movie Reviews

###  Admin Module
- Admin Login
- Dashboard
- Manage Movies
  - Add Movie
  - Edit Movie
  - Delete Movie
- Manage Halls
- Manage Show Timings
- Manage Snacks
- View All Bookings
- Manage Users
- View Customer Reviews

---

##  Technologies Used

| Technology | Purpose |
|------------|---------|
| PHP | Backend Development |
| MySQL | Database |
| HTML5 | Page Structure |
| CSS3 | Styling |
| Bootstrap | Responsive UI |
| JavaScript | Client-side Functionality |
| XAMPP | Local Server |

---

##  Project Structure

```
MovieBookingSystem/
│
├── admin/
│   ├── dashboard.php
│   ├── manage_movie.php
│   ├── manage_halls.php
│   ├── manage_users.php
│   ├── manage_snacks.php
│   └── ...
│
├── css/
├── images/
├── uploads/
├── includes/
│
├── index.php
├── login.php
├── register.php
├── movie_details.php
├── seat_selection.php
├── select_snacks.php
├── payment.php
├── process_payment.php
├── booking_history.php
├── logout.php
│
└── database.sql
```

---

##  Database

The project uses **MySQL** as the backend database.

### Main Tables

- users
- admin
- movies
- halls
- showtimes
- bookings
- booking_seats
- snacks
- booking_snacks
- reviews

---

## 🚀 Installation

### 1. Clone Repository

```bash
git clone https://github.com/yourusername/movie-booking-system.git
```

### 2. Move Project

Copy the project folder into:

```
xampp/htdocs/
```

### 3. Import Database

- Open **phpMyAdmin**
- Create a database (e.g., `movie_booking`)
- Import the provided SQL file.

### 4. Configure Database

Update your database connection file.

Example:

```php
$host = "localhost";
$user = "root";
$password = "";
$database = "movie_booking";
```

### 5. Start Server

Start

- Apache
- MySQL

from the XAMPP Control Panel.

### 6. Open Browser

```
http://localhost/MovieBookingSystem/
```

---

## 🎯 Booking Workflow

1. Register/Login
2. Browse Movies
3. Select Movie
4. Choose Show Time
5. Select Seats
6. Add Snacks (Optional)
7. Proceed to Payment
8. Booking Confirmed
9. View Booking History

---

##  Future Enhancements

- Online Payment Gateway Integration
- Email Ticket Confirmation
- QR Code Tickets
- Mobile Responsive Improvements
- Movie Search & Filters
- Coupon & Discount System
- OTP Authentication
- Live Seat Availability
- Recommendation System

---

##  Learning Outcomes

This project helped in understanding:

- PHP CRUD Operations
- Session Management
- Authentication
- MySQL Database Design
- Form Validation
- Responsive Web Design
- Dynamic Content Rendering
- Booking Management System Development

---

##  Author

**Ahana Gupta**


## ⭐ If you found this project useful

Give this repository a ⭐ on GitHub!
