# VendorConnect Frontend Documentation

## Overview
VendorConnect is a modern, responsive web application built with Next.js 14, TypeScript, and Tailwind CSS. The frontend provides an intuitive interface for managing vendors, tasks, projects, and client relationships.

**Base URL**: `https://vc.themastermind.com.au`  
**Framework**: Next.js 14 (App Router)  
**Language**: TypeScript  
**Styling**: Tailwind CSS  
**State Management**: Zustand  
**UI Components**: Custom components with shadcn/ui  
**Last Updated**: September 1, 2025

---

## 🏗️ Architecture Overview

### Technology Stack
- **Framework**: Next.js 14 (App Router)
- **Language**: TypeScript
- **Styling**: Tailwind CSS
- **State Management**: Zustand
- **Authentication**: Bearer tokens (Sanctum) stored in localStorage and cookies
- **UI Components**: Custom components with shadcn/ui
- **Notifications**: React Hot Toast
- **Theme**: Dark/Light mode support
- **Charts**: Chart.js with react-chartjs-2
- **Icons**: Lucide React
- **Forms**: React Hook Form with Zod validation

### Project Structure
```
vendorconnect-frontend/
├── src/
│   ├── app/                    # Next.js App Router pages
│   │   ├── (auth)/            # Authentication routes
│   │   ├── dashboard/         # Dashboard pages
│   │   ├── projects/          # Project management
│   │   ├── tasks/             # Task management
│   │   ├── clients/           # Client management
│   │   ├── users/             # User management
│   │   ├── portfolio/         # Portfolio management
│   │   ├── settings/          # Settings and configuration
│   │   ├── globals.css        # Global styles
│   │   ├── layout.tsx         # Root layout
│   │   └── page.tsx           # Home page
│   ├── components/            # Reusable UI components
│   │   ├── ui/               # shadcn/ui components
│   │   ├── forms/            # Form components
│   │   ├── charts/           # Chart components
│   │   ├── navigation/       # Navigation components
│   │   └── layout/           # Layout components
│   ├── lib/                   # Utilities and configurations
│   │   ├── utils.ts          # Utility functions
│   │   ├── validations.ts    # Zod schemas
│   │   ├── constants.ts      # Application constants
│   │   └── api.ts            # API client configuration
│   ├── hooks/                 # Custom React hooks
│   │   ├── useAuth.ts        # Authentication hook
│   │   ├── useApi.ts         # API interaction hook
│   │   └── useLocalStorage.ts # Local storage hook
│   ├── stores/                # Zustand state stores
│   │   ├── auth-store.ts     # Authentication state
│   │   ├── ui-store.ts       # UI state
│   │   └── filter-store.ts   # Filter state
│   ├── types/                 # TypeScript type definitions
│   │   ├── api.ts            # API response types
│   │   ├── user.ts           # User-related types
│   │   └── common.ts         # Common types
│   └── middleware.ts          # Next.js middleware
├── public/                     # Static assets
├── package.json               # Dependencies and scripts
├── tailwind.config.js         # Tailwind configuration
├── tsconfig.json              # TypeScript configuration
└── next.config.js             # Next.js configuration
```

---

## ⚠️ CRITICAL: Field Naming Standards

**IMPORTANT**: Frontend interfaces must use consistent field names that match the database schema:

### **Correct Field Usage:**
```typescript
// ✅ CORRECT - Use database field names
interface Task {
  status: {
    id: number;
    title: string;  // NOT status.name
  };
  priority: {
    id: number;
    title: string;  // NOT priority.name
  };
  task_type: {
    id: number;
    task_type: string;  // NOT task_type.name
  };
  project: {
    id: number;
    title: string;  // ✅ Correct
  };
}
```

### **Incorrect Field Usage:**
```typescript
// ❌ INCORRECT - Don't use appended 'name' fields
interface Task {
  status: {
    id: number;
    name: string;  // Avoid - use title instead
  };
  priority: {
    id: number;
    name: string;  // Avoid - use title instead
  };
  task_type: {
    id: number;
    name: string;  // Avoid - use task_type instead
  };
}
```

**Reason**: API responses include both fields due to Laravel model `$appends`, but frontend should use the primary database field names for consistency.

---

## 👥 User Roles & Permissions

### 1. **Administrator**
**Capabilities**:
- Full system access and control
- User management (create, edit, delete users)
- Role and permission management
- System settings configuration
- View all projects, tasks, and clients
- Analytics and reporting access

**Accessible Pages**:
- All pages and features
- Admin dashboard with system statistics
- User management interface
- System configuration settings

### 2. **Requester**
**Capabilities**:
- Create and manage projects/tasks
- View assigned items and portfolio
- Limited user data access
- Task assignment and monitoring

**Accessible Pages**:
- Dashboard (limited view)
- Project management
- Task creation and management
- Client portfolio viewing
- Personal settings

### 3. **Tasker**
**Capabilities**:
- View and work on assigned tasks only
- Update task status and progress
- Upload deliverables
- Add comments to tasks

**Accessible Pages**:
- Dashboard (task-focused)
- Assigned tasks only
- Task detail pages
- Personal profile settings

---

## 🔐 Authentication System

### Authentication Flow
1. **Login**: User enters credentials
2. **Token Generation**: Server returns Bearer token
3. **Token Storage**: Token stored in localStorage and cookies
4. **API Requests**: Token included in Authorization header
5. **Token Refresh**: Automatic refresh before expiration
6. **Logout**: Token removed from storage

### Implementation
```typescript
// Authentication hook
const useAuth = () => {
  const [user, setUser] = useState<User | null>(null);
  const [token, setToken] = useState<string | null>(null);
  const [loading, setLoading] = useState(true);

  const login = async (email: string, password: string) => {
    const response = await api.post('/auth/login', { email, password });
    const { user, token } = response.data.data;
    
    setUser(user);
    setToken(token);
    localStorage.setItem('auth_token', token);
    localStorage.setItem('user', JSON.stringify(user));
  };

  const logout = () => {
    setUser(null);
    setToken(null);
    localStorage.removeItem('auth_token');
    localStorage.removeItem('user');
  };

  return { user, token, login, logout, loading };
};
```

### Protected Routes
```typescript
// Middleware for route protection
export function middleware(request: NextRequest) {
  const token = request.cookies.get('auth_token')?.value;
  
  if (!token && !request.nextUrl.pathname.startsWith('/login')) {
    return NextResponse.redirect(new URL('/login', request.url));
  }
  
  return NextResponse.next();
}
```

---

## 🎨 UI/UX Design System

### Design Principles
- **Modern & Clean**: Minimalist design with clear hierarchy
- **Responsive**: Mobile-first approach with progressive enhancement
- **Accessible**: WCAG 2.1 AA compliance
- **Consistent**: Unified design language across all components
- **Intuitive**: User-friendly navigation and interactions

### Color Palette
```css
/* Primary Colors */
--primary: #3b82f6;      /* Blue */
--primary-foreground: #ffffff;

/* Secondary Colors */
--secondary: #64748b;    /* Slate */
--secondary-foreground: #ffffff;

/* Accent Colors */
--accent: #f1f5f9;      /* Light gray */
--accent-foreground: #0f172a;

/* Status Colors */
--success: #10b981;     /* Green */
--warning: #f59e0b;     /* Amber */
--error: #ef4444;       /* Red */
--info: #3b82f6;        /* Blue */
```

### Typography
```css
/* Font Family */
font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;

/* Font Sizes */
--text-xs: 0.75rem;     /* 12px */
--text-sm: 0.875rem;    /* 14px */
--text-base: 1rem;      /* 16px */
--text-lg: 1.125rem;    /* 18px */
--text-xl: 1.25rem;     /* 20px */
--text-2xl: 1.5rem;     /* 24px */
--text-3xl: 1.875rem;   /* 30px */
```

### Component Library
Built with shadcn/ui components:
- **Buttons**: Primary, secondary, outline, ghost variants
- **Forms**: Input, select, textarea, checkbox, radio
- **Navigation**: Breadcrumbs, pagination, tabs
- **Feedback**: Alerts, toasts, progress bars
- **Data Display**: Tables, cards, badges, avatars
- **Layout**: Containers, grids, dividers

---

## 📊 State Management

### Zustand Stores

#### Authentication Store
```typescript
interface AuthStore {
  user: User | null;
  token: string | null;
  isAuthenticated: boolean;
  login: (email: string, password: string) => Promise<void>;
  logout: () => void;
  updateUser: (user: User) => void;
}

const useAuthStore = create<AuthStore>((set) => ({
  user: null,
  token: null,
  isAuthenticated: false,
  login: async (email, password) => {
    // Login implementation
  },
  logout: () => {
    // Logout implementation
  },
  updateUser: (user) => set({ user })
}));
```

#### UI Store
```typescript
interface UIStore {
  sidebarOpen: boolean;
  theme: 'light' | 'dark';
  toggleSidebar: () => void;
  setTheme: (theme: 'light' | 'dark') => void;
}
```

#### Filter Store
```typescript
interface FilterStore {
  filters: Record<string, any>;
  setFilter: (key: string, value: any) => void;
  clearFilters: () => void;
  getFilters: () => Record<string, any>;
}
```

---

## 🔧 API Integration

### API Client Configuration
```typescript
// lib/api.ts
import axios from 'axios';

const api = axios.create({
  baseURL: process.env.NEXT_PUBLIC_API_URL || 'https://vc.themastermind.com.au/api/v1',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// Request interceptor for authentication
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('auth_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Response interceptor for error handling
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      // Handle token expiration
      localStorage.removeItem('auth_token');
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);
```

### API Hooks
```typescript
// hooks/useApi.ts
export const useApi = () => {
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const request = async <T>(
    method: 'GET' | 'POST' | 'PUT' | 'DELETE',
    url: string,
    data?: any
  ): Promise<T> => {
    setLoading(true);
    setError(null);
    
    try {
      const response = await api.request({ method, url, data });
      return response.data;
    } catch (err) {
      setError(err.response?.data?.message || 'An error occurred');
      throw err;
    } finally {
      setLoading(false);
    }
  };

  return { request, loading, error };
};
```

---

## 📱 Responsive Design

### Breakpoints
```css
/* Tailwind CSS breakpoints */
sm: 640px   /* Small devices */
md: 768px   /* Medium devices */
lg: 1024px  /* Large devices */
xl: 1280px  /* Extra large devices */
2xl: 1536px /* 2X large devices */
```

### Mobile-First Approach
- **Base styles**: Mobile layout (320px+)
- **Progressive enhancement**: Add features for larger screens
- **Touch-friendly**: Minimum 44px touch targets
- **Gesture support**: Swipe, pinch, and tap interactions

### Responsive Components
```typescript
// Example responsive component
const ResponsiveTable = ({ data }: { data: any[] }) => {
  return (
    <div className="overflow-x-auto">
      <table className="min-w-full">
        {/* Table content */}
      </table>
    </div>
  );
};
```

---

## 🎯 Performance Optimization

### Code Splitting
- **Route-based**: Each page loads independently
- **Component-based**: Large components loaded on demand
- **Library-based**: Third-party libraries split by usage

### Image Optimization
```typescript
import Image from 'next/image';

// Optimized image component
<Image
  src="/logo.png"
  alt="Logo"
  width={200}
  height={100}
  priority={true}
  placeholder="blur"
  blurDataURL="data:image/jpeg;base64,..."
/>
```

### Caching Strategy
- **Static assets**: Long-term caching (1 year)
- **API responses**: Short-term caching (5 minutes)
- **User data**: Session-based caching
- **Configuration**: Build-time caching

### Bundle Optimization
```javascript
// next.config.js
module.exports = {
  experimental: {
    optimizeCss: true,
    optimizePackageImports: ['@radix-ui/react-icons', 'lucide-react'],
  },
  webpack: (config) => {
    config.optimization.splitChunks = {
      chunks: 'all',
      cacheGroups: {
        vendor: {
          test: /[\\/]node_modules[\\/]/,
          name: 'vendors',
          chunks: 'all',
        },
      },
    };
    return config;
  },
};
```

---

## 🔍 Search & Filtering

### Global Search
```typescript
const GlobalSearch = () => {
  const [query, setQuery] = useState('');
  const [results, setResults] = useState<SearchResult[]>([]);
  const [loading, setLoading] = useState(false);

  const search = useDebounce(async (searchQuery: string) => {
    if (searchQuery.length < 2) return;
    
    setLoading(true);
    try {
      const response = await api.get(`/search?q=${searchQuery}`);
      setResults(response.data.data);
    } catch (error) {
      console.error('Search failed:', error);
    } finally {
      setLoading(false);
    }
  }, 300);

  useEffect(() => {
    search(query);
  }, [query]);

  return (
    <div className="relative">
      <Input
        placeholder="Search projects, tasks, clients..."
        value={query}
        onChange={(e) => setQuery(e.target.value)}
      />
      {loading && <Spinner />}
      {results.length > 0 && (
        <SearchResults results={results} />
      )}
    </div>
  );
};
```

### Advanced Filtering
```typescript
const TaskFilters = () => {
  const { filters, setFilter, clearFilters } = useFilterStore();

  return (
    <div className="space-y-4">
      <Select
        value={filters.status}
        onValueChange={(value) => setFilter('status', value)}
      >
        <SelectTrigger>
          <SelectValue placeholder="Filter by status" />
        </SelectTrigger>
        <SelectContent>
          <SelectItem value="all">All Statuses</SelectItem>
          <SelectItem value="active">Active</SelectItem>
          <SelectItem value="completed">Completed</SelectItem>
        </SelectContent>
      </Select>
      
      <Button onClick={clearFilters} variant="outline">
        Clear Filters
      </Button>
    </div>
  );
};
```

---

## 📊 Data Visualization

### Chart Components
```typescript
import { Line, Bar, Doughnut } from 'react-chartjs-2';

const TaskTrendsChart = ({ data }: { data: ChartData }) => {
  const chartData = {
    labels: data.map(d => d.date),
    datasets: [{
      label: 'Completed Tasks',
      data: data.map(d => d.completed),
      borderColor: 'rgb(59, 130, 246)',
      backgroundColor: 'rgba(59, 130, 246, 0.1)',
    }]
  };

  return (
    <div className="p-4 bg-white rounded-lg shadow">
      <h3 className="text-lg font-semibold mb-4">Task Completion Trends</h3>
      <Line data={chartData} options={chartOptions} />
    </div>
  );
};
```

### Dashboard Widgets
```typescript
const DashboardWidget = ({ title, value, change, icon }: WidgetProps) => {
  return (
    <div className="p-6 bg-white rounded-lg shadow">
      <div className="flex items-center justify-between">
        <div>
          <p className="text-sm font-medium text-gray-600">{title}</p>
          <p className="text-2xl font-bold text-gray-900">{value}</p>
        </div>
        <div className="p-2 bg-blue-100 rounded-lg">
          {icon}
        </div>
      </div>
      {change && (
        <div className="mt-4">
          <span className={`text-sm ${change > 0 ? 'text-green-600' : 'text-red-600'}`}>
            {change > 0 ? '+' : ''}{change}% from last month
          </span>
        </div>
      )}
    </div>
  );
};
```

---

## 🧪 Testing Strategy

### Unit Testing
```typescript
// __tests__/components/Button.test.tsx
import { render, screen, fireEvent } from '@testing-library/react';
import { Button } from '@/components/ui/button';

describe('Button', () => {
  it('renders with correct text', () => {
    render(<Button>Click me</Button>);
    expect(screen.getByText('Click me')).toBeInTheDocument();
  });

  it('calls onClick when clicked', () => {
    const handleClick = jest.fn();
    render(<Button onClick={handleClick}>Click me</Button>);
    
    fireEvent.click(screen.getByText('Click me'));
    expect(handleClick).toHaveBeenCalledTimes(1);
  });
});
```

### Integration Testing
```typescript
// __tests__/pages/dashboard.test.tsx
import { render, screen, waitFor } from '@testing-library/react';
import Dashboard from '@/app/dashboard/page';

describe('Dashboard', () => {
  it('loads and displays dashboard data', async () => {
    render(<Dashboard />);
    
    await waitFor(() => {
      expect(screen.getByText('Recent Tasks')).toBeInTheDocument();
    });
  });
});
```

### E2E Testing
```typescript
// cypress/e2e/login.cy.ts
describe('Login', () => {
  it('should login successfully', () => {
    cy.visit('/login');
    cy.get('[data-testid="email"]').type('admin@vendorconnect.com');
    cy.get('[data-testid="password"]').type('password');
    cy.get('[data-testid="login-button"]').click();
    
    cy.url().should('include', '/dashboard');
    cy.get('[data-testid="user-menu"]').should('be.visible');
  });
});
```

---

## 🚀 Deployment

### Build Process
```bash
# Install dependencies
npm install

# Build for production
npm run build

# Start production server
npm start
```

### Environment Configuration
```env
# .env.local
NEXT_PUBLIC_API_URL=https://vc.themastermind.com.au/api/v1
NEXT_PUBLIC_APP_URL=https://vc.themastermind.com.au
NEXT_PUBLIC_ENVIRONMENT=production
```

### Docker Deployment
```dockerfile
# Dockerfile
FROM node:18-alpine AS base

# Install dependencies
FROM base AS deps
COPY package*.json ./
RUN npm ci --only=production

# Build application
FROM base AS builder
COPY . .
COPY --from=deps /app/node_modules ./node_modules
RUN npm run build

# Production image
FROM base AS runner
COPY --from=builder /app/public ./public
COPY --from=builder /app/.next/standalone ./
COPY --from=builder /app/.next/static ./.next/static

EXPOSE 3000
ENV PORT 3000
ENV NODE_ENV production

CMD ["node", "server.js"]
```

---

## 🔧 Development Guidelines

### Code Style
- **TypeScript**: Strict mode enabled
- **ESLint**: Airbnb configuration
- **Prettier**: Consistent formatting
- **Husky**: Pre-commit hooks

### Component Guidelines
```typescript
// ✅ Good component structure
interface ComponentProps {
  title: string;
  children: React.ReactNode;
  className?: string;
  onClick?: () => void;
}

export const Component: React.FC<ComponentProps> = ({
  title,
  children,
  className = '',
  onClick
}) => {
  return (
    <div className={`component ${className}`} onClick={onClick}>
      <h2>{title}</h2>
      {children}
    </div>
  );
};
```

### Error Handling
```typescript
// Error boundary component
class ErrorBoundary extends React.Component {
  constructor(props) {
    super(props);
    this.state = { hasError: false };
  }

  static getDerivedStateFromError(error) {
    return { hasError: true };
  }

  componentDidCatch(error, errorInfo) {
    console.error('Error caught by boundary:', error, errorInfo);
  }

  render() {
    if (this.state.hasError) {
      return <ErrorFallback />;
    }

    return this.props.children;
  }
}
```

---

## 📞 Support & Maintenance

### Development Workflow
1. **Feature Development**: Create feature branch from main
2. **Code Review**: Submit pull request for review
3. **Testing**: Ensure all tests pass
4. **Deployment**: Merge to main triggers deployment

### Monitoring
- **Error Tracking**: Sentry integration
- **Performance**: Core Web Vitals monitoring
- **Analytics**: User behavior tracking
- **Uptime**: Service availability monitoring

### Maintenance Tasks
- **Dependencies**: Regular updates and security patches
- **Performance**: Bundle size optimization
- **Accessibility**: Regular audits and improvements
- **Documentation**: Keep documentation up to date

---

*This documentation is maintained as part of the VendorConnect project. For questions or updates, please contact the development team.*
