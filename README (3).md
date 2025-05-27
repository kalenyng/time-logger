# Time Logger

**Time Logger** is a simple PHP-based web application designed to help users track their time efficiently. It provides functionalities for user registration, login, and logging time entries.

## Features

- User Registration and Authentication
- Log Time Entries with Descriptions
- View Logged Time Entries
- Delete Account Functionality

## Getting Started

### Prerequisites

- PHP 7.0 or higher
- MySQL or compatible database
- Web server (e.g., Apache, Nginx)

### Installation

1. **Clone the repository:**

   ```bash
   git clone https://github.com/kalenyng/time-logger.git
   ```

2. **Set up the database:**
   - Create a new MySQL database.
   - Use the following SQL query to create the necessary table:

     ```sql
     CREATE TABLE time_entries (
         id INT AUTO_INCREMENT PRIMARY KEY,
         user_id INT NOT NULL,
         description TEXT NOT NULL,
         start_time DATETIME NOT NULL,
         end_time DATETIME NOT NULL,
         created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
     );
     ```

3. **Configure the application:**
   - Update the database connection settings in the application files as needed.

4. **Deploy the application:**
   - Place the application files in your web server's root directory.
   - Ensure the server has the necessary permissions to execute PHP scripts and access the database.

## Usage

- Navigate to the application's URL in your web browser.
- Register a new account or log in with existing credentials.
- Log new time entries by providing a description, start time, and end time.
- View your logged time entries on the dashboard.
- Use the delete account feature if you wish to remove your account and all associated data.

## Contributing

Contributions are welcome! Please fork the repository and submit a pull request for any enhancements or bug fixes.

## License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.