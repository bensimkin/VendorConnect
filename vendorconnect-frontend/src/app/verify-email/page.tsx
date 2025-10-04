'use client';

export const dynamic = 'force-dynamic';
import { useState, useEffect } from 'react';
import { useRouter, useSearchParams } from 'next/navigation';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import * as z from 'zod';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { toast } from 'react-hot-toast';
import { Loader2, ArrowLeft, CheckCircle, Mail, RefreshCw } from 'lucide-react';
import Link from 'next/link';
import { useAuthStore } from '@/lib/auth-store';

const resendVerificationSchema = z.object({
  email: z.string().email('Invalid email address'),
});

type ResendVerificationFormData = z.infer<typeof resendVerificationSchema>;

export default function VerifyEmailPage() {
  const router = useRouter();
  const searchParams = useSearchParams();
  const { verifyEmail, resendVerification, isLoading } = useAuthStore();
  const [isVerified, setIsVerified] = useState(false);
  const [isEmailSent, setIsEmailSent] = useState(false);
  const [verificationStatus, setVerificationStatus] = useState<'pending' | 'success' | 'error'>('pending');

  const {
    register,
    handleSubmit,
    formState: { errors },
    getValues,
  } = useForm<ResendVerificationFormData>({
    resolver: zodResolver(resendVerificationSchema),
  });

  useEffect(() => {
    const id = searchParams.get('id');
    const hash = searchParams.get('hash');
    
    if (id && hash) {
      handleVerifyEmail(id, hash);
    }
  }, [searchParams]);

  const handleVerifyEmail = async (id: string, hash: string) => {
    try {
      await verifyEmail(id, hash);
      setIsVerified(true);
      setVerificationStatus('success');
    } catch (error: any) {
      console.error('Email verification error:', error);
      setVerificationStatus('error');
    }
  };

  const handleResendVerification = async (data: ResendVerificationFormData) => {
    try {
      await resendVerification(data.email);
      setIsEmailSent(true);
    } catch (error: any) {
      console.error('Resend verification error:', error);
    }
  };

  if (isVerified) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-primary/20 via-background to-secondary/20">
        <div className="absolute inset-0 bg-grid-white/10 bg-grid-8 [mask-image:radial-gradient(ellipse_at_center,transparent_20%,black)]" />
        <Card className="w-full max-w-md relative">
          <CardHeader className="space-y-1 text-center">
            <div className="mx-auto w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mb-4">
              <CheckCircle className="w-6 h-6 text-green-600" />
            </div>
            <CardTitle className="text-2xl font-bold">Email Verified</CardTitle>
            <CardDescription>
              Your email has been successfully verified. You can now access all features.
            </CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="text-sm text-muted-foreground text-center">
              <p>You will be redirected to the login page in a few seconds.</p>
            </div>
          </CardContent>
          <CardFooter className="flex flex-col space-y-4">
            <Button
              onClick={() => router.push('/login')}
              className="w-full"
            >
              Go to Login
            </Button>
          </CardFooter>
        </Card>
      </div>
    );
  }

  if (isEmailSent) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-primary/20 via-background to-secondary/20">
        <div className="absolute inset-0 bg-grid-white/10 bg-grid-8 [mask-image:radial-gradient(ellipse_at_center,transparent_20%,black)]" />
        <Card className="w-full max-w-md relative">
          <CardHeader className="space-y-1 text-center">
            <div className="mx-auto w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mb-4">
              <Mail className="w-6 h-6 text-blue-600" />
            </div>
            <CardTitle className="text-2xl font-bold">Check Your Email</CardTitle>
            <CardDescription>
              We've sent a verification link to <strong>{getValues('email')}</strong>
            </CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="text-sm text-muted-foreground text-center">
              <p>Click the link in the email to verify your account.</p>
              <p className="mt-2">Didn't receive the email? Check your spam folder or try again.</p>
            </div>
          </CardContent>
          <CardFooter className="flex flex-col space-y-4">
            <Button
              variant="outline"
              onClick={() => setIsEmailSent(false)}
              className="w-full"
            >
              Try Different Email
            </Button>
            <Button
              variant="ghost"
              onClick={() => router.push('/login')}
              className="w-full"
            >
              <ArrowLeft className="mr-2 h-4 w-4" />
              Back to Login
            </Button>
          </CardFooter>
        </Card>
      </div>
    );
  }

  if (verificationStatus === 'error') {
    return (
      <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-primary/20 via-background to-secondary/20">
        <div className="absolute inset-0 bg-grid-white/10 bg-grid-8 [mask-image:radial-gradient(ellipse_at_center,transparent_20%,black)]" />
        <Card className="w-full max-w-md relative">
          <CardHeader className="space-y-1 text-center">
            <CardTitle className="text-2xl font-bold">Verification Failed</CardTitle>
            <CardDescription>
              The verification link is invalid or has expired.
            </CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="text-sm text-muted-foreground text-center">
              <p>Please request a new verification email.</p>
            </div>
          </CardContent>
          <CardFooter className="flex flex-col space-y-4">
            <Button
              onClick={() => setVerificationStatus('pending')}
              className="w-full"
            >
              Request New Verification Email
            </Button>
            <Button
              variant="ghost"
              onClick={() => router.push('/login')}
              className="w-full"
            >
              <ArrowLeft className="mr-2 h-4 w-4" />
              Back to Login
            </Button>
          </CardFooter>
        </Card>
      </div>
    );
  }

  return (
    <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-primary/20 via-background to-secondary/20">
      <div className="absolute inset-0 bg-grid-white/10 bg-grid-8 [mask-image:radial-gradient(ellipse_at_center,transparent_20%,black)]" />
      <Card className="w-full max-w-md relative">
        <CardHeader className="space-y-1">
          <CardTitle className="text-3xl font-bold text-center">Verify Your Email</CardTitle>
          <CardDescription className="text-center">
            Enter your email address to receive a verification link
          </CardDescription>
        </CardHeader>
        <form onSubmit={handleSubmit(handleResendVerification)}>
          <CardContent className="space-y-4">
            <div className="space-y-2">
              <Label htmlFor="email">Email Address</Label>
              <Input
                id="email"
                type="email"
                placeholder="Enter your email address"
                {...register('email')}
                disabled={isLoading}
              />
              {errors.email && (
                <p className="text-sm text-destructive">{errors.email.message}</p>
              )}
            </div>
          </CardContent>
          <CardFooter className="flex flex-col space-y-4">
            <Button
              type="submit"
              className="w-full"
              disabled={isLoading}
            >
              {isLoading && <Loader2 className="mr-2 h-4 w-4 animate-spin" />}
              <RefreshCw className="mr-2 h-4 w-4" />
              Send Verification Email
            </Button>
            <Button
              variant="ghost"
              onClick={() => router.push('/login')}
              className="w-full"
            >
              <ArrowLeft className="mr-2 h-4 w-4" />
              Back to Login
            </Button>
          </CardFooter>
        </form>
      </Card>
    </div>
  );
}
