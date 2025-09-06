'use client';

import { useState } from 'react';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Calendar, Clock, BarChart3 } from 'lucide-react';
import { cn } from '@/lib/utils';

export type TimeRange = 'all' | '30_days' | '7_days';

interface DateRangeSelectorProps {
  value: TimeRange;
  onChange: (value: TimeRange) => void;
  className?: string;
}

const timeRangeOptions = [
  {
    value: 'all' as TimeRange,
    label: 'All Time',
    description: 'View all data',
    icon: BarChart3,
    color: 'text-gray-600',
    bgColor: 'bg-gray-100',
  },
  {
    value: '30_days' as TimeRange,
    label: 'Last 30 Days',
    description: 'Recent month data',
    icon: Calendar,
    color: 'text-blue-600',
    bgColor: 'bg-blue-100',
  },
  {
    value: '7_days' as TimeRange,
    label: 'Last 7 Days',
    description: 'Recent week data',
    icon: Clock,
    color: 'text-green-600',
    bgColor: 'bg-green-100',
  },
];

export default function DateRangeSelector({ value, onChange, className }: DateRangeSelectorProps) {
  return (
    <div className={cn('flex gap-2', className)}>
      {timeRangeOptions.map((option) => {
        const Icon = option.icon;
        const isSelected = value === option.value;
        
        return (
          <Button
            key={option.value}
            variant={isSelected ? 'default' : 'outline'}
            onClick={() => onChange(option.value)}
            className={cn(
              'flex items-center gap-2 px-4 py-2 h-auto',
              isSelected 
                ? 'bg-primary text-primary-foreground' 
                : 'hover:bg-gray-50'
            )}
          >
            <Icon className={cn('h-4 w-4', isSelected ? 'text-primary-foreground' : option.color)} />
            <div className="text-left">
              <div className="font-medium text-sm">{option.label}</div>
              <div className={cn('text-xs', isSelected ? 'text-primary-foreground/80' : 'text-muted-foreground')}>
                {option.description}
              </div>
            </div>
          </Button>
        );
      })}
    </div>
  );
}

// Alternative compact version for smaller spaces
export function CompactDateRangeSelector({ value, onChange, className }: DateRangeSelectorProps) {
  return (
    <div className={cn('flex gap-1', className)}>
      {timeRangeOptions.map((option) => {
        const Icon = option.icon;
        const isSelected = value === option.value;
        
        return (
          <Button
            key={option.value}
            variant={isSelected ? 'default' : 'outline'}
            size="sm"
            onClick={() => onChange(option.value)}
            className={cn(
              'flex items-center gap-1 px-3 py-1',
              isSelected 
                ? 'bg-primary text-primary-foreground' 
                : 'hover:bg-gray-50'
            )}
          >
            <Icon className={cn('h-3 w-3', isSelected ? 'text-primary-foreground' : option.color)} />
            <span className="text-xs font-medium">{option.label}</span>
          </Button>
        );
      })}
    </div>
  );
}
