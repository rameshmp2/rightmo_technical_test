# Product Management Frontend

Next.js frontend application for the Product Management System.

## Features

- Server-Side Rendering (SSR) with Next.js 15 App Router
- TypeScript for type safety
- Tailwind CSS for styling
- Authentication with protected routes
- Product listing with search, filter, sort, and pagination
- Product detail pages with dynamic routing
- Responsive design for mobile, tablet, and desktop

## Tech Stack

- Next.js 15.1.3
- React 19.0.0
- TypeScript 5
- Tailwind CSS 3.4.17
- Axios 1.7.0

## Setup

1. **Install dependencies:**
   ```bash
   npm install
   ```

2. **Configure environment:**
   Create `.env.local` file:
   ```env
   NEXT_PUBLIC_API_URL=http://localhost:8000/api
   ```

3. **Run development server:**
   ```bash
   npm run dev
   ```

4. **Build for production:**
   ```bash
   npm run build
   npm start
   ```

## Pages

- `/` - Home (redirects to dashboard or login)
- `/login` - Login page
- `/dashboard` - Product dashboard with filters
- `/products/[id]` - Product detail page

## Authentication

The application uses token-based authentication with Laravel Sanctum. Tokens are stored in localStorage and automatically included in API requests via Axios interceptors.

## API Integration

All API calls are handled through the `lib/api.ts` module, which provides:
- Automatic token injection
- Error handling
- Response interceptors
- TypeScript types

## Styling

The project uses Tailwind CSS with a mobile-first approach. All components are fully responsive.
