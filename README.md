# Vendor Connect v0.5
BusinessNET (c) 2024
Benjamin Simkin

# VendorConnect

VendorConnect is an open-source project management solution designed to streamline collaboration with external contractors and vendors. It provides a structured way to manage repeatable tasks, maintain client requirements, and ensure consistent project delivery across distributed teams.

## 🌟 Key Features

### Template-Based Task Management
- Create reusable task templates for standardized workflows
- Customize templates with specific instructions while maintaining core requirements
- Perfect for recurring projects like video production, content creation, or design work
- Add custom fields and requirements specific to each project type
- Make new templates to create new variations

### Comprehensive Vendor Management 
- Create and maintain a database of third-party contractors and vendors
- Track vendor performance, specialties, and availability
- Assign tasks based on vendor expertise and workload
- Maintain communication history and project outcomes
- Set different access levels and permissions for various vendor types

### Client Resource Integration
- Attach permanent client resources that automatically sync with related tasks
- Store and version control brand guides, style sheets, and other reference materials
- Ensure contractors always have access to the latest client requirements
- Organize resources by client, project type, or department
- Control access and sharing permissions for sensitive materials

### Advanced Deadline Management
- Set flexible or strict deadlines for task completion
- Automated enforcement of deadline restrictions
- Block submissions after deadline expiration for time-sensitive projects
- Configure grace periods and extension requests
- Receive notifications for approaching deadlines

### Project Oversight
- Real-time progress tracking and status updates
- Customizable approval workflows
- Quality control checkpoints
- Revision management and version control
- Detailed audit trails of all project activities

## 💡 Use Cases

### Marketing Agencies
- Manage multiple contractors for content creation
- Maintain consistent brand guidelines across projects
- Track deliverables and deadlines for campaigns
- Streamline review and approval processes

### Creative Services
- Coordinate with freelance designers and artists
- Maintain project specifications and requirements
- Ensure consistent quality across deliverables
- Manage revisions and feedback cycles

### Business Operations
- Standardize vendor onboarding and management
- Track vendor performance and compliance
- Maintain audit trails for all external work
- Ensure consistent service delivery

## 🚀 Installation & Setup

### Database Configuration Instructions
Follow the steps below to configure the database for the project:

1. **Download the SQL File**
   - Download the taskmanagement.sql file provided with the project.
    
2. **Create a New Database in phpMyAdmin**
   - Open phpMyAdmin.
   - Create a new database. You can name the database anything you prefer.
  
3. **Import the SQL File**
   - Select the new database you just created.
   - Click on the Import tab in phpMyAdmin.
   - Upload the taskmanagement.sql file and execute the import process.

4. **Configure Environment File**
   - Open the .env file in the project root directory.
   - Update the database credentials to match your new database configuration. Example:

   ```
   DB_CONNECTION=mysql
   DB_HOST=localhost
   DB_PORT=3306
   DB_DATABASE=task_management
   DB_USERNAME=root
   DB_PASSWORD=
   ```
   
   - Replace DB_DATABASE with the name of your database.
   - Update DB_USERNAME and DB_PASSWORD according to your phpMyAdmin credentials.