import Link from 'next/link';

export default function NotFound() {
  return (
    <div className="min-h-[70vh] flex items-center justify-center px-4">
      <div className="text-center">
        <h1 className="text-7xl font-bold text-red-700 mb-4">404</h1>
        <p className="text-xl text-gray-600 mb-8">Page not found</p>
        <Link
          href="/en"
          className="inline-block bg-red-700 text-white px-6 py-3 rounded-xl font-medium hover:bg-red-800 transition"
        >
          Return to Home
        </Link>
      </div>
    </div>
  );
}
