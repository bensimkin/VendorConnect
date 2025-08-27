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

  const normalizeRole = (name?: string) => (name || '').toLowerCase().replace(/\s+/g, '_').trim();
  const allowed = new Set((allowedRoles || []).map(normalizeRole));

  useEffect(() => {
    if (!user) {
      toast.error('Please log in to access this page');
      router.push('/login');
      return;
    }

    // Debug logging
    console.log('RoleGuard Debug:', {
      user: user.email,
      userRoles: user.roles?.map(r => r.name),
      allowedRoles: allowedRoles,
      normalizedAllowed: Array.from(allowed),
      normalizedUserRoles: user.roles?.map(r => normalizeRole(r.name))
    });

    const hasAccess = user.roles?.some(role => allowed.has(normalizeRole(role.name)));
    
    if (!hasAccess) {
      toast.error('You do not have permission to access this page');
      router.push('/dashboard');
    }
  }, [user, allowedRoles, router]);

  if (!user) {
    return fallback || <div>Loading...</div>;
  }

  const hasAccess = user.roles?.some(role => allowed.has(normalizeRole(role.name)));
  
  if (!hasAccess) {
    return fallback || <div>Access Denied</div>;
  }

  return <>{children}</>;
}
