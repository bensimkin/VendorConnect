'use client';

import * as React from 'react';
import { X, ChevronDown } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';

interface MultiSelectProps {
  options: Array<{ id: number; name: string; company?: string }>;
  selected: number[];
  onSelectionChange: (selected: number[]) => void;
  placeholder?: string;
  disabled?: boolean;
  maxSelections?: number;
}

export function MultiSelect({
  options,
  selected,
  onSelectionChange,
  placeholder = "Select options...",
  disabled = false,
  maxSelections,
}: MultiSelectProps) {
  const [open, setOpen] = React.useState(false);
  const [inputValue, setInputValue] = React.useState('');

  const handleUnselect = (id: number) => {
    onSelectionChange(selected.filter((item) => item !== id));
  };

  const handleSelect = (id: number) => {
    if (maxSelections && selected.length >= maxSelections) {
      return;
    }
    if (!selected.includes(id)) {
      onSelectionChange([...selected, id]);
    }
    setOpen(false);
  };

  const filteredOptions = options.filter((option) =>
    option.name.toLowerCase().includes(inputValue.toLowerCase()) ||
    (option.company && option.company.toLowerCase().includes(inputValue.toLowerCase()))
  );

  const selectedOptions = selected.map(id => options.find(opt => opt.id === id)).filter(Boolean);

  return (
    <div className="relative">
      <div className="group border border-input px-3 py-2 text-sm ring-offset-background rounded-md focus-within:ring-2 focus-within:ring-ring focus-within:ring-offset-2">
        <div className="flex gap-1 flex-wrap">
          {selectedOptions.map((option) => (
            <Badge key={option!.id} variant="secondary" className="rounded-sm px-1 font-normal">
              {option!.name} {option!.company && `(${option!.company})`}
              <button
                className="ml-1 ring-offset-background rounded-full outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
                onKeyDown={(e) => {
                  if (e.key === "Enter") {
                    handleUnselect(option!.id);
                  }
                }}
                onMouseDown={(e) => {
                  e.preventDefault();
                  e.stopPropagation();
                }}
                onClick={() => handleUnselect(option!.id)}
              >
                <X className="h-3 w-3 text-muted-foreground hover:text-foreground" />
              </button>
            </Badge>
          ))}
          <input
            placeholder={selected.length === 0 ? placeholder : undefined}
            value={inputValue}
            onChange={(e) => setInputValue(e.target.value)}
            onFocus={() => setOpen(true)}
            disabled={disabled}
            className="ml-2 bg-transparent outline-none placeholder:text-muted-foreground flex-1 min-w-0"
          />
        </div>
        <Button
          type="button"
          variant="ghost"
          size="sm"
          className="absolute right-0 top-0 h-full px-2"
          onClick={() => setOpen(!open)}
        >
          <ChevronDown className="h-4 w-4" />
        </Button>
      </div>
      
      {open && (
        <div className="absolute w-full z-10 top-full mt-1 rounded-md border bg-popover text-popover-foreground shadow-md outline-none">
          <div className="max-h-60 overflow-auto p-1">
            {filteredOptions.length === 0 ? (
              <div className="px-2 py-1.5 text-sm text-muted-foreground">
                No options found
              </div>
            ) : (
              filteredOptions.map((option) => {
                const isSelected = selected.includes(option.id);
                return (
                  <button
                    key={option.id}
                    onClick={() => handleSelect(option.id)}
                    className={`w-full text-left px-2 py-1.5 text-sm rounded-sm hover:bg-accent hover:text-accent-foreground ${
                      isSelected ? 'bg-accent text-accent-foreground' : ''
                    }`}
                  >
                    <div className="flex items-center justify-between">
                      <span>
                        {option.name} {option.company && `(${option.company})`}
                      </span>
                      {isSelected && (
                        <div className="flex items-center justify-center w-4 h-4 rounded-sm border border-primary bg-primary text-primary-foreground">
                          <div className="w-2 h-2 bg-primary-foreground rounded-sm" />
                        </div>
                      )}
                    </div>
                  </button>
                );
              })
            )}
          </div>
        </div>
      )}
    </div>
  );
}
