import { NextResponse } from 'next/server'
import type { NextRequest } from 'next/server'

export function middleware(request: NextRequest) {
  const token = request.cookies.get('auth_token')?.value

  // List of paths that don't require authentication
  const publicPaths = ['/login', '/forgot-password', '/reset-password']
  const isPublicPath = publicPaths.some(path => request.nextUrl.pathname.startsWith(path))

  // Debug logging
  console.log('🔍 [MIDDLEWARE] Path:', request.nextUrl.pathname)
  console.log('🔍 [MIDDLEWARE] Token from cookie:', token ? 'Present' : 'Missing')
  console.log('🔍 [MIDDLEWARE] Is public path:', isPublicPath)

  if (!token && !isPublicPath) {
    console.log('🔍 [MIDDLEWARE] Redirecting to login - no token found')
    return NextResponse.redirect(new URL('/login', request.url))
  }

  if (token && request.nextUrl.pathname === '/login') {
    console.log('🔍 [MIDDLEWARE] Redirecting to dashboard - user already logged in')
    return NextResponse.redirect(new URL('/dashboard', request.url))
  }

  console.log('🔍 [MIDDLEWARE] Proceeding with request')
  return NextResponse.next()
}

export const config = {
  matcher: [
    /*
     * Match all request paths except for the ones starting with:
     * - api (API routes)
     * - _next/static (static files)
     * - _next/image (image optimization files)
     * - favicon.ico (favicon file)
     */
    '/((?!api|_next/static|_next/image|favicon.ico).*)',
  ],
}
