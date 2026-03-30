export default function Loading() {
  return (
    <div className="max-w-2xl mx-auto px-4 py-12 animate-pulse">
      <div className="h-8 bg-gray-100 rounded w-48 mx-auto mb-8" />
      <div className="space-y-6">
        <div><div className="h-4 bg-gray-100 rounded w-24 mb-2" /><div className="h-12 bg-gray-100 rounded-xl" /></div>
        <div><div className="h-4 bg-gray-100 rounded w-24 mb-2" /><div className="h-12 bg-gray-100 rounded-xl" /></div>
        <div><div className="h-4 bg-gray-100 rounded w-24 mb-2" /><div className="h-12 bg-gray-100 rounded-xl" /></div>
        <div><div className="h-4 bg-gray-100 rounded w-24 mb-2" /><div className="h-32 bg-gray-100 rounded-xl" /></div>
      </div>
    </div>
  );
}
