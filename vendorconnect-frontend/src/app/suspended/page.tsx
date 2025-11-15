'use client';

import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { AlertCircle, ExternalLink } from 'lucide-react';
import { useEffect } from 'react';
import { useRouter } from 'next/navigation';

export default function SuspendedPage() {
  const router = useRouter();

  useEffect(() => {
    // Clear auth tokens
    localStorage.removeItem('auth_token');
    document.cookie = 'auth_token=; path=/; expires=Thu, 01 Jan 1970 00:00:00 GMT';
  }, []);

  return (
    <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-red-50 via-orange-50 to-yellow-50 p-4">
      <Card className="w-full max-w-lg">
        <CardHeader className="text-center">
          <div className="mx-auto w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mb-4">
            <AlertCircle className="h-10 w-10 text-red-600" />
          </div>
          <CardTitle className="text-2xl font-bold text-red-900">
            Membership Required
          </CardTitle>
          <CardDescription className="text-base">
            Your access to VendorConnect has been suspended
          </CardDescription>
        </CardHeader>
        <CardContent className="space-y-6">
          <div className="text-center space-y-4">
            <p className="text-muted-foreground">
              VendorConnect is exclusively available to <strong>active members of The Mastermind</strong>.
            </p>
            
            <div className="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
              <p className="text-sm text-yellow-900">
                <strong>Your membership status:</strong> Inactive or Not Found
              </p>
              <p className="text-sm text-yellow-800 mt-2">
                To regain access, please ensure your Mastermind membership is active.
              </p>
            </div>

            <div className="space-y-3 pt-4">
              <Button
                asChild
                className="w-full"
                size="lg"
              >
                <a 
                  href="https://www.themastermind.com.au" 
                  target="_blank" 
                  rel="noopener noreferrer"
                  className="flex items-center justify-center gap-2"
                >
                  Visit The Mastermind
                  <ExternalLink className="h-4 w-4" />
                </a>
              </Button>

              <Button
                variant="outline"
                className="w-full"
                onClick={() => router.push('/login')}
              >
                Back to Login
              </Button>
            </div>

            <div className="text-xs text-muted-foreground pt-4">
              If you believe this is an error, please contact{' '}
              <a href="mailto:support@themastermind.com.au" className="text-primary underline">
                support@themastermind.com.au
              </a>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  );
}

