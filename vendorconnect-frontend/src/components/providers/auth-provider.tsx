'use client';

import { useEffect } from 'react';
import { useAuthStore } from '@/lib/auth-store';

export function AuthProvider({ children }: { children: React.ReactNode }) {
  const { checkAuth } = useAuthStore();

  useEffect(() => {
    console.log('AuthProvider: Initializing authentication...');
    checkAuth();
  }, [checkAuth]);

  return <>{children}</>;
}
