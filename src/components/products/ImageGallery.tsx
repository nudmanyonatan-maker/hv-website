'use client';

import { useState, useEffect, useCallback } from 'react';
import Image from 'next/image';
import type { ProductImage } from '@/types';

interface ImageGalleryProps {
  images: ProductImage[];
  alt: string;
}

export default function ImageGallery({ images, alt }: ImageGalleryProps) {
  const [selectedIndex, setSelectedIndex] = useState(0);
  const [lightboxOpen, setLightboxOpen] = useState(false);

  const mainSrc = images[selectedIndex]?.pic ?? images[0]?.pic ?? '';

  const openLightbox = () => setLightboxOpen(true);
  const closeLightbox = () => setLightboxOpen(false);

  const goNext = useCallback(() => {
    if (images.length > 1) {
      setSelectedIndex((prev) => (prev + 1) % images.length);
    }
  }, [images.length]);

  const goPrev = useCallback(() => {
    if (images.length > 1) {
      setSelectedIndex((prev) => (prev - 1 + images.length) % images.length);
    }
  }, [images.length]);

  useEffect(() => {
    if (!lightboxOpen) return;

    function handleKeyDown(e: KeyboardEvent) {
      if (e.key === 'Escape') closeLightbox();
      if (e.key === 'ArrowRight') goNext();
      if (e.key === 'ArrowLeft') goPrev();
    }

    window.addEventListener('keydown', handleKeyDown);
    return () => window.removeEventListener('keydown', handleKeyDown);
  }, [lightboxOpen, goNext, goPrev]);

  return (
    <div>
      {/* Main image */}
      <div
        className="relative aspect-square bg-white rounded-xl overflow-hidden cursor-zoom-in"
        onClick={openLightbox}
      >
        <Image
          src={mainSrc}
          alt={alt}
          fill
          className="object-contain p-4"
          sizes="(max-width: 768px) 100vw, 50vw"
          priority
        />
      </div>

      {/* Thumbnails */}
      {images.length > 1 && (
        <div className="flex gap-2 mt-3 overflow-x-auto">
          {images.map((img, i) => (
            <button
              key={img.img_id}
              onClick={() => setSelectedIndex(i)}
              className={`relative w-20 h-20 flex-shrink-0 rounded-lg overflow-hidden border-2 transition ${
                i === selectedIndex ? 'border-red-700' : 'border-gray-100 hover:border-gray-300'
              }`}
            >
              <Image src={img.pic} alt="" fill className="object-cover" sizes="80px" />
            </button>
          ))}
        </div>
      )}

      {/* Lightbox */}
      {lightboxOpen && (
        <div
          className="fixed inset-0 z-50 bg-black/90 flex items-center justify-center"
          onClick={closeLightbox}
        >
          {/* Close button */}
          <button
            onClick={closeLightbox}
            className="absolute top-4 right-4 text-white hover:text-gray-300 transition z-10"
            aria-label="Close"
          >
            <svg xmlns="http://www.w3.org/2000/svg" className="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
              <path strokeLinecap="round" strokeLinejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>

          {/* Previous arrow */}
          {images.length > 1 && (
            <button
              onClick={(e) => { e.stopPropagation(); goPrev(); }}
              className="absolute left-4 top-1/2 -translate-y-1/2 text-white hover:text-gray-300 transition z-10"
              aria-label="Previous image"
            >
              <svg xmlns="http://www.w3.org/2000/svg" className="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                <path strokeLinecap="round" strokeLinejoin="round" d="M15 19l-7-7 7-7" />
              </svg>
            </button>
          )}

          {/* Lightbox image */}
          <div
            className="relative w-[90vw] h-[90vh] max-w-5xl"
            onClick={(e) => e.stopPropagation()}
          >
            <Image
              src={images[selectedIndex]?.pic ?? mainSrc}
              alt={alt}
              fill
              className="object-contain"
              sizes="90vw"
              priority
            />
          </div>

          {/* Next arrow */}
          {images.length > 1 && (
            <button
              onClick={(e) => { e.stopPropagation(); goNext(); }}
              className="absolute right-4 top-1/2 -translate-y-1/2 text-white hover:text-gray-300 transition z-10"
              aria-label="Next image"
            >
              <svg xmlns="http://www.w3.org/2000/svg" className="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                <path strokeLinecap="round" strokeLinejoin="round" d="M9 5l7 7-7 7" />
              </svg>
            </button>
          )}
        </div>
      )}
    </div>
  );
}
