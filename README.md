# VendorConnect v0.5
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

## 🛠️ Technical Stack

### Backend
- **Laravel 10**: PHP framework for robust API development
- **MySQL**: Reliable database management
- **Spatie Laravel Permission**: Role-based access control
- **Laravel Sanctum**: API authentication
- **Comprehensive API**: RESTful endpoints

### Frontend
- **Next.js 14**: React framework with App Router
- **TypeScript**: Type-safe development
- **Tailwind CSS**: Utility-first styling
- **Shadcn/ui**: Modern component library
- **React Hot Toast**: User feedback notifications

## 🔧 Installation & Setup

### Prerequisites
- PHP 8.1+
- Node.js 18+
- MySQL 8.0+
- Composer
- npm

### Backend Setup
```bash
# Install PHP dependencies
composer install

# Configure environment
cp .env.example .env
# Edit .env with your database and app settings

# Run migrations
php artisan migrate

# Generate application key
php artisan key:generate

# Clear caches
php artisan cache:clear
php artisan config:clear
```

### Frontend Setup
```bash
cd vendorconnect-frontend

# Install dependencies
npm install

# Build for production
npm run build

# Start development server
npm run dev
```

### Production Deployment
```bash
# Use the provided rebuild scripts
./rebuild.sh          # Local development
./rebuild-server.sh   # Production server
```

## 📋 Version History

### v0.5 (Current)
- Basic template-based task management
- Vendor and client management
- Project oversight and deadline management
- Core API functionality
- Basic frontend interface

### v1.0 (Next Release)
- Interactive task management with real-time updates
- Global search functionality across all entities
- Role-based access control and navigation
- Modern UI/UX with responsive design
- Advanced dashboards with analytics
- Persistent filters and state management
- Enhanced project management features
- Portfolio integration and management

## 🤝 Contributing

VendorConnect is an open-source project. Contributions are welcome! Please read our contributing guidelines and ensure all code follows our coding standards.

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 🆘 Support

For support and questions:
- Create an issue on GitHub
- Contact: benjamin@businessnet.com.au
- Documentation: Available in the `/docs` directory

---

**VendorConnect v0.5** - Streamlining vendor collaboration with project management tools.

