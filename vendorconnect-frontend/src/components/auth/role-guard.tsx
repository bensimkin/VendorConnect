'use client';

import { useAuthStore } from '@/lib/auth-store';
import { useRouter } from 'next/navigation';
import { useEffect } from 'react';
import { toast } from 'react-hot-toast';

interface RoleGuardProps {
  children: React.ReactNode;
  allowedRoles: string[];
  fallback?: React.ReactNode;
}

export default function RoleGuard({ children, allowedRoles, fallback }: RoleGuardProps) {
  const { user } = useAuthStore();
  const router = useRouter();

  useEffect(() => {
    if (!user) {
      toast.error('Please log in to access this page');
      router.push('/login');
      return;
    }

    const hasAccess = user.roles?.some(role => allowedRoles.includes(role.name));
    
    if (!hasAccess) {
      toast.error('You do not have permission to access this page');
      router.push('/dashboard');
    }
  }, [user, allowedRoles, router]);

  if (!user) {
    return fallback || <div>Loading...</div>;
  }

  const hasAccess = user.roles?.some(role => allowedRoles.includes(role.name));
  
  if (!hasAccess) {
    return fallback || <div>Access Denied</div>;
  }

  return <>{children}</>;
}
