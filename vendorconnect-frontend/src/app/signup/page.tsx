'use client';

import { useState } from 'react';
import { useRouter } from 'next/navigation';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import * as z from 'zod';
import { useAuthStore } from '@/lib/auth-store';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { toast } from 'react-hot-toast';
import { Loader2, Building2, User, Mail, Lock } from 'lucide-react';
import Link from 'next/link';
import { apiClient } from '@/lib/api-client';

const signupSchema = z.object({
  company_name: z.string().min(2, 'Company name must be at least 2 characters'),
  company_email: z.string().email('Invalid email address').optional().or(z.literal('')),
  first_name: z.string().min(2, 'First name must be at least 2 characters'),
  last_name: z.string().min(2, 'Last name must be at least 2 characters'),
  email: z.string().email('Invalid email address'),
  password: z.string().min(6, 'Password must be at least 6 characters'),
  password_confirmation: z.string().min(6, 'Password must be at least 6 characters'),
}).refine((data) => data.password === data.password_confirmation, {
  message: "Passwords don't match",
  path: ["password_confirmation"],
});

type SignupFormData = z.infer<typeof signupSchema>;

export default function SignupPage() {
  const router = useRouter();
  const [isLoading, setIsLoading] = useState(false);

  const {
    register,
    handleSubmit,
    formState: { errors },
  } = useForm<SignupFormData>({
    resolver: zodResolver(signupSchema),
  });

  const onSubmit = async (data: SignupFormData) => {
    setIsLoading(true);
    try {
      const response = await apiClient.post('/company/register', data);
      
      if (response.data.success) {
        const { user, token } = response.data.data;
        
        // Save auth token
        localStorage.setItem('auth_token', token);
        document.cookie = `auth_token=${token}; path=/; max-age=${7 * 24 * 60 * 60}; SameSite=Lax`;
        
        toast.success('Company registered successfully! Welcome to VendorConnect!');
        
        // Redirect to dashboard
        setTimeout(() => {
          window.location.href = '/dashboard';
        }, 1000);
      }
    } catch (error: any) {
      console.error('Registration error:', error);
      const message = error.response?.data?.message || error.response?.data?.errors || 'Registration failed. Please try again.';
      
      if (typeof message === 'object') {
        // Handle validation errors
        Object.values(message).forEach((msg: any) => {
          toast.error(Array.isArray(msg) ? msg[0] : msg);
        });
      } else {
        toast.error(message);
      }
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-primary/20 via-background to-secondary/20 p-4">
      <Card className="w-full max-w-2xl">
        <form onSubmit={handleSubmit(onSubmit)}>
          <CardHeader className="space-y-1">
            <CardTitle className="text-3xl font-bold text-center">Create Your Account</CardTitle>
            <CardDescription className="text-center text-lg">
              Start managing your projects with VendorConnect
            </CardDescription>
          </CardHeader>
          <CardContent className="space-y-6">
            {/* Company Information Section */}
            <div className="space-y-4">
              <div className="flex items-center gap-2 text-sm font-semibold text-muted-foreground">
                <Building2 className="h-4 w-4" />
                <span>Company Information</span>
              </div>
              
              <div className="space-y-2">
                <Label htmlFor="company_name">
                  Company Name <span className="text-destructive">*</span>
                </Label>
                <Input
                  id="company_name"
                  type="text"
                  placeholder="Your Company Ltd"
                  {...register('company_name')}
                  disabled={isLoading}
                />
                {errors.company_name && (
                  <p className="text-sm text-destructive">{errors.company_name.message}</p>
                )}
              </div>

              <div className="space-y-2">
                <Label htmlFor="company_email">Company Email (optional)</Label>
                <Input
                  id="company_email"
                  type="email"
                  placeholder="contact@yourcompany.com"
                  {...register('company_email')}
                  disabled={isLoading}
                />
                {errors.company_email && (
                  <p className="text-sm text-destructive">{errors.company_email.message}</p>
                )}
              </div>
            </div>

            <div className="border-t pt-6"></div>

            {/* Admin User Information Section */}
            <div className="space-y-4">
              <div className="flex items-center gap-2 text-sm font-semibold text-muted-foreground">
                <User className="h-4 w-4" />
                <span>Your Account (Admin User)</span>
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div className="space-y-2">
                  <Label htmlFor="first_name">
                    First Name <span className="text-destructive">*</span>
                  </Label>
                  <Input
                    id="first_name"
                    type="text"
                    placeholder="John"
                    {...register('first_name')}
                    disabled={isLoading}
                  />
                  {errors.first_name && (
                    <p className="text-sm text-destructive">{errors.first_name.message}</p>
                  )}
                </div>

                <div className="space-y-2">
                  <Label htmlFor="last_name">
                    Last Name <span className="text-destructive">*</span>
                  </Label>
                  <Input
                    id="last_name"
                    type="text"
                    placeholder="Doe"
                    {...register('last_name')}
                    disabled={isLoading}
                  />
                  {errors.last_name && (
                    <p className="text-sm text-destructive">{errors.last_name.message}</p>
                  )}
                </div>
              </div>

              <div className="space-y-2">
                <Label htmlFor="email">
                  Email <span className="text-destructive">*</span>
                </Label>
                <Input
                  id="email"
                  type="email"
                  placeholder="john@yourcompany.com"
                  {...register('email')}
                  disabled={isLoading}
                />
                {errors.email && (
                  <p className="text-sm text-destructive">{errors.email.message}</p>
                )}
              </div>

              <div className="space-y-2">
                <Label htmlFor="password">
                  Password <span className="text-destructive">*</span>
                </Label>
                <Input
                  id="password"
                  type="password"
                  placeholder="••••••••"
                  {...register('password')}
                  disabled={isLoading}
                />
                {errors.password && (
                  <p className="text-sm text-destructive">{errors.password.message}</p>
                )}
              </div>

              <div className="space-y-2">
                <Label htmlFor="password_confirmation">
                  Confirm Password <span className="text-destructive">*</span>
                </Label>
                <Input
                  id="password_confirmation"
                  type="password"
                  placeholder="••••••••"
                  {...register('password_confirmation')}
                  disabled={isLoading}
                />
                {errors.password_confirmation && (
                  <p className="text-sm text-destructive">{errors.password_confirmation.message}</p>
                )}
              </div>
            </div>
          </CardContent>
          <CardFooter className="flex flex-col space-y-4">
            <Button
              type="submit"
              className="w-full"
              disabled={isLoading}
            >
              {isLoading && <Loader2 className="mr-2 h-4 w-4 animate-spin" />}
              Create Account
            </Button>
            
            <div className="text-center space-y-2">
              <p className="text-sm text-muted-foreground">
                Already have an account?{' '}
                <Link href="/login" className="text-primary hover:underline font-medium">
                  Sign in
                </Link>
              </p>
              
              <p className="text-xs text-muted-foreground italic">
                VendorConnect is <span className="font-semibold text-primary">Complimentary</span> for The Mastermind Members
              </p>
            </div>
          </CardFooter>
        </form>
      </Card>
    </div>
  );
}

