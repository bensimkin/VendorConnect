'use client';

import { useState, useEffect, useRef } from 'react';
import { useRouter } from 'next/navigation';
import { Search, Building2, FolderOpen, CheckSquare, Briefcase, X } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import apiClient from '@/lib/api-client';
import { cn } from '@/lib/utils';

interface SearchResult {
  id: number;
  type: 'client' | 'project' | 'task' | 'portfolio';
  title: string;
  subtitle: string;
  url: string;
  icon: string;
  description: string;
  priority?: string;
  project?: string;
  client?: string;
  task?: string;
}

interface SearchResults {
  clients: SearchResult[];
  projects: SearchResult[];
  tasks: SearchResult[];
  portfolio: SearchResult[];
  total_results: number;
}

const iconMap = {
  Building2: Building2,
  FolderOpen: FolderOpen,
  CheckSquare: CheckSquare,
  Briefcase: Briefcase,
};

export default function GlobalSearch() {
  const router = useRouter();
  const [query, setQuery] = useState('');
  const [results, setResults] = useState<SearchResults | null>(null);
  const [isLoading, setIsLoading] = useState(false);
  const [showResults, setShowResults] = useState(false);
  const searchRef = useRef<HTMLDivElement>(null);

  // Debounced search
  useEffect(() => {
    const timeoutId = setTimeout(() => {
      if (query.length >= 2) {
        performSearch();
      } else {
        setResults(null);
        setShowResults(false);
      }
    }, 300);

    return () => clearTimeout(timeoutId);
  }, [query]);

  // Close results when clicking outside
  useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      if (searchRef.current && !searchRef.current.contains(event.target as Node)) {
        setShowResults(false);
      }
    };

    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);

  const performSearch = async () => {
    if (query.length < 2) return;

    setIsLoading(true);
    try {
      const response = await apiClient.get(`/search?q=${encodeURIComponent(query)}`);
      setResults(response.data);
      setShowResults(true);
    } catch (error) {
      console.error('Search error:', error);
      setResults(null);
    } finally {
      setIsLoading(false);
    }
  };

  const handleResultClick = (result: SearchResult) => {
    router.push(result.url);
    setShowResults(false);
    setQuery('');
  };

  const handleKeyDown = (e: React.KeyboardEvent) => {
    if (e.key === 'Escape') {
      setShowResults(false);
      setQuery('');
    }
  };

  const clearSearch = () => {
    setQuery('');
    setResults(null);
    setShowResults(false);
  };

  const getIconComponent = (iconName: string) => {
    const IconComponent = iconMap[iconName as keyof typeof iconMap];
    return IconComponent ? <IconComponent className="h-4 w-4" /> : <Search className="h-4 w-4" />;
  };

  const renderResultItem = (result: SearchResult) => {
    const IconComponent = getIconComponent(result.icon);
    
    return (
      <div
        key={`${result.type}-${result.id}`}
        className="flex items-center gap-3 p-3 hover:bg-accent cursor-pointer rounded-lg transition-colors"
        onClick={() => handleResultClick(result)}
      >
        <div className="flex-shrink-0 text-muted-foreground">
          {IconComponent}
        </div>
        <div className="flex-1 min-w-0">
          <div className="flex items-center gap-2 mb-1">
            <h4 className="text-sm font-medium truncate">{result.title}</h4>
            <Badge variant="secondary" className="text-xs">
              {result.type}
            </Badge>
          </div>
          <p className="text-xs text-muted-foreground mb-1">{result.subtitle}</p>
          {result.description && (
            <p className="text-xs text-muted-foreground truncate">{result.description}</p>
          )}
          {(result.priority || result.project || result.client || result.task) && (
            <div className="flex items-center gap-2 mt-1">
              {result.priority && (
                <Badge variant="outline" className="text-xs">
                  {result.priority}
                </Badge>
              )}
              {result.project && (
                <span className="text-xs text-blue-600">Project: {result.project}</span>
              )}
              {result.client && (
                <span className="text-xs text-green-600">Client: {result.client}</span>
              )}
              {result.task && (
                <span className="text-xs text-purple-600">Task: {result.task}</span>
              )}
            </div>
          )}
        </div>
      </div>
    );
  };

  const renderSection = (title: string, results: SearchResult[], icon: string) => {
    if (results.length === 0) return null;

    return (
      <div key={title} className="mb-4">
        <div className="flex items-center gap-2 mb-2 px-3">
          {getIconComponent(icon)}
          <h3 className="text-sm font-medium text-muted-foreground">{title}</h3>
          <Badge variant="secondary" className="text-xs">
            {results.length}
          </Badge>
        </div>
        <div className="space-y-1">
          {results.map(renderResultItem)}
        </div>
      </div>
    );
  };

  return (
    <div ref={searchRef} className="relative flex-1 max-w-md">
      <div className="relative">
        <Search className="pointer-events-none absolute inset-y-0 left-0 h-full w-5 text-muted-foreground ml-3" />
        <Input
          type="search"
          placeholder="Search clients, projects, tasks, portfolio..."
          value={query}
          onChange={(e) => setQuery(e.target.value)}
          onKeyDown={handleKeyDown}
          onFocus={() => {
            if (results && results.total_results > 0) {
              setShowResults(true);
            }
          }}
          className="pl-10 pr-10"
        />
        {query && (
          <Button
            variant="ghost"
            size="icon"
            className="absolute inset-y-0 right-0 h-full w-10"
            onClick={clearSearch}
          >
            <X className="h-4 w-4" />
          </Button>
        )}
      </div>

      {/* Search Results Dropdown */}
      {showResults && (
        <div className="absolute top-full left-0 right-0 mt-2 bg-card border rounded-lg shadow-lg z-50 max-h-96 overflow-y-auto">
          {isLoading ? (
            <div className="p-4 text-center text-muted-foreground">
              <div className="inline-block animate-spin rounded-full h-4 w-4 border-b-2 border-primary mr-2"></div>
              Searching...
            </div>
          ) : results && results.total_results > 0 ? (
            <div className="p-3">
              <div className="mb-3 pb-2 border-b">
                <p className="text-sm text-muted-foreground">
                  Found {results.total_results} result{results.total_results !== 1 ? 's' : ''}
                </p>
              </div>
              
              {renderSection('Clients', results.clients, 'Building2')}
              {renderSection('Projects', results.projects, 'FolderOpen')}
              {renderSection('Tasks', results.tasks, 'CheckSquare')}
              {renderSection('Portfolio', results.portfolio, 'Briefcase')}
            </div>
          ) : query.length >= 2 ? (
            <div className="p-4 text-center text-muted-foreground">
              No results found for "{query}"
            </div>
          ) : null}
        </div>
      )}
    </div>
  );
}
