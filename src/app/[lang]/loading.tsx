export default function Loading() {
  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      {/* Hero skeleton */}
      <div className="bg-gray-100 rounded-2xl px-6 sm:px-10 py-10 sm:py-14 mb-10 animate-pulse">
        <div className="h-8 bg-gray-200 rounded-lg w-80 mx-auto mb-3" />
        <div className="h-5 bg-gray-200 rounded-lg w-64 mx-auto" />
      </div>

      {/* Search bar skeleton */}
      <div className="mb-6">
        <div className="h-12 bg-gray-100 rounded-xl w-full max-w-md mx-auto animate-pulse" />
      </div>

      {/* Category bar skeleton */}
      <div className="flex justify-center gap-2 mb-8 overflow-hidden">
        {Array.from({ length: 6 }).map((_, i) => (
          <div
            key={i}
            className="h-9 bg-gray-100 rounded-full animate-pulse shrink-0"
            style={{ width: `${70 + i * 12}px` }}
          />
        ))}
      </div>

      {/* Product grid skeleton */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        {Array.from({ length: 8 }).map((_, i) => (
          <div key={i} className="bg-white rounded-xl border border-gray-100 overflow-hidden animate-pulse">
            <div className="aspect-square bg-gray-100" />
            <div className="p-4 space-y-3">
              <div className="h-4 bg-gray-100 rounded w-3/4" />
              <div className="h-3 bg-gray-100 rounded w-1/2" />
              <div className="h-3 bg-gray-100 rounded w-full" />
              <div className="h-5 bg-gray-100 rounded w-1/3 mt-2" />
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}
