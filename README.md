# FindWork - Job Portal

FindWork is a web-based job portal that connects job seekers with recruiters. The platform allows recruiters to post job opportunities and job seekers to browse and apply for positions that match their skills and experience.

## Features

### For Job Seekers
- Browse job listings with filtering options (job type, location, experience, salary, company)
- View detailed job descriptions
- Save jobs for later viewing
- Apply to jobs with resume upload
- Track application status (applied, selected, rejected)
- User account management with profile information
- Phone number collection during signup for better recruiter contact

### For Recruiters
- Post new job opportunities with form validation and duplicate submission prevention
- Manage job listings (open/close positions)
- View applicants for each job posting
- Update application statuses (select/reject candidates)
- Dashboard with comprehensive application statistics
- Separate "My Jobs" view for simplified job management

## Technology Stack

- **Frontend**: HTML, CSS, JavaScript
- **Backend**: PHP
- **Database**: MySQL
- **Server**: Apache

## Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache web server
- Composer (optional, for dependencies)

### Setup Instructions

1. **Clone the repository**
   ```
   git clone https://github.com/yourusername/findwork.git
   cd findwork
   ```

2. **Database Setup**
   - Create a MySQL database named `findwork`
   - Import the database schema from `database/findwork.sql`
   ```
   mysql -u username -p findwork < database/findwork.sql
   ```

3. **Configuration**
   - Update database connection details in `includes/db.php`
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   define('DB_NAME', 'findwork');
   ```

4. **Web Server Configuration**
   - Configure your web server to point to the project directory
   - Ensure the document root is set to the project root

5. **Test the Application**
   - Navigate to `http://localhost/findwork` in your web browser

## Sample Credentials

The database comes pre-loaded with sample accounts for testing:

### Job Seeker Account
- **Email**: jobseeker@example.com
- **Password**: password123
- **Role**: Job Seeker

### Recruiter Account
- **Email**: recruiter@example.com
- **Password**: 123456
- **Role**: Recruiter (Tech Corp)

### Sample Jobs
The database also includes sample job listings:
1. Senior PHP Developer (Full-time, Mumbai)
2. Web Development Intern (Internship, Remote)

## Project Structure

```
findwork/
├── database/                # Database schema and sample data
│   └── findwork.sql
├── includes/                # Shared PHP components
│   ├── db.php               # Database connection
│   ├── header.php           # Page header
│   ├── footer.php           # Page footer
│   └── functions.php        # Helper functions
├── recruiter/               # Recruiter-specific pages
│   ├── dashboard.php        # Recruiter dashboard with statistics
│   ├── my-jobs.php          # Simplified job management
│   ├── view-applicants.php  # View job applicants
│   ├── close-job.php        # Close job listing
│   └── reopen-job.php       # Reopen closed job listing
├── index.php                # Home page with job listings and filters
├── login.php                # User login
├── signup.php               # User registration with phone number
├── dashboard.php            # Job seeker dashboard
├── post-job.php             # Post new job (recruiters)
├── job-details.php          # Detailed job view
├── apply.php                # Apply for jobs
├── save-job.php             # Save/unsave jobs
├── saved-jobs.php           # View saved jobs
├── remove-saved-job.php     # Remove jobs from saved list
├── upload-resume.php        # Upload/update resume
├── logout.php               # User logout
├── style.css                # Main stylesheet
└── README.md                # Project documentation
```

## Usage

### For Job Seekers

1. **Create an Account or Use Sample Account**
   - Use the sample account: jobseeker@example.com / password123
   - Or click "Sign Up" and select "Job Seeker" role to create a new account
   - Provide your phone number during signup for better recruiter contact

2. **Browse Jobs**
   - Use the home page to view all available jobs
   - Apply filters to narrow down your search by job type, location, experience, salary, or company

3. **Save Jobs for Later**
   - Click the bookmark icon on any job listing to save it
   - View all saved jobs in the "Saved Jobs" section
   - Remove jobs from saved list when no longer interested

4. **Apply for Jobs**
   - Click "Apply Now" on any job listing
   - Upload your resume and provide contact information
   - Add any additional information requested by the recruiter

5. **Track Applications**
   - Visit "My Applications" to see the status of your applications

### For Recruiters

1. **Create an Account or Use Sample Account**
   - Use the sample account: recruiter@example.com / password123
   - Or click "Sign Up" and select "Recruiter" role to create a new account

2. **Post a Job**
   - Click "Post Job" in the navigation menu
   - Fill in all job details and submit
   - Form validation prevents incomplete submissions
   - Duplicate submission prevention ensures jobs aren't posted multiple times

3. **Manage Applications**
   - Visit your dashboard to see all posted jobs with detailed statistics
   - Use "My Jobs" for a simplified view of your job listings
   - Click "View Applicants" to see candidates for each job
   - Update application statuses (select/reject)

4. **Close/Reopen Job Listings**
   - Click "Close Job" when a position is filled
   - Click "Reopen Job" if you need more applicants for a previously closed position

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Contact

Your Name - your.email@example.com

Project Link: https://github.com/yourusername/findwork
