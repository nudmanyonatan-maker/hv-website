export default function Loading() {
  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      {/* Back link placeholder */}
      <div className="h-4 bg-gray-100 rounded w-20 mb-6 animate-pulse" />

      <div className="grid grid-cols-1 md:grid-cols-2 gap-10">
        {/* Left column: image + thumbnails */}
        <div>
          <div className="aspect-square bg-gray-100 rounded-xl animate-pulse" />
          <div className="flex gap-2 mt-3">
            {Array.from({ length: 4 }).map((_, i) => (
              <div key={i} className="w-16 h-16 bg-gray-100 rounded-lg animate-pulse" />
            ))}
          </div>
        </div>

        {/* Right column: product details */}
        <div className="space-y-4">
          {/* Title */}
          <div className="h-8 bg-gray-100 rounded w-3/4 animate-pulse" />
          {/* SKU */}
          <div className="h-4 bg-gray-100 rounded w-1/3 animate-pulse" />
          {/* Price box */}
          <div className="h-24 bg-gray-100 rounded-xl animate-pulse" />
          {/* Stock */}
          <div className="h-4 bg-gray-100 rounded w-1/4 animate-pulse" />
          {/* Description block */}
          <div className="space-y-2">
            <div className="h-4 bg-gray-100 rounded w-full animate-pulse" />
            <div className="h-4 bg-gray-100 rounded w-5/6 animate-pulse" />
            <div className="h-4 bg-gray-100 rounded w-4/6 animate-pulse" />
          </div>
        </div>
      </div>
    </div>
  );
}
