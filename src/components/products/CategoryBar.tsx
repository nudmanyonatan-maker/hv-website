'use client';

import type { Category, Dictionary } from '@/types';
import { cn } from '@/lib/utils';

interface CategoryBarProps {
  categories: Category[];
  activeId: string | null;
  onSelect: (id: string | null) => void;
  dict: Dictionary;
  counts?: Map<string, number>;
}

export default function CategoryBar({ categories, activeId, onSelect, dict, counts }: CategoryBarProps) {
  return (
    <div className="flex gap-2 overflow-x-auto pb-2 scrollbar-hide">
      <button
        onClick={() => onSelect(null)}
        className={cn(
          'whitespace-nowrap px-4 py-2 rounded-full text-sm font-medium transition',
          activeId === null
            ? 'bg-red-700 text-white'
            : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
        )}
      >
        {dict.home.all_categories}
      </button>
      {categories.map((cat) => (
        <button
          key={cat.category_id}
          onClick={() => onSelect(String(cat.category_id))}
          className={cn(
            'whitespace-nowrap px-4 py-2 rounded-full text-sm font-medium transition',
            activeId === String(cat.category_id)
              ? 'bg-red-700 text-white'
              : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
          )}
        >
          {cat.category_name} ({counts?.get(String(cat.category_id)) ?? 0})
        </button>
      ))}
    </div>
  );
}
