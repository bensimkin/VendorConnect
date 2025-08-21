# VendorConnect Frontend

A beautiful, modern task management system built with Next.js 14, TypeScript, and Tailwind CSS.

## 🚀 Features

- **Modern UI/UX**: Beautiful, responsive design with Tailwind CSS
- **TypeScript**: Full type safety throughout the application
- **Real-time Data**: Connected to Laravel API backend
- **Authentication**: Secure login/logout with JWT tokens
- **Dashboard**: Beautiful analytics and task overview
- **Responsive**: Works perfectly on desktop, tablet, and mobile

## 🛠️ Tech Stack

- **Framework**: Next.js 14 (React 18)
- **Language**: TypeScript
- **Styling**: Tailwind CSS
- **HTTP Client**: Axios
- **Icons**: Lucide React
- **Forms**: React Hook Form + Zod
- **State Management**: React Query (TanStack Query)

## 📦 Installation

1. **Install dependencies:**
   ```bash
   npm install
   ```

2. **Start development server:**
   ```bash
   npm run dev
   ```

3. **Open your browser:**
   Navigate to [http://localhost:3000](http://localhost:3000)

## 🔐 Authentication

The frontend connects to the Laravel API backend. Demo credentials:

- **Email**: admin@admin.com
- **Password**: admin123

## 📁 Project Structure

```
src/
├── app/                    # Next.js App Router
│   ├── dashboard/         # Dashboard page
│   ├── login/            # Login page
│   └── page.tsx          # Home page (redirects)
├── lib/                   # Utility libraries
│   ├── api.ts            # API configuration
│   └── utils.ts          # Helper functions
└── types/                 # TypeScript type definitions
    └── index.ts          # All application types
```

## 🎨 Design System

- **Colors**: Blue/Indigo gradient theme
- **Typography**: Modern, clean fonts
- **Components**: Consistent design patterns
- **Spacing**: Tailwind's spacing scale
- **Shadows**: Subtle, modern shadows

## 🔧 Development

### Available Scripts

- `npm run dev` - Start development server
- `npm run build` - Build for production
- `npm run start` - Start production server
- `npm run lint` - Run ESLint

### API Integration

The frontend is fully integrated with the Laravel API backend:

- **Base URL**: https://vc.themastermind.com.au/api/v1
- **Authentication**: JWT Bearer tokens
- **Error Handling**: Automatic token refresh and logout
- **Type Safety**: Full TypeScript types for all API responses

## 🚀 Deployment

The frontend can be deployed to:

- **Vercel** (recommended for Next.js)
- **Netlify**
- **AWS Amplify**
- **Any static hosting service**

### Environment Variables

Create a `.env.local` file:

```env
NEXT_PUBLIC_API_URL=https://vc.themastermind.com.au/api/v1
```

## 📱 Responsive Design

The application is fully responsive and works on:

- **Desktop**: Full-featured dashboard
- **Tablet**: Optimized layout
- **Mobile**: Touch-friendly interface

## 🎯 Next Steps

Future enhancements planned:

- [ ] Task management pages
- [ ] User management
- [ ] Client management
- [ ] Project management
- [ ] Real-time notifications
- [ ] Dark mode
- [ ] Advanced filtering
- [ ] Data export
- [ ] Mobile app (React Native)

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 📄 License

This project is proprietary software.

---

Built with ❤️ using Next.js 14, TypeScript, and Tailwind CSS