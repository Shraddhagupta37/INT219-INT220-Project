# Code Lens: Hackathon Assessment Platform - INT219 & INT220 Group Project

CodeLens is a collaborative group project that provides a secure online assessment platform for evaluating hackathon candidates. Built with PHP, MySQL, and Tailwind CSS, it features real-time proctoring, automated scoring, and detailed analytics. Admins can create tests while candidates can take assessments in a monitored environment.

---

## 🚀 Key Features (2025 Update)

### For Administrators
- Create, edit, and manage test questions with correct answers and difficulty levels
- Set up tests: duration, passing score, and question selection
- Monitor test attempts in real-time
- View detailed analytics and candidate performance reports
- Categorize questions by topic and difficulty
- Track candidate performance metrics

### For Candidates
- Register and login securely
- Take proctored tests with a timer and automatic time tracking
- Receive immediate, detailed test results (including correct/incorrect answers, correct options, and feedback)
- Review past test attempts and performance statistics
- Access practice materials and mock tests

---

## 🛠️ Technology Stack

**Frontend:**  
- HTML5  
- Tailwind CSS  
- JavaScript  
- Chart.js (analytics visualization)

**Backend:**  
- PHP  
- MySQL  
- PDO for database operations

---

## 📁 Project Structure

```
/Frontend
    /Pages          # All PHP pages for UI (login, dashboard, test, results, admin, etc.)
    /src            # CSS (Tailwind), JS, and static assets
/Backend
    /PHP            # All backend PHP scripts (auth, DB, test logic, etc.)
README.md           # Project documentation
```

---

## 🗄️ Database Schema

- Users Table
- Questions Table (with correct_option)
- Tests Table
- Test Attempts Table (tracks score, correct answers, time taken)
- User Answers Table (tracks selected answers per question)

---

## ⚡ Usage

1. Clone the repo and set up XAMPP/LAMP.
2. Import the provided SQL schema.
3. Update database credentials in `/Backend/PHP/config.php`.
4. Access the platform via your localhost.

---

## 📝 Recent Changes

- Fixed: Correct answers now display properly on the results page.
- Improved: Time tracking and scoring logic.
- Enhanced: User feedback for each question (shows selected and correct options).
- Updated: Admin and candidate dashboards.

---

## 🤝 Contributing

Feel free to open issues or submit pull requests!

---

## 📜 License

MIT

---

# Code Lens: Hackathon Assessment Platform

CodeLens is a web-based proctored examination system designed specifically for conducting hackathon candidate assessments as part of the INT219 & INT220 Group Project. The platform provides a secure and efficient way to evaluate candidates through standardized tests.

## Database Schema Example

### Users Table
```sql
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    fullname VARCHAR(100) NOT NULL,
    uid VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(15) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'candidate') NOT NULL DEFAULT 'candidate',
    remember_token VARCHAR(64) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Questions Table
```sql
CREATE TABLE questions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    admin_id INT NOT NULL,
    question_text TEXT NOT NULL,
    option_a TEXT NOT NULL,
    option_b TEXT NOT NULL,
    option_c TEXT NOT NULL,
    option_d TEXT NOT NULL,
    correct_answer CHAR(1) NOT NULL,
    category VARCHAR(50) NOT NULL,
    difficulty ENUM('easy', 'medium', 'hard') NOT NULL,
    FOREIGN KEY (admin_id) REFERENCES users(id)
);
```

## Installation

1. Clone the repository
```bash
git clone https://github.com/shreyanshtripathi-01/INT_219_220_Project
```

2. Set up your XAMPP environment
- Place the project folder in `htdocs` directory
- Start Apache and MySQL services

3. Database Configuration
- Create a database named 'codelens'
- Import the provided SQL schema
- Update database credentials in `Backend/PHP/config.php`

4. Access the Application
- Open your browser and navigate to `http://localhost/Project`

## Usage

### Administrator
1. Register as an admin user
2. Login with admin credentials
3. Access the admin dashboard
4. Create and manage questions
5. View analytics and reports

### Candidate
1. Register as a candidate
2. Login with registered credentials
3. Access available tests
4. Take tests and view results
5. Track performance history

## Security Features

- Password hashing using PHP's password_hash()
- Session-based authentication
- PDO prepared statements for SQL injection prevention
- Input validation and sanitization
- Role-based access control

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## Authors

- Shreyansh Tripathi
  Contact: 14shreyansh2006@gmail.com
  GitHub: [@shreyanshtripathi-01](https://github.com/shreyanshtripathi-01)

- Shraddha Gupta
  Contact: gshraddha246850@gmail.com
  GitHub: [@Shraddhagupta37](https://github.com/Shraddhagupta37)

- Khushi Gupta
  Contact: kg677579@gmail.com
  GitHub: [@KhushiGupta113](https://github.com/KhushiGupta113)

- Aastha Kumari
  Contact: 14shreyansh2006@gmail.com
  GitHub: [@aasthakumari5551](https://github.com/aasthakumari5551)

## Acknowledgments

- Tailwind CSS for the UI components
- Chart.js for data visualization
- XAMPP for the development environment
