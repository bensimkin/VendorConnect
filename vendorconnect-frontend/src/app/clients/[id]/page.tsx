'use client';

import React from 'react';
import { useParams } from 'next/navigation';
import MainLayout from '@/components/layout/main-layout';

export default function ClientDetailPage() {
  const params = useParams();
  const clientId = params.id as string;

  return (
    <MainLayout>
      <div className="container mx-auto px-4 py-8">
        <h1>Client {clientId}</h1>
        <p>Debugging client page...</p>
      </div>
    </MainLayout>
  );
}
